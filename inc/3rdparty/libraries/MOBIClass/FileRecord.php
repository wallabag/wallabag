<?php

/**
 * Description of FileRecord
 *
 * @author Sander
 */
class FileRecord extends FileObject {
	/**
	 * @var Record
	 */
	private $record;

	/**
	 * Make a record to be stored in a file
	 * @param Record $record
	 */
	public function __construct($record){
		$this->record = $record;
	}

	public function getByteLength(){
		return $this->getLength();
	}

	public function getLength(){
		return $this->record->getLength();
	}

	public function get(){
		return $this->record;
	}

	public function set($record){
		$this->record = $record;
	}

	public function serialize() {
		return $this->record->serialize();
	}

	public function unserialize($data) {
		__construct($this->record->unserialize($data));
	}
}
?>
