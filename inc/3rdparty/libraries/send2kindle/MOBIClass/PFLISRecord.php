<?php
/**
 * A Record of a End file
 *
 * @author Pratyush
 */
 class PFLISRecord extends FileObject {
 /**
	 * @var FileElement
	 */
	private $elements;

	public function __construct($leng){
	$this->elements = new FileElement(array(
			"offsetL0"=>new FileString("FLIS", 4),	//FLIS
			"offsetL4"=>new FileInt(0x08),
			"offsetL8"=>new FileShort(0x41),
			"offsetL10"=>new FileString(6),
			"offsetL16"=>new FileInt(0xFFFFFFFF),
			"offsetL20"=>new FileShort(0x01),		
			"offsetL22"=>new FileShort(0x03),
			"offsetL24"=>new FileInt(0x03),
			"offsetL28"=>new FileInt(0x01),
			"offsetL32"=>new FileInt(0xFFFFFFFF)
	
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