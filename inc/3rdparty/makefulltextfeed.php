<?php
// Full-Text RSS: Create Full-Text Feeds
// Author: Keyvan Minoukadeh
// Copyright (c) 2013 Keyvan Minoukadeh
// License: AGPLv3
// Version: 3.2
// Date: 2013-05-13
// More info: http://fivefilters.org/content-only/
// Help: http://help.fivefilters.org

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Usage
// -----
// Request this file passing it a web page or feed URL in the querystring: makefulltextfeed.php?url=example.org/article
// For more request parameters, see http://help.fivefilters.org/customer/portal/articles/226660-usage

//error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
@set_time_limit(120);
libxml_use_internal_errors(true); 


// Deal with magic quotes
if (get_magic_quotes_gpc()) {
	$process = array(&$_GET, &$_POST, &$_REQUEST);
	while (list($key, $val) = each($process)) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

// set include path
set_include_path(realpath(dirname(__FILE__).'/libraries').PATH_SEPARATOR.get_include_path());

require_once dirname(__FILE__).'/makefulltextfeedHelpers.php';

////////////////////////////////
// Load config file
////////////////////////////////
require dirname(__FILE__).'/config.php';

////////////////////////////////
// Prevent indexing/following by search engines because:
// 1. The content is already public and presumably indexed (why create duplicates?)
// 2. Not doing so might increase number of requests from search engines, thus increasing server load
// Note: feed readers and services such as Yahoo Pipes will not be affected by this header.
// Note: Using Disallow in a robots.txt file will be more effective (search engines will check
// that before even requesting makefulltextfeed.php).
////////////////////////////////
header('X-Robots-Tag: noindex, nofollow');

////////////////////////////////
// Check if service is enabled
////////////////////////////////
if (!$options->enabled) {
	die('The full-text RSS service is currently disabled');
}

////////////////////////////////
// Debug mode?
// See the config file for debug options.
////////////////////////////////
$debug_mode = false;
if (isset($_GET['debug'])) {
	if ($options->debug === true || $options->debug == 'user') {
		$debug_mode = true;
	} elseif ($options->debug == 'admin') {
		session_start();
		$debug_mode = (@$_SESSION['auth'] == 1);
	}
	if ($debug_mode) {
		header('Content-Type: text/plain; charset=utf-8');
	} else {
		if ($options->debug == 'admin') {
			die('You must be logged in to the <a href="admin/">admin area</a> to see debug output.');
		} else {
			die('Debugging is disabled.');
		}
	}
}

////////////////////////////////
// Check for APC
////////////////////////////////
$options->apc = $options->apc && function_exists('apc_add');
if ($options->apc) {
	debug('APC is enabled and available on server');
} else {
	debug('APC is disabled or not available on server');
}

////////////////////////////////
// Check for smart cache
////////////////////////////////
$options->smart_cache = $options->smart_cache && function_exists('apc_inc');

////////////////////////////////
// Check for feed URL
////////////////////////////////
if (!isset($_GET['url'])) {
	die('No URL supplied');
}
$url = trim($_GET['url']);
if (strtolower(substr($url, 0, 7)) == 'feed://') {
	$url = 'http://'.substr($url, 7);
}
if (!preg_match('!^https?://.+!i', $url)) {
	$url = 'http://'.$url;
}

$url = filter_var($url, FILTER_SANITIZE_URL);
$test = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
// deal with bug http://bugs.php.net/51192 (present in PHP 5.2.13 and PHP 5.3.2)
if ($test === false) {
	$test = filter_var(strtr($url, '-', '_'), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
}
if ($test !== false && $test !== null && preg_match('!^https?://!', $url)) {
	// all okay
	unset($test);
} else {
	die('Invalid URL supplied');
}
debug("Supplied URL: $url");

/////////////////////////////////
// Redirect to hide API key
/////////////////////////////////
if (isset($_GET['key']) && ($key_index = array_search($_GET['key'], $options->api_keys)) !== false) {
	$host = $_SERVER['HTTP_HOST'];
	$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
	$_qs_url = (strtolower(substr($url, 0, 7)) == 'http://') ? substr($url, 7) : $url;
	$redirect = 'http://'.htmlspecialchars($host.$path).'/makefulltextfeed.php?url='.urlencode($_qs_url);
	$redirect .= '&key='.$key_index;
	$redirect .= '&hash='.urlencode(sha1($_GET['key'].$url));
	if (isset($_GET['html'])) $redirect .= '&html='.urlencode($_GET['html']);
	if (isset($_GET['max'])) $redirect .= '&max='.(int)$_GET['max'];
	if (isset($_GET['links'])) $redirect .= '&links='.urlencode($_GET['links']);
	if (isset($_GET['exc'])) $redirect .= '&exc='.urlencode($_GET['exc']);
	if (isset($_GET['format'])) $redirect .= '&format='.urlencode($_GET['format']);
	if (isset($_GET['callback'])) $redirect .= '&callback='.urlencode($_GET['callback']);
	if (isset($_GET['l'])) $redirect .= '&l='.urlencode($_GET['l']);
	if (isset($_GET['xss'])) $redirect .= '&xss';
	if (isset($_GET['use_extracted_title'])) $redirect .= '&use_extracted_title';
	if (isset($_GET['content'])) $redirect .= '&content='.urlencode($_GET['content']);
	if (isset($_GET['summary'])) $redirect .= '&summary='.urlencode($_GET['summary']);
	if (isset($_GET['debug'])) $redirect .= '&debug';
	if ($debug_mode) {
		debug('Redirecting to hide access key, follow URL below to continue');
		debug("Location: $redirect");
	} else {
		header("Location: $redirect");
	}
	exit;
}

///////////////////////////////////////////////
// Set timezone.
// Prevents warnings, but needs more testing -
// perhaps if timezone is set in php.ini we
// don't need to set it at all...
///////////////////////////////////////////////
if (!ini_get('date.timezone') || !@date_default_timezone_set(ini_get('date.timezone'))) {
	date_default_timezone_set('UTC');
}

///////////////////////////////////////////////
// Check if the request is explicitly for an HTML page
///////////////////////////////////////////////
$html_only = (isset($_GET['html']) && ($_GET['html'] == '1' || $_GET['html'] == 'true'));

///////////////////////////////////////////////
// Check if valid key supplied
///////////////////////////////////////////////
$valid_key = false;
if (isset($_GET['key']) && isset($_GET['hash']) && isset($options->api_keys[(int)$_GET['key']])) {
	$valid_key = ($_GET['hash'] == sha1($options->api_keys[(int)$_GET['key']].$url));
}
$key_index = ($valid_key) ? (int)$_GET['key'] : 0;
if (!$valid_key && $options->key_required) {
	die('A valid key must be supplied');
}
if (!$valid_key && isset($_GET['key']) && $_GET['key'] != '') {
	die('The entered key is invalid');
}

if (file_exists('custom_init.php')) require 'custom_init.php';

///////////////////////////////////////////////
// Check URL against list of blacklisted URLs
///////////////////////////////////////////////
if (!url_allowed($url)) die('URL blocked');

///////////////////////////////////////////////
// Max entries
// see config.php to find these values
///////////////////////////////////////////////
if (isset($_GET['max'])) {
	$max = (int)$_GET['max'];
	if ($valid_key) {
		$max = min($max, $options->max_entries_with_key);
	} else {
		$max = min($max, $options->max_entries);
	}
} else {
	if ($valid_key) {
		$max = $options->default_entries_with_key;
	} else {
		$max = $options->default_entries;
	}
}

///////////////////////////////////////////////
// Link handling
///////////////////////////////////////////////
if (isset($_GET['links']) && in_array($_GET['links'], array('preserve', 'footnotes', 'remove'))) {
	$links = $_GET['links'];
} else {
	$links = 'preserve';
}

///////////////////////////////////////////////
// Favour item titles in feed?
///////////////////////////////////////////////
$favour_feed_titles = true;
if ($options->favour_feed_titles == 'user') {
	$favour_feed_titles = !isset($_GET['use_extracted_title']);
} else {
	$favour_feed_titles = $options->favour_feed_titles;
}

///////////////////////////////////////////////
// Include full content in output?
///////////////////////////////////////////////
if ($options->content === 'user') {
	if (isset($_GET['content']) && $_GET['content'] === '0') {
		$options->content = false;
	} else {
		$options->content = true;
	}
}

///////////////////////////////////////////////
// Include summaries in output?
///////////////////////////////////////////////
if ($options->summary === 'user') {
	if (isset($_GET['summary']) && $_GET['summary'] === '1') {
		$options->summary = true;
	} else {
		$options->summary = false;
	}
}

///////////////////////////////////////////////
// Exclude items if extraction fails
///////////////////////////////////////////////
if ($options->exclude_items_on_fail === 'user') {
	$exclude_on_fail = (isset($_GET['exc']) && ($_GET['exc'] == '1'));
} else {
	$exclude_on_fail = $options->exclude_items_on_fail;
}

///////////////////////////////////////////////
// Detect language
///////////////////////////////////////////////
if ($options->detect_language === 'user') {
	if (isset($_GET['l'])) {
		$detect_language = (int)$_GET['l'];
	} else {
		$detect_language = 1;
	}
} else {
	$detect_language = $options->detect_language;
}

$use_cld = extension_loaded('cld') && (version_compare(PHP_VERSION, '5.3.0') >= 0);

/////////////////////////////////////
// Check for valid format
// (stick to RSS (or RSS as JSON) for the time being)
/////////////////////////////////////
if (isset($_GET['format']) && $_GET['format'] == 'json') {
	$format = 'json';
} else {
	$format = 'rss';
}

/////////////////////////////////////
// Should we do XSS filtering?
/////////////////////////////////////
if ($options->xss_filter === 'user') {
	$xss_filter = isset($_GET['xss']);
} else {
	$xss_filter = $options->xss_filter;
}
if (!$xss_filter && isset($_GET['xss'])) {
	die('XSS filtering is disabled in config');
}

/////////////////////////////////////
// Check for JSONP
// Regex from https://gist.github.com/1217080
/////////////////////////////////////
$callback = null;
if ($format =='json' && isset($_GET['callback'])) {
	$callback = trim($_GET['callback']);
	foreach (explode('.', $callback) as $_identifier) {
		if (!preg_match('/^[a-zA-Z_$][0-9a-zA-Z_$]*(?:\[(?:".+"|\'.+\'|\d+)\])*?$/', $_identifier)) {
			die('Invalid JSONP callback');
		}
	}
	debug("JSONP callback: $callback");
}

//////////////////////////////////
// Enable Cross-Origin Resource Sharing (CORS)
//////////////////////////////////
if ($options->cors) header('Access-Control-Allow-Origin: *');

//////////////////////////////////
// Check for cached copy
//////////////////////////////////
if ($options->caching) {
	debug('Caching is enabled...');
	$cache_id = md5($max.$url.(int)$valid_key.$links.(int)$favour_feed_titles.(int)$options->content.(int)$options->summary.(int)$xss_filter.(int)$exclude_on_fail.$format.$detect_language.(int)isset($_GET['pubsub']));
	$check_cache = true;
	if ($options->apc && $options->smart_cache) {
		apc_add("cache.$cache_id", 0, 10*60);
		$apc_cache_hits = (int)apc_fetch("cache.$cache_id");
		$check_cache = ($apc_cache_hits >= 2);
		apc_inc("cache.$cache_id");
		if ($check_cache) {
			debug('Cache key found in APC, we\'ll try to load cache file from disk');
		} else {
			debug('Cache key not found in APC');
		}
	}
	if ($check_cache) {
		$cache = get_cache();
		if ($data = $cache->load($cache_id)) {
			if ($debug_mode) {
				debug('Loaded cached copy');
				exit;
			}
			if ($format == 'json') {
				if ($callback === null) {
					header('Content-type: application/json; charset=UTF-8');
				} else {
					header('Content-type: application/javascript; charset=UTF-8');
				}
			} else {
				header('Content-type: text/xml; charset=UTF-8');
				header('X-content-type-options: nosniff');
			}
			if (headers_sent()) die('Some data has already been output, can\'t send RSS file');
			if ($callback) {
				echo "$callback($data);";
			} else {
				echo $data;
			}
			exit;
		}
	}
}

//////////////////////////////////
// Set Expires header
//////////////////////////////////
if (!$debug_mode) {
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+(60*10)) . ' GMT');
}

