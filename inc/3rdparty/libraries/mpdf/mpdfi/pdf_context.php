<?php
//
//  FPDI - Version 1.2
//
//    Copyright 2004-2007 Setasign - Jan Slabon
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

class pdf_context {

	var $file;
	var $buffer;
	var $offset;
	var $length;

	var $stack;

	// Constructor

	function pdf_context($f) {
		$this->file = $f;
		$this->reset();
	}

	// Optionally move the file
	// pointer to a new location
	// and reset the buffered data

	function reset($pos = null, $l = 100) {
		if (!is_null ($pos)) {
			fseek ($this->file, $pos);
		}

		$this->buffer = $l > 0 ? fread($this->file, $l) : '';
		$this->offset = 0;
		$this->length = strlen($this->buffer);
		$this->stack = array();
	}

	// Make sure that there is at least one
	// character beyond the current offset in
	// the buffer to prevent the tokenizer
	// from attempting to access data that does
	// not exist

	function ensure_content() {
		if ($this->offset >= $this->length - 1) {
			return $this->increase_length();
		} else {
			return true;
		}
	}

	// Forcefully read more data into the buffer

	function increase_length($l=100) {
		if (feof($this->file)) {
			return false;
		} else {
			$this->buffer .= fread($this->file, $l);
			$this->length = strlen($this->buffer);
			return true;
		}
	}

}
?>