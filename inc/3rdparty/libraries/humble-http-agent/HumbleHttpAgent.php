<?php
/**
 * Humble HTTP Agent
 * 
 * This class is designed to take advantage of parallel HTTP requests
 * offered by PHP's PECL HTTP extension or the curl_multi_* functions. 
 * For environments which do not have these options, it reverts to standard sequential 
 * requests (using file_get_contents())
 * 
 * @version 1.4
 * @date 2013-05-10
 * @see http://php.net/HttpRequestPool
 * @author Keyvan Minoukadeh
 * @copyright 2011-2013 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */

class HumbleHttpAgent
{
	const METHOD_REQUEST_POOL = 1;
	const METHOD_CURL_MULTI = 2;
	const METHOD_FILE_GET_CONTENTS = 4;
	//const UA_BROWSER = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';
	const UA_BROWSER = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2';
	const UA_PHP = 'PHP/5.4';
	const REF_GOOGLE = 'http://www.google.co.uk/url?sa=t&source=web&cd=1';
	
	protected $requests = array();
	protected $redirectQueue = array();
	protected $requestOptions;
	protected $maxParallelRequests = 5;
	protected $cache = null; //TODO
	protected $httpContext;
	protected $minimiseMemoryUse = false; //TODO
	protected $method;
	protected $cookieJar;
	public $debug = false;
	public $debugVerbose = false;
	public $rewriteHashbangFragment = true; // see http://code.google.com/web/ajaxcrawling/docs/specification.html
	public $maxRedirects = 5;
	public $userAgentMap = array();
	public $rewriteUrls = array();
	public $userAgentDefault;
	public $referer;
	//public $userAgent = 'Mozilla/5.0';
	
	// Prevent certain file/mime types
	// HTTP responses which match these content types will
	// be returned without body.
	public $headerOnlyTypes = array();
	// URLs ending with one of these extensions will
	// prompt Humble HTTP Agent to send a HEAD request first
	// to see if returned content type matches $headerOnlyTypes.
	public $headerOnlyClues = array('pdf','mp3','zip','exe','gif','gzip','gz','jpeg','jpg','mpg','mpeg','png','ppt','mov');
	// AJAX triggers to search for.
	// for AJAX sites, e.g. Blogger with its dynamic views templates.
	public $ajaxTriggers = array("<meta name='fragment' content='!'",'<meta name="fragment" content="!"',"<meta content='!' name='fragment'",'<meta content="!" name="fragment"');
	
	//TODO: set max file size
	//TODO: normalise headers
	
	function __construct($requestOptions=null, $method=null) {
		$this->userAgentDefault = self::UA_BROWSER;
		$this->referer = self::REF_GOOGLE;
		// set the request method
		if (in_array($method, array(1,2,4))) {
			$this->method = $method;
		} else {
			if (class_exists('HttpRequestPool')) {
				$this->method = self::METHOD_REQUEST_POOL;
			} elseif (function_exists('curl_multi_init')) {
				$this->method = self::METHOD_CURL_MULTI;
			} else {
				$this->method = self::METHOD_FILE_GET_CONTENTS;
			}
		}
		if ($this->method == self::METHOD_CURL_MULTI) {
			require_once(dirname(__FILE__).'/RollingCurl.php');
		}
		// create cookie jar
		$this->cookieJar = new CookieJar();
		// set request options (redirect must be 0)
		$this->requestOptions = array(
			'timeout' => 15,
			'connecttimeout' => 15,
			'dns_cache_timeout' => 300,
			'redirect' => 0 // we handle redirects manually so we can rewrite the new hashbang URLs that are creeping up over the web
			// TODO: test onprogress?
		);
		if (is_array($requestOptions)) {
			$this->requestOptions = array_merge($this->requestOptions, $requestOptions);
		}
		$this->httpContext = array(
			'http' => array(
				'ignore_errors' => true,
				'timeout' => $this->requestOptions['timeout'],
				'max_redirects' => $this->requestOptions['redirect'],
				'header' => "Accept: */*\r\n"
				)
			);
	}
	