//////////////////////////////////
// Set up HTTP agent
//////////////////////////////////
global $http;
$http = new HumbleHttpAgent();
$http->debug = $debug_mode;
$http->userAgentMap = $options->user_agents;
$http->headerOnlyTypes = array_keys($options->content_type_exc);
$http->rewriteUrls = $options->rewrite_url;

//////////////////////////////////
// Set up Content Extractor
//////////////////////////////////
global $extractor;
$extractor = new ContentExtractor(dirname(__FILE__).'/site_config/custom', dirname(__FILE__).'/site_config/standard');
$extractor->debug = $debug_mode;
SiteConfig::$debug = $debug_mode;
SiteConfig::use_apc($options->apc);
$extractor->fingerprints = $options->fingerprints;
$extractor->allowedParsers = $options->allowed_parsers;

////////////////////////////////
// Get RSS/Atom feed
////////////////////////////////
if (!$html_only) {
	debug('--------');
	debug("Attempting to process URL as feed");
	// Send user agent header showing PHP (prevents a HTML response from feedburner)
	$http->userAgentDefault = HumbleHttpAgent::UA_PHP;
	// configure SimplePie HTTP extension class to use our HumbleHttpAgent instance
	SimplePie_HumbleHttpAgent::set_agent($http);
	$feed = new SimplePie();
	// some feeds use the text/html content type - force_feed tells SimplePie to process anyway
	$feed->force_feed(true);
	$feed->set_file_class('SimplePie_HumbleHttpAgent');
	//$feed->set_feed_url($url); // colons appearing in the URL's path get encoded
	$feed->feed_url = $url;
	$feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
	$feed->set_timeout(20);
	$feed->enable_cache(false);
	$feed->set_stupidly_fast(true);
	$feed->enable_order_by_date(false); // we don't want to do anything to the feed
	$feed->set_url_replacements(array());
	// initialise the feed
	// the @ suppresses notices which on some servers causes a 500 internal server error
	$result = @$feed->init();
	//$feed->handle_content_type();
	//$feed->get_title();
	if ($result && (!is_array($feed->data) || count($feed->data) == 0)) {
		die('Sorry, no feed items found');
	}
	// from now on, we'll identify ourselves as a browser
	$http->userAgentDefault = HumbleHttpAgent::UA_BROWSER;
}

