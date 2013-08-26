<?php
/**
 * Site Config
 * 
 * Each instance of this class should hold extraction patterns and other directives
 * for a website. See ContentExtractor class to see how it's used.
 * 
 * @version 0.6
 * @date 2011-10-30
 * @author Keyvan Minoukadeh
 * @copyright 2011 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */

class SiteConfig
{
	// Use first matching element as title (0 or more xpath expressions)
	public $title = array();
	
	// Use first matching element as body (0 or more xpath expressions)
	public $body = array();
	
	// Use first matching element as author (0 or more xpath expressions)
	public $author = array();
	
	// Use first matching element as date (0 or more xpath expressions)
	public $date = array();
	
	// Strip elements matching these xpath expressions (0 or more)
	public $strip = array();
	
	// Strip elements which contain these strings (0 or more) in the id or class attribute 
	public $strip_id_or_class = array();
	
	// Strip images which contain these strings (0 or more) in the src attribute 
	public $strip_image_src = array();
	
	// Additional HTTP headers to send
	// NOT YET USED
	public $http_header = array();
	
	// Process HTML with tidy before creating DOM
	public $tidy = true;
	
	// Autodetect title/body if xpath expressions fail to produce results.
	// Note that this applies to title and body separately, ie. 
	//   * if we get a body match but no title match, this option will determine whether we autodetect title 
	//   * if neither match, this determines whether we autodetect title and body.
	// Also note that this only applies when there is at least one xpath expression in title or body, ie.
	//   * if title and body are both empty (no xpath expressions), this option has no effect (both title and body will be auto-detected)
	//   * if there's an xpath expression for title and none for body, body will be auto-detected and this option will determine whether we auto-detect title if the xpath expression for it fails to produce results.
	// Usage scenario: you want to extract something specific from a set of URLs, e.g. a table, and if the table is not found, you want to ignore the entry completely. Auto-detection is unlikely to succeed here, so you construct your patterns and set this option to false. Another scenario may be a site where auto-detection has proven to fail (or worse, picked up the wrong content).
	public $autodetect_on_failure = true;
	
	// Clean up content block - attempt to remove elements that appear to be superfluous
	public $prune = true;
	
	// Test URL - if present, can be used to test the config above
	public $test_url = null;
	
	// Single-page link - should identify a link element or URL pointing to the page holding the entire article
	// This is useful for sites which split their articles across multiple pages. Links to such pages tend to 
	// display the first page with links to the other pages at the bottom. Often there is also a link to a page
	// which displays the entire article on one page (e.g. 'print view').
	// This should be an XPath expression identifying the link to that page. If present and we find a match,
	// we will retrieve that page and the rest of the options in this config will be applied to the new page.
	public $single_page_link = array();
	
	// Single-page link in feed? - same as above, but patterns applied to item description HTML taken from feed
	public $single_page_link_in_feed = array();
	
	// TODO: which parser to use for turning raw HTML into a DOMDocument
	public $parser = 'libxml';
	
	// String replacement to be made on HTML before processing begins
	public $replace_string = array();
	
	// the options below cannot be set in the config files which this class represents
	
	public static $debug = false;
	protected static $config_path;
	protected static $config_path_fallback;
	protected static $config_cache = array();
	const HOSTNAME_REGEX = '/^(([a-zA-Z0-9-]*[a-zA-Z0-9])\.)*([A-Za-z0-9-]*[A-Za-z0-9])$/';
	
	protected static function debug($msg) {
		if (self::$debug) {
			$mem = round(memory_get_usage()/1024, 2);
			$memPeak = round(memory_get_peak_usage()/1024, 2);
			echo '* ',$msg;
			echo ' - mem used: ',$mem," (peak: $memPeak)\n";	
			ob_flush();
			flush();
		}
	}	
	
	public static function set_config_path($path, $fallback=null) {
		self::$config_path = $path;
		self::$config_path_fallback = $fallback;
	}
	
	public static function add_to_cache($host, SiteConfig $config) {
		$host = strtolower($host);
		self::$config_cache[$host] = $config;	
	}
	
	// returns SiteConfig instance if an appropriate one is found, false otherwise
	public static function build($host) {
		$host = strtolower($host);
		if (substr($host, 0, 4) == 'www.') $host = substr($host, 4);
		if (!$host || (strlen($host) > 200) || !preg_match(self::HOSTNAME_REGEX, $host)) return false;
		// check for site configuration
		$try = array($host);
		$split = explode('.', $host);
		if (count($split) > 1) {
			array_shift($split);
			$try[] = '.'.implode('.', $split);
		}
		foreach ($try as $h) {
			if (array_key_exists($h, self::$config_cache)) {
				self::debug("... cached ($h)");
				return self::$config_cache[$h];
			} elseif (file_exists(self::$config_path."/$h.txt")) {
				self::debug("... from file ($h)");
				$file = self::$config_path."/$h.txt";
				break;
			}
		}
		if (!isset($file)) {
			if (isset(self::$config_path_fallback)) {
				self::debug("... trying fallback ($host)");
				foreach ($try as $h) {
					if (file_exists(self::$config_path_fallback."/$h.txt")) {
						self::debug("... from fallback file ($h)");
						$file = self::$config_path_fallback."/$h.txt";
						break;
					}
				}
				if (!isset($file)) {
					self::debug("... no match in fallback directory");
					return false;
				}
			} else {
				self::debug("... no match ($host)");
				return false;
			}
		}
		$config_file = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!$config_file || !is_array($config_file)) return false;
		$config = new SiteConfig();
		foreach ($config_file as $line) {
			$line = trim($line);
			
			// skip comments, empty lines
			if ($line == '' || $line[0] == '#') continue;
			
			// get command
			$command = explode(':', $line, 2);
			// if there's no colon ':', skip this line
			if (count($command) != 2) continue;
			$val = trim($command[1]);
			$command = trim($command[0]);
			if ($command == '' || $val == '') continue;
			
			// check for commands where we accept multiple statements
			if (in_array($command, array('title', 'body', 'author', 'date', 'strip', 'strip_id_or_class', 'strip_image_src', 'single_page_link', 'single_page_link_in_feed', 'http_header'))) {
				array_push($config->$command, $val);
			// check for single statement commands that evaluate to true or false
			} elseif (in_array($command, array('tidy', 'prune', 'autodetect_on_failure'))) {
				$config->$command = ($val == 'yes');
			// check for single statement commands stored as strings
			} elseif (in_array($command, array('test_url', 'parser'))) {
				$config->$command = $val;
			} elseif ((substr($command, -1) == ')') && preg_match('!^([a-z0-9_]+)\((.*?)\)$!i', $command, $match)) {
				if (in_array($match[1], array('replace_string'))) {
					$command = $match[1];
					array_push($config->$command, array($match[2], $val));
				}
			}
		}
		return $config;
	}
}
?>