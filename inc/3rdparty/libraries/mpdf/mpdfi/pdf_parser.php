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

if (!defined ('PDF_TYPE_NULL'))
    define ('PDF_TYPE_NULL', 0);
if (!defined ('PDF_TYPE_NUMERIC'))
    define ('PDF_TYPE_NUMERIC', 1);
if (!defined ('PDF_TYPE_TOKEN'))
    define ('PDF_TYPE_TOKEN', 2);
if (!defined ('PDF_TYPE_HEX'))
    define ('PDF_TYPE_HEX', 3);
if (!defined ('PDF_TYPE_STRING'))
    define ('PDF_TYPE_STRING', 4);
if (!defined ('PDF_TYPE_DICTIONARY'))
    define ('PDF_TYPE_DICTIONARY', 5);
if (!defined ('PDF_TYPE_ARRAY'))
    define ('PDF_TYPE_ARRAY', 6);
if (!defined ('PDF_TYPE_OBJDEC'))
    define ('PDF_TYPE_OBJDEC', 7);
if (!defined ('PDF_TYPE_OBJREF'))
    define ('PDF_TYPE_OBJREF', 8);
if (!defined ('PDF_TYPE_OBJECT'))
    define ('PDF_TYPE_OBJECT', 9);
if (!defined ('PDF_TYPE_STREAM'))
    define ('PDF_TYPE_STREAM', 10);


class pdf_parser {
	
	/**
     * Filename
     * @var string
     */
    var $filename;
    
    /**
     * File resource
     * @var resource
     */
    var $f;
    
    /**
     * PDF Context
     * @var object pdf_context-Instance
     */
    var $c;
    
    /**
     * xref-Data
     * @var array
     */
    var $xref;

    /**
     * root-Object
     * @var array
     */
    var $root;
	
    // mPDF 4.0 Added flag to show success on loading file
    var $success;
    var $errormsg;

    /**
     * Constructor
     *
     * @param string $filename  Source-Filename
     */
	function pdf_parser($filename) {
        $this->filename = $filename;
	  // mPDF 4.0
	  $this->success = true;

        $this->f = @fopen($this->filename, "rb");

        if (!$this->f) {
            $this->success = false;
            $this->errormsg = sprintf("Cannot open %s !", $filename);
		return false;
	  }
	// mPDF 5.0 Removed pass by reference =&
        $this->c = new pdf_context($this->f);
        // Read xref-Data
	  $offset = $this->pdf_find_xref();
        if ($offset===false) {
            $this->success = false;
            $this->errormsg = sprintf("Cannot open %s !", $filename);
		return false;
	  }
        $this->pdf_read_xref($this->xref, $offset);
        if ($this->success == false) { return false; }

        // Check for Encryption
        $this->getEncryption();
        if ($this->success == false) { return false; }

        // Read root
        $this->pdf_read_root();
        if ($this->success == false) { return false; }
    }
    
    /**
     * Close the opened file
     */
    function closeFile() {
    	if (isset($this->f)) {
    	    fclose($this->f);	
    		unset($this->f);
    	}	
    }
    
      /**
     * Print Error and die
     *
     * @param string $msg  Error-Message
     */
    function error($msg) {
    	die("<b>PDF-Parser Error:</b> ".$msg);	
    }
  
    /**
     * Check Trailer for Encryption
     */
    function getEncryption() {
        if (isset($this->xref['trailer'][1]['/Encrypt'])) {
	 	// mPDF 4.0
           	$this->success = false;
            $this->errormsg = sprintf("File is encrypted!");
		return false;
        }
    }
    
	/**
     * Find/Return /Root
     *
     * @return array
     */
    function pdf_find_root() {
        if ($this->xref['trailer'][1]['/Root'][0] != PDF_TYPE_OBJREF) {
	 	// mPDF 4.0
           	$this->success = false;
            $this->errormsg = sprintf("Wrong Type of Root-Element! Must be an indirect reference");
		return false;
        }
        return $this->xref['trailer'][1]['/Root'];
    }

    /**
     * Read the /Root
     */
    function pdf_read_root() {
        // read root
	  $root = $this->pdf_find_root();
        if ($root ===false) {
            $this->success = false;
		return false;
	  }
        $this->root = $this->pdf_resolve_object($this->c, $root);
    }
    
    /**
     * Find the xref-Table
     */
    function pdf_find_xref() {
       	fseek ($this->f, -min(filesize($this->filename),1500), SEEK_END);
        $data = fread($this->f, 1500);
        
        $pos = strlen($data) - strpos(strrev($data), strrev('startxref')); 
        $data = substr($data, $pos);
        
        if (!preg_match('/\s*(\d+).*$/s', $data, $matches)) {
	 	// mPDF 4.0
           	$this->success = false;
            $this->errormsg = sprintf("Unable to find pointer to xref table");
		return false;
    	}

    	return (int) $matches[1];
    }

