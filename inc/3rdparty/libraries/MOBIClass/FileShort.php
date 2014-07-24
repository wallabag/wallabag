<?php

/**
 * Description of FileShort
 *
 * @author Sander
 */
class FileShort extends FileObject {
	private $data;

	/**
	 * Make a short to be stored in a file
	 * @param short $n
	 */
	public function __construct($n = 0){
		parent::__construct(2);
		$this->set($n);
	}

	public function get(){
		return $this->data;
	}

	public function set($value){
		$this->data = intval($value) & 0xFFFF;
	}

	public function serialize() {
		return $this->shortToString($this->data);
	}

	public function unserialize($data) {
		__construct($this->toInt($data));
	}


	public function __toString(){
		return "FileShort: {".$this->shortAsString($this->data)."}";
	}
}
?>
