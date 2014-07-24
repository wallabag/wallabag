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


class fpdi_pdf_parser extends pdf_parser {

    /**
     * Pages
     * Index beginns at 0
     *
     * @var array
     */
    var $pages;
    
    /**
     * Page count
     * @var integer
     */
    var $page_count;
    
    /**
     * actual page number
     * @var integer
     */
    var $pageno;
    
    
    /**
     * FPDI Reference
     * @var object
     */
    var $fpdi;
    
    /**
     * Available BoxTypes
     *
     * @var array
     */
    var $availableBoxes = array("/MediaBox","/CropBox","/BleedBox","/TrimBox","/ArtBox");
        
    /**
     * Constructor
     *
     * @param string $filename  Source-Filename
     * @param object $fpdi      Object of type fpdi
     */
    function fpdi_pdf_parser($filename,&$fpdi) {
        $this->fpdi =& $fpdi;
	  $this->filename = $filename;

        parent::pdf_parser($filename);
        if ($this->success == false) { return false; }

        // resolve Pages-Dictonary
        $pages = $this->pdf_resolve_object($this->c, $this->root[1][1]['/Pages']);
        if ($this->success == false) { return false; }

        // Read pages
        $this->read_pages($this->c, $pages, $this->pages);
        if ($this->success == false) { return false; }

        // count pages;
        $this->page_count = count($this->pages);
    }
    
    
    /**
     * Get pagecount from sourcefile
     *
     * @return int
     */
    function getPageCount() {
        return $this->page_count;
    }


    /**
     * Set pageno
     *
     * @param int $pageno Pagenumber to use
     */
    function setPageno($pageno) {
        $pageno = ((int) $pageno) - 1;

        if ($pageno < 0 || $pageno >= $this->getPageCount()) {
            $this->fpdi->error("Pagenumber is wrong!");
        }

        $this->pageno = $pageno;
    }
    
    /**
     * Get page-resources from current page
     *
     * @return array
     */
    function getPageResources() {
        return $this->_getPageResources($this->pages[$this->pageno]);
    }
    
    /**
     * Get page-resources from /Page
     *
     * @param array $obj Array of pdf-data
     */
    function _getPageResources ($obj) { // $obj = /Page
    	$obj = $this->pdf_resolve_object($this->c, $obj);

        // If the current object has a resources
    	// dictionary associated with it, we use
    	// it. Otherwise, we move back to its
    	// parent object.
        if (isset ($obj[1][1]['/Resources'])) {
    		$res = $this->pdf_resolve_object($this->c, $obj[1][1]['/Resources']);
    		if ($res[0] == PDF_TYPE_OBJECT)
                return $res[1];
            return $res;
    	} else {
    		if (!isset ($obj[1][1]['/Parent'])) {
    			return false;
    		} else {
                $res = $this->_getPageResources($obj[1][1]['/Parent']);
                if ($res[0] == PDF_TYPE_OBJECT)
                    return $res[1];
                return $res;
    		}
    	}
    }


    /**
     * Get content of current page
     *
     * If more /Contents is an array, the streams are concated
     *
     * @return string
     */
    function getContent() {
        $buffer = "";
        
        if (isset($this->pages[$this->pageno][1][1]['/Contents'])) {
            $contents = $this->_getPageContent($this->pages[$this->pageno][1][1]['/Contents']);
            foreach($contents AS $tmp_content) {
                $buffer .= $this->_rebuildContentStream($tmp_content).' ';
            }
        }
        
        return $buffer;
    }
    
    
    /**
     * Resolve all content-objects
     *
     * @param array $content_ref
     * @return array
     */
    function _getPageContent($content_ref) {
        $contents = array();
        
        if ($content_ref[0] == PDF_TYPE_OBJREF) {
            $content = $this->pdf_resolve_object($this->c, $content_ref);
            if ($content[1][0] == PDF_TYPE_ARRAY) {
                $contents = $this->_getPageContent($content[1]);
            } else {
                $contents[] = $content;
            }
        } else if ($content_ref[0] == PDF_TYPE_ARRAY) {
            foreach ($content_ref[1] AS $tmp_content_ref) {
                $contents = array_merge($contents,$this->_getPageContent($tmp_content_ref));
            }
        }

        return $contents;
    }