    /**
     * Read xref-table
     *
     * @param array $result Array of xref-table
     * @param integer $offset of xref-table
     * @param integer $start start-position in xref-table
     * @param integer $end end-position in xref-table
     */
    function pdf_read_xref(&$result, $offset, $start = null, $end = null) {
        if (is_null ($start) || is_null ($end)) {
		fseek($this->f, $o_pos = $offset);
            $data = trim(fgets($this->f,1024));

            if (strlen($data) == 0) 
                $data = trim(fgets($this->f,1024));

            if ($data !== 'xref') {
            	fseek($this->f, $o_pos);
            	$data = trim(_fgets($this->f, true));
            	if ($data !== 'xref') {
            	    if (preg_match('/(.*xref)(.*)/m', $data, $m)) { // xref 0 128 - in one line
                        fseek($this->f, $o_pos+strlen($m[1]));            	        
            	    } elseif (preg_match('/(x|r|e|f)+/', $data, $m)) { // correct invalid xref-pointer
            	        $tmpOffset = $offset-4+strlen($m[0]);
            	        $this->pdf_read_xref($result, $tmpOffset, $start, $end);
            	        return;
                    } else {
	 			// mPDF 4.0
           			$this->success = false;
            		$this->errormsg = sprintf("Unable to find xref table - Maybe a Problem with 'auto_detect_line_endings'");
				return;
            	    }
            	}
    		}

    		$o_pos = ftell($this->f);
    	    $data = explode(' ', trim(fgets($this->f,1024)));
			if (count($data) != 2) {
    	        fseek($this->f, $o_pos);
    	        $data = explode(' ', trim(_fgets($this->f, true)));
			
            	if (count($data) != 2) {
            	    if (count($data) > 2) { // no lineending
            	        $n_pos = $o_pos+strlen($data[0])+strlen($data[1])+2;
            	        fseek($this->f, $n_pos);
            	    } else {
	 			// mPDF 4.0
           			$this->success = false;
            		$this->errormsg = sprintf("Unexpected header in xref table");
				return;
            	    }
            	}
            }
            $start = $data[0];
            $end = $start + $data[1];
        }

        if (!isset($result['xref_location'])) {
            $result['xref_location'] = $offset;
    	}

    	if (!isset($result['max_object']) || $end > $result['max_object']) {
    	    $result['max_object'] = $end;
    	}

    	for (; $start < $end; $start++) {
    		$data = ltrim(fread($this->f, 20)); // Spezifications says: 20 bytes including newlines
    		$offset = substr($data, 0, 10);
    		$generation = substr($data, 11, 5);

    	    if (!isset ($result['xref'][$start][(int) $generation])) {
    	    	$result['xref'][$start][(int) $generation] = (int) $offset;
    	    }
    	}

    	$o_pos = ftell($this->f);
        $data = fgets($this->f,1024);
		if (strlen(trim($data)) == 0) 
		    $data = fgets($this->f, 1024);

        if (preg_match("/trailer/",$data)) {
            if (preg_match("/(.*trailer[ \n\r]*)/",$data,$m)) {
            	fseek($this->f, $o_pos+strlen($m[1]));
    		}

			// mPDF 5.0 Removed pass by reference =&
			$c = new pdf_context($this->f);
    	    $trailer = $this->pdf_read_value($c);
    	    
    	    if (isset($trailer[1]['/Prev'])) {
    	    	$this->pdf_read_xref($result, $trailer[1]['/Prev'][1]);
    		    $result['trailer'][1] = array_merge($result['trailer'][1], $trailer[1]);
    	    } else {
    	        $result['trailer'] = $trailer;
            }
    	} else {
    	    $data = explode(' ', trim($data));
            
    		if (count($data) != 2) {
            	fseek($this->f, $o_pos);
        		$data = explode(' ', trim (_fgets ($this->f, true)));

        		if (count($data) != 2) {
	 			// mPDF 4.0
           			$this->success = false;
            		$this->errormsg = sprintf("Unexpected data in xref table");
				return;
        		}
		    }
		    
		    $this->pdf_read_xref($result, null, (int) $data[0], (int) $data[0] + (int) $data[1]);
    	}
    }


