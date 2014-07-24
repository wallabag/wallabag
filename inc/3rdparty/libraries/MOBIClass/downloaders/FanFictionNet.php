<?php

/**
 * Description of FanFictionNet
 *
 * @author Sander
 */
class FanFictionNet extends MultipleFileHandler {
	private static $prefix = "http://www.fanfiction.net/s/";
	private $downloadedMetadata = false;
	private $id = 0;
	private $chapterCount = -1;
	
	public function  __construct($url) {
		$ending = substr($url, strlen(self::$prefix));
		$this->id = intval(substr($ending, 0, strpos($ending, "/")));

		for($i = 1; $i <= max(1, $this->chapterCount); $i++){
			$this->addChapter($i);
		}
	}

	private function addChapter($n){
		$doc = new DOMDocument();
		$file = Http::Request(self::$prefix.$this->id."/".$n."/");
		@$doc->loadHTML($file) or die($file);
		
		if(!$this->downloadedMetadata){
			$this->loadMetadata($doc);
			$this->downloadedMetadata = true;
		}
		if($this->chapterCount < 0){
			$this->chapterCount = $this->getNumberChapters($doc);

			if($this->chapterCount > 4){
				die("Too many files to download, don't use php for this!");
			}
		}

		$textEl = $doc->getElementById("storytext");
		if($textEl == null) die("Error: ".$doc->saveHTML());
		$horizontalRulebars = $doc->getElementsByTagName('hr');
		/**
		 * @var DOMNode
		 */
		$hr;
		foreach($horizontalRulebars as $hr) {
			$hr->setAttribute("size", null);
			$hr->setAttribute("noshade", null);
		}
		$text = $this->innerHtml($textEl);
		
		$title = "";
		$selects = $doc->getElementsByTagName('select');
		foreach($selects as $select) {
			if($select->hasAttribute("name") && $select->getAttribute("name") == "chapter"){
				$options = $select->getElementsByTagName("option");

				$test = $n.". ";
				foreach($options as $option){
					$val = $option->nodeValue;
					if(substr($val, 0, strlen($test)) == $test){
						$title = substr($val, strlen($test));
						break;
					}
				}
				break;
			}
		}
		$this->addPage($text, $title);
	}

	private function getNumberChapters($doc){
		$selects = $doc->getElementsByTagName('select');
		foreach($selects as $select) {
			if($select->hasAttribute("name") && $select->getAttribute("name") == "chapter"){
				$options = $select->getElementsByTagName("option");

				$count = $options->length;
				return $count;
			}
		}
	}

	private function loadMetadata($doc){
		//Author
		$links = $doc->getElementsByTagName('a');
		foreach($links as $link) {
			if($link == null){
				var_dump($link);
			}
			if($link->hasAttribute("href") && substr($link->getAttribute("href"), 0, 3) == "/u/"){
				$this->setMetadata("author", $link->nodeValue);
			}
		}
		//Title
		/*
		$links = $doc->getElementsByTagName('link');
		foreach($links as $link) {
			if($link->hasAttribute("rel") && $link->getAttribute("rel") == "canonical"){
				$url = $link->getAttribute("href");
				$title = str_replace("_", " ", substr($url, strrpos($url, "/")+1));
				$this->setMetadata("title", $title);
			}
		}*/

		//TODO: Find a more reliable way to extract the title
		$title = $doc->getElementsByTagName("b")->item(0)->nodeValue;
		$this->setMetadata("title", $title);
	}

	private function innerHtml($node){
		$doc = new DOMDocument();
		foreach ($node->childNodes as $child)
			$doc->appendChild($doc->importNode($child, true));
		
		return $doc->saveHTML();
	}

	public static function Matches($url){
		//TODO: Implement with regex
		return strpos($url, self::$prefix) !== false;
	}
}
?>
