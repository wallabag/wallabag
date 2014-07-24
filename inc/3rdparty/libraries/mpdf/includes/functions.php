<?php

// mPDF 5.6.23
function array_insert(&$array, $value, $offset) {
	if (is_array($array)) {
		$array  = array_values($array);
		$offset = intval($offset);
		if ($offset < 0 || $offset >= count($array)) { array_push($array, $value); }
		else if ($offset == 0) { array_unshift($array, $value); }
		else { 
			$temp  = array_slice($array, 0, $offset);
			array_push($temp, $value);
			$array = array_slice($array, $offset);
			$array = array_merge($temp, $array);
		}
	}
	else { $array = array($value); }
	return count($array);
}

function urlencode_part($url) {	// mPDF 5.6.02
	if (!preg_match('/^[a-z]+:\/\//i',$url)) { return $url; }
	$file=$url;
	$query='';
	if (preg_match('/[?]/',$url)) {
		$bits = preg_split('/[?]/',$url,2);
		$file=$bits[0];
		$query='?'.$bits[1];
	}
	$file = str_replace(array(" ","!","$","&","'","(",")","*","+",",",";","="),array("%20","%21","%24","%26","%27","%28","%29","%2A","%2B","%2C","%3B","%3D"),$file);
	return $file.$query;
}


function _strspn($str1, $str2, $start=null, $length=null) {
	$numargs = func_num_args();
	if ($numargs == 2) {
		return strspn($str1, $str2);
	}
	else if ($numargs == 3) {
		return strspn($str1, $str2, $start);
	}
	else {
		return strspn($str1, $str2, $start, $length);
	}
}


function _strcspn($str1, $str2, $start=null, $length=null) {
	$numargs = func_num_args();
	if ($numargs == 2) {
		return strcspn($str1, $str2);
	} 
	else if ($numargs == 3) {
		return strcspn($str1, $str2, $start);
	} 
	else {
		return strcspn($str1, $str2, $start, $length);
	}
}

function _fgets (&$h, $force=false) {
	$startpos = ftell($h);
	$s = fgets($h, 1024);
	if ($force && preg_match("/^([^\r\n]*[\r\n]{1,2})(.)/",trim($s), $ns)) {
		$s = $ns[1];
		fseek($h,$startpos+strlen($s));
	}
	return $s;
}


// For PHP4 compatability
if(!function_exists('str_ireplace')) {
  function str_ireplace($search,$replace,$subject) {
	$search = preg_quote($search, "/");
	return preg_replace("/".$search."/i", $replace, $subject); 
  }
}
if(!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode ($str) {
		return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

function PreparePreText($text,$ff='//FF//') {
	$text = htmlspecialchars($text);
	if ($ff) { $text = str_replace($ff,'</pre><formfeed /><pre>',$text); }
	return ('<pre>'.$text.'</pre>');
}

if(!function_exists('strcode2utf')){ 
  function strcode2utf($str,$lo=true) {
	//converts all the &#nnn; and &#xhhh; in a string to Unicode
	if ($lo) { $lo = 1; } else { $lo = 0; }
	$str = preg_replace('/\&\#([0-9]+)\;/me', "code2utf('\\1',{$lo})",$str);
	$str = preg_replace('/\&\#x([0-9a-fA-F]+)\;/me', "codeHex2utf('\\1',{$lo})",$str);
	return $str;
  }
}

if(!function_exists('code2utf')){ 
  function code2utf($num,$lo=true){
	//Returns the utf string corresponding to the unicode value
	if ($num<128) {
		if ($lo) return chr($num);
		else return '&#'.$num.';';
	}
	if ($num<2048) return chr(($num>>6)+192).chr(($num&63)+128);
	if ($num<65536) return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	if ($num<2097152) return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
	return '?';
  }
}


if(!function_exists('codeHex2utf')){ 
  function codeHex2utf($hex,$lo=true){
	$num = hexdec($hex);
	if (($num<128) && !$lo) return '&#x'.$hex.';';
	return code2utf($num,$lo);
  }
}


?>