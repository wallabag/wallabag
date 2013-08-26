<?php
/**
 * Humble HTTP Agent extension for SimplePie_File
 * 
 * This class is designed to extend and override SimplePie_File
 * in order to prevent duplicate HTTP requests being sent out.
 * The idea is to initialise an instance of Humble HTTP Agent
 * and attach it, to a static class variable, of this class.
 * SimplePie will then automatically initialise this class
 * 
 * @date 2011-02-28
 */

class SimplePie_HumbleHttpAgent extends SimplePie_File
{
	protected static $agent;
	var $url;
	var $useragent;
	var $success = true;
	var $headers = array();
	var $body;
	var $status_code;
	var $redirects = 0;
	var $error;
	var $method = SIMPLEPIE_FILE_SOURCE_NONE;

	public static function set_agent(HumbleHttpAgent $agent) {
		self::$agent = $agent;
	}
	
	public function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		if (class_exists('idna_convert'))
		{
			$idn = new idna_convert();
			$parsed = SimplePie_Misc::parse_url($url);
			$url = SimplePie_Misc::compress_parse_url($parsed['scheme'], $idn->encode($parsed['authority']), $parsed['path'], $parsed['query'], $parsed['fragment']);
		}
		$this->url = $url;
		$this->useragent = $useragent;
		if (preg_match('/^http(s)?:\/\//i', $url))
		{
			if (!is_array($headers))
			{
				$headers = array();
			}
			$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE | SIMPLEPIE_FILE_SOURCE_CURL;
			$headers2 = array();
			foreach ($headers as $key => $value) {
				$headers2[] = "$key: $value";
			}
			//TODO: allow for HTTP headers
			// curl_setopt($fp, CURLOPT_HTTPHEADER, $headers2);

			$response = self::$agent->get($url);
			
			if ($response === false || !isset($response['status_code'])) {
				$this->error = 'failed to fetch URL';
				$this->success = false;
			} else {
				// The extra lines at the end are there to satisfy SimplePie's HTTP parser.
				// The class expects a full HTTP message, whereas we're giving it only
				// headers - the new lines indicate the start of the body.
				$parser = new SimplePie_HTTP_Parser($response['headers']."\r\n\r\n");
				if ($parser->parse()) {
					$this->headers = $parser->headers;
					//$this->body = $parser->body;
					$this->body = $response['body'];
					$this->status_code = $parser->status_code;
				}
			}
		}
		else
		{
			$this->error = 'invalid URL';
			$this->success = false;
		}
	}
}
?>