	protected function debug($msg) {
		if ($this->debug) {
			$mem = round(memory_get_usage()/1024, 2);
			$memPeak = round(memory_get_peak_usage()/1024, 2);
			echo '* ',$msg;
			if ($this->debugVerbose) echo ' - mem used: ',$mem," (peak: $memPeak)";
			echo "\n";
			ob_flush();
			flush();
		}
	}
	
	protected function getUserAgent($url, $asArray=false) {
		$host = @parse_url($url, PHP_URL_HOST);
		if (strtolower(substr($host, 0, 4)) == 'www.') {
			$host = substr($host, 4);
		}
		if ($host) {
			$try = array($host);
			$split = explode('.', $host);
			if (count($split) > 1) {
				array_shift($split);
				$try[] = '.'.implode('.', $split);
			}
			foreach ($try as $h) {
				if (isset($this->userAgentMap[$h])) {
					$ua = $this->userAgentMap[$h];
					break;
				}
			}
		}
		if (!isset($ua)) $ua = $this->userAgentDefault;
		if ($asArray) {
			return array('User-Agent' => $ua);
		} else {
			return 'User-Agent: '.$ua;
		}
	}
	
	public function rewriteHashbangFragment($url) {
		// return $url if there's no '#!'
		if (strpos($url, '#!') === false) return $url;
		// split $url and rewrite
		// TODO: is SimplePie_IRI included?
		$iri = new SimplePie_IRI($url);
		$fragment = substr($iri->fragment, 1); // strip '!'
		$iri->fragment = null;
		if (isset($iri->query)) {
			parse_str($iri->query, $query);
		} else {
			$query = array();
		}
		$query['_escaped_fragment_'] = (string)$fragment;
		$iri->query = str_replace('%2F', '/', http_build_query($query)); // needed for some sites
		return $iri->get_iri();
	}
	
	public function getRedirectURLfromHTML($url, $html) {
		$redirect_url = $this->getMetaRefreshURL($url, $html);
		if (!$redirect_url) {
			$redirect_url = $this->getUglyURL($url, $html);
		}
		return $redirect_url;
	}
	
	public function getMetaRefreshURL($url, $html) {
		if ($html == '') return false;
		// <meta HTTP-EQUIV="REFRESH" content="0; url=http://www.bernama.com/bernama/v6/newsindex.php?id=943513">
		if (!preg_match('!<meta http-equiv=["\']?refresh["\']? content=["\']?[0-9];\s*url=["\']?([^"\'>]+)["\']*>!i', $html, $match)) {
			return false;
		}
		$redirect_url = $match[1];
		if (preg_match('!^https?://!i', $redirect_url)) {
			// already absolute
			$this->debug('Meta refresh redirect found (http-equiv="refresh"), new URL: '.$redirect_url);
			return $redirect_url;
		}
		// absolutize redirect URL
		$base = new SimplePie_IRI($url);
		// remove '//' in URL path (causes URLs not to resolve properly)
		if (isset($base->path)) $base->path = preg_replace('!//+!', '/', $base->path);
		if ($absolute = SimplePie_IRI::absolutize($base, $redirect_url)) {
			$this->debug('Meta refresh redirect found (http-equiv="refresh"), new URL: '.$absolute);
			return $absolute;
		}
		return false;
	}	
	
	public function getUglyURL($url, $html) {
		if ($html == '') return false;
		$found = false;
		foreach ($this->ajaxTriggers as $string) {
			if (stripos($html, $string)) {
				$found = true;
				break;
			}
		}
		if (!$found) return false;
		$iri = new SimplePie_IRI($url);
		if (isset($iri->query)) {
			parse_str($iri->query, $query);
		} else {
			$query = array();
		}
		$query['_escaped_fragment_'] = '';
		$iri->query = str_replace('%2F', '/', http_build_query($query)); // needed for some sites
		$ugly_url = $iri->get_iri();
		$this->debug('AJAX trigger (meta name="fragment" content="!") found, new URL: '.$ugly_url);
		return $ugly_url;
	}
	
	public function removeFragment($url) {
		$pos = strpos($url, '#');
		if ($pos === false) {
			return $url;
		} else {
			return substr($url, 0, $pos);
		}
	}
	
