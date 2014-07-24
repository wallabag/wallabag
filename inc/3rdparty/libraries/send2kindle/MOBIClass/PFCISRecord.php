<?php
/**
 * A Record of a End file
 *
 * @author Pratyush
 */
 class PFCISRecord extends FileObject {
 /**
	 * @var FileElement
	 */
	private $elements;

	public function __construct($leng){
	$this->elements = new FileElement(array(
			"offset0"=>new FileString("FCIS", 4),	//FCIS
			"offset4"=>new FileInt(0x014),
			"offset8"=>new FileInt(0x10),
			"offset12"=>new FileInt(0x01),
			"offset16"=>new FileInt(),
			"offset20"=>new FileInt($leng),
			"offset24"=>new FileInt(),
			"offset28"=>new FileInt(0x20),
			"offset32"=>new FileInt(0x08),
			"offset36"=>new FileShort(0x01),
			"offset38"=>new FileShort(0x01),
			"offset40"=>new FileInt()
				));
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