    /**
     * Rebuild content-streams
     *
     * @param array $obj
     * @return string
     */
    function _rebuildContentStream($obj) {
        $filters = array();
        
        if (isset($obj[1][1]['/Filter'])) {
            $_filter = $obj[1][1]['/Filter'];

            if ($_filter[0] == PDF_TYPE_TOKEN) {
                $filters[] = $_filter;
            } else if ($_filter[0] == PDF_TYPE_ARRAY) {
                $filters = $_filter[1];
            }
        }

        $stream = $obj[2][1];

        foreach ($filters AS $_filter) {
            switch ($_filter[1]) {
                case "/FlateDecode":
			if (function_exists('gzuncompress')) {
                        $stream = (strlen($stream) > 0) ? @gzuncompress($stream) : '';                        
			} else {
                        $this->fpdi->error(sprintf("To handle %s filter, please compile php with zlib support.",$_filter[1]));
			}
			if ($stream === false) { 
                        $this->fpdi->error("Error while decompressing stream.");
			}
                break;
			// mPDF 4.2.003
                case '/LZWDecode': 
			include_once(_MPDF_PATH.'mpdfi/filters/FilterLZW.php');
			// mPDF 5.0 Removed pass by reference =&
			$decoder = new FilterLZW();
			$stream = $decoder->decode($stream);
			break;
                case '/ASCII85Decode':
			include_once(_MPDF_PATH.'mpdfi/filters/FilterASCII85.php');
			// mPDF 5.0 Removed pass by reference =&
			$decoder = new FilterASCII85();
			$stream = $decoder->decode($stream);
			break;
                case null:
			$stream = $stream;
			break;
                default:
			$this->fpdi->error(sprintf("Unsupported Filter: %s",$_filter[1]));
            }
        }
        
        return $stream;
    }
    
    
    /**
     * Get a Box from a page
     * Arrayformat is same as used by fpdf_tpl
     *
     * @param array $page a /Page
     * @param string $box_index Type of Box @see $availableBoxes
     * @return array
     */
    function getPageBox($page, $box_index) {
        $page = $this->pdf_resolve_object($this->c,$page);
        $box = null;
        if (isset($page[1][1][$box_index]))
            $box =& $page[1][1][$box_index];
        
        if (!is_null($box) && $box[0] == PDF_TYPE_OBJREF) {
            $tmp_box = $this->pdf_resolve_object($this->c,$box);
            $box = $tmp_box[1];
        }
            
        if (!is_null($box) && $box[0] == PDF_TYPE_ARRAY) {
            $b =& $box[1];
            return array("x" => $b[0][1]/_MPDFK,
                         "y" => $b[1][1]/_MPDFK,
                         "w" => abs($b[0][1]-$b[2][1])/_MPDFK,
                         "h" => abs($b[1][1]-$b[3][1])/_MPDFK);	// mPDF 5.3.90
        } else if (!isset ($page[1][1]['/Parent'])) {
            return false;
        } else {
            return $this->getPageBox($this->pdf_resolve_object($this->c, $page[1][1]['/Parent']), $box_index);
        }
    }

    function getPageBoxes($pageno) {
        return $this->_getPageBoxes($this->pages[$pageno-1]);
    }
    
    /**
     * Get all Boxes from /Page
     *
     * @param array a /Page
     * @return array
     */
    function _getPageBoxes($page) {
        $boxes = array();

        foreach($this->availableBoxes AS $box) {
            if ($_box = $this->getPageBox($page,$box)) {
                $boxes[$box] = $_box;
            }
        }

        return $boxes;
    }

    function getPageRotation($pageno) {
        return $this->_getPageRotation($this->pages[$pageno-1]);
    }
    
    function _getPageRotation ($obj) { // $obj = /Page
    	$obj = $this->pdf_resolve_object($this->c, $obj);
    	if (isset ($obj[1][1]['/Rotate'])) {
    		$res = $this->pdf_resolve_object($this->c, $obj[1][1]['/Rotate']);
    		if ($res[0] == PDF_TYPE_OBJECT)
                return $res[1];
            return $res;
    	} else {
    		if (!isset ($obj[1][1]['/Parent'])) {
    			return false;
    		} else {
                $res = $this->_getPageRotation($obj[1][1]['/Parent']);
                if ($res[0] == PDF_TYPE_OBJECT)
                    return $res[1];
                return $res;
    		}
    	}
    }
    
    /**
     * Read all /Page(es)
     *
     * @param object pdf_context
     * @param array /Pages
     * @param array the result-array
     */
    function read_pages (&$c, &$pages, &$result) {
        // Get the kids dictionary
    	$kids = $this->pdf_resolve_object ($c, $pages[1][1]['/Kids']);

        if (!is_array($kids)) {
	 		// mPDF 4.0
           		$this->success = false;
            	$this->errormsg = sprintf("Cannot find /Kids in current /Page-Dictionary");
			return false;
	  }
        foreach ($kids[1] as $v) {
    		$pg = $this->pdf_resolve_object ($c, $v);
            if ($pg[1][1]['/Type'][1] === '/Pages') {
                // If one of the kids is an embedded
    			// /Pages array, resolve it as well.
                $this->read_pages ($c, $pg, $result);
    		} else {
    			$result[] = $pg;
    		}
    	}
    }

    
    
    
}

?>