	public function rewriteUrls($url) {
		foreach ($this->rewriteUrls as $find => $action) {
			if (strpos($url, $find) !== false) {
				if (is_array($action)) {
					return strtr($url, $action);
				}
			}
		}
		return $url;
	}
	
	public function enableDebug($bool=true) {
		$this->debug = (bool)$bool;
	}
	
	public function minimiseMemoryUse($bool = true) {
		$this->minimiseMemoryUse = $bool;
	}
	
	public function setMaxParallelRequests($max) {
		$this->maxParallelRequests = $max;
	}
	
	public function validateUrl($url) {
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$test = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
		// deal with bug http://bugs.php.net/51192 (present in PHP 5.2.13 and PHP 5.3.2)
		if ($test === false) {
			$test = filter_var(strtr($url, '-', '_'), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
		}
		if ($test !== false && $test !== null && preg_match('!^https?://!', $url)) {
			return $url;
		} else {
			return false;
		}
	}
	
	public function fetchAll(array $urls) {
		$this->fetchAllOnce($urls, $isRedirect=false);
		$redirects = 0;
		while (!empty($this->redirectQueue) && ++$redirects <= $this->maxRedirects) {
			$this->debug("Following redirects #$redirects...");
			$this->fetchAllOnce($this->redirectQueue, $isRedirect=true);
		}
	}
	
	// fetch all URLs without following redirects
	public function fetchAllOnce(array $urls, $isRedirect=false) {
		if (!$isRedirect) $urls = array_unique($urls);
		if (empty($urls)) return;
		
		//////////////////////////////////////////////////////
		// parallel (HttpRequestPool)
		if ($this->method == self::METHOD_REQUEST_POOL) {
			$this->debug('Starting parallel fetch (HttpRequestPool)');
			try {
				while (count($urls) > 0) {
					$this->debug('Processing set of '.min($this->maxParallelRequests, count($urls)));
					$subset = array_splice($urls, 0, $this->maxParallelRequests);
					$pool = new HttpRequestPool();
					foreach ($subset as $orig => $url) {
						if (!$isRedirect) $orig = $url;
						unset($this->redirectQueue[$orig]);
						$this->debug("...$url");
						if (!$isRedirect && isset($this->requests[$url])) {
							$this->debug("......in memory");
						/*
						} elseif ($this->isCached($url)) {
							$this->debug("......is cached");
							if (!$this->minimiseMemoryUse) {
								$this->requests[$url] = $this->getCached($url);
							}
						*/
						} else {
							$this->debug("......adding to pool");
							$req_url = $this->rewriteUrls($url);
							$req_url = ($this->rewriteHashbangFragment) ? $this->rewriteHashbangFragment($req_url) : $req_url;
							$req_url = $this->removeFragment($req_url);
							if (!empty($this->headerOnlyTypes) && !isset($this->requests[$orig]['wrongGuess']) && $this->possibleUnsupportedType($req_url)) {
								$_meth = HttpRequest::METH_HEAD;
							} else {
								$_meth = HttpRequest::METH_GET;
								unset($this->requests[$orig]['wrongGuess']);
							}
							$httpRequest = new HttpRequest($req_url, $_meth, $this->requestOptions);
							// send cookies, if we have any
							if ($cookies = $this->cookieJar->getMatchingCookies($req_url)) {
								$this->debug("......sending cookies: $cookies");
								$httpRequest->addHeaders(array('Cookie' => $cookies));
							}
							//$httpRequest->addHeaders(array('User-Agent' => $this->userAgent));
							$httpRequest->addHeaders($this->getUserAgent($req_url, true));
							// add referer for picky sites
							$httpRequest->addheaders(array('Referer' => $this->referer));
							$this->requests[$orig] = array('headers'=>null, 'body'=>null, 'httpRequest'=>$httpRequest);
							$this->requests[$orig]['original_url'] = $orig;
							$pool->attach($httpRequest);
						}
					}
					// did we get anything into the pool?
					if (count($pool) > 0) {
						$this->debug('Sending request...');
						try {
							$pool->send();
						} catch (HttpRequestPoolException $e) {
							// do nothing
						}
						$this->debug('Received responses');
						foreach($subset as $orig => $url) {
							if (!$isRedirect) $orig = $url;
							$request = $this->requests[$orig]['httpRequest'];
							//$this->requests[$orig]['headers'] = $this->headersToString($request->getResponseHeader());
							// getResponseHeader() doesn't return status line, so, for consistency...
							$this->requests[$orig]['headers'] = substr($request->getRawResponseMessage(), 0, $request->getResponseInfo('header_size'));
							// check content type
							// TODO: use getResponseHeader('content-type') or getResponseInfo()
							if ($this->headerOnlyType($this->requests[$orig]['headers'])) {
								$this->requests[$orig]['body'] = '';
								$_header_only_type = true;
								$this->debug('Header only type returned');
							} else {
								$this->requests[$orig]['body'] = $request->getResponseBody();
								$_header_only_type = false;
							}
							$this->requests[$orig]['effective_url'] = $request->getResponseInfo('effective_url');
							$this->requests[$orig]['status_code'] = $status_code = $request->getResponseCode();
							// is redirect?
							if ((in_array($status_code, array(300, 301, 302, 303, 307)) || $status_code > 307 && $status_code < 400) && $request->getResponseHeader('location')) {
								$redirectURL = $request->getResponseHeader('location');
								if (!preg_match('!^https?://!i', $redirectURL)) {
									$redirectURL = SimplePie_Misc::absolutize_url($redirectURL, $url);
								}
								if ($this->validateURL($redirectURL)) {
									$this->debug('Redirect detected. Valid URL: '.$redirectURL);
									// store any cookies
									$cookies = $request->getResponseHeader('set-cookie');
									if ($cookies && !is_array($cookies)) $cookies = array($cookies);
									if ($cookies) $this->cookieJar->storeCookies($url, $cookies);
									$this->redirectQueue[$orig] = $redirectURL;
								} else {
									$this->debug('Redirect detected. Invalid URL: '.$redirectURL);
								}
							} elseif (!$_header_only_type && $request->getMethod() === HttpRequest::METH_HEAD) {
								// the response content-type did not match our 'header only' types, 
								// but we'd issues a HEAD request because we assumed it would. So
								// let's queue a proper GET request for this item...
								$this->debug('Wrong guess at content-type, queing GET request');
								$this->requests[$orig]['wrongGuess'] = true;
								$this->redirectQueue[$orig] = $this->requests[$orig]['effective_url'];
							} elseif (strpos($this->requests[$orig]['effective_url'], '_escaped_fragment_') === false) {
								// check for <meta name='fragment' content='!'/>
								// for AJAX sites, e.g. Blogger with its dynamic views templates.
								// Based on Google's spec: https://developers.google.com/webmasters/ajax-crawling/docs/specification
								if (isset($this->requests[$orig]['body'])) {
									$redirectURL = $this->getRedirectURLfromHTML($this->requests[$orig]['effective_url'], substr($this->requests[$orig]['body'], 0, 4000));
									if ($redirectURL) {
										$this->redirectQueue[$orig] = $redirectURL;
									}
								}
							}
							//die($url.' -multi- '.$request->getResponseInfo('effective_url'));
							$pool->detach($request);
							unset($this->requests[$orig]['httpRequest'], $request);
							/*
							if ($this->minimiseMemoryUse) {
								if ($this->cache($url)) {
									unset($this->requests[$url]);
								}
							}
							*/
						}
					}
				}
			} catch (HttpException $e) {
				$this->debug($e);
				return false;
			}
		}
		
		//////////////////////////////////////////////////////////
		// parallel (curl_multi_*)
		elseif ($this->method == self::METHOD_CURL_MULTI) {
			$this->debug('Starting parallel fetch (curl_multi_*)');
			while (count($urls) > 0) {
				$this->debug('Processing set of '.min($this->maxParallelRequests, count($urls)));
				$subset = array_splice($urls, 0, $this->maxParallelRequests);
				$pool = new RollingCurl(array($this, 'handleCurlResponse'));
				$pool->window_size = count($subset);		
				
				foreach ($subset as $orig => $url) {
					if (!$isRedirect) $orig = $url;
					unset($this->redirectQueue[$orig]);
					$this->debug("...$url");
					if (!$isRedirect && isset($this->requests[$url])) {
						$this->debug("......in memory");
					/*
					} elseif ($this->isCached($url)) {
						$this->debug("......is cached");
						if (!$this->minimiseMemoryUse) {
							$this->requests[$url] = $this->getCached($url);
						}
					*/
					} else {
						$this->debug("......adding to pool");
						$req_url = $this->rewriteUrls($url);
						$req_url = ($this->rewriteHashbangFragment) ? $this->rewriteHashbangFragment($req_url) : $req_url;
						$req_url = $this->removeFragment($req_url);
						if (!empty($this->headerOnlyTypes) && !isset($this->requests[$orig]['wrongGuess']) && $this->possibleUnsupportedType($req_url)) {
							$_meth = 'HEAD';
						} else {
							$_meth = 'GET';
							unset($this->requests[$orig]['wrongGuess']);
						}						
						$headers = array();
						//$headers[] = 'User-Agent: '.$this->userAgent;
						$headers[] = $this->getUserAgent($req_url);
						// add referer for picky sites
						$headers[] = 'Referer: '.$this->referer;
						// send cookies, if we have any
						if ($cookies = $this->cookieJar->getMatchingCookies($req_url)) {
							$this->debug("......sending cookies: $cookies");
							$headers[] = 'Cookie: '.$cookies;
						}
						$httpRequest = new RollingCurlRequest($req_url, $_meth, null, $headers, array(
							CURLOPT_CONNECTTIMEOUT => $this->requestOptions['timeout'],
							CURLOPT_TIMEOUT => $this->requestOptions['timeout']
							));
						$httpRequest->set_original_url($orig);
						$this->requests[$orig] = array('headers'=>null, 'body'=>null, 'httpRequest'=>$httpRequest);
						$this->requests[$orig]['original_url'] = $orig; // TODO: is this needed anymore?
						$pool->add($httpRequest);
					}
				}
				// did we get anything into the pool?
				if (count($pool) > 0) {
					$this->debug('Sending request...');
					$pool->execute(); // this will call handleCurlResponse() and populate $this->requests[$orig]
					$this->debug('Received responses');
					foreach($subset as $orig => $url) {
						if (!$isRedirect) $orig = $url;
						// $this->requests[$orig]['headers']
						// $this->requests[$orig]['body']
						// $this->requests[$orig]['effective_url']
						// check content type
						if ($this->headerOnlyType($this->requests[$orig]['headers'])) {
							$this->requests[$orig]['body'] = '';
							$_header_only_type = true;
							$this->debug('Header only type returned');
						} else {
							$_header_only_type = false;
						}
						$status_code = $this->requests[$orig]['status_code'];
						if ((in_array($status_code, array(300, 301, 302, 303, 307)) || $status_code > 307 && $status_code < 400) && isset($this->requests[$orig]['location'])) {
							$redirectURL = $this->requests[$orig]['location'];
							if (!preg_match('!^https?://!i', $redirectURL)) {
								$redirectURL = SimplePie_Misc::absolutize_url($redirectURL, $url);
							}
							if ($this->validateURL($redirectURL)) {
								$this->debug('Redirect detected. Valid URL: '.$redirectURL);
								// store any cookies
								$cookies = $this->cookieJar->extractCookies($this->requests[$orig]['headers']);
								if (!empty($cookies)) $this->cookieJar->storeCookies($url, $cookies);							
								$this->redirectQueue[$orig] = $redirectURL;
							} else {
								$this->debug('Redirect detected. Invalid URL: '.$redirectURL);
							}
						} elseif (!$_header_only_type && $this->requests[$orig]['method'] == 'HEAD') {
							// the response content-type did not match our 'header only' types, 
							// but we'd issues a HEAD request because we assumed it would. So
							// let's queue a proper GET request for this item...
							$this->debug('Wrong guess at content-type, queing GET request');
							$this->requests[$orig]['wrongGuess'] = true;
							$this->redirectQueue[$orig] = $this->requests[$orig]['effective_url'];
						} elseif (strpos($this->requests[$orig]['effective_url'], '_escaped_fragment_') === false) {
							// check for <meta name='fragment' content='!'/>
							// for AJAX sites, e.g. Blogger with its dynamic views templates.
							// Based on Google's spec: https://developers.google.com/webmasters/ajax-crawling/docs/specification
							if (isset($this->requests[$orig]['body'])) {
								$redirectURL = $this->getRedirectURLfromHTML($this->requests[$orig]['effective_url'], substr($this->requests[$orig]['body'], 0, 4000));
								if ($redirectURL) {
									$this->redirectQueue[$orig] = $redirectURL;
								}
							}
						}
						// die($url.' -multi- '.$request->getResponseInfo('effective_url'));
						unset($this->requests[$orig]['httpRequest'], $this->requests[$orig]['method']);
					}
				}
			}
		}

		//////////////////////////////////////////////////////
		// sequential (file_get_contents)
		else {
			$this->debug('Starting sequential fetch (file_get_contents)');
			$this->debug('Processing set of '.count($urls));
			foreach ($urls as $orig => $url) {
				if (!$isRedirect) $orig = $url;
				unset($this->redirectQueue[$orig]);
				$this->debug("...$url");
				if (!$isRedirect && isset($this->requests[$url])) {
					$this->debug("......in memory");
				/*
				} elseif ($this->isCached($url)) {
					$this->debug("......is cached");
					if (!$this->minimiseMemoryUse) {
						$this->requests[$url] = $this->getCached($url);
					}
				*/
				} else {
					$this->debug("Sending request for $url");
					$this->requests[$orig]['original_url'] = $orig;
					$req_url = $this->rewriteUrls($url);
					$req_url = ($this->rewriteHashbangFragment) ? $this->rewriteHashbangFragment($req_url) : $req_url;
					$req_url = $this->removeFragment($req_url);
					// send cookies, if we have any
					$httpContext = $this->httpContext;
					$httpContext['http']['header'] .= $this->getUserAgent($req_url)."\r\n";
					// add referer for picky sites
					$httpContext['http']['header'] .= 'Referer: '.$this->referer."\r\n";
					if ($cookies = $this->cookieJar->getMatchingCookies($req_url)) {
						$this->debug("......sending cookies: $cookies");
						$httpContext['http']['header'] .= 'Cookie: '.$cookies."\r\n";
					}
					if (false !== ($html = @file_get_contents($req_url, false, stream_context_create($httpContext)))) {
						$this->debug('Received response');
						// get status code
						if (!isset($http_response_header[0]) || !preg_match('!^HTTP/\d+\.\d+\s+(\d+)!', trim($http_response_header[0]), $match)) {
							$this->debug('Error: no status code found');
							// TODO: handle error - no status code
						} else {
							$this->requests[$orig]['headers'] = $this->headersToString($http_response_header, false);
							// check content type
							if ($this->headerOnlyType($this->requests[$orig]['headers'])) {
								$this->requests[$orig]['body'] = '';
							} else {
								$this->requests[$orig]['body'] = $html;
							}
							$this->requests[$orig]['effective_url'] = $req_url;
							$this->requests[$orig]['status_code'] = $status_code = (int)$match[1];
							unset($match);
							// handle redirect
							if (preg_match('/^Location:(.*?)$/mi', $this->requests[$orig]['headers'], $match)) {
								$this->requests[$orig]['location'] =  trim($match[1]);
							}
							if ((in_array($status_code, array(300, 301, 302, 303, 307)) || $status_code > 307 && $status_code < 400) && isset($this->requests[$orig]['location'])) {
								$redirectURL = $this->requests[$orig]['location'];
								if (!preg_match('!^https?://!i', $redirectURL)) {
									$redirectURL = SimplePie_Misc::absolutize_url($redirectURL, $url);
								}
								if ($this->validateURL($redirectURL)) {
									$this->debug('Redirect detected. Valid URL: '.$redirectURL);
									// store any cookies
									$cookies = $this->cookieJar->extractCookies($this->requests[$orig]['headers']);
									if (!empty($cookies)) $this->cookieJar->storeCookies($url, $cookies);
									$this->redirectQueue[$orig] = $redirectURL;
								} else {
									$this->debug('Redirect detected. Invalid URL: '.$redirectURL);
								}
							} elseif (strpos($this->requests[$orig]['effective_url'], '_escaped_fragment_') === false) {
								// check for <meta name='fragment' content='!'/>
								// for AJAX sites, e.g. Blogger with its dynamic views templates.
								// Based on Google's spec: https://developers.google.com/webmasters/ajax-crawling/docs/specification
								if (isset($this->requests[$orig]['body'])) {
									$redirectURL = $this->getRedirectURLfromHTML($this->requests[$orig]['effective_url'], substr($this->requests[$orig]['body'], 0, 4000));
									if ($redirectURL) {
										$this->redirectQueue[$orig] = $redirectURL;
									}
								}
							}
						}
					} else {
						$this->debug('Error retrieving URL');
						//print_r($req_url);
						//print_r($http_response_header);
						//print_r($html);
						
						// TODO: handle error - failed to retrieve URL
					}
				}
			}
		}
	}
	
	public function handleCurlResponse($response, $info, $request) {
		$orig = $request->url_original;
		$this->requests[$orig]['headers'] = substr($response, 0, $info['header_size']);
		$this->requests[$orig]['body'] = substr($response, $info['header_size']);
		$this->requests[$orig]['method'] = $request->method;
		$this->requests[$orig]['effective_url'] = $info['url'];
		$this->requests[$orig]['status_code'] = (int)$info['http_code'];
		if (preg_match('/^Location:(.*?)$/mi', $this->requests[$orig]['headers'], $match)) {
			$this->requests[$orig]['location'] =  trim($match[1]);
		}
	}
	
	protected function headersToString(array $headers, $associative=true) {
		if (!$associative) {
			return implode("\n", $headers);
		} else {
			$str = '';
			foreach ($headers as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $v) $str .= "$key: $v\n";
				} else {
					$str .= "$key: $val\n";
				}
			}
			return rtrim($str);
		}
	}
	
	public function get($url, $remove=false, $gzdecode=true) {
		$url = "$url";
		if (isset($this->requests[$url]) && isset($this->requests[$url]['body'])) {
			$this->debug("URL already fetched - in memory ($url, effective: {$this->requests[$url]['effective_url']})");
			$response = $this->requests[$url];
		/*
		} elseif ($this->isCached($url)) {
			$this->debug("URL already fetched - in disk cache ($url)");
			$response = $this->getCached($url);
			$this->requests[$url] = $response;
		*/
		} else {
			$this->debug("Fetching URL ($url)");
			$this->fetchAll(array($url));
			if (isset($this->requests[$url]) && isset($this->requests[$url]['body'])) {
				$response = $this->requests[$url];
			} else {
				$this->debug("Request failed");
				$response = false;
			}
		}
		/*
		if ($this->minimiseMemoryUse && $response) {
			$this->cache($url);
			unset($this->requests[$url]);
		}
		*/
		if ($remove && $response) unset($this->requests[$url]);
		if ($gzdecode && stripos($response['headers'], 'Content-Encoding: gzip')) {
			if ($html = gzdecode($response['body'])) {
				$response['body'] = $html;
			}
		}
		return $response;
	}
	
	public function parallelSupport() {
		return class_exists('HttpRequestPool') || function_exists('curl_multi_init');
	}
	
	private function headerOnlyType($headers) {
		if (preg_match('!^Content-Type:\s*(([a-z-]+)/([^;\r\n ]+))!im', $headers, $match)) {
			// look for full mime type (e.g. image/jpeg) or just type (e.g. image)
			$match[1] = strtolower(trim($match[1]));
			$match[2] = strtolower(trim($match[2]));
			foreach (array($match[1], $match[2]) as $mime) {
				if (in_array($mime, $this->headerOnlyTypes)) return true;
			}
		}
		return false;
	}
	
	private function possibleUnsupportedType($url) {
		$path = @parse_url($url, PHP_URL_PATH);
		if ($path && strpos($path, '.') !== false) {
			$ext = strtolower(trim(pathinfo($path, PATHINFO_EXTENSION)));
			return in_array($ext, $this->headerOnlyClues);
		}
		return false;
	}
}

