<?php
/**
 * A Record of a End file
 *
 * @author Pratyush
 */
 class PEOFRecord extends FileObject {
 /**
	 * @var FileElement
	 */
	private $elements;

	public function __construct($leng){
	$this->elements = new FileElement(array(
			
			"offset44"=>new FileInt(0xe98e0d0a)
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