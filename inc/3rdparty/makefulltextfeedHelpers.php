<?php

// Autoloading of classes allows us to include files only when they're
// needed. If we've got a cached copy, for example, only Zend_Cache is loaded.
function autoload($class_name) {
	static $dir = null;
	if ($dir === null) $dir = dirname(__FILE__).'/libraries/';
	static $mapping = array(
		// Include FeedCreator for RSS/Atom creation
		'FeedWriter' => 'feedwriter/FeedWriter.php',
		'FeedItem' => 'feedwriter/FeedItem.php',
		// Include ContentExtractor and Readability for identifying and extracting content from URLs
		'ContentExtractor' => 'content-extractor/ContentExtractor.php',
		'SiteConfig' => 'content-extractor/SiteConfig.php',
		'Readability' => 'readability/Readability.php',
		// Include Humble HTTP Agent to allow parallel requests and response caching
		'HumbleHttpAgent' => 'humble-http-agent/HumbleHttpAgent.php',
		'SimplePie_HumbleHttpAgent' => 'humble-http-agent/SimplePie_HumbleHttpAgent.php',
		'CookieJar' => 'humble-http-agent/CookieJar.php',
		// Include Zend Cache to improve performance (cache results)
		'Zend_Cache' => 'Zend/Cache.php',
		// Language detect
		'Text_LanguageDetect' => 'language-detect/LanguageDetect.php',
		// HTML5 Lib
		'HTML5_Parser' => 'html5/Parser.php',
		// htmLawed - used if XSS filter is enabled (xss_filter)
		'htmLawed' => 'htmLawed/htmLawed.php'
	);
	if (isset($mapping[$class_name])) {
		debug("** Loading class $class_name ({$mapping[$class_name]})");
		require $dir.$mapping[$class_name];
		return true;
	} else {
		return false;
	}
}
spl_autoload_register('autoload');
require dirname(__FILE__).'/libraries/simplepie/autoloader.php';


class DummySingleItemFeed {
	public $item;
	function __construct($url) { $this->item = new DummySingleItem($url); }
	public function get_title() { return ''; }
	public function get_description() { return 'Content extracted from '.$this->item->url; }
	public function get_link() { return $this->item->url; }
	public function get_language() { return false; }
	public function get_image_url() { return false; }
	public function get_items($start=0, $max=1) { return array(0=>$this->item); }
}
class DummySingleItem {
	public $url;
	function __construct($url) { $this->url = $url; }
	public function get_permalink() { return $this->url; }
	public function get_title() { return null; }
	public function get_date($format='') { return false; }
	public function get_author($key=0) { return null; }
	public function get_authors() { return null; }
	public function get_description() { return ''; }
	public function get_enclosure($key=0, $prefer=null) { return null; }
	public function get_enclosures() { return null; }
	public function get_categories() { return null; }
}

///////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////

// Adapted from WordPress
// http://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/formatting.php#L2173
function get_excerpt($text, $num_words=55, $more=null) {
	if (null === $more) $more = '&hellip;';
	$text = strip_tags($text);
	//TODO: Check if word count is based on single characters (East Asian characters)
	/*
	if (1==2) {
  	$text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
  	preg_match_all('/./u', $text, $words_array);
  	$words_array = array_slice($words_array[0], 0, $num_words + 1);
  	$sep = '';
	} else {
  	$words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
  	$sep = ' ';
	}
	*/
	$words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
	$sep = ' ';
	if (count($words_array) > $num_words) {
		array_pop($words_array);
		$text = implode($sep, $words_array);
		$text = $text.$more;
	} else {
		$text = implode($sep, $words_array);
	}
	// trim whitespace at beginning or end of string
	// See: http://stackoverflow.com/questions/4166896/trim-unicode-whitespace-in-php-5-2
	$text = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $text);
	return $text;
}

function url_allowed($url) {
	global $options;
	if (!empty($options->allowed_urls)) {
		$allowed = false;
		foreach ($options->allowed_urls as $allowurl) {
			if (stristr($url, $allowurl) !== false) {
				$allowed = true;
				break;
			}
		}
		if (!$allowed) return false;
	} else {
		foreach ($options->blocked_urls as $blockurl) {
			if (stristr($url, $blockurl) !== false) {
				return false;
			}
		}
	}
	return true;
}

