<?php

/**
 * Description of Settings
 *
 * @author Sander
 */
class Settings {
	/**
	 * Values of the settings
	 * @var array 
	 */
	public $values;

	/**
	 * Construct a Settings object with the default settings. If necessary,
	 * those settings can be extended with additional settings
	 * @param array $additionalSettings Additional settings to add (should
	 * be added with a key/value pair format.
	 */
	public function  __construct($additionalSettings = array()) {
		// Most values shouldn't be changed (the result will be an invalid file)
		$this->values = array(
			"attributes"=>0,
			"version"=>0,
			"creationTime"=>time()+94694400,
			"modificationTime"=>time()+94694400,
			"backupTime"=>0,
			"modificationNumber"=>0,
			"appInfoID"=>0,
			"sortInfoID"=>0,
			"prcType"=>"BOOK",
			"creator"=>"MOBI",
			"uniqueIDSeed"=>rand(),
			"nextRecordListID"=>0,
			"recordAttributes"=>0,
			"compression"=>NO_COMPRESSION,
			"recordSize"=>RECORD_SIZE,
			"encryptionType"=>NO_ENCRYPTION,
			"mobiIdentifier"=>"MOBI",
			"mobiHeaderLength"=>0xe8,
			"mobiType"=>MOBIPOCKET_BOOK,
			"textEncoding"=>UTF8,
			"uniqueID"=>rand(),
			"fileVersion"=>6,
			"locale"=>0x09,
			"inputLanguage"=>0,
			"outputLanguage"=>0,
			"minimumVersion"=>6,
			"huffmanRecordOffset"=>0,
			"huffmanRecordCount"=>0,
			"exthFlags"=>0x40,
			"drmOffset"=>0xFFFFFFFF,
			"drmCount"=>0,
			"drmSize"=>0,
			"drmFlags"=>0,
			"extraDataFlags"=>0,
			"exthIdentifier"=>"EXTH",
			// These can be changed without any risk
			"title"=>"Unknown title",
			"author"=>"Unknown author",
			"subject"=>"Unknown subject"
		);
		
		foreach($additionalSettings as $key=>$value){
			$this->values[$key] = $value;
		}
	}

	/**
	 * Get a value from the settings
	 * @param string $key Key of the setting
	 * @return mixed The value of the setting
	 */
	public function get($key){
		return $this->values[$key];
	}

	/**
	 * Checks if a value is set
	 * @param string $key Key of the setting
	 * @return bool True if the value exists
	 */
	public function exists($key){
		return isset($this->values[$key]);
	}

	public function __toString() {
		$out = "Settings: {\n";
		foreach($this->values as $key=>$value){
			$out .= "\t".$key.": ".$value."\n";
		}
		$out .= "}";
		return $out;
	}
}
?>