    /**
     * Reads an Value
     *
     * @param object $c pdf_context
     * @param string $token a Token
     * @return mixed
     */
    function pdf_read_value(&$c, $token = null) {
    	if (is_null($token)) {
    	    $token = $this->pdf_read_token($c);
    	}
    	
        if ($token === false) {
    	    return false;
    	}

       	switch ($token) {
            case	'<':
    			// This is a hex string.
    			// Read the value, then the terminator

                $pos = $c->offset;

    			while(1) {

                    $match = strpos ($c->buffer, '>', $pos);
				
    				// If you can't find it, try
    				// reading more data from the stream

    				if ($match === false) {
    					if (!$c->increase_length()) {
    						return false;
    					} else {
                        	continue;
                    	}
    				}

    				$result = substr ($c->buffer, $c->offset, $match - $c->offset);
    				$c->offset = $match+1;
    				
    				return array (PDF_TYPE_HEX, $result);
                }
                
                break;
    		case	'<<':
    			// This is a dictionary.

    			$result = array();

    			// Recurse into this function until we reach
    			// the end of the dictionary.
    			while (($key = $this->pdf_read_token($c)) !== '>>') {
    				if ($key === false) {
    					return false;
    				}
					
    				if (($value =   $this->pdf_read_value($c)) === false) {
    					return false;
    				}
                    $result[$key] = $value;
    			}
				
    			return array (PDF_TYPE_DICTIONARY, $result);

    		case	'[':
    			// This is an array.

    			$result = array();

    			// Recurse into this function until we reach
    			// the end of the array.
    			while (($token = $this->pdf_read_token($c)) !== ']') {
                    if ($token === false) {
    					return false;
    				}
					
    				if (($value = $this->pdf_read_value($c, $token)) === false) {
                        return false;
    				}
					
    				$result[] = $value;
    			}
    			
                return array (PDF_TYPE_ARRAY, $result);

    		case	'('		:
                // This is a string

    			$pos = $c->offset;

    			while(1) {

                    // Start by finding the next closed
    				// parenthesis

    				$match = strpos ($c->buffer, ')', $pos);

    				// If you can't find it, try
    				// reading more data from the stream

    				if ($match === false) {
    					if (!$c->increase_length()) {
                            return false;
    					} else {
                            continue;
                        }
    				}

    				// Make sure that there is no backslash
    				// before the parenthesis. If there is,
    				// move on. Otherwise, return the string.
                    $esc = preg_match('/([\\\\]+)$/', $tmpresult = substr($c->buffer, $c->offset, $match - $c->offset), $m);
                    
                    if ($esc === 0 || strlen($m[1]) % 2 == 0) {
    				    $result = $tmpresult;
                        $c->offset = $match + 1;
                        return array (PDF_TYPE_STRING, $result);
    				} else {
    					$pos = $match + 1;

    					if ($pos > $c->offset + $c->length) {
    						$c->increase_length();
    					}
    				}    				
                }

            case "stream":
            	$o_pos = ftell($c->file)-strlen($c->buffer);
		        $o_offset = $c->offset;
		        
		        $c->reset($startpos = $o_pos + $o_offset);
		        
		        $e = 0; // ensure line breaks in front of the stream
		        if ($c->buffer[0] == chr(10) || $c->buffer[0] == chr(13))
		        	$e++;
		        if ($c->buffer[1] == chr(10) && $c->buffer[0] != chr(10))
		        	$e++;
		        
		        if ($this->actual_obj[1][1]['/Length'][0] == PDF_TYPE_OBJREF) {
				// mPDF 5.0 Removed pass by reference =&
		        	$tmp_c = new pdf_context($this->f);
		        	$tmp_length = $this->pdf_resolve_object($tmp_c,$this->actual_obj[1][1]['/Length']);
		        	$length = $tmp_length[1][1];
		        } else {
		        	$length = $this->actual_obj[1][1]['/Length'][1];	
		        }
		        
		        if ($length > 0) {
    		        $c->reset($startpos+$e,$length);
    		        $v = $c->buffer;
		        } else {
		            $v = '';   
		        }
		        $c->reset($startpos+$e+$length+9); // 9 = strlen("endstream")
		        
		        return array(PDF_TYPE_STREAM, $v);
		        
    		default	:
            	if (is_numeric ($token)) {
                    // A numeric token. Make sure that
    				// it is not part of something else.
    				if (($tok2 = $this->pdf_read_token ($c)) !== false) {
                        if (is_numeric ($tok2)) {

    						// Two numeric tokens in a row.
    						// In this case, we're probably in
    						// front of either an object reference
    						// or an object specification.
    						// Determine the case and return the data
    						if (($tok3 = $this->pdf_read_token ($c)) !== false) {
                                switch ($tok3) {
    								case	'obj'	:
                                        return array (PDF_TYPE_OBJDEC, (int) $token, (int) $tok2);
    								case	'R'		:
    									return array (PDF_TYPE_OBJREF, (int) $token, (int) $tok2);
    							}
    							// If we get to this point, that numeric value up
    							// there was just a numeric value. Push the extra
    							// tokens back into the stack and return the value.
    							array_push ($c->stack, $tok3);
    						}
    					}

    					array_push ($c->stack, $tok2);
    				}

    				return array (PDF_TYPE_NUMERIC, $token);
    			} else {

                    // Just a token. Return it.
    				return array (PDF_TYPE_TOKEN, $token);
    			}

         }
    }
    
