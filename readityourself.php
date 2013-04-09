<?php
header('Content-type:text/html; charset=utf-8');

setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set('Europe/Paris');

require_once dirname(__FILE__).'/inc/Readability.php';
require_once dirname(__FILE__).'/inc/Encoding.php';
require_once dirname(__FILE__).'/inc/rain.tpl.class.php';
include dirname(__FILE__).'/inc/functions.php';

if(isset($_GET['url']) && $_GET['url'] != null && trim($_GET['url']) != "") {
	// get url link
	if(strlen(trim($_GET['url'])) > 2048) {
		echo "Error URL is too large !!";
	} else {
		$url = trim($_GET['url']);

		// decode it
		$url = html_entity_decode($url);

		// if url use https protocol change it to http
		if (!preg_match('!^https?://!i', $url)) $url = 'http://'.$url;

		// convert page to utf-8
		$html = Encoding::toUTF8(get_external_file($url,15));

		if(isset($html) and strlen($html) > 0) {

			// send result to readability library
			$r = new Readability($html, $url);

			if($r->init()) {
				generate_page($url,$r->articleTitle->innerHTML,$r->articleContent->innerHTML);
			} else {
				// return data into an iframe
				echo "<iframe id='readabilityframe'>".$html."</iframe>";
			}
		} else {
			echo "Error unable to get link : ".$url;
		}
	}
}