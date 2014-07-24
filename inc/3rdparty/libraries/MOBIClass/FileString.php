<?php

/**
 * Description of FileString
 *
 * @author Sander
 */
class FileString extends FileObject {
	private $forcedLength;
	private $data;

	/**
	 * Make a string to be stored in a file
	 * @param string|int $first Optional, if it is a string, it will be the contents,
	 * if it is a number, it will set the forced length.
	 * @param int $second Optional, will set the forced length. Can only be used when the
	 * first argument is contents.
	 */
	public function __construct($first = null, $second = null){
		$this->forcedLength = -1;
		$this->data = "";
		
		if($second != null){
			$this->data = $first;
			$this->forcedLength = $second;
		}else if($first != null){
			if(is_string($first)){
				$this->data = $first;
			}else{
				$this->forcedLength = $first;
			}
		}
	}

	public function getByteLength(){
		return $this->getLength();
	}

	public function getLength(){
		if($this->forcedLength >= 0){
			return $this->forcedLength;
		}
		return strlen($this->data);
	}

	public function get(){
		return $this->data;
	}

	public function set($value){
		$this->data = $value;
	}

	public function serialize() {
		$output = $this->data;
		$curLength = strlen($output);

		if($this->forcedLength >= 0){
			if($this->forcedLength > $curLength){
				return str_pad($output, $this->forcedLength, "\0", STR_PAD_RIGHT);
			}elseif($this->forcedLength == $curLength){
				return $output;
			}else{
				return substr($output, 0, $this->forcedLength);
			}
		}
		return $output;
	}

	public function unserialize($data) {
		__construct($data);
	}

	public function __toString(){
		$out = "FileString";
		if($this->forcedLength >= 0){
			$out .= " ".$this->forcedLength;
		}
		$out .= ": {\"".str_replace(array(" ", "\0"), "&nbsp;", $this->serialize())."\"}";
		return $out;
	}
}
?>
