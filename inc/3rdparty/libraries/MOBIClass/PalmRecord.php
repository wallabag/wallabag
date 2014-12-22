<?php
/**
 * A Record of a PDB file
 *
 * @author Sander
 */
class PalmRecord extends FileObject {
	/**
	 * @var FileElement
	 */
	private $elements;

	public function __construct($settings, $records, $textRecords, $textLength, $images){
		$this->elements = new FileElement(array(
			"compression"=>new FileShort(),
			"unused"=>new FileShort(),
			"textLength"=>new FileInt(),
			"recordCount"=>new FileShort(),
			"recordSize"=>new FileShort(),
			"encryptionType"=>new FileShort(),
			"unused2"=>new FileShort(),
			//MOBI Header
			"mobiIdentifier"=>new FileString("MOBI", 4),
			"mobiHeaderLength"=>new FileInt(),
			"mobiType"=>new FileInt(),
			"textEncoding"=>new FileInt(),
			"uniqueID"=>new FileInt(),
			"fileVersion"=>new FileInt(),
			"reserved"=>new FileString(40),
			"firstNonBookIndex"=>new FileInt(),
			"fullNameOffset"=>new FileInt(),
			"fullNameLength"=>new FileInt(),
			"locale"=>new FileInt(),
			"inputLanguage"=>new FileInt(),
			"outputLanguage"=>new FileInt(),
			"minimumVersion"=>new FileInt(),
			"firstImageIndex"=>new FileInt(),
			"huffmanRecordOffset"=>new FileInt(),
			"huffmanRecordCount"=>new FileInt(),
			"unused3"=>new FileString(8),
			"exthFlags"=>new FileInt(0x40),
			"unknown"=>new FileString(32),
			"drmOffset"=>new FileInt(0xFFFFFFFF),
			"drmCount"=>new FileShort(0xFFFFFFFF),
			"drmSize"=>new FileShort(),
			"drmFlags"=>new FileInt(),
			"mobiFiller"=>new FileString(72),
			//EXTH Header
			"exthIdentifier"=>new FileString("EXTH", 4),
			"exthHeaderLength"=>new FileInt(),
			"exthRecordCount"=>new FileInt(),
			"exthRecords"=>new FileElement(),
			"exthPadding"=>new FileString(),
			//"fullNamePadding"=>new FileString(100),
			"fullName"=>new FileString()
				));

		//Set values from the info block
		foreach($settings->values as $name => $val){
			//echo $name.", ";
			if($this->elements->exists($name)){
				$this->elements->get($name)->set($settings->get($name));
			}
		}

		$els = $settings->values;

		$exthElems = new FileElement();
		$i = 0;
		$l = 0;
		foreach($els as $name=>$val){
			$type = EXTHHelper::textToType($name);
			if($type !== false){
				$type = new FileInt($type);
				$length = new FileInt(8+strlen($val));
				$data = new FileString($val);
				$l += 8+strlen($val);
				$exthElems->add("type".$i, $type);
				$exthElems->add("length".$i, $length);
				$exthElems->add("data".$i, $data);
				$i++;
			}
		}

		if($images > 0){
			$this->elements->get("firstImageIndex")->set($textRecords+1);
		}
		$this->elements->get("firstNonBookIndex")->set($textRecords+2+$images);
		$this->elements->get("reserved")->set(str_pad("", 40, chr(255), STR_PAD_RIGHT));
		$this->elements->get("exthRecordCount")->set($i);
		$this->elements->set("exthRecords", $exthElems);
		$pad = $l%4;
		$pad = (4-$pad)%4;
		$this->elements->get("exthPadding")->set(str_pad("", $pad, "\0", STR_PAD_RIGHT));
		$this->elements->get("exthHeaderLength")->set(12+$l+$pad);


		$this->elements->get("recordCount")->set($textRecords);

		$this->elements->get("fullNameOffset")->set($this->elements->offsetToEntry("fullName"));
		$this->elements->get("fullNameLength")->set(strlen($settings->get("title")));
		$this->elements->get("fullName")->set($settings->get("title"));
		$this->elements->get("textLength")->set($textLength);
	}

	public function getByteLength(){
		return $this->getLength();
	}

	public function getLength(){
		return $this->elements->getByteLength();
	}

	public function get(){
		return $this;
	}

	public function set($elements){
		throw new Exception("Unallowed set");
	}

	public function serialize() {
		return $this->elements->serialize();
	}

	public function unserialize($data) {
		$this->elements->unserialize($data);
	}

	public function __toString(){
		$output = "PalmDoc Record (".$this->getByteLength()." bytes):\n";
		$output .= $this->elements;
		return $output;
	}
}
?>