//////////////////////////////////////////////
// Convert $html to UTF8
// (uses HTTP headers and HTML to find encoding)
// adapted from http://stackoverflow.com/questions/910793/php-detect-encoding-and-make-everything-utf-8
//////////////////////////////////////////////
function convert_to_utf8($html, $header=null)
{
	$encoding = null;
	if ($html || $header) {
		if (is_array($header)) $header = implode("\n", $header);
		if (!$header || !preg_match_all('/^Content-Type:\s+([^;]+)(?:;\s*charset=["\']?([^;"\'\n]*))?/im', $header, $match, PREG_SET_ORDER)) {
			// error parsing the response
			debug('Could not find Content-Type header in HTTP response');
		} else {
			$match = end($match); // get last matched element (in case of redirects)
			if (isset($match[2])) $encoding = trim($match[2], "\"' \r\n\0\x0B\t");
		}
		// TODO: check to see if encoding is supported (can we convert it?)
		// If it's not, result will be empty string.
		// For now we'll check for invalid encoding types returned by some sites, e.g. 'none'
		// Problem URL: http://facta.co.jp/blog/archives/20111026001026.html
		if (!$encoding || $encoding == 'none') {
			// search for encoding in HTML - only look at the first 50000 characters
			// Why 50000? See, for example, http://www.lemonde.fr/festival-de-cannes/article/2012/05/23/deux-cretes-en-goguette-sur-la-croisette_1705732_766360.html
			// TODO: improve this so it looks at smaller chunks first
			$html_head = substr($html, 0, 50000);
			if (preg_match('/^<\?xml\s+version=(?:"[^"]*"|\'[^\']*\')\s+encoding=("[^"]*"|\'[^\']*\')/s', $html_head, $match)) {
				$encoding = trim($match[1], '"\'');
			} elseif (preg_match('/<meta\s+http-equiv=["\']?Content-Type["\']? content=["\'][^;]+;\s*charset=["\']?([^;"\'>]+)/i', $html_head, $match)) {
				$encoding = trim($match[1]);
			} elseif (preg_match_all('/<meta\s+([^>]+)>/i', $html_head, $match)) {
				foreach ($match[1] as $_test) {
					if (preg_match('/charset=["\']?([^"\']+)/i', $_test, $_m)) {
						$encoding = trim($_m[1]);
						break;
					}
				}
			}
		}
		if (isset($encoding)) $encoding = trim($encoding);
		// trim is important here!
		if (!$encoding || (strtolower($encoding) == 'iso-8859-1')) {
			// replace MS Word smart qutoes
			$trans = array();
			$trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
			$trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
			$trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
			$trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
			$trans[chr(134)] = '&dagger;';    // Dagger
			$trans[chr(135)] = '&Dagger;';    // Double Dagger
			$trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
			$trans[chr(137)] = '&permil;';    // Per Mille Sign
			$trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
			$trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
			$trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
			$trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
			$trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
			$trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
			$trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
			$trans[chr(149)] = '&bull;';    // Bullet
			$trans[chr(150)] = '&ndash;';    // En Dash
			$trans[chr(151)] = '&mdash;';    // Em Dash
			$trans[chr(152)] = '&tilde;';    // Small Tilde
			$trans[chr(153)] = '&trade;';    // Trade Mark Sign
			$trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
			$trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
			$trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
			$trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
			$html = strtr($html, $trans);
		}
		if (!$encoding) {
			debug('No character encoding found, so treating as UTF-8');
			$encoding = 'utf-8';
		} else {
			debug('Character encoding: '.$encoding);
			if (strtolower($encoding) != 'utf-8') {
				debug('Converting to UTF-8');
				$html = SimplePie_Misc::change_encoding($html, $encoding, 'utf-8');
			}
		}
	}
	return $html;
}

function makeAbsolute($base, $elem) {
	$base = new SimplePie_IRI($base);
	// remove '//' in URL path (used to prevent URLs from resolving properly)
	// TODO: check if this is still the case
	if (isset($base->path)) $base->path = preg_replace('!//+!', '/', $base->path);
	foreach(array('a'=>'href', 'img'=>'src') as $tag => $attr) {
		$elems = $elem->getElementsByTagName($tag);
		for ($i = $elems->length-1; $i >= 0; $i--) {
			$e = $elems->item($i);
			//$e->parentNode->replaceChild($articleContent->ownerDocument->createTextNode($e->textContent), $e);
			makeAbsoluteAttr($base, $e, $attr);
		}
		if (strtolower($elem->tagName) == $tag) makeAbsoluteAttr($base, $elem, $attr);
	}
}
function makeAbsoluteAttr($base, $e, $attr) {
	if ($e->hasAttribute($attr)) {
		// Trim leading and trailing white space. I don't really like this but
		// unfortunately it does appear on some sites. e.g.  <img src=" /path/to/image.jpg" />
		$url = trim(str_replace('%20', ' ', $e->getAttribute($attr)));
		$url = str_replace(' ', '%20', $url);
		if (!preg_match('!https?://!i', $url)) {
			if ($absolute = SimplePie_IRI::absolutize($base, $url)) {
				$e->setAttribute($attr, $absolute);
			}
		}
	}
}
function makeAbsoluteStr($base, $url) {
	$base = new SimplePie_IRI($base);
	// remove '//' in URL path (causes URLs not to resolve properly)
	if (isset($base->path)) $base->path = preg_replace('!//+!', '/', $base->path);
	if (preg_match('!^https?://!i', $url)) {
		// already absolute
		return $url;
	} else {
		if ($absolute = SimplePie_IRI::absolutize($base, $url)) {
			return $absolute;
		}
		return false;
	}
}
// returns single page response, or false if not found
function getSinglePage($item, $html, $url) {
	global $http, $extractor;
	debug('Looking for site config files to see if single page link exists');
	$site_config = $extractor->buildSiteConfig($url, $html);
	$splink = null;
	if (!empty($site_config->single_page_link)) {
		$splink = $site_config->single_page_link;
	} elseif (!empty($site_config->single_page_link_in_feed)) {
		// single page link xpath is targeted at feed
		$splink = $site_config->single_page_link_in_feed;
		// so let's replace HTML with feed item description
		$html = $item->get_description();
	}
	if (isset($splink)) {
		// Build DOM tree from HTML
		$readability = new Readability($html, $url);
		$xpath = new DOMXPath($readability->dom);
		// Loop through single_page_link xpath expressions
		$single_page_url = null;
		foreach ($splink as $pattern) {
			$elems = @$xpath->evaluate($pattern, $readability->dom);
			if (is_string($elems)) {
				$single_page_url = trim($elems);
				break;
			} elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
				foreach ($elems as $item) {
					if ($item instanceof DOMElement && $item->hasAttribute('href')) {
						$single_page_url = $item->getAttribute('href');
						break 2;
					} elseif ($item instanceof DOMAttr && $item->value) {
						$single_page_url = $item->value;
						break 2;
					}
				}
			}
		}
		// If we've got URL, resolve against $url
		if (isset($single_page_url) && ($single_page_url = makeAbsoluteStr($url, $single_page_url))) {
			// check it's not what we have already!
			if ($single_page_url != $url) {
				// it's not, so let's try to fetch it...
				$_prev_ref = $http->referer;
				$http->referer = $single_page_url;
				if (($response = $http->get($single_page_url, true)) && $response['status_code'] < 300) {
					$http->referer = $_prev_ref;
					return $response;
				}
				$http->referer = $_prev_ref;
			}
		}
	}
	return false;
}