    /**
     * Resolve an object
     *
     * @param object $c pdf_context
     * @param array $obj_spec The object-data
     * @param boolean $encapsulate Must set to true, cause the parsing and fpdi use this method only without this para
     */
    function pdf_resolve_object(&$c, $obj_spec, $encapsulate = true) {
        // Exit if we get invalid data
    	if (!is_array($obj_spec)) {
            return false;
    	}

    	if ($obj_spec[0] == PDF_TYPE_OBJREF) {

    		// This is a reference, resolve it
    		if (isset($this->xref['xref'][$obj_spec[1]][$obj_spec[2]])) {

    			// Save current file position
    			// This is needed if you want to resolve
    			// references while you're reading another object
    			// (e.g.: if you need to determine the length
    			// of a stream)

    			$old_pos = ftell($c->file);

    			// Reposition the file pointer and
    			// load the object header.
				
    			$c->reset($this->xref['xref'][$obj_spec[1]][$obj_spec[2]]);

    			$header = $this->pdf_read_value($c,null,true);

    			if ($header[0] != PDF_TYPE_OBJDEC || $header[1] != $obj_spec[1] || $header[2] != $obj_spec[2]) {
	 			// mPDF 4.0
           			$this->success = false;
            		$this->errormsg = sprintf("Unable to find object ({$obj_spec[1]}, {$obj_spec[2]}) at expected location");
				return false;
    			}

    			// If we're being asked to store all the information
    			// about the object, we add the object ID and generation
    			// number for later use
				$this->actual_obj =& $result;
    			if ($encapsulate) {
    				$result = array (
    					PDF_TYPE_OBJECT,
    					'obj' => $obj_spec[1],
    					'gen' => $obj_spec[2]
    				);
    			} else {
    				$result = array();
    			}

    			// Now simply read the object data until
    			// we encounter an end-of-object marker
    			while(1) {
                    $value = $this->pdf_read_value($c);
					if ($value === false || count($result) > 4) {
						// in this case the parser coudn't find an endobj so we break here
						break;
    				}

    				if ($value[0] == PDF_TYPE_TOKEN && $value[1] === 'endobj') {
    					break;
    				}

                    $result[] = $value;
    			}

    			$c->reset($old_pos);

                if (isset($result[2][0]) && $result[2][0] == PDF_TYPE_STREAM) {
                    $result[0] = PDF_TYPE_STREAM;
                }

    			return $result;
    		}
    	} else {
    		return $obj_spec;
    	}
    }

    
    
    /**
     * Reads a token from the file
     *
     * @param object $c pdf_context
     * @return mixed
     */
    function pdf_read_token(&$c)
    {
    	// If there is a token available
    	// on the stack, pop it out and
    	// return it.

    	if (count($c->stack)) {
    		return array_pop($c->stack);
    	}

    	// Strip away any whitespace

    	do {
    		if (!$c->ensure_content()) {
    			return false;
    		}
    		$c->offset += _strspn($c->buffer, " \n\r\t", $c->offset);
    	} while ($c->offset >= $c->length - 1);

    	// Get the first character in the stream

    	$char = $c->buffer[$c->offset++];

    	switch ($char) {

    		case '['	:
    		case ']'	:
    		case '('	:
    		case ')'	:

    			// This is either an array or literal string
    			// delimiter, Return it

    			return $char;

    		case '<'	:
    		case '>'	:

    			// This could either be a hex string or
    			// dictionary delimiter. Determine the
    			// appropriate case and return the token

    			if ($c->buffer[$c->offset] == $char) {
    				if (!$c->ensure_content()) {
    				    return false;
    				}
    				$c->offset++;
    				return $char . $char;
    			} else {
    				return $char;
    			}

    		default		:

    			// This is "another" type of token (probably
    			// a dictionary entry or a numeric value)
    			// Find the end and return it.

    			if (!$c->ensure_content()) {
    				return false;
    			}

    			while(1) {

    				// Determine the length of the token

    				$pos = _strcspn($c->buffer, " []<>()\r\n\t/", $c->offset);
    				if ($c->offset + $pos <= $c->length - 1) {
    					break;
    				} else {
    					// If the script reaches this point,
    					// the token may span beyond the end
    					// of the current buffer. Therefore,
    					// we increase the size of the buffer
    					// and try again--just to be safe.

    					$c->increase_length();
    				}
    			}

    			$result = substr($c->buffer, $c->offset - 1, $pos + 1);

    			$c->offset += $pos;
    			return $result;
    	}
    }

	
}

?>