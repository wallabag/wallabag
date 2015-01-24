<?php
require_once(dirname(__FILE__)."/../readability/Readability.php");
require_once(dirname(__FILE__).'/CharacterEntities.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/ContentProvider.php');
require_once(dirname(__FILE__).'/MultipleFileHandler.php');
require_once(dirname(__FILE__)."/downloaders/FanFictionNet.php");
require_once(dirname(__FILE__).'/EXTHHelper.php');
require_once(dirname(__FILE__).'/FileObject.php');
require_once(dirname(__FILE__).'/FileByte.php');
require_once(dirname(__FILE__).'/FileDate.php');
require_once(dirname(__FILE__).'/FileElement.php');
require_once(dirname(__FILE__).'/FileInt.php');
require_once(dirname(__FILE__).'/FileRecord.php');
require_once(dirname(__FILE__).'/FileShort.php');
require_once(dirname(__FILE__).'/FileString.php');
require_once(dirname(__FILE__).'/FileTri.php');
require_once(dirname(__FILE__).'/Http.php');
require_once(dirname(__FILE__).'/http_build_url.php');
require_once(dirname(__FILE__).'/ImageHandler.php');
require_once(dirname(__FILE__).'/MOBIFile.php');
require_once(dirname(__FILE__).'/OnlineArticle.php');
require_once(dirname(__FILE__).'/PalmRecord.php');
require_once(dirname(__FILE__).'/Prc.php');
require_once(dirname(__FILE__).'/PreprocessedArticle.php');
require_once(dirname(__FILE__).'/RecognizeURL.php');
require_once(dirname(__FILE__).'/Record.php');
require_once(dirname(__FILE__).'/RecordFactory.php');
require_once(dirname(__FILE__).'/Settings.php');

/**
 * Description of MOBI.
 *
 * Usage:
 * include("MOBIClass/MOBI.php");
 *
 * $mobi = new MOBI();
 *
 * //Then use one of the following ways to prepare information (it should be in the form of valid html)
 * $mobi->setInternetSource($url);		//Load URL, the result will be cleaned using a Readability port
 * $mobi->setFileSource($file);			//Load a local file without any extra changes
 * $mobi->setData($data);				//Load data
 *
 * //If you want, you can set some optional settings (see Settings.php for all recognized settings)
 * $options = array(
 *		"title"=>"Insert title here",
 *		"author"=>"Author"
 * );
 * $mobi->setOptions($options);
 *
 * //Then there are two ways to output it:
 * $mobi->save($file);					//Save the file locally
 * $mobi->download($name);				//Let the client download the file, make sure the page
 *										//that calls it doesn't output anything, otherwise it might
 *										//conflict with the download. $name contains the file name,
 *										//usually something like "title.mobi" (where the title should
 *										//be cleaned so as not to contain illegal characters).
 *
 *
 * @author Sander Kromwijk
 */
class MOBI {
	private $source = false;
	private $images = array();
	private $optional = array();
	private $imgCounter = 0;
	private $debug = false;
	private $prc = false;
	
	public function __construct(){

	}

	public function getTitle(){
		if(isset($this->optional["title"])){
			return $this->optional["title"];
		}
		return false;
	}
	
	/**
	 * Set a content provider as source
	 * @param ContentProvider $content Content Provider to use
	 */
	public function setContentProvider($content){
		$this->setOptions($content->getMetaData());
		$this->setImages($content->getImages());
		$this->setData($content->getTextData());
	}

	/**
	 * Set a local file as source
	 * @param string $file Path to the file
	 */
	public function setFileSource($file){
		$this->setData(file_get_contents($file));
	}

	/**
	 * Set the data to use
	 * @param string $data Data to put in the file
	 */
	public function setData($data){
		//$data = utf8_encode($data);
		$data = CharacterEntities::convert($data);
		//$data = utf8_decode($data);
		//$this->source = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data);
		$this->source = $data;
		$this->prc = false;
	}

	/**
	 * Set the images to use
	 * @param array $data Data to put in the file
	 */
	public function setImages($data){
		$this->images = $data;
		$this->prc = false;
	}

	/**
	 * Set options, usually for things like titles, authors, etc...
	 * @param array $options Options to set
	 */
	public function setOptions($options){
		$this->optional = $options;
		$this->prc = false;
	}

	/**
	 * Prepare the prc file
	 * @return Prc The file that can be used to be saved/downloaded
	 */
	private function preparePRC(){
		if($this->source === false){
			throw new Exception("No data set");
		}
		if($this->prc !== false) return $this->prc;

		$data = $this->source;
		$len = strlen($data);
		
		$settings = new Settings($this->optional);
		$rec = new RecordFactory($settings);
		$dataRecords = $rec->createRecords($data);
		$nRecords = sizeof($dataRecords);
		$mobiHeader = new PalmRecord($settings, $dataRecords, $nRecords, $len, sizeof($this->images));
		array_unshift($dataRecords, $mobiHeader);
		$dataRecords = array_merge($dataRecords, $this->images);
		$dataRecords[] = $rec->createFLISRecord();
		$dataRecords[] = $rec->createFCISRecord($len);
		$dataRecords[] = $rec->createEOFRecord();
		$this->prc = new Prc($settings, $dataRecords);
		return $this->prc;
	}

	/**
	 * Save the file locally
	 * @param string $filename Path to save the file
	 */
	public function save($filename){
		$prc = $this->preparePRC();
		$prc->save($filename);
	}

	/**
	 * Let the client download the file. Warning! No data should be
	 * outputted before or after.
	 * @param string $name Name used for download, usually "title.mobi"
	 */
	public function download($name){
		$prc = $this->preparePRC();
		$data = $prc->serialize();
		$length = strlen($data);

		if($this->debug) return;		//In debug mode, don't start the download

		header("Content-Type: application/x-mobipocket-ebook");
		header("Content-Disposition: attachment; filename=\"".$name."\"");
		header("Content-Transfer-Encoding: binary");
		header("Accept-Ranges: bytes");
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Content-Length: ".$length);
		
		echo $data;
		//Finished!
	}
	
}
?>
