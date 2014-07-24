<?php
/**
 * A Record of a PDB file
 *
 * @author Sander
 */
class Record extends FileObject {
	/**
	 * Data in the record
	 * @var string
	 */
	private $data;
	/**
	 * Length of the record
	 * @var int
	 */
	private $length;

	/**
	 * Create a record
	 * @param string $data Data contained in the record
	 * @param int $length Length of the record (if set to -1,
	 * the length of $data will be taken)
	 */
	public function __construct($data = "", $length = -1){
		$this->data = $data;
		if($length >= 0){
			$this->length = $length;
		}else{
			$this->length = strlen($data);
		}
	}

	public function compress($compression_method){
		switch($compression_method){
			case NO_COMPRESSION:
				//Finished!
				break;
			case PALMDOC_COMPRESSION:
				throw new Exception("Not implemented yet");
				break;
			case HUFF:
				throw new Exception("Not implemented yet");
				break;
			default:
				throw new Exception("Invalid argument");
		}
	}

	public function getByteLength(){
		return $this->getLength();
	}

	/**
	 * Get the length of the record
	 * @return int Length of the data
	 */
	public function getLength(){
		return $this->length;
	}

	/**
	 * Get the data contained in the record
	 * @return string Data contained in the record
	 */
	public function get(){
		return $this->data;
	}

	/**
	 * Set the data contained in the record
	 * @param string $value Data contained in the record
	 */
	public function set($value){
		$this->data = $value;
	}
	
    public function serialize(){
        return $this->data;
    }
    public function unserialize($data){
        __construct($data);
    }
	
	public function __toString() {
		$toShow = $this->data;
		if(strlen($this->data) > 103){
			$toShow = substr($this->data, 0, 100)."...";
		}
		$out = "Record: {\n";
		$out .= "\t".htmlspecialchars($toShow)."\n";
		$out .= "}";
		return $out;
	}
}
?>