////////////////////////////////////////////////////////////////////////////////
// Our given URL is not a feed, so let's create our own feed with a single item:
// the given URL. This basically treats all non-feed URLs as if they were
// single-item feeds.
////////////////////////////////////////////////////////////////////////////////
$isDummyFeed = false;
if ($html_only || !$result) {
	debug('--------');
	debug("Constructing a single-item feed from URL");
	$isDummyFeed = true;
	unset($feed, $result);
	// create single item dummy feed object
	$feed = new DummySingleItemFeed($url);
}

////////////////////////////////////////////
// Create full-text feed
////////////////////////////////////////////
$output = new FeedWriter();
$output->setTitle(strip_tags($feed->get_title()));
$output->setDescription(strip_tags($feed->get_description()));
$output->setXsl('css/feed.xsl'); // Chrome uses this, most browsers ignore it
if ($valid_key && isset($_GET['pubsub'])) { // used only on fivefilters.org at the moment
	$output->addHub('http://fivefilters.superfeedr.com/');
	$output->addHub('http://pubsubhubbub.appspot.com/');
	$output->setSelf('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}
$output->setLink($feed->get_link()); // Google Reader uses this for pulling in favicons
if ($img_url = $feed->get_image_url()) {
	$output->setImage($feed->get_title(), $feed->get_link(), $img_url);
}

////////////////////////////////////////////
// Loop through feed items
////////////////////////////////////////////
$items = $feed->get_items(0, $max);
// Request all feed items in parallel (if supported)
$urls_sanitized = array();
$urls = array();
foreach ($items as $key => $item) {
	$permalink = htmlspecialchars_decode($item->get_permalink());
	// Colons in URL path segments get encoded by SimplePie, yet some sites expect them unencoded
	$permalink = str_replace('%3A', ':', $permalink);
	// validateUrl() strips non-ascii characters
	// simplepie already sanitizes URLs so let's not do it again here.
	//$permalink = $http->validateUrl($permalink);
	if ($permalink) {
		$urls_sanitized[] = $permalink;
	}
	$urls[$key] = $permalink;
}
debug('--------');
debug('Fetching feed items');
$http->fetchAll($urls_sanitized);
//$http->cacheAll();

// count number of items added to full feed
$item_count = 0;

foreach ($items as $key => $item) {
	debug('--------');
	debug('Processing feed item '.($item_count+1));
	$do_content_extraction = true;
	$extract_result = false;
	$text_sample = null;
	$permalink = $urls[$key];
	debug("Item URL: $permalink");
	$extracted_title = '';
	$feed_item_title = $item->get_title();
	if ($feed_item_title !== null) {
		$feed_item_title = strip_tags(htmlspecialchars_decode($feed_item_title));
	}
	$newitem = $output->createNewItem();
	$newitem->setTitle($feed_item_title);
	if ($valid_key && isset($_GET['pubsub'])) { // used only on fivefilters.org at the moment
		if ($permalink !== false) {
			$newitem->setLink('http://fivefilters.org/content-only/redirect.php?url='.urlencode($permalink));
		} else {
			$newitem->setLink('http://fivefilters.org/content-only/redirect.php?url='.urlencode($item->get_permalink()));
		}
	} else {
		if ($permalink !== false) {
			$newitem->setLink($permalink);
		} else {
			$newitem->setLink($item->get_permalink());
		}
	}
	//if ($permalink && ($response = $http->get($permalink, true)) && $response['status_code'] < 300) {
	// Allowing error codes - some sites return correct content with error status
	// e.g. prospectmagazine.co.uk returns 403
	if ($permalink && ($response = $http->get($permalink, true)) && ($response['status_code'] < 300 || $response['status_code'] > 400)) {
		$effective_url = $response['effective_url'];
		if (!url_allowed($effective_url)) continue;
		// check if action defined for returned Content-Type
		$mime_info = get_mime_action_info($response['headers']);
		if (isset($mime_info['action'])) {
			if ($mime_info['action'] == 'exclude') {
				continue; // skip this feed item entry
			} elseif ($mime_info['action'] == 'link') {
				if ($mime_info['type'] == 'image') {
					$html = "<a href=\"$effective_url\"><img src=\"$effective_url\" alt=\"{$mime_info['name']}\" /></a>";
				} else {
					$html = "<a href=\"$effective_url\">Download {$mime_info['name']}</a>";
				}
				$extracted_title = $mime_info['name'];
				$do_content_extraction = false;
			}
		}
		if ($do_content_extraction) {
			$html = $response['body'];
			// remove strange things
			$html = str_replace('</[>', '', $html);
			$html = convert_to_utf8($html, $response['headers']);
			// check site config for single page URL - fetch it if found
			$is_single_page = false;
			if ($single_page_response = getSinglePage($item, $html, $effective_url)) {
				$is_single_page = true;
				$effective_url = $single_page_response['effective_url'];
				// check if action defined for returned Content-Type
				$mime_info = get_mime_action_info($single_page_response['headers']);
				if (isset($mime_info['action'])) {
					if ($mime_info['action'] == 'exclude') {
						continue; // skip this feed item entry
					} elseif ($mime_info['action'] == 'link') {
						if ($mime_info['type'] == 'image') {
							$html = "<a href=\"$effective_url\"><img src=\"$effective_url\" alt=\"{$mime_info['name']}\" /></a>";
						} else {
							$html = "<a href=\"$effective_url\">Download {$mime_info['name']}</a>";
						}
						$extracted_title = $mime_info['name'];
						$do_content_extraction = false;
					}
				}
				if ($do_content_extraction) {
					$html = $single_page_response['body'];
					// remove strange things
					$html = str_replace('</[>', '', $html);
					$html = convert_to_utf8($html, $single_page_response['headers']);
					debug("Retrieved single-page view from $effective_url");
				}
				unset($single_page_response);
			}
		}
		if ($do_content_extraction) {
			debug('--------');
			debug('Attempting to extract content');
			$extract_result = $extractor->process($html, $effective_url);
			$readability = $extractor->readability;
			$content_block = ($extract_result) ? $extractor->getContent() : null;
			$extracted_title = ($extract_result) ? $extractor->getTitle() : '';
			// Deal with multi-page articles
			//die('Next: '.$extractor->getNextPageUrl());
			$is_multi_page = (!$is_single_page && $extract_result && $extractor->getNextPageUrl());
			if ($options->multipage && $is_multi_page && $options->content) {
				debug('--------');
				debug('Attempting to process multi-page article');
				$multi_page_urls = array();
				$multi_page_content = array();
				while ($next_page_url = $extractor->getNextPageUrl()) {
					debug('--------');
					debug('Processing next page: '.$next_page_url);
					// If we've got URL, resolve against $url
					if ($next_page_url = makeAbsoluteStr($effective_url, $next_page_url)) {
						// check it's not what we have already!
						if (!in_array($next_page_url, $multi_page_urls)) {
							// it's not, so let's attempt to fetch it
							$multi_page_urls[] = $next_page_url;
							$_prev_ref = $http->referer;
							if (($response = $http->get($next_page_url, true)) && $response['status_code'] < 300) {
								// make sure mime type is not something with a different action associated
								$page_mime_info = get_mime_action_info($response['headers']);
								if (!isset($page_mime_info['action'])) {
									$html = $response['body'];
									// remove strange things
									$html = str_replace('</[>', '', $html);
									$html = convert_to_utf8($html, $response['headers']);
									if ($extractor->process($html, $next_page_url)) {
										$multi_page_content[] = $extractor->getContent();
										continue;
									} else { debug('Failed to extract content'); }
								} else { debug('MIME type requires different action'); }
							} else { debug('Failed to fetch URL'); }
						} else { debug('URL already processed'); }
					} else { debug('Failed to resolve against '.$effective_url); }
					// failed to process next_page_url, so cancel further requests
					$multi_page_content = array();
					break;
				}
				// did we successfully deal with this multi-page article?
				if (empty($multi_page_content)) {
					debug('Failed to extract all parts of multi-page article, so not going to include them');
					$_page = $readability->dom->createElement('p');
					$_page->innerHTML = '<em>This article appears to continue on subsequent pages which we could not extract</em>';
					$multi_page_content[] = $_page;
				}
				foreach ($multi_page_content as $_page) {
					$_page = $content_block->ownerDocument->importNode($_page, true);
					$content_block->appendChild($_page);
				}
				unset($multi_page_urls, $multi_page_content, $page_mime_info, $next_page_url, $_page);
			}
		}
		// use extracted title for both feed and item title if we're using single-item dummy feed
		if ($isDummyFeed) {
			$output->setTitle($extracted_title);
			$newitem->setTitle($extracted_title);
		} else {
			// use extracted title instead of feed item title?
			if (!$favour_feed_titles && $extracted_title != '') {
				debug('Using extracted title in generated feed');
				$newitem->setTitle($extracted_title);
			}
		}
	}
	if ($do_content_extraction) {
		// if we failed to extract content...
		if (!$extract_result) {
			if ($exclude_on_fail) {
				debug('Failed to extract, so skipping (due to exclude on fail parameter)');
				continue; // skip this and move to next item
			}
			//TODO: get text sample for language detection
			$html = $options->error_message;
			// keep the original item description
			$html .= $item->get_description();
		} else {
			$readability->clean($content_block, 'select');
			// get base URL
			$base_url = get_base_url($readability->dom);
			if (!$base_url) $base_url = $effective_url;
			// rewrite URLs
			if ($options->rewrite_relative_urls) makeAbsolute($base_url, $content_block);
			// footnotes
			if (($links == 'footnotes') && (strpos($effective_url, 'wikipedia.org') === false)) {
				$readability->addFootnotes($content_block);
			}
			// remove nesting: <div><div><div><p>test</p></div></div></div> = <p>test</p>
			while ($content_block->childNodes->length == 1 && $content_block->firstChild->nodeType === XML_ELEMENT_NODE) {
				// only follow these tag names
				if (!in_array(strtolower($content_block->tagName), array('div', 'article', 'section', 'header', 'footer'))) break;
				//$html = $content_block->firstChild->innerHTML; // FTR 2.9.5
				$content_block = $content_block->firstChild;
			}
			// convert content block to HTML string
			// Need to preserve things like body: //img[@id='feature']
			if (in_array(strtolower($content_block->tagName), array('div', 'article', 'section', 'header', 'footer'))) {
				$html = $content_block->innerHTML;
			} else {
				$html = $content_block->ownerDocument->saveXML($content_block); // essentially outerHTML
			}
			//unset($content_block);
			// post-processing cleanup
			$html = preg_replace('!<p>[\s\h\v]*</p>!u', '', $html);
			if ($links == 'remove') {
				$html = preg_replace('!</?a[^>]*>!', '', $html);
			}
			// get text sample for language detection
			$text_sample = strip_tags(substr($html, 0, 500));
			$html = make_substitutions($options->message_to_prepend).$html;
			$html .= make_substitutions($options->message_to_append);
		}
	}

	if ($valid_key && isset($_GET['pubsub'])) { // used only on fivefilters.org at the moment
		$newitem->addElement('guid', 'http://fivefilters.org/content-only/redirect.php?url='.urlencode($item->get_permalink()), array('isPermaLink'=>'false'));
	} else {
		$newitem->addElement('guid', $item->get_permalink(), array('isPermaLink'=>'true'));
	}
	// filter xss?
	if ($xss_filter) {
		debug('Filtering HTML to remove XSS');
		$html = htmLawed::hl($html, array('safe'=>1, 'deny_attribute'=>'style', 'comment'=>1, 'cdata'=>1));
	}

	// add content
	if ($options->summary === true) {
		// get summary
		$summary = '';
		if (!$do_content_extraction) {
			$summary = $html;
		} else {
			// Try to get first few paragraphs
			if (isset($content_block) && ($content_block instanceof DOMElement)) {
				$_paras = $content_block->getElementsByTagName('p');
				foreach ($_paras as $_para) {
					$summary .= preg_replace("/[\n\r\t ]+/", ' ', $_para->textContent).' ';
					if (strlen($summary) > 200) break;
				}
			} else {
				$summary = $html;
			}
		}
		unset($_paras, $_para);
		$summary = get_excerpt($summary);
		$newitem->setDescription($summary);
		if ($options->content) $newitem->setElement('content:encoded', $html);
	} else {
		if ($options->content) $newitem->setDescription($html);
	}

	// set date
	if ((int)$item->get_date('U') > 0) {
		$newitem->setDate((int)$item->get_date('U'));
	} elseif ($extractor->getDate()) {
		$newitem->setDate($extractor->getDate());
	}

	// add authors
	if ($authors = $item->get_authors()) {
		foreach ($authors as $author) {
			// for some feeds, SimplePie stores author's name as email, e.g. http://feeds.feedburner.com/nymag/intel
			if ($author->get_name() !== null) {
				$newitem->addElement('dc:creator', $author->get_name());
			} elseif ($author->get_email() !== null) {
				$newitem->addElement('dc:creator', $author->get_email());
			}
		}
	} elseif ($authors = $extractor->getAuthors()) {
		//TODO: make sure the list size is reasonable
		foreach ($authors as $author) {
			// TODO: xpath often selects authors from other articles linked from the page.
			// for now choose first item
			$newitem->addElement('dc:creator', $author);
			break;
		}
	}

	// add language
	if ($detect_language) {
		$language = $extractor->getLanguage();
		if (!$language) $language = $feed->get_language();
		if (($detect_language == 3 || (!$language && $detect_language == 2)) && $text_sample) {
			try {
				if ($use_cld) {
					// Use PHP-CLD extension
					$php_cld = 'CLD\detect'; // in quotes to prevent PHP 5.2 parse error
					$res = $php_cld($text_sample);
					if (is_array($res) && count($res) > 0) {
						$language = $res[0]['code'];
					}
				} else {
					//die('what');
					// Use PEAR's Text_LanguageDetect
					if (!isset($l))	{
					  $l = new Text_LanguageDetect();
					  $l->setNameMode(2); // return ISO 639-1 codes (e.g. "en")
					}
					$l_result = $l->detect($text_sample, 1);
					if (count($l_result) > 0) {
						$language = key($l_result);
					}
				}
			} catch (Exception $e) {
				//die('error: '.$e);
				// do nothing
			}
		}
		if ($language && (strlen($language) < 7)) {
			$newitem->addElement('dc:language', $language);
		}
	}

	// add MIME type (if it appeared in our exclusions lists)
	if (isset($mime_info['mime'])) $newitem->addElement('dc:format', $mime_info['mime']);
	// add effective URL (URL after redirects)
	if (isset($effective_url)) {
		//TODO: ensure $effective_url is valid witout - sometimes it causes problems, e.g.
		//http://www.siasat.pk/forum/showthread.php?108883-Pakistan-Chowk-by-Rana-Mubashir-�-25th-March-2012-Special-Program-from-Liari-(Karachi)
		//temporary measure: use utf8_encode()
		$newitem->addElement('dc:identifier', remove_url_cruft(utf8_encode($effective_url)));
	} else {
		$newitem->addElement('dc:identifier', remove_url_cruft($item->get_permalink()));
	}

	// add categories
	if ($categories = $item->get_categories()) {
		foreach ($categories as $category) {
			if ($category->get_label() !== null) {
				$newitem->addElement('category', $category->get_label());
			}
		}
	}

	// check for enclosures
	if ($options->keep_enclosures) {
		if ($enclosures = $item->get_enclosures()) {
			foreach ($enclosures as $enclosure) {
				// thumbnails
				foreach ((array)$enclosure->get_thumbnails() as $thumbnail) {
					$newitem->addElement('media:thumbnail', '', array('url'=>$thumbnail));
				}
				if (!$enclosure->get_link()) continue;
				$enc = array();
				// Media RSS spec ($enc): http://search.yahoo.com/mrss
				// SimplePie methods ($enclosure): http://simplepie.org/wiki/reference/start#methods4
				$enc['url'] = $enclosure->get_link();
				if ($enclosure->get_length()) $enc['fileSize'] = $enclosure->get_length();
				if ($enclosure->get_type()) $enc['type'] = $enclosure->get_type();
				if ($enclosure->get_medium()) $enc['medium'] = $enclosure->get_medium();
				if ($enclosure->get_expression()) $enc['expression'] = $enclosure->get_expression();
				if ($enclosure->get_bitrate()) $enc['bitrate'] = $enclosure->get_bitrate();
				if ($enclosure->get_framerate()) $enc['framerate'] = $enclosure->get_framerate();
				if ($enclosure->get_sampling_rate()) $enc['samplingrate'] = $enclosure->get_sampling_rate();
				if ($enclosure->get_channels()) $enc['channels'] = $enclosure->get_channels();
				if ($enclosure->get_duration()) $enc['duration'] = $enclosure->get_duration();
				if ($enclosure->get_height()) $enc['height'] = $enclosure->get_height();
				if ($enclosure->get_width()) $enc['width'] = $enclosure->get_width();
				if ($enclosure->get_language()) $enc['lang'] = $enclosure->get_language();
				$newitem->addElement('media:content', '', $enc);
			}
		}
	}
	$output->addItem($newitem);
	unset($html);
	$item_count++;
}

// output feed
debug('Done!');
/*
if ($debug_mode) {
	$_apc_data = apc_cache_info('user');
	var_dump($_apc_data); exit;
}
*/
if (!$debug_mode) {
	if ($callback) echo "$callback("; // if $callback is set, $format also == 'json'
	if ($format == 'json') $output->setFormat(($callback === null) ? JSON : JSONP);
	$add_to_cache = $options->caching;
	// is smart cache mode enabled?
	if ($add_to_cache && $options->apc && $options->smart_cache) {
		// yes, so only cache if this is the second request for this URL
		$add_to_cache = ($apc_cache_hits >= 2);
		// purge cache
		if ($options->cache_cleanup > 0) {
			if (rand(1, $options->cache_cleanup) == 1) {
				// apc purge code adapted from from http://www.thimbleopensource.com/tutorials-snippets/php-apc-expunge-script
				$_apc_data = apc_cache_info('user');
				foreach ($_apc_data['cache_list'] as $_apc_item) {
				  if ($_apc_item['ttl'] > 0 && ($_apc_item['ttl'] + $_apc_item['creation_time'] < time())) {
					apc_delete($_apc_item['info']);
				  }
				}
			}
		}
	}
	if ($add_to_cache) {
		ob_start();
		$output->genarateFeed(false);
		$output = ob_get_contents();
		ob_end_clean();
		if ($html_only && $item_count == 0) {
			// do not cache - in case of temporary server glitch at source URL
		} else {
			$cache = get_cache();
			if ($add_to_cache) $cache->save($output, $cache_id);
		}
		echo $output;
	} else {
		$output->genarateFeed(false);
	}
	if ($callback) echo ');';
}

