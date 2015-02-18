<?php

/**
 * Description of FileElement
 *
 * @author Sander
 */
class FileElement {
	/**
	 * @var FileObject
	 */
	public $elements;

	/**
	 * Make a record to be stored in a file
	 * @param Record $record
	 */
	public function __construct($elements = array()){
		$this->elements = $elements;
	}

	public function getByteLength(){
		return $this->getLength();
	}

	public function getLength(){
		$total = 0;
		foreach($this->elements as $val){
			$total += $val->getByteLength();
		}
		return $total;
	}

	public function offsetToEntry($name){
		$pos = 0;
		foreach($this->elements as $key=>$value){
			if($name == $key){
				break;
			}
			$pos += $value->getByteLength();
		}
		return $pos;
	}

	public function exists($key){
		return isset($this->elements[$key]);
	}
	/**
	 * @param string $key
	 * @return FileObject
	 */
	public function get($key){
		return $this->elements[$key];
	}

	/**
	 * @param string $key
	 * @param FileObject $value
	 */
	public function set($key, $value){
		$this->elements[$key] = $value;
	}

	public function add($key, $value){
		$this->elements[$key] = $value;
	}

	public function serialize() {
		$result = "";
		foreach($this->elements as $val){
			$result .= $val->serialize();
		}
		return $result;
	}

	public function unserialize($data) {
		//TODO: If reading is needed -> way more complex
	}

	public function __toString(){
		$output = "FileElement (".$this->getByteLength()." bytes): {\n";
		foreach($this->elements as $key=>$value){
			$output .= "\t".$key.": ".$value."\n";
		}
		$output .= "}";
		return $output;
	}
}
?>
