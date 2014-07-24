<?php

/**
 * Description of MultipleFileHandler
 *
 * @author Sander
 */
abstract class MultipleFileHandler extends ContentProvider {
	/**
	 * @var array
	 */
	private $files = array();
	/**
	 * @var array
	 */
	private $images = array();
	/**
	 * @var array
	 */
	private $metadata = array();

	private $toc = array();

	/**
	 * Add a page to the file
	 * @param string $contents Contents of the chapter/page
	 * @param string $title Optional, title of the chapter/page. Will automatically add a h2
	 * before the contents
	 */
	public function addPage($contents, $title = ""){
		if($title != ""){
			//TODO: Add in TOC (and add a way of generating it
			$contents = "<h2>".$title."</h2>".$contents."<mbp:pagebreak>";
		}
		$pos = 0;

		if(sizeof($this->toc) > 0){
			$lastToc = $this->toc[sizeof($this->toc)-1];
			$lastFile = $this->files[sizeof($this->files)-1];
			$pos = $lastToc["pos"] + strlen($lastFile) + 1;
		}
		
		$this->files[] = $contents;
		$this->toc[] = array("title"=>$title, "pos"=>$pos);
	}

	/**
	 * Add an image to the file
	 * @param string $imageContents Data string containing the binary data of the image
	 * @return int The reference of the image
	 */
	public function addImage($imageContents){
		$this->images[] = $imageContents;
		return sizeof($this->images)-1;
	}

	/**
	 * Add an image to the file
	 * @param string $url Url to the image
	 * @return int The reference of the image, false if the image couldn't be downloaded
	 */
	public function addImageFromUrl($url){
		$image = ImageHandler::DownloadImage($url);

		if($image === false) return false;
		return $this->addImage($image);
	}

	/**
	 * Set the metadata
	 * @param string $key Key
	 * @param string $value Value
	 */
	public function setMetadata($key, $value){
		$this->metadata[$key] = $value;
	}

	/**
	 * Get the text data to be integrated in the MOBI file
	 * @return string
	 */
	public function getTextData(){
		$data = implode("\n", $this->files);
		$begin = "<html><head><guide><reference title='CONTENT' type='toc' filepos=0000000000 /></guide></head><body>";
		$beforeTOC = $begin.$data;

		$tocPos = strlen($beforeTOC);

		$toc = $this->generateTOC(strlen($begin));

		$customBegin = "<html><head><guide><reference title='CONTENT' type='toc' filepos=".$this->forceLength($tocPos, 10)." /></guide></head><body>";
		$data = $customBegin.$data.$toc."</body></html>";
		return $data;
	}

	public function forceLength($n, $l){
		$str = $n."";
		$cur = strlen($str);
		while($cur < $l){
			$str = "0".$str;
			$cur++;
		}
		return $str;
	}

	public function generateTOC($base = 0){
		$toc = "<h2>Contents</h2>";
		$toc .= "<blockquote><table summary='Table of Contents'><b><col/><col/><tbody>";
		for($i = 0, $len = sizeof($this->toc); $i < $len; $i++){
			$entry = $this->toc[$i];
			$position = $entry["pos"]+$base;
			$toc .= "<tr><td>".($i+1).".</td><td><a filepos=".$position.">".$entry["title"]."</a></td></tr>";
		}
		$toc .= "</tbody></b></table></blockquote>";

		return $toc;
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
	
}
?>
