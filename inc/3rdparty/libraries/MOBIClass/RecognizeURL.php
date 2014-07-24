<?php

/**
 * Description of RecognizeURL
 *
 * @author Sander
 */
class RecognizeURL {
	public static function GetContentHandler($url){
		if(FanFictionNet::Matches($url)){
			return new FanFictionNet($url);
		}
		return null;
	}
}
?>
