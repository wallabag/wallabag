<?php
class Http{
	private static $cache = false;

	public static function Request($url){
		$url_parts = parse_url($url);
		$url_parts["port"] = isset($url_parts["port"]) ? $url_parts["port"] : 80;
		$url_parts["path"] = isset($url_parts["path"]) ? $url_parts["path"] : "/";

		return self::FullRequest("GET", $url_parts["host"], $url_parts["port"], $url_parts["path"]);
	}

	public static function FullRequest(
			$verb = 'GET',             /* HTTP Request Method (GET and POST supported) */
			$ip,                       /* Target IP/Hostname */
			$port = 80,                /* Target TCP port */
			$uri = '/',                /* Target URI */
			$getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
			$postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
			$cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */
			$custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */
			$timeout = 1000,           /* Socket timeout in milliseconds */
			$req_hdr = false,          /* Include HTTP request headers */
			$res_hdr = false,           /* Include HTTP response headers */
			$depth = 4					/* Depth of the iteration left (to avoid redirection loops) */
			)
	{
		if(self::$cache){
			$cacheFile = "cache/".$ip."/".str_replace("/", "...", $uri);

			if(is_file($cacheFile)){
				$data = file_get_contents($cacheFile);

				return self::resolveTruncated($data);
			}
		}
		$ret = '';
		$verb = strtoupper($verb);
		$cookie_str = '';
		$getdata_str = count($getdata) ? '?' : '';
		$postdata_str = '';

		foreach ($getdata as $k => $v)
			$getdata_str .= urlencode($k) .'='. urlencode($v);

		foreach ($postdata as $k => $v)
			$postdata_str .= urlencode($k) .'='. urlencode($v) .'&';

		foreach ($cookie as $k => $v)
			$cookie_str .= urlencode($k) .'='. urlencode($v) .'; ';

		$crlf = "\r\n";
		$req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
		$req .= 'Host: '. $ip . $crlf;
		$req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
		$req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
		$req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
		$req .= 'Accept-Encoding: deflate' . $crlf;
		$req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;
		

		foreach ($custom_headers as $k => $v)
			$req .= $k .': '. $v . $crlf;

		if (!empty($cookie_str))
			$req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf;

		if ($verb == 'POST' && !empty($postdata_str))
		{
			$postdata_str = substr($postdata_str, 0, -1);
			$req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
			$req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf;
			$req .= $postdata_str;
		}
		else $req .= $crlf;

		if ($req_hdr)
			$ret .= $req;

		if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
			return "Error $errno: $errstr\n";

		stream_set_timeout($fp, 0, $timeout * 1000);

		fputs($fp, $req);
		$ret .= stream_get_contents($fp);
		fclose($fp);

		$headerSplit = strpos($ret, "\r\n\r\n");
		$header = substr($ret, 0, $headerSplit);

		$redirectURL = self::CheckForRedirect($header);

		if($redirectURL !== false){
			if($depth > 0){
				$url_parts = parse_url($redirectURL);
				$url_parts["port"] = isset($url_parts["port"]) ? $url_parts["port"] : 80;
				$url_parts["path"] = isset($url_parts["path"]) ? $url_parts["path"] : "/";

				return self::FullRequest($verb, $url_parts["host"], $url_parts["port"], $url_parts["path"], $getdata, $postdata, $cookie, $custom_headers, $timeout, $req_hdr, $res_hdr, $depth-1);
			}else{
				return "Redirect loop, stopping...";
			}
		}

		$truncated = false;
		$headerLines = explode("\r\n", $header);
		foreach($headerLines as $line){
			list($name, $value) = explode(":", $line);
			$name = trim($name);
			$value = trim($value);

			if(strtolower($name) == "transfer-encoding" && strtolower($value) == "chunked"){		//TODO: Put right values!
				$truncated = true;
			}
		}

		if (!$res_hdr)
			$ret = substr($ret, $headerSplit + 4);

		if($truncated){
			$ret = self::resolveTruncated($ret);
		}
		if(self::$cache){
			if(!is_dir("cache")){
				mkdir("cache");
			}
			if(!is_dir("cache/".$ip)){
				mkdir("cache/".$ip);
			}
			if(!is_file("cache/".$ip."/".str_replace("/", "...", $uri))){
				$h = fopen("cache/".$ip."/".str_replace("/", "...", $uri), "w");
				fwrite($h, $ret);
				fclose($h);
			}
		}
		
		return $ret;
	}

	private static function resolveTruncated($data){
		$pos = 0;
		$end = strlen($data);
		$out = "";

		while($pos < $end){
			$endVal = strpos($data, "\r\n", $pos);
			$value = hexdec(substr($data, $pos, $endVal-$pos));
			$out .= substr($data, $endVal+2, $value);
			$pos = $endVal+2+$value;
		}

		return $out;
	}

	private static function CheckForRedirect($header){
		$firstLine = substr($header, 0, strpos($header, "\r\n"));
		list($httpVersion, $statusCode, $message) = explode(" ", $firstLine);

		if(substr($statusCode, 0, 1) == "3"){
			$part = substr($header, strpos(strtolower($header), "location: ")+strlen("location: "));
			$location = trim(substr($part, 0, strpos($part, "\r\n")));

			if(strlen($location) > 0){
				return $location;
			}
		}
		return false;
	}
}
?>