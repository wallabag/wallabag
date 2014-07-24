<?php

/**
 * Description of FileObject
 *
 * @author Sander
 */
abstract class FileObject {
	private $byteLength = -1;

	public function __construct($byteLength = -1){
		$this->byteLength = $byteLength;
	}

	public function getByteLength(){
		if($this->byteLength >= 0){
			return $this->byteLength;
		}
		return $this->getLength();
	}

	public function getLength(){
		throw new Exception("Sub-class needs to implement this if it doesn't have a fixed length");
	}

	/**
	 * Convert a string to byte format (maximum 4 bytes)
	 * @param string $string Input string
	 * @return int Output integer
	 */
	public function toInt($string){
		$out = 0;
		for($i = 0, $len = min(4, strlen($string)); $i < $len; $i++){
			$out = $out | (ord($string[$i]) << (($len-$i-1)*8));
		}
		return $out;
	}

	/**
	 * Convert a byte (stored in an integer) to a string
	 * @param byte $int
	 * @return string
	 */
	public function byteToString($int){
		return $this->toString($int, 1);
	}

	/**
	 * Convert a byte (stored in an integer) to a string
	 * @param byte $int
	 * @return string
	 */
	public function byteAsString($int){
		return $this->asString($int, 1);
	}

	/**
	 * Convert a short (stored in an integer) to a string
	 * @param short $int
	 * @return string
	 */
	public function shortToString($int){
		return $this->toString($int, 2);
	}

	/**
	 * Convert a short (stored in an integer) to a string
	 * @param short $int
	 * @return string
	 */
	public function shortAsString($int){
		return $this->asString($int, 2);
	}

	/**
	 * Convert a tri-byte (stored in an integer) to a string
	 * @param tri-byte $int
	 * @return string
	 */
	public function triToString($int){
		return $this->toString($int, 3);
	}

	/**
	 * Convert a tri-byte (stored in an integer) to a string
	 * @param tri-byte $int
	 * @return string
	 */
	public function triAsString($int){
		return $this->asString($int, 3);
	}

	/**
	 * Convert an integer to a string
	 * @param int $int
	 * @return string
	 */
	public function intToString($int){
		return $this->toString($int, 4);
	}

	/**
	 * Convert an integer to a string
	 * @param int $int
	 * @return string
	 */
	public function intAsString($int){
		return $this->asString($int, 4);
	}

	/**
	 * Convert a number of n bytes to a string
	 * @param int $int Number that should be converted
	 * @param int $size Number of bytes to convert
	 * @return string Output string
	 */
	private function toString($int, $size){
		$out = "";
		for($i = 0; $i < $size; $i++){
			$out = chr($int & 0xFF).$out;
			$int = $int >> 8;
		}
		return $out;
	}

	/**
	 * Convert a number of n bytes to a string
	 * @param int $int Number that should be converted
	 * @param int $size Number of bytes to convert
	 * @return string Output string
	 */
	private function asString($int, $size){
		$out = "";
		for($i = 0; $i < $size; $i++){
			if($i > 0) $out = " ".$out;
			$byte = dechex($int & 0xFF);
			if(strlen($byte) == 1) $byte = "0".$byte;
			$out = $byte.$out;
			$int = $int >> 8;
		}
		return $out;
	}

	/**
	 * Get the value
	 * @return mixed Value to get
	 */
    abstract public function get();

	/**
	 * Set the value
	 * @return mixed Value to set
	 */
    abstract public function set($value);

	/**
	 * Serialize the object
	 * @return string String representation
	 */
    abstract public function serialize();

	/**
	 * Unserialize the object
	 * @param string $data String representation
	 */
    abstract public function unserialize($data);
}
?>
