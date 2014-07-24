<?php

/**
 * Description of FileTri
 *
 * @author Sander
 */
class FileTri extends FileObject {
	private $data;

	/**
	 * Make a tri-byte to be stored in a file
	 * @param tri-byte $n
	 */
	public function __construct($n = 0){
		parent::__construct(3);
		$this->set($n);
	}

	public function get(){
		return $this->data;
	}

	public function set($value){
		$this->data = intval($value) & 0xFFFFFF;
	}

	public function serialize() {
		return $this->triToString($this->data);
	}

	public function unserialize($data) {
		__construct($this->toInt($data));
	}


	public function __toString(){
		return "FileTri: {".$this->triAsString($this->data)."}";
	}
}
?>
