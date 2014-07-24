<?php

/**
 * Description of OnlineArticle
 *
 * @author Sander
 */
class PreprocessedArticle extends ContentProvider {
	private $text;
	private $images;
	private $metadata = array();
	private $imgCounter = 0;

	public function  __construct($textData, $imageLinks, $metadata) {
		$this->text = $textData;
		$this->metadata = $metadata;

		$this->images = $this->downloadImages($imageLinks);
	}

	/**
	 * Create a Preprocessed article from a json string
	 * @param string $json JSON data. Should be of the following format:
	 * {"text": "TEXT", "images: ["imageURL1", "imageURL2"], "metadata": {"key": "value"}}
	 *
	 * Note: Any image tags should have the recindex attribute set to the appropriate index (the
	 * same index as the image in the array)
	 * @return PreprocessedArticle The generated preprocessed array
	 */
	static public function CreateFromJson($json){
		$data = json_decode($json);
		return new PreprocessedArticle($data["text"], $data["images"], $data["metadata"]);
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
	private function downloadImages($links){
		$images = array();
		foreach($links as $link) {
			$imgFile = @imagecreatefromstring(Http::Request($link));

			if($imgFile === false){
				$imgFile = @imagecreate(1, 1);
				$black = @imagecolorallocate($imgFile, 255, 255, 255);
			}
			if($imgFile !== false){
				@imagefilter($imgFile, IMG_FILTER_GRAYSCALE);

				ob_start();
				@imagejpeg($imgFile);
				$image = ob_get_contents();
				ob_end_clean();

				$images[$this->imgCounter] = new FileRecord(new Record($image));
				imagedestroy($imgFile);
				
				$this->imgCounter++;
			}
		}

		return $images;
	}
}
?>