// based on content-type http header, decide what to do
// param: HTTP headers string
// return: array with keys: 'mime', 'type', 'subtype', 'action', 'name'
// e.g. array('mime'=>'image/jpeg', 'type'=>'image', 'subtype'=>'jpeg', 'action'=>'link', 'name'=>'Image')
function get_mime_action_info($headers) {
	global $options;
	// check if action defined for returned Content-Type
	$info = array();
	if (preg_match('!^Content-Type:\s*(([-\w]+)/([-\w\+]+))!im', $headers, $match)) {
		// look for full mime type (e.g. image/jpeg) or just type (e.g. image)
		// match[1] = full mime type, e.g. image/jpeg
		// match[2] = first part, e.g. image
		// match[3] = last part, e.g. jpeg
		$info['mime'] = strtolower(trim($match[1]));
		$info['type'] = strtolower(trim($match[2]));
		$info['subtype'] = strtolower(trim($match[3]));
		foreach (array($info['mime'], $info['type']) as $_mime) {
			if (isset($options->content_type_exc[$_mime])) {
				$info['action'] = $options->content_type_exc[$_mime]['action'];
				$info['name'] = $options->content_type_exc[$_mime]['name'];
				break;
			}
		}
	}
	return $info;
}

function remove_url_cruft($url) {
	// remove google analytics for the time being
	// regex adapted from http://navitronic.co.uk/2010/12/removing-google-analytics-cruft-from-urls/
	// https://gist.github.com/758177
	return preg_replace('/(\?|\&)utm_[a-z]+=[^\&]+/', '', $url);
}

function make_substitutions($string) {
	if ($string == '') return $string;
	global $item, $effective_url;
	$string = str_replace('{url}', htmlspecialchars($item->get_permalink()), $string);
	$string = str_replace('{effective-url}', htmlspecialchars($effective_url), $string);
	return $string;
}

function get_cache() {
	global $options, $valid_key;
	static $cache = null;
	if ($cache === null) {
		$frontendOptions = array(
			'lifetime' => 10*60, // cache lifetime of 10 minutes
			'automatic_serialization' => false,
			'write_control' => false,
			'automatic_cleaning_factor' => $options->cache_cleanup,
			'ignore_user_abort' => false
		);
		$backendOptions = array(
			'cache_dir' => ($valid_key) ? $options->cache_dir.'/rss-with-key/' : $options->cache_dir.'/rss/', // directory where to put the cache files
			'file_locking' => false,
			'read_control' => true,
			'read_control_type' => 'strlen',
			'hashed_directory_level' => $options->cache_directory_level,
			'hashed_directory_perm' => 0777,
			'cache_file_perm' => 0664,
			'file_name_prefix' => 'ff'
		);
		// getting a Zend_Cache_Core object
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	}
	return $cache;
}

function debug($msg) {
	global $debug_mode;
	if ($debug_mode) {
		echo '* ',$msg,"\n";
		ob_flush();
		flush();
	}
}

function get_base_url($dom) {
	$xpath = new DOMXPath($dom);
	$base_url = @$xpath->evaluate('string(//head/base/@href)', $dom);
	if ($base_url !== '') {
		return $base_url;
	} else {
		return false;
	}
}
