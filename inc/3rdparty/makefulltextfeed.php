<?php
// Full-Text RSS: Create Full-Text Feeds
// Author: Keyvan Minoukadeh
// Copyright (c) 2013 Keyvan Minoukadeh
// License: AGPLv3
// Version: 3.1
// Date: 2013-03-05
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
// Request this file passing it your feed in the querystring: makefulltextfeed.php?url=mysite.org
// The following options can be passed in the querystring:
// * URL: url=[feed or website url] (required, should be URL-encoded - in php: urlencode($url))
// * URL points to HTML (not feed): html=true (optional, by default it's automatically detected)
// * API key: key=[api key] (optional, refer to config.php)
// * Max entries to process: max=[max number of items] (optional)

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
@set_time_limit(120);

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

if ($detect_language >= 2) {
	$language_codes = array('albanian' => 'sq','arabic' => 'ar','azeri' => 'az','bengali' => 'bn','bulgarian' => 'bg',
	'cebuano' => 'ceb', // ISO 639-2
	'croatian' => 'hr','czech' => 'cs','danish' => 'da','dutch' => 'nl','english' => 'en','estonian' => 'et','farsi' => 'fa','finnish' => 'fi','french' => 'fr','german' => 'de','hausa' => 'ha',
	'hawaiian' => 'haw', // ISO 639-2 
	'hindi' => 'hi','hungarian' => 'hu','icelandic' => 'is','indonesian' => 'id','italian' => 'it','kazakh' => 'kk','kyrgyz' => 'ky','latin' => 'la','latvian' => 'lv','lithuanian' => 'lt','macedonian' => 'mk','mongolian' => 'mn','nepali' => 'ne','norwegian' => 'no','pashto' => 'ps',
	'pidgin' => 'cpe', // ISO 639-2  
	'polish' => 'pl','portuguese' => 'pt','romanian' => 'ro','russian' => 'ru','serbian' => 'sr','slovak' => 'sk','slovene' => 'sl','somali' => 'so','spanish' => 'es','swahili' => 'sw','swedish' => 'sv','tagalog' => 'tl','turkish' => 'tr','ukrainian' => 'uk','urdu' => 'ur','uzbek' => 'uz','vietnamese' => 'vi','welsh' => 'cy');
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
	$cache_id = md5($max.$url.$valid_key.$links.$favour_feed_titles.$xss_filter.$exclude_on_fail.$format.$detect_language.(int)isset($_GET['pubsub']));
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
$http = new HumbleHttpAgent();
$http->debug = $debug_mode;
$http->userAgentMap = $options->user_agents;
$http->headerOnlyTypes = array_keys($options->content_type_exc);
$http->rewriteUrls = $options->rewrite_url;

//////////////////////////////////
// Set up Content Extractor
//////////////////////////////////
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
				$html = $single_page_response['body'];
				// remove strange things
				$html = str_replace('</[>', '', $html);	
				$html = convert_to_utf8($html, $single_page_response['headers']);
				$effective_url = $single_page_response['effective_url'];
				debug("Retrieved single-page view from $effective_url");
				unset($single_page_response);
			}
			debug('--------');
			debug('Attempting to extract content');
			$extract_result = $extractor->process($html, $effective_url);
			$readability = $extractor->readability;
			$content_block = ($extract_result) ? $extractor->getContent() : null;			
			$extracted_title = ($extract_result) ? $extractor->getTitle() : '';
			// Deal with multi-page articles
			//die('Next: '.$extractor->getNextPageUrl());
			$is_multi_page = (!$is_single_page && $extract_result && $extractor->getNextPageUrl());
			if ($options->multipage && $is_multi_page) {
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
					$multi_page_content[] = $readability->dom->createElement('p')->innerHTML = '<em>This article appears to continue on subsequent pages which we could not extract</em>';
				}
				foreach ($multi_page_content as $_page) {
					$_page = $content_block->ownerDocument->importNode($_page, true);
					$content_block->appendChild($_page);
				}
				unset($multi_page_urls, $multi_page_content, $page_mime_info, $next_page_url);
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
			if ($options->rewrite_relative_urls) makeAbsolute($effective_url, $content_block);
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
			unset($content_block);
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
		$newitem->setDescription($html);
		
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
							$l = new Text_LanguageDetect('libraries/language-detect/lang.dat', 'libraries/language-detect/unicode_blocks.dat');
						}
						$l_result = $l->detect($text_sample, 1);
						if (count($l_result) > 0) {
							$language = $language_codes[key($l_result)];
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
			//http://www.siasat.pk/forum/showthread.php?108883-Pakistan-Chowk-by-Rana-Mubashir-–-25th-March-2012-Special-Program-from-Liari-(Karachi)
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
	/* } */
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
		$output->genarateFeed();
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
		$output->genarateFeed();
	}
	if ($callback) echo ');';
}

///////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////

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
				/*
				if (function_exists('iconv')) {
					// iconv appears to handle certain character encodings better than mb_convert_encoding
					$html = iconv($encoding, 'utf-8', $html);
				} else {
					$html = mb_convert_encoding($html, 'utf-8', $encoding);
				}
				*/
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