// gzdecode from http://www.php.net/manual/en/function.gzdecode.php#82930
if (!function_exists('gzdecode')) {
	function gzdecode($data,&$filename='',&$error='',$maxlength=null) 
	{
		$len = strlen($data);
		if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
			$error = "Not in GZIP format.";
			return null;  // Not GZIP format (See RFC 1952)
		}
		$method = ord(substr($data,2,1));  // Compression method
		$flags  = ord(substr($data,3,1));  // Flags
		if ($flags & 31 != $flags) {
			$error = "Reserved bits not allowed.";
			return null;
		}
		// NOTE: $mtime may be negative (PHP integer limitations)
		$mtime = unpack("V", substr($data,4,4));
		$mtime = $mtime[1];
		$xfl   = substr($data,8,1);
		$os    = substr($data,8,1);
		$headerlen = 10;
		$extralen  = 0;
		$extra     = "";
		if ($flags & 4) {
			// 2-byte length prefixed EXTRA data in header
			if ($len - $headerlen - 2 < 8) {
				return false;  // invalid
			}
			$extralen = unpack("v",substr($data,8,2));
			$extralen = $extralen[1];
			if ($len - $headerlen - 2 - $extralen < 8) {
				return false;  // invalid
			}
			$extra = substr($data,10,$extralen);
			$headerlen += 2 + $extralen;
		}
		$filenamelen = 0;
		$filename = "";
		if ($flags & 8) {
			// C-style string
			if ($len - $headerlen - 1 < 8) {
				return false; // invalid
			}
			$filenamelen = strpos(substr($data,$headerlen),chr(0));
			if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
				return false; // invalid
			}
			$filename = substr($data,$headerlen,$filenamelen);
			$headerlen += $filenamelen + 1;
		}
		$commentlen = 0;
		$comment = "";
		if ($flags & 16) {
			// C-style string COMMENT data in header
			if ($len - $headerlen - 1 < 8) {
				return false;    // invalid
			}
			$commentlen = strpos(substr($data,$headerlen),chr(0));
			if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
				return false;    // Invalid header format
			}
			$comment = substr($data,$headerlen,$commentlen);
			$headerlen += $commentlen + 1;
		}
		$headercrc = "";
		if ($flags & 2) {
			// 2-bytes (lowest order) of CRC32 on header present
			if ($len - $headerlen - 2 < 8) {
				return false;    // invalid
			}
			$calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
			$headercrc = unpack("v", substr($data,$headerlen,2));
			$headercrc = $headercrc[1];
			if ($headercrc != $calccrc) {
				$error = "Header checksum failed.";
				return false;    // Bad header CRC
			}
			$headerlen += 2;
		}
		// GZIP FOOTER
		$datacrc = unpack("V",substr($data,-8,4));
		$datacrc = sprintf('%u',$datacrc[1] & 0xFFFFFFFF);
		$isize = unpack("V",substr($data,-4));
		$isize = $isize[1];
		// decompression:
		$bodylen = $len-$headerlen-8;
		if ($bodylen < 1) {
			// IMPLEMENTATION BUG!
			return null;
		}
		$body = substr($data,$headerlen,$bodylen);
		$data = "";
		if ($bodylen > 0) {
			switch ($method) {
			case 8:
				// Currently the only supported compression method:
				$data = gzinflate($body,$maxlength);
				break;
			default:
				$error = "Unknown compression method.";
				return false;
			}
		}  // zero-byte body content is allowed
		// Verifiy CRC32
		$crc   = sprintf("%u",crc32($data));
		$crcOK = $crc == $datacrc;
		$lenOK = $isize == strlen($data);
		if (!$lenOK || !$crcOK) {
			$error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
			return false;
		}
		return $data;
	}
}