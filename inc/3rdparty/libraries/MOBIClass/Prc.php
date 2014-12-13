<?php

/**
 * Description of Prc
 *
 * @author Sander
 */
class Prc extends FileElement {
	public function __construct($settings, $records){
		parent::__construct(array(
			"title"=>new FileString(32),
			"attributes"=>new FileShort(),
			"version"=>new FileShort(),
			"creationTime"=>new FileDate(),
			"modificationTime"=>new FileDate(),
			"backupTime"=>new FileDate(),
			"modificationNumber"=>new FileInt(),
			"appInfoID"=>new FileInt(),
			"sortInfoID"=>new FileInt(),
			"prcType"=>new FileString(4),
			"creator"=>new FileString(4),
			"uniqueIDSeed"=>new FileInt(),
			"nextRecordListID"=>new FileInt(),
			"numberRecords"=>new FileShort(),
			"recordList"=>new FileElement(),
			"filler"=>new FileShort(),
			"records"=>new FileElement()
		));

		//Set values from the info block
		foreach($this->elements as $name => $val){
			if($settings->exists($name)){
				$this->get($name)->set($settings->get($name));
			}
		}
		
		$this->get("numberRecords")->set(sizeof($records));

		$i = 0;
		foreach($records as $record){
			$offset = new FileInt();
			$attr = new FileByte();
			$uniqueID = new FileTri($i);
			
			$this->elements["recordList"]->add("Rec".$i, new FileElement(array(
				"offset"=>$offset,
				"attribute"=>$attr,
				"uniqueID"=>$uniqueID
			)));
			
			$this->elements["records"]->add("Rec".$i, $record);
			$i++;
		}

		$this->updateOffsets($records);
	}

	public function getByteLength(){
		throw new Exception("Test");
	}

	public function updateOffsets($records){
		$base = $this->offsetToEntry("records");

		$i = 0;
		
		foreach($records as $record){
			$el = $this->elements["recordList"]->get("Rec".$i);
			
			$local = $this->elements["records"]->offsetToEntry("Rec".$i);
			
			$el->get("offset")->set($base+$local);

			$i++;
		}
	}

	public function save($file){
		$handle = fopen($file, "w");
		fwrite($handle, $this->serialize());
		fclose($handle);
	}

	public function output(){
		echo $this->serialize();
	}

	public function __toString(){
		$output = "Prc (".$this->getByteLength()." bytes): {\n";
		foreach($this->elements as $key=>$value){
			$output .= "\t".$key.": ".$value."\n";
		}
		$output .= "}";
		return $output;
	}
}
?>
