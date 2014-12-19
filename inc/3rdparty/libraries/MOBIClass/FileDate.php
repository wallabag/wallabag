<?php

/**
 * Description of FileDate
 *
 * @author Sander
 */
class FileDate extends FileObject {
	private $data;

	/**
	 * Make an integer to be stored in a file
	 * @param int $n
	 */
	public function __construct($n = 0){
		parent::__construct(4);
		$this->set($n);
	}

	public function get(){
		return $this->data;
	}

	public function set($value){
		$this->data = intval($value);
	}
	
	public function serialize() {
		return $this->intToString($this->data);
	}

	public function unserialize($data) {
		__construct($this->toInt($data));
	}

	public function __toString(){
		return "FileDate: {".(date("r", $this->data-94694400))."}";
	}
}
?>
