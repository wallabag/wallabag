<?php

/**
 * Description of OnlineArticle
 *
 * @author Sander
 */
class OnlineArticle extends ContentProvider {
	private $text;
	private $images;
	private $metadata = array();
	private $imgCounter = 0;

	public function  __construct($url) {
		if (!preg_match('!^https?://!i', $url)) $url = 'http://'.$url;

		$data = Http::Request($url);
		//$enc = mb_detect_encoding($str, "UTF-8,ISO-8859-1,ASCII");
		$html = mb_convert_encoding($data, "UTF-8", "UTF-8,ISO-8859-1,ASCII");
		//$html = utf8_encode($html);
		$r = new Readability($html, $url);
		$r->init();
		if(!isset($this->metadata["title"])){
			$this->metadata["title"] = CharacterEntities::convert(strip_tags($r->getTitle()->innerHTML));
		}
		if(!isset($this->metadata["author"])){
			$parts = parse_url($url);
			$this->metadata["author"] = $parts["host"];
		}

		$article = $r->getContent()->innerHTML;
		if(substr($article, 0, 5) == "<body"){
			$article = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/></head>".$article."</html>";
		}else{
			$article = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/></head><body>".$article."</body></html>";
		}
		$doc = new DOMDocument();
		@$doc->loadHTML($article) or die($article);
		$doc->normalizeDocument();

		$this->images = $this->handleImages($doc, $url);
		$this->text = $doc->saveHTML();
	}

	/**
	 * Get the text data to be integrated in the MOBI file
	 * @return string
	 */
	public function getTextData(){
		return $this->text;
	}
	/**
	 * Get the images (an array containing the jpeg data). Array entry 0 will
	 * correspond to image record 0.
	 * @return array
	 */
	public function getImages(){
		return $this->images;
	}
	/**
	 * Get the metadata in the form of a hashtable (for example, title or author).
	 * @return array
	 */
	public function getMetaData(){
		return $this->metadata;
	}
	/**
	 *
	 * @param DOMElement $dom
	 * @return array
	 */
	private function handleImages($dom, $url){
		$images = array();

		$parts = parse_url($url);

		$savedImages = array();

		$imgElements = $dom->getElementsByTagName('img');
		foreach($imgElements as $img) {
			$src = $img->getAttribute("src");
			
			$is_root = false;
			if(substr($src, 0, 1) == "/"){
				$is_root = true;
			}
			
			$parsed = parse_url($src);

			if(!isset($parsed["host"])){
				if($is_root){
					$src = http_build_url($url, $parsed, HTTP_URL_REPLACE);
				}else{
					$src = http_build_url($url, $parsed, HTTP_URL_JOIN_PATH);
				}
			}
			$img->setAttribute("src", "");
			if(isset($savedImages[$src])){
				$img->setAttribute("recindex", $savedImages[$src]);
			}else{
				$image = ImageHandler::DownloadImage($src);
				
				if($image !== false){
					$images[$this->imgCounter] = new FileRecord(new Record($image));

					$img->setAttribute("recindex", $this->imgCounter);
					$savedImages[$src] = $this->imgCounter;
					$this->imgCounter++;
				}
			}
		}

		return $images;
	}
}
?>
