<?php

/**
 * Description of FileByte
 *
 * @author Sander
 */
class FileByte extends FileObject {
	private $data;

	/**
	 * Make a short to be stored in a file
	 * @param short $n
	 */
	public function __construct($n = 0){
		parent::__construct(1);
		$this->set($n);
	}

	public function get(){
		return $this->data;
	}

	public function set($value){
		$this->data = intval($value) & 0xFF;
	}

	public function serialize() {
		return $this->byteToString($this->data);
	}

	public function unserialize($data) {
		__construct($this->toInt($data));
	}


	public function __toString(){
		return "FileByte: {".$this->byteAsString($this->data)."}";
	}
}
?>
