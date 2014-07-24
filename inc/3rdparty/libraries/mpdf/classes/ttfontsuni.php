<?php

/*******************************************************************************
* TTFontFile class                                                             *
*                                                                              *
* Version:  2.01		                                                       *
* Date:     2012-02-25                                                         *
* Author:   Ian Back <ianb@bpm1.com>                                           *
* License:  LGPL                                                               *
* Copyright (c) Ian Back, 2010                                                 *
* This class is based on The ReportLab Open Source PDF library                 *
* written in Python - http://www.reportlab.com/software/opensource/            *
* together with ideas from the OpenOffice source code and others.              * 
* This header must be retained in any redistribution or                        *
* modification of the file.                                                    *
*                                                                              *
*******************************************************************************/

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
if (!defined('_TTF_MAC_HEADER')) define("_TTF_MAC_HEADER", false);

// Recalculate correct metadata/profiles when making subset fonts (not SIP/SMP)
// e.g. xMin, xMax, maxNContours
if (!defined('_RECALC_PROFILE')) define("_RECALC_PROFILE", false);

// TrueType Font Glyph operators
define("GF_WORDS",(1 << 0));
define("GF_SCALE",(1 << 3));
define("GF_MORE",(1 << 5));
define("GF_XYSCALE",(1 << 6));
define("GF_TWOBYTWO",(1 << 7));



class TTFontFile {

var $unAGlyphs;	// mPDF 5.4.05
var $panose;
var $maxUni;
var $sFamilyClass;
var $sFamilySubClass;
var $sipset;
var $smpset;
var $_pos;
var $numTables;
var $searchRange;
var $entrySelector;
var $rangeShift;
var $tables;
var $otables;
var $filename;
var $fh;
var $glyphPos;
var $charToGlyph;
var $ascent;
var $descent;
var $name;
var $familyName;
var $styleName;
var $fullName;
var $uniqueFontID;
var $unitsPerEm;
var $bbox;
var $capHeight;
var $stemV;
var $italicAngle;
var $flags;
var $underlinePosition;
var $underlineThickness;
var $charWidths;
var $defaultWidth;
var $maxStrLenRead;
var $numTTCFonts;
var $TTCFonts;
var $maxUniChar;
var $kerninfo;

	function TTFontFile() {
		$this->maxStrLenRead = 200000;	// Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
	}


	function getMetrics($file, $TTCfontID=0, $debug=false, $BMPonly=false, $kerninfo=false, $unAGlyphs=false) {	// mPDF 5.4.05
		$this->unAGlyphs = $unAGlyphs;	// mPDF 5.4.05
		$this->filename = $file;
		$this->fh = fopen($file,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->kerninfo = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		$this->panose = array();
		if ($version==0x4F54544F) 
			die("Postscript outlines are not supported");
		if ($version==0x74746366 && !$TTCfontID) 
			die("ERROR - You must define the TTCfontID for a TrueType Collection in config_fonts.php (". $file.")");
		if (!in_array($version, array(0x00010000,0x74727565)) && !$TTCfontID)
			die("Not a TrueType font: version=".$version);
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);
		$this->extractInfo($debug, $BMPonly, $kerninfo); 
		fclose($this->fh);
	}


	function readTableDirectory($debug=false) {
	    $this->numTables = $this->read_ushort();
            $this->searchRange = $this->read_ushort();
            $this->entrySelector = $this->read_ushort();
            $this->rangeShift = $this->read_ushort();
            $this->tables = array();	
            for ($i=0;$i<$this->numTables;$i++) {
                $record = array();
                $record['tag'] = $this->read_tag();
                $record['checksum'] = array($this->read_ushort(),$this->read_ushort());
                $record['offset'] = $this->read_ulong();
                $record['length'] = $this->read_ulong();
                $this->tables[$record['tag']] = $record;
		}
		if ($debug) $this->checksumTables();
	}

	function checksumTables() {
		// Check the checksums for all tables
		foreach($this->tables AS $t) {
		  if ($t['length'] > 0 && $t['length'] < $this->maxStrLenRead) {	// 1.02
            	$table = $this->get_chunk($t['offset'], $t['length']);
            	$checksum = $this->calcChecksum($table);
            	if ($t['tag'] == 'head') {
				$up = unpack('n*', substr($table,8,4));
				$adjustment[0] = $up[1];
				$adjustment[1] = $up[2];
            		$checksum = $this->sub32($checksum, $adjustment);
			}
            	$xchecksum = $t['checksum'];
            	if ($xchecksum != $checksum) 
            	    die(sprintf('TTF file "%s": invalid checksum %s table: %s (expected %s)', $this->filename,dechex($checksum[0]).dechex($checksum[1]),$t['tag'],dechex($xchecksum[0]).dechex($xchecksum[1])));
		  }
		}
	}

	function sub32($x, $y) {
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) { $xlo += 1 << 16; $yhi += 1; }
		$reslo = $xlo-$ylo;
		if ($yhi > $xhi) { $xhi += 1 << 16;  }
		$reshi = $xhi-$yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	function calcChecksum($data)  {
		if (strlen($data) % 4) { $data .= str_repeat("\0",(4-(strlen($data) % 4))); }
		$len = strlen($data);
		$hi=0x0000;
		$lo=0x0000;
		for($i=0;$i<$len;$i+=4) {
			$hi += (ord($data[$i])<<8) + ord($data[$i+1]);
			$lo += (ord($data[$i+2])<<8) + ord($data[$i+3]);
			$hi += ($lo >> 16) & 0xFFFF;
			$lo = $lo & 0xFFFF;
		}
		return array($hi, $lo);
	}

	function get_table_pos($tag) {
		$offset = $this->tables[$tag]['offset'];
		$length = $this->tables[$tag]['length'];
		return array($offset, $length);
	}

	function seek($pos) {
		$this->_pos = $pos;
		fseek($this->fh,$this->_pos);
	}

	function skip($delta) {
		$this->_pos = $this->_pos + $delta;
		fseek($this->fh,$delta,SEEK_CUR);
	}

	function seek_table($tag, $offset_in_table = 0) {
		$tpos = $this->get_table_pos($tag);
		$this->_pos = $tpos[0] + $offset_in_table;
		fseek($this->fh, $this->_pos);
		return $this->_pos;
	}

	function read_tag() {
		$this->_pos += 4;
		return fread($this->fh,4);
	}

	function read_short() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { 
			$a = ($a - (1 << 16)); 
		}
		return $a;
	}

	function unpack_short($s) {
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { 
			$a = ($a - (1 << 16)); 
		}
		return $a;
	}

	function read_ushort() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function read_ulong() {
		$this->_pos += 4;
		$s = fread($this->fh,4);
		// if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function get_ushort($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function get_ulong($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,4);
		// iF large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function pack_short($val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		return pack("n",$val); 
	}

	function splice($stream, $offset, $value) {
		return substr($stream,0,$offset) . $value . substr($stream,$offset+strlen($value));
	}

	function _set_ushort($stream, $offset, $value) {
		$up = pack("n", $value);
		return $this->splice($stream, $offset, $up);
	}

	function _set_short($stream, $offset, $val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		$up = pack("n",$val); 
		return $this->splice($stream, $offset, $up);
	}

	function get_chunk($pos, $length) {
		fseek($this->fh,$pos);
		if ($length <1) { return ''; }
		return (fread($this->fh,$length));
	}

	function get_table($tag) {
		list($pos, $length) = $this->get_table_pos($tag);
		if ($length == 0) { return ''; }
		fseek($this->fh,$pos);
		return (fread($this->fh,$length));
	}

	function add($tag, $data) {
		if ($tag == 'head') {
			$data = $this->splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}



/////////////////////////////////////////////////////////////////////////////////////////
	function getCTG($file, $TTCfontID=0, $debug=false, $unAGlyphs=false) {	// mPDF 5.4.05
		$this->unAGlyphs = $unAGlyphs;	// mPDF 5.4.05
		$this->filename = $file;
		$this->fh = fopen($file,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->skip(4);
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);


		// cmap - Character to glyph index mapping table
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if ($platformID == 3 && $encodingID == 1) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			else if ($platformID == 0) { // Unicode -- assume all encodings are compatible
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		$glyphToChar = array();
		$charToGlyph = array();
		$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		fclose($this->fh);
		return ($charToGlyph);
	}

/////////////////////////////////////////////////////////////////////////////////////////
	function getTTCFonts($file) {
		$this->filename = $file;
		$this->fh = fopen($file,'rb');
		if (!$this->fh) { return ('ERROR - Can\'t open file ' . $file); }
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		if ($version==0x74746366) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				return("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
		}
		else {
			return("ERROR - Not a TrueType Collection: version=".$version." - " . $file);
		}
		$this->numTTCFonts = $this->read_ulong();
		for ($i=1; $i<=$this->numTTCFonts; $i++) {
	            $this->TTCFonts[$i]['offset'] = $this->read_ulong();
		}
	}



/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////

	function extractInfo($debug=false, $BMPonly=false, $kerninfo=false) {
		$this->panose = array();
		$this->sFamilyClass = 0;
		$this->sFamilySubClass = 0;
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
			$name_offset = $this->seek_table("name");
			$format = $this->read_ushort();
			if ($format != 0 && $format != 1)
				die("Unknown name table format ".$format);
			$numRecords = $this->read_ushort();
			$string_data_offset = $name_offset + $this->read_ushort();
			$names = array(1=>'',2=>'',3=>'',4=>'',6=>'');
			$K = array_keys($names);
			$nameCount = count($names);
			for ($i=0;$i<$numRecords; $i++) {
				$platformId = $this->read_ushort();
				$encodingId = $this->read_ushort();
				$languageId = $this->read_ushort();
				$nameId = $this->read_ushort();
				$length = $this->read_ushort();
				$offset = $this->read_ushort();
				if (!in_array($nameId,$K)) continue;
				$N = '';
				if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
					$opos = $this->_pos;
					$this->seek($string_data_offset + $offset);
					if ($length % 2 != 0)
						die("PostScript name is UTF-16BE string of odd length");
					$length /= 2;
					$N = '';
					while ($length > 0) {
						$char = $this->read_ushort();
						$N .= (chr($char));
						$length -= 1;
					}
					$this->_pos = $opos;
					$this->seek($opos);
				}
				else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
					$opos = $this->_pos;
					$N = $this->get_chunk($string_data_offset + $offset, $length);
					$this->_pos = $opos;
					$this->seek($opos);
				}
				if ($N && $names[$nameId]=='') {
					$names[$nameId] = $N;
					$nameCount -= 1;
					if ($nameCount==0) break;
				}
			}
			if ($names[6])
				$psName = $names[6];
			else if ($names[4])
				$psName = preg_replace('/ /','-',$names[4]);
			else if ($names[1])
				$psName = preg_replace('/ /','-',$names[1]);
			else
				$psName = '';
			if (!$psName)
				die("Could not find PostScript font name: ".$this->filename);
			if ($debug) {
			   for ($i=0;$i<count($psName);$i++) {
				$c = $psName[$i];
				$oc = ord($c);
				if ($oc>126 || strpos(' [](){}<>/%',$c)!==false)
					die("psName=".$psName." contains invalid character ".$c." ie U+".ord(c));
			   }
			}
			$this->name = $psName;
			if ($names[1]) { $this->familyName = $names[1]; } else { $this->familyName = $psName; }
			if ($names[2]) { $this->styleName = $names[2]; } else { $this->styleName = 'Regular'; }
			if ($names[4]) { $this->fullName = $names[4]; } else { $this->fullName = $psName; }
			if ($names[3]) { $this->uniqueFontID = $names[3]; } else { $this->uniqueFontID = $psName; }

			if ($names[6]) { $this->fullName = $names[6]; }

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown head table version '. $ver_maj .'.'. $ver_min);
			$this->fontRevision = $this->read_ushort() . $this->read_ushort();

			$this->skip(4);
			$magic = $this->read_ulong();
			if ($magic != 0x5F0F3CF5) 
				die('Invalid head table magic ' .$magic);
			$this->skip(2);
		}
		else {
			$this->skip(18); 
		}
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->skip(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin*$scale), ($yMin*$scale), ($xMax*$scale), ($yMax*$scale));
		$this->skip(3*2);
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();
		if ($glyphDataFormat != 0)
			die('Unknown glyph data format '.$glyphDataFormat);

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		// ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->skip(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$this->ascent = ($hheaAscender *$scale);
			$this->descent = ($hheaDescender *$scale);
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->skip(2);
			$usWeightClass = $this->read_ushort();
			$this->skip(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
				global $overrideTTFFontRestriction;
				if (!$overrideTTFFontRestriction) die('ERROR - Font file '.$this->filename.' cannot be embedded due to copyright restrictions.');
				$this->restrictedUse = true;
			}
			$this->skip(20);
			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			$this->_pos += 10;  //PANOSE = 10 byte length
			$panose = fread($this->fh,10);
			$this->panose = array();
			for ($p=0;$p<strlen($panose);$p++) { $this->panose[] = ord($panose[$p]); }
			$this->skip(26);
			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			if (!$this->ascent) $this->ascent = ($sTypoAscender*$scale);
			if (!$this->descent) $this->descent = ($sTypoDescender*$scale);
			if ($version > 1) {
				$this->skip(16);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight*$scale);
			}
			else {
				$this->capHeight = $this->ascent;
			}
		}
		else {
			$usWeightClass = 500;
			if (!$this->ascent) $this->ascent = ($yMax*$scale);
			if (!$this->descent) $this->descent = ($yMin*$scale);
			$this->capHeight = $this->ascent;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0),2));

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj <1 || $ver_maj >4) 
				die('Unknown post table version '.$ver_maj);
		}
		else {
			$this->skip(4); 
		}
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle!= 0) 
			$this->flags = $this->flags | 64;
		if ($usWeightClass >= 600)
			$this->flags = $this->flags | 262144;
		if ($isFixedPitch)
			$this->flags = $this->flags | 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown hhea table version '.$ver_maj);
			$this->skip(28);
		}
		else {
			$this->skip(32); 
		}
		$metricDataFormat = $this->read_ushort();
		if ($metricDataFormat != 0)
			die('Unknown horizontal metric data format '.$metricDataFormat);
		$numberOfHMetrics = $this->read_ushort();
		if ($numberOfHMetrics == 0) 
			die('Number of horizontal metrics is 0');

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		if ($debug) { 
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				die('Unknown maxp table version '.$ver_maj);
		}
		else {
			$this->skip(4); 
		}
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if (!$unicode_cmap_offset) $unicode_cmap_offset = $cmap_offset + $offset;
					if ($BMPonly) break;
				}
			}
			// Microsoft, Unicode Format 12 table HKCS
			else if ((($platformID == 3 && $encodingID == 10) || $platformID == 0) && !$BMPonly) {
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}
		if (!$unicode_cmap_offset)
			die('Font ('.$this->filename .') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


		$sipset = false;
		$smpset = false;
		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12 && !$BMPonly) {
			$this->maxUniChar = 0;
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);

			$nGroups = $this->read_ulong();

			$glyphToChar = array();
			$charToGlyph = array();
			for($i=0; $i<$nGroups ; $i++) { 
				$startCharCode = $this->read_ulong(); 
				$endCharCode = $this->read_ulong(); 
				$startGlyphCode = $this->read_ulong(); 
				if (($endCharCode > 0x20000 && $endCharCode < 0x2A6DF) || ($endCharCode > 0x2F800 && $endCharCode < 0x2FA1F)) {
					$sipset = true; 
				}
				else if ($endCharCode > 0x10000 && $endCharCode < 0x1FFFF) {
					$smpset = true; 
				}
				$offset = 0;
				for ($unichar=$startCharCode;$unichar<=$endCharCode;$unichar++) {
					$glyph = $startGlyphCode + $offset ;
					$offset++;
					$charToGlyph[$unichar] = $glyph;
					if ($unichar < 196608) { $this->maxUniChar = max($unichar,$this->maxUniChar); }
					$glyphToChar[$glyph][] = $unichar;
				}
			}
		}
		else {

			$glyphToChar = array();
			$charToGlyph = array();
			$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		}
		$this->sipset = $sipset ;
		$this->smpset = $smpset ;

		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// kern - Kerning pair table
		///////////////////////////////////
		if ($kerninfo) {
			// Recognises old form of Kerning table - as required by Windows - Format 0 only
			$kern_offset = $this->seek_table("kern");
			$version = $this->read_ushort();
			$nTables = $this->read_ushort();
			// subtable header
			$sversion = $this->read_ushort();
			$slength = $this->read_ushort();
			$scoverage = $this->read_ushort();
			$format = $scoverage >> 8;
 			if ($kern_offset && $version==0 && $format==0) {
				// Format 0
				$nPairs = $this->read_ushort();
				$this->skip(6);
				for ($i=0; $i<$nPairs; $i++) {
					$left = $this->read_ushort();
					$right = $this->read_ushort();
					$val = $this->read_short();
					if (count($glyphToChar[$left])==1 && count($glyphToChar[$right])==1) {
					  if ($left != 32 && $right != 32) {
						$this->kerninfo[$glyphToChar[$left][0]][$glyphToChar[$right][0]] = intval($val*$scale);
					  }
					}
				}
			}
		}
	}


/////////////////////////////////////////////////////////////////////////////////////////


	function makeSubset($file, &$subset, $TTCfontID=0, $debug=false, $unAGlyphs=false) {	// mPDF 5.4.05
		$this->unAGlyphs = $unAGlyphs;	// mPDF 5.4.05
		$this->filename = $file;
		$this->fh = fopen($file ,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->skip(4);
		$this->maxUni = 0;
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$this->skip(50); 
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		$this->skip(32); 
		$metricDataFormat = $this->read_ushort();
		$orignHmetrics = $numberOfHMetrics = $this->read_ushort();

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		$this->skip(4);
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		if (!$unicode_cmap_offset)
			die('Font ('.$this->filename .') does not have Unicode cmap (platform 3, encoding 1, format 4, or platform 0 [any encoding] format 4)');


		$glyphToChar = array();
		$charToGlyph = array();
		$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		$this->charToGlyph = $charToGlyph;


		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$scale = 1;	// not used
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// loca - Index to location
		///////////////////////////////////
		$this->getLOCA($indexToLocFormat, $numGlyphs);

		$subsetglyphs = array(0=>0, 1=>1, 2=>2);
		$subsetCharToGlyph = array();
		foreach($subset AS $code) {
			if (isset($this->charToGlyph[$code])) {
				$subsetglyphs[$this->charToGlyph[$code]] = $code;	// Old Glyph ID => Unicode
				$subsetCharToGlyph[$code] = $this->charToGlyph[$code];	// Unicode to old GlyphID

			}
			$this->maxUni = max($this->maxUni, $code);
		}

		list($start,$dummy) = $this->get_table_pos('glyf');

		$glyphSet = array();
		ksort($subsetglyphs);
		$n = 0;
		$fsLastCharIndex = 0;	// maximum Unicode index (character code) in this font, according to the cmap subtable for platform ID 3 and platform- specific encoding ID 0 or 1.
		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			$fsLastCharIndex = max($fsLastCharIndex , $uni); 
			$glyphSet[$originalGlyphIdx] = $n;	// old glyphID to new glyphID
			$n++;
		}

		ksort($subsetCharToGlyph);
		foreach($subsetCharToGlyph AS $uni => $originalGlyphIdx) {
			$codeToGlyph[$uni] = $glyphSet[$originalGlyphIdx] ;
		}
		$this->codeToGlyph = $codeToGlyph;

		ksort($subsetglyphs);
		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			$this->getGlyphs($originalGlyphIdx, $start, $glyphSet, $subsetglyphs);
		}

		$numGlyphs = $numberOfHMetrics = count($subsetglyphs );

		///////////////////////////////////
		// name - table copied from the original
		///////////////////////////////////
		$this->add('name', $this->get_table('name'));

		///////////////////////////////////
		//tables copied from the original
		///////////////////////////////////
		$tags = array ('cvt ', 'fpgm', 'prep', 'gasp');
		foreach($tags AS $tag) {
			if (isset($this->tables[$tag])) { $this->add($tag, $this->get_table($tag)); }
		}

		///////////////////////////////////
		// post - PostScript
		///////////////////////////////////
		if (isset($this->tables['post'])) {
			$opost = $this->get_table('post');
			$post = "\x00\x03\x00\x00" . substr($opost,4,12) . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
			$this->add('post', $post);
		}

		///////////////////////////////////
		// Sort CID2GID map into segments of contiguous codes
		///////////////////////////////////
		ksort($codeToGlyph);
		unset($codeToGlyph[0]);
		//unset($codeToGlyph[65535]);
		$rangeid = 0;
		$range = array();
		$prevcid = -2;
		$prevglidx = -1;
		// for each character
		foreach ($codeToGlyph as $cid => $glidx) {
			if ($cid == ($prevcid + 1) && $glidx == ($prevglidx + 1)) {
				$range[$rangeid][] = $glidx;
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $glidx;
			}
			$prevcid = $cid;
			$prevglidx = $glidx;
		}



		///////////////////////////////////
		// CMap table
		///////////////////////////////////
		// cmap - Character to glyph mapping 
		$segCount = count($range) + 1;	// + 1 Last segment has missing character 0xFFFF
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $segCount ) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 2;
		$rangeShift = $segCount * 2 - $searchRange;
		$length = 16 + (8*$segCount ) + ($numGlyphs+1);
		$cmap = array(0, 3,		// Index : version, number of encoding subtables
			0, 0,				// Encoding Subtable : platform (UNI=0), encoding 0
			0, 28,			// Encoding Subtable : offset (hi,lo)
			0, 3,				// Encoding Subtable : platform (UNI=0), encoding 3
			0, 28,			// Encoding Subtable : offset (hi,lo)
			3, 1,				// Encoding Subtable : platform (MS=3), encoding 1
			0, 28,			// Encoding Subtable : offset (hi,lo)
			4, $length, 0, 		// Format 4 Mapping subtable: format, length, language
			$segCount*2,
			$searchRange,
			$entrySelector,
			$rangeShift);

		// endCode(s)
		foreach($range AS $start=>$subrange) {
			$endCode = $start + (count($subrange)-1);
			$cmap[] = $endCode;	// endCode(s)
		}
		$cmap[] =	0xFFFF;	// endCode of last Segment
		$cmap[] =	0;	// reservedPad

		// startCode(s)
		foreach($range AS $start=>$subrange) {
			$cmap[] = $start;	// startCode(s)
		}
		$cmap[] =	0xFFFF;	// startCode of last Segment
		// idDelta(s) 
		foreach($range AS $start=>$subrange) {
			$idDelta = -($start-$subrange[0]);
			$n += count($subrange);
			$cmap[] = $idDelta;	// idDelta(s)
		}
		$cmap[] =	1;	// idDelta of last Segment
		// idRangeOffset(s) 
		foreach($range AS $subrange) {
			$cmap[] = 0;	// idRangeOffset[segCount]  	Offset in bytes to glyph indexArray, or 0

		}
		$cmap[] =	0;	// idRangeOffset of last Segment
		foreach($range AS $subrange) {
			foreach($subrange AS $glidx) {
				$cmap[] = $glidx;
			}
		}
		$cmap[] = 0;	// Mapping for last character
		$cmapstr = '';
		foreach($cmap AS $cm) { $cmapstr .= pack("n",$cm); }
		$this->add('cmap', $cmapstr);


		///////////////////////////////////
		// glyf - Glyph data
		///////////////////////////////////
		list($glyfOffset,$glyfLength) = $this->get_table_pos('glyf');
		if ($glyfLength < $this->maxStrLenRead) {
			$glyphData = $this->get_table('glyf');
		}

		$offsets = array();
		$glyf = '';
		$pos = 0;
		$hmtxstr = '';
		$xMinT = 0;
		$yMinT = 0;
		$xMaxT = 0;
		$yMaxT = 0;
		$advanceWidthMax = 0;
		$minLeftSideBearing = 0;
		$minRightSideBearing = 0;
		$xMaxExtent = 0;
		$maxPoints = 0;			// points in non-compound glyph
		$maxContours = 0;			// contours in non-compound glyph
		$maxComponentPoints = 0;	// points in compound glyph
		$maxComponentContours = 0;	// contours in compound glyph
		$maxComponentElements = 0;	// number of glyphs referenced at top level
		$maxComponentDepth = 0;		// levels of recursion, set to 0 if font has only simple glyphs
		$this->glyphdata = array();

		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			// hmtx - Horizontal Metrics
			$hm = $this->getHMetric($orignHmetrics, $originalGlyphIdx);
			$hmtxstr .= $hm;

			$offsets[] = $pos;
			$glyphPos = $this->glyphPos[$originalGlyphIdx];
			$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
			if ($glyfLength < $this->maxStrLenRead) {
				$data = substr($glyphData,$glyphPos,$glyphLen);
			}
			else {
				if ($glyphLen > 0) $data = $this->get_chunk($glyfOffset+$glyphPos,$glyphLen);
				else $data = '';
			}

			if ($glyphLen > 0) {
			  if (_RECALC_PROFILE) {
				$xMin = $this->unpack_short(substr($data,2,2));
				$yMin = $this->unpack_short(substr($data,4,2));
				$xMax = $this->unpack_short(substr($data,6,2));
				$yMax = $this->unpack_short(substr($data,8,2));
				$xMinT = min($xMinT,$xMin);
				$yMinT = min($yMinT,$yMin);
				$xMaxT = max($xMaxT,$xMax);
				$yMaxT = max($yMaxT,$yMax);
				$aw = $this->unpack_short(substr($hm,0,2)); 
				$lsb = $this->unpack_short(substr($hm,2,2));
				$advanceWidthMax = max($advanceWidthMax,$aw);
				$minLeftSideBearing = min($minLeftSideBearing,$lsb);
				$minRightSideBearing = min($minRightSideBearing,($aw - $lsb - ($xMax - $xMin)));
				$xMaxExtent = max($xMaxExtent,($lsb + ($xMax - $xMin)));
			   }
				$up = unpack("n", substr($data,0,2));
			}
			if ($glyphLen > 2 && ($up[1] & (1 << 15)) ) {	// If number of contours <= -1 i.e. composiste glyph
				$pos_in_glyph = 10;
				$flags = GF_MORE;
				$nComponentElements = 0;
				while ($flags & GF_MORE) {
					$nComponentElements += 1;	// number of glyphs referenced at top level
					$up = unpack("n", substr($data,$pos_in_glyph,2));
					$flags = $up[1];
					$up = unpack("n", substr($data,$pos_in_glyph+2,2));
					$glyphIdx = $up[1];
					$this->glyphdata[$originalGlyphIdx]['compGlyphs'][] = $glyphIdx;
					$data = $this->_set_ushort($data, $pos_in_glyph + 2, $glyphSet[$glyphIdx]);
					$pos_in_glyph += 4;
					if ($flags & GF_WORDS) { $pos_in_glyph += 4; }
					else { $pos_in_glyph += 2; }
					if ($flags & GF_SCALE) { $pos_in_glyph += 2; }
					else if ($flags & GF_XYSCALE) { $pos_in_glyph += 4; }
					else if ($flags & GF_TWOBYTWO) { $pos_in_glyph += 8; }
				}
				$maxComponentElements = max($maxComponentElements, $nComponentElements);
			}
			// Simple Glyph
			else if (_RECALC_PROFILE && $glyphLen > 2 && $up[1] < (1 << 15) && $up[1] > 0) { 	// Number of contours > 0 simple glyph
				$nContours = $up[1];
				$this->glyphdata[$originalGlyphIdx]['nContours'] = $nContours;
				$maxContours = max($maxContours, $nContours);

				// Count number of points in simple glyph
				$pos_in_glyph = 10 + ($nContours  * 2) - 2;	// Last endContourPoint
				$up = unpack("n", substr($data,$pos_in_glyph,2));
				$points = $up[1]+1;
				$this->glyphdata[$originalGlyphIdx]['nPoints'] = $points;
				$maxPoints = max($maxPoints, $points);
			}

			$glyf .= $data;
			$pos += $glyphLen;
			if ($pos % 4 != 0) {
				$padding = 4 - ($pos % 4);
				$glyf .= str_repeat("\0",$padding);
				$pos += $padding;
			}
		}

		if (_RECALC_PROFILE) {
		   foreach($this->glyphdata AS $originalGlyphIdx => $val) {
			$maxdepth = $depth = -1;
			$points = 0;
			$contours = 0;
			$this->getGlyphData($originalGlyphIdx, $maxdepth, $depth, $points, $contours) ;
			$maxComponentDepth = max($maxComponentDepth , $maxdepth);
			$maxComponentPoints = max($maxComponentPoints , $points);
			$maxComponentContours = max($maxComponentContours , $contours);
		   }
		}


		$offsets[] = $pos;
		$this->add('glyf', $glyf);

		///////////////////////////////////
		// hmtx - Horizontal Metrics
		///////////////////////////////////
		$this->add('hmtx', $hmtxstr);


		///////////////////////////////////
		// loca - Index to location
		///////////////////////////////////
		$locastr = '';
		if ((($pos + 1) >> 1) > 0xFFFF) {
			$indexToLocFormat = 1;        // long format
			foreach($offsets AS $offset) { $locastr .= pack("N",$offset); }
		}
		else {
			$indexToLocFormat = 0;        // short format
			foreach($offsets AS $offset) { $locastr .= pack("n",($offset/2)); }
		}
		$this->add('loca', $locastr);

		///////////////////////////////////
		// head - Font header
		///////////////////////////////////
		$head = $this->get_table('head');
		$head = $this->_set_ushort($head, 50, $indexToLocFormat);
		if (_RECALC_PROFILE) {
			$head = $this->_set_short($head, 36, $xMinT);	// for all glyph bounding boxes
			$head = $this->_set_short($head, 38, $yMinT);	// for all glyph bounding boxes
			$head = $this->_set_short($head, 40, $xMaxT);	// for all glyph bounding boxes
			$head = $this->_set_short($head, 42, $yMaxT);	// for all glyph bounding boxes
			$head[17] = chr($head[17] & ~(1 << 4)); 	// Unset Bit 4 (as hdmx/LTSH tables not included)
		}
		$this->add('head', $head);


		///////////////////////////////////
		// hhea - Horizontal Header
		///////////////////////////////////
		$hhea = $this->get_table('hhea');
		$hhea = $this->_set_ushort($hhea, 34, $numberOfHMetrics);
		if (_RECALC_PROFILE) {
			$hhea = $this->_set_ushort($hhea, 10, $advanceWidthMax);	
			$hhea = $this->_set_short($hhea, 12, $minLeftSideBearing);	
			$hhea = $this->_set_short($hhea, 14, $minRightSideBearing);	
			$hhea = $this->_set_short($hhea, 16, $xMaxExtent);	
		}
		$this->add('hhea', $hhea);

		///////////////////////////////////
		// maxp - Maximum Profile
		///////////////////////////////////
		$maxp = $this->get_table('maxp');
		$maxp = $this->_set_ushort($maxp, 4, $numGlyphs);
		if (_RECALC_PROFILE) {
			$maxp = $this->_set_ushort($maxp, 6, $maxPoints);	// points in non-compound glyph
			$maxp = $this->_set_ushort($maxp, 8, $maxContours);	// contours in non-compound glyph
			$maxp = $this->_set_ushort($maxp, 10, $maxComponentPoints);	// points in compound glyph
			$maxp = $this->_set_ushort($maxp, 12, $maxComponentContours);	// contours in compound glyph
			$maxp = $this->_set_ushort($maxp, 28, $maxComponentElements);	// number of glyphs referenced at top level
			$maxp = $this->_set_ushort($maxp, 30, $maxComponentDepth);	// levels of recursion, set to 0 if font has only simple glyphs
		}
		$this->add('maxp', $maxp);


		///////////////////////////////////
		// OS/2 - OS/2
		///////////////////////////////////
		if (isset($this->tables['OS/2'])) { 
			$os2_offset = $this->seek_table("OS/2");
			if (_RECALC_PROFILE) {
				$fsSelection = $this->get_ushort($os2_offset+62);
				$fsSelection = ($fsSelection & ~(1 << 6)); 	// 2-byte bit field containing information concerning the nature of the font patterns
					// bit#0 = Italic; bit#5=Bold
					// Match name table's font subfamily string
					// Clear bit#6 used for 'Regular' and optional
			}

			// NB Currently this method never subsets characters above BMP
			// Could set nonBMP bit according to $this->maxUni 
			$nonBMP = $this->get_ushort($os2_offset+46);
			$nonBMP = ($nonBMP & ~(1 << 9)); 	// Unset Bit 57 (indicates non-BMP) - for interactive forms

			$os2 = $this->get_table('OS/2');
			if (_RECALC_PROFILE) {
				$os2 = $this->_set_ushort($os2, 62, $fsSelection);	
				$os2 = $this->_set_ushort($os2, 66, $fsLastCharIndex);
				$os2 = $this->_set_ushort($os2, 42, 0x0000);	// ulCharRange (ulUnicodeRange) bits 24-31 | 16-23
				$os2 = $this->_set_ushort($os2, 44, 0x0000);	// ulCharRange (Unicode ranges) bits  8-15 |  0-7
				$os2 = $this->_set_ushort($os2, 46, $nonBMP);	// ulCharRange (Unicode ranges) bits 56-63 | 48-55
				$os2 = $this->_set_ushort($os2, 48, 0x0000);	// ulCharRange (Unicode ranges) bits 40-47 | 32-39
				$os2 = $this->_set_ushort($os2, 50, 0x0000);	// ulCharRange (Unicode ranges) bits  88-95 | 80-87
				$os2 = $this->_set_ushort($os2, 52, 0x0000);	// ulCharRange (Unicode ranges) bits  72-79 | 64-71
				$os2 = $this->_set_ushort($os2, 54, 0x0000);	// ulCharRange (Unicode ranges) bits  120-127 | 112-119
				$os2 = $this->_set_ushort($os2, 56, 0x0000);	// ulCharRange (Unicode ranges) bits  104-111 | 96-103
			}
			$os2 = $this->_set_ushort($os2, 46, $nonBMP);	// Unset Bit 57 (indicates non-BMP) - for interactive forms

			$this->add('OS/2', $os2 );
		}

		fclose($this->fh);
		// Put the TTF file together
		$stm = '';
		$this->endTTFile($stm);
		//file_put_contents('testfont.ttf', $stm); exit;
		return $stm ;
	}

//================================================================================

	// Also does SMP
	function makeSubsetSIP($file, &$subset, $TTCfontID=0, $debug=false) {
		$this->fh = fopen($file ,'rb') or die('Can\'t open file ' . $file);
		$this->filename = $file;
		$this->_pos = 0;
		$this->unAGlyphs = false;	// mPDF 5.4.05
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->skip(4);
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);



		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$this->skip(50); 
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		$this->skip(32); 
		$metricDataFormat = $this->read_ushort();
		$orignHmetrics = $numberOfHMetrics = $this->read_ushort();

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		$this->skip(4);
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////

		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 10) || $platformID == 0) { // Microsoft, Unicode Format 12 table HKCS
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		if (!$unicode_cmap_offset)
			die('Font does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');
		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12) {
			$this->maxUniChar = 0;
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);

			$nGroups = $this->read_ulong();

			$glyphToChar = array();
			$charToGlyph = array();
			for($i=0; $i<$nGroups ; $i++) { 
				$startCharCode = $this->read_ulong(); 
				$endCharCode = $this->read_ulong(); 
				$startGlyphCode = $this->read_ulong(); 
				$offset = 0;
				for ($unichar=$startCharCode;$unichar<=$endCharCode;$unichar++) {
					$glyph = $startGlyphCode + $offset ;
					$offset++;
					$charToGlyph[$unichar] = $glyph;
					if ($unichar < 196608) { $this->maxUniChar = max($unichar,$this->maxUniChar); }
					$glyphToChar[$glyph][] = $unichar;
				}
			}
		}
		else 
			die('Font does not have cmap for Unicode (format 12)');


		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$scale = 1; // not used here
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// loca - Index to location
		///////////////////////////////////
		$this->getLOCA($indexToLocFormat, $numGlyphs);

		///////////////////////////////////////////////////////////////////

		$glyphMap = array(0=>0); 
		$glyphSet = array(0=>0);
		$codeToGlyph = array();
		// Set a substitute if ASCII characters do not have glyphs
		if (isset($charToGlyph[0x3F])) { $subs = $charToGlyph[0x3F]; }	// Question mark
		else { $subs = $charToGlyph[32]; }
		foreach($subset AS $code) {
			if (isset($charToGlyph[$code]))
				$originalGlyphIdx = $charToGlyph[$code];
			else if ($code<128) {
				$originalGlyphIdx = $subs;
			}
			else { $originalGlyphIdx = 0; }
			if (!isset($glyphSet[$originalGlyphIdx])) {
				$glyphSet[$originalGlyphIdx] = count($glyphMap);
				$glyphMap[] = $originalGlyphIdx;
			}
			$codeToGlyph[$code] = $glyphSet[$originalGlyphIdx];
		}

		list($start,$dummy) = $this->get_table_pos('glyf');

		$n = 0;
		while ($n < count($glyphMap)) {
			$originalGlyphIdx = $glyphMap[$n];
			$glyphPos = $this->glyphPos[$originalGlyphIdx];
			$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
			$n += 1;
			if (!$glyphLen) continue;
			$this->seek($start + $glyphPos);
			$numberOfContours = $this->read_short();
			if ($numberOfContours < 0) {
				$this->skip(8);
				$flags = GF_MORE;
				while ($flags & GF_MORE) {
					$flags = $this->read_ushort();
					$glyphIdx = $this->read_ushort();
					if (!isset($glyphSet[$glyphIdx])) {
						$glyphSet[$glyphIdx] = count($glyphMap);
						$glyphMap[] = $glyphIdx;
					}
					if ($flags & GF_WORDS)
						$this->skip(4);
					else
						$this->skip(2);
					if ($flags & GF_SCALE)
						$this->skip(2);
					else if ($flags & GF_XYSCALE)
						$this->skip(4);
					else if ($flags & GF_TWOBYTWO)
						$this->skip(8);
				}
			}
		}

		$numGlyphs = $n = count($glyphMap);
		$numberOfHMetrics = $n;

		///////////////////////////////////
		// name
		///////////////////////////////////
		// Needs to have a name entry in 3,0 (e.g. symbol) - original font will be 3,1 (i.e. Unicode)
		$name = $this->get_table('name'); 
		$name_offset = $this->seek_table("name");
		$format = $this->read_ushort();
		$numRecords = $this->read_ushort();
		$string_data_offset = $name_offset + $this->read_ushort();
		for ($i=0;$i<$numRecords; $i++) {
			$platformId = $this->read_ushort();
			$encodingId = $this->read_ushort();
			if ($platformId == 3 && $encodingId == 1) {
				$pos = 6 + ($i * 12) + 2;
				$name = $this->_set_ushort($name, $pos, 0x00);	// Change encoding to 3,0 rather than 3,1
			}
			$this->skip(8);
		}
		$this->add('name', $name);

		///////////////////////////////////
		// OS/2
		///////////////////////////////////
		if (isset($this->tables['OS/2'])) { 
			$os2 = $this->get_table('OS/2');
			$os2 = $this->_set_ushort($os2, 42, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 44, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 46, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 48, 0x00);	// ulCharRange (Unicode ranges)

			$os2 = $this->_set_ushort($os2, 50, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 52, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 54, 0x00);	// ulCharRange (Unicode ranges)
			$os2 = $this->_set_ushort($os2, 56, 0x00);	// ulCharRange (Unicode ranges)
			// Set Symbol character only in ulCodePageRange
			$os2 = $this->_set_ushort($os2, 78, 0x8000);	// ulCodePageRange = Bit #31 Symbol ****  78 = Bit 16-31
			$os2 = $this->_set_ushort($os2, 80, 0x0000);	// ulCodePageRange = Bit #31 Symbol ****  80 = Bit 0-15
			$os2 = $this->_set_ushort($os2, 82, 0x0000);	// ulCodePageRange = Bit #32- Symbol **** 82 = Bits 48-63
			$os2 = $this->_set_ushort($os2, 84, 0x0000);	// ulCodePageRange = Bit #32- Symbol **** 84 = Bits 32-47
	
			$os2 = $this->_set_ushort($os2, 64, 0x01);		// FirstCharIndex
			$os2 = $this->_set_ushort($os2, 66, count($subset));		// LastCharIndex
			// Set PANOSE first bit to 5 for Symbol
			$os2 = $this->splice($os2, 32, chr(5).chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(0).chr(0).chr(0));
			$this->add('OS/2', $os2 );
		}


		///////////////////////////////////
		//tables copied from the original
		///////////////////////////////////
		$tags = array ('cvt ', 'fpgm', 'prep', 'gasp');	
		foreach($tags AS $tag) { 	// 1.02
			if (isset($this->tables[$tag])) { $this->add($tag, $this->get_table($tag)); } 
		}

		///////////////////////////////////
		// post - PostScript
		///////////////////////////////////
		if (isset($this->tables['post'])) { 
			$opost = $this->get_table('post');
			$post = "\x00\x03\x00\x00" . substr($opost,4,12) . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
		}
		$this->add('post', $post);

		///////////////////////////////////
		// hhea - Horizontal Header
		///////////////////////////////////
		$hhea = $this->get_table('hhea');
		$hhea = $this->_set_ushort($hhea, 34, $numberOfHMetrics);
		$this->add('hhea', $hhea);

		///////////////////////////////////
		// maxp - Maximum Profile
		///////////////////////////////////
		$maxp = $this->get_table('maxp');
		$maxp = $this->_set_ushort($maxp, 4, $numGlyphs);
		$this->add('maxp', $maxp);


		///////////////////////////////////
		// CMap table Formats [1,0,]6 and [3,0,]4
		///////////////////////////////////
		///////////////////////////////////
		// Sort CID2GID map into segments of contiguous codes
		///////////////////////////////////
		$rangeid = 0;
		$range = array();
		$prevcid = -2;
		$prevglidx = -1;
		// for each character
		foreach ($subset as $cid => $code) {
			$glidx = $codeToGlyph[$code]; 
			if ($cid == ($prevcid + 1) && $glidx == ($prevglidx + 1)) {
				$range[$rangeid][] = $glidx;
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $glidx;
			}
			$prevcid = $cid;
			$prevglidx = $glidx;
		}
		// cmap - Character to glyph mapping 
		$segCount = count($range) + 1;	// + 1 Last segment has missing character 0xFFFF
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $segCount ) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 2;
		$rangeShift = $segCount * 2 - $searchRange;
		$length = 16 + (8*$segCount ) + ($numGlyphs+1);
		$cmap = array(
			4, $length, 0, 		// Format 4 Mapping subtable: format, length, language
			$segCount*2,
			$searchRange,
			$entrySelector,
			$rangeShift);

		// endCode(s)
		foreach($range AS $start=>$subrange) {
			$endCode = $start + (count($subrange)-1);
			$cmap[] = $endCode;	// endCode(s)
		}
		$cmap[] =	0xFFFF;	// endCode of last Segment
		$cmap[] =	0;	// reservedPad

		// startCode(s)
		foreach($range AS $start=>$subrange) {
			$cmap[] = $start;	// startCode(s)
		}
		$cmap[] =	0xFFFF;	// startCode of last Segment
		// idDelta(s) 
		foreach($range AS $start=>$subrange) {
			$idDelta = -($start-$subrange[0]);
			$n += count($subrange);
			$cmap[] = $idDelta;	// idDelta(s)
		}
		$cmap[] =	1;	// idDelta of last Segment
		// idRangeOffset(s) 
		foreach($range AS $subrange) {
			$cmap[] = 0;	// idRangeOffset[segCount]  	Offset in bytes to glyph indexArray, or 0

		}
		$cmap[] =	0;	// idRangeOffset of last Segment
		foreach($range AS $subrange) {
			foreach($subrange AS $glidx) {
				$cmap[] = $glidx;
			}
		}
		$cmap[] = 0;	// Mapping for last character
		$cmapstr4 = '';
		foreach($cmap AS $cm) { $cmapstr4 .= pack("n",$cm); }

		///////////////////////////////////
		// cmap - Character to glyph mapping
		///////////////////////////////////
		$entryCount = count($subset);
		$length = 10 + $entryCount * 2;

		$off = 20 + $length;
		$hoff = $off >> 16;
		$loff = $off & 0xFFFF;

		$cmap = array(0, 2,	// Index : version, number of subtables
			1, 0,			// Subtable : platform, encoding
			0, 20,		// offset (hi,lo)
			3, 0,			// Subtable : platform, encoding
			$hoff, $loff,	// offset (hi,lo)
			6, $length, 	// Format 6 Mapping table: format, length
			0, 1,			// language, First char code
			$entryCount
		);
		$cmapstr = '';
		foreach($subset AS $code) { $cmap[] = $codeToGlyph[$code]; }
		foreach($cmap AS $cm) { $cmapstr .= pack("n",$cm); }
		$cmapstr .= $cmapstr4;
		$this->add('cmap', $cmapstr);

		///////////////////////////////////
		// hmtx - Horizontal Metrics
		///////////////////////////////////
		$hmtxstr = '';
		for($n=0;$n<$numGlyphs;$n++) {
			$originalGlyphIdx = $glyphMap[$n];
			$hm = $this->getHMetric($orignHmetrics, $originalGlyphIdx);
			$hmtxstr .= $hm;
		}
		$this->add('hmtx', $hmtxstr);

		///////////////////////////////////
		// glyf - Glyph data
		///////////////////////////////////
		list($glyfOffset,$glyfLength) = $this->get_table_pos('glyf');
		if ($glyfLength < $this->maxStrLenRead) {
			$glyphData = $this->get_table('glyf');
		}

		$offsets = array();
		$glyf = '';
		$pos = 0;
		for ($n=0;$n<$numGlyphs;$n++) {
			$offsets[] = $pos;
			$originalGlyphIdx = $glyphMap[$n];
			$glyphPos = $this->glyphPos[$originalGlyphIdx];
			$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
			if ($glyfLength < $this->maxStrLenRead) {
				$data = substr($glyphData,$glyphPos,$glyphLen);
			}
			else {
				if ($glyphLen > 0) $data = $this->get_chunk($glyfOffset+$glyphPos,$glyphLen);
				else $data = '';
			}
			if ($glyphLen > 0) $up = unpack("n", substr($data,0,2));
			if ($glyphLen > 2 && ($up[1] & (1 << 15)) ) {
				$pos_in_glyph = 10;
				$flags = GF_MORE;
				while ($flags & GF_MORE) {
					$up = unpack("n", substr($data,$pos_in_glyph,2));
					$flags = $up[1];
					$up = unpack("n", substr($data,$pos_in_glyph+2,2));
					$glyphIdx = $up[1];
					$data = $this->_set_ushort($data, $pos_in_glyph + 2, $glyphSet[$glyphIdx]);
					$pos_in_glyph += 4;
					if ($flags & GF_WORDS) { $pos_in_glyph += 4; }
					else { $pos_in_glyph += 2; }
					if ($flags & GF_SCALE) { $pos_in_glyph += 2; }
					else if ($flags & GF_XYSCALE) { $pos_in_glyph += 4; }
					else if ($flags & GF_TWOBYTWO) { $pos_in_glyph += 8; }
				}
			}
			$glyf .= $data;
			$pos += $glyphLen;
			if ($pos % 4 != 0) {
				$padding = 4 - ($pos % 4);
				$glyf .= str_repeat("\0",$padding);
				$pos += $padding;
			}
		}
		$offsets[] = $pos;
		$this->add('glyf', $glyf);

		///////////////////////////////////
		// loca - Index to location
		///////////////////////////////////
		$locastr = '';
		if ((($pos + 1) >> 1) > 0xFFFF) {
			$indexToLocFormat = 1;        // long format
			foreach($offsets AS $offset) { $locastr .= pack("N",$offset); }
		}
		else {
			$indexToLocFormat = 0;        // short format
			foreach($offsets AS $offset) { $locastr .= pack("n",($offset/2)); }
		}
		$this->add('loca', $locastr);

		///////////////////////////////////
		// head - Font header
		///////////////////////////////////
		$head = $this->get_table('head');
		$head = $this->_set_ushort($head, 50, $indexToLocFormat);
		$this->add('head', $head);

		fclose($this->fh);

		// Put the TTF file together
		$stm = '';
		$this->endTTFile($stm);
		//file_put_contents('testfont.ttf', $stm); exit;
		return $stm ;
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyph data
	function getGlyphData($originalGlyphIdx, &$maxdepth, &$depth, &$points, &$contours) {
		$depth++;
		$maxdepth = max($maxdepth, $depth);
		if (count($this->glyphdata[$originalGlyphIdx]['compGlyphs'])) {
			foreach($this->glyphdata[$originalGlyphIdx]['compGlyphs'] AS $glyphIdx) {
				$this->getGlyphData($glyphIdx, $maxdepth, $depth, $points, $contours);
			}
		}
		else if (($this->glyphdata[$originalGlyphIdx]['nContours'] > 0) && $depth > 0) {	// simple
			$contours += $this->glyphdata[$originalGlyphIdx]['nContours'];
			$points += $this->glyphdata[$originalGlyphIdx]['nPoints'];
		}
		$depth--;
	}


	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyphs
	function getGlyphs($originalGlyphIdx, &$start, &$glyphSet, &$subsetglyphs) {
		$glyphPos = $this->glyphPos[$originalGlyphIdx];
		$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
		if (!$glyphLen) { 
			return;
		}
		$this->seek($start + $glyphPos);
		$numberOfContours = $this->read_short();
		if ($numberOfContours < 0) {
			$this->skip(8);
			$flags = GF_MORE;
			while ($flags & GF_MORE) {
				$flags = $this->read_ushort();
				$glyphIdx = $this->read_ushort();
				if (!isset($glyphSet[$glyphIdx])) {
					$glyphSet[$glyphIdx] = count($subsetglyphs);	// old glyphID to new glyphID
					$subsetglyphs[$glyphIdx] = true;
				}
				$savepos = ftell($this->fh);
				$this->getGlyphs($glyphIdx, $start, $glyphSet, $subsetglyphs);
				$this->seek($savepos);
				if ($flags & GF_WORDS)
					$this->skip(4);
				else
					$this->skip(2);
				if ($flags & GF_SCALE)
					$this->skip(2);
				else if ($flags & GF_XYSCALE)
					$this->skip(4);
				else if ($flags & GF_TWOBYTWO)
					$this->skip(8);
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////////

	function getHMTX($numberOfHMetrics, $numGlyphs, &$glyphToChar, $scale) {
		$start = $this->seek_table("hmtx");
		$aw = 0;
		$this->charWidths = str_pad('', 256*256*2, "\x00");
		if ($this->maxUniChar > 65536) { $this->charWidths .= str_pad('', 256*256*2, "\x00"); }	// Plane 1 SMP
		if ($this->maxUniChar > 131072) { $this->charWidths .= str_pad('', 256*256*2, "\x00"); }	// Plane 2 SMP
		$nCharWidths = 0;
		if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
			$data = $this->get_chunk($start,($numberOfHMetrics*4));
			$arr = unpack("n*", $data);
		}
		else { $this->seek($start); }
		for( $glyph=0; $glyph<$numberOfHMetrics; $glyph++) {
			if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
				$aw = $arr[($glyph*2)+1];
			}
			else {
				$aw = $this->read_ushort();
				$lsb = $this->read_ushort();
			}
			if (isset($glyphToChar[$glyph]) || $glyph == 0) {

				if ($aw >= (1 << 15) ) { $aw = 0; }	// 1.03 Some (arabic) fonts have -ve values for width
					// although should be unsigned value - comes out as e.g. 65108 (intended -50)
				if ($glyph == 0) {
					$this->defaultWidth = $scale*$aw;
					continue;
				}
				foreach($glyphToChar[$glyph] AS $char) {
					//$this->charWidths[$char] = intval(round($scale*$aw));
					if ($char != 0 && $char != 65535) {
 						$w = intval(round($scale*$aw));
						if ($w == 0) { $w = 65535; }
						if ($char < 196608) {
							$this->charWidths[$char*2] = chr($w >> 8);
							$this->charWidths[$char*2 + 1] = chr($w & 0xFF);
							$nCharWidths++;
						}
					}
				}
			}
		}
		$data = $this->get_chunk(($start+$numberOfHMetrics*4),($numGlyphs*2));
		$arr = unpack("n*", $data);
		$diff = $numGlyphs-$numberOfHMetrics;
		$w = intval(round($scale*$aw));
		if ($w == 0) { $w = 65535; }
		for( $pos=0; $pos<$diff; $pos++) {
			$glyph = $pos + $numberOfHMetrics;
			if (isset($glyphToChar[$glyph])) {
				foreach($glyphToChar[$glyph] AS $char) {
					if ($char != 0 && $char != 65535) {
						if ($char < 196608) { 
							$this->charWidths[$char*2] = chr($w >> 8);
							$this->charWidths[$char*2 + 1] = chr($w & 0xFF);
							$nCharWidths++;
						}
					}
				}
			}
		}
		// NB 65535 is a set width of 0
		// First bytes define number of chars in font
		$this->charWidths[0] = chr($nCharWidths >> 8);
		$this->charWidths[1] = chr($nCharWidths & 0xFF);
	}

	function getHMetric($numberOfHMetrics, $gid) {
		$start = $this->seek_table("hmtx");
		if ($gid < $numberOfHMetrics) {
			$this->seek($start+($gid*4));
			$hm = fread($this->fh,4);
		}
		else {
			$this->seek($start+(($numberOfHMetrics-1)*4));
			$hm = fread($this->fh,2);
			$this->seek($start+($numberOfHMetrics*2)+($gid*2));
			$hm .= fread($this->fh,2);
		}
		return $hm;
	}

	function getLOCA($indexToLocFormat, $numGlyphs) {
		$start = $this->seek_table('loca');
		$this->glyphPos = array();
		if ($indexToLocFormat == 0) {
			$data = $this->get_chunk($start,($numGlyphs*2)+2);
			$arr = unpack("n*", $data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n+1] * 2);
			}
		}
		else if ($indexToLocFormat == 1) {
			$data = $this->get_chunk($start,($numGlyphs*4)+4);
			$arr = unpack("N*", $data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n+1]);
			}
		}
		else 
			die('Unknown location table format '.$indexToLocFormat);
	}


	// CMAP Format 4
	function getCMAP4($unicode_cmap_offset, &$glyphToChar, &$charToGlyph ) {
		$this->maxUniChar = 0;	
		$this->seek($unicode_cmap_offset + 2);
		$length = $this->read_ushort();
		$limit = $unicode_cmap_offset + $length;
		$this->skip(2);

		$segCount = $this->read_ushort() / 2;
		$this->skip(6);
		$endCount = array();
		for($i=0; $i<$segCount; $i++) { $endCount[] = $this->read_ushort(); }
		$this->skip(2);
		$startCount = array();
		for($i=0; $i<$segCount; $i++) { $startCount[] = $this->read_ushort(); }
		$idDelta = array();
		for($i=0; $i<$segCount; $i++) { $idDelta[] = $this->read_short(); }		// ???? was unsigned short
		$idRangeOffset_start = $this->_pos;
		$idRangeOffset = array();
		for($i=0; $i<$segCount; $i++) { $idRangeOffset[] = $this->read_ushort(); }

		for ($n=0;$n<$segCount;$n++) {
			$endpoint = ($endCount[$n] + 1);
			for ($unichar=$startCount[$n];$unichar<$endpoint;$unichar++) {
				if ($idRangeOffset[$n] == 0)
					$glyph = ($unichar + $idDelta[$n]) & 0xFFFF;
				else {
					$offset = ($unichar - $startCount[$n]) * 2 + $idRangeOffset[$n];
					$offset = $idRangeOffset_start + 2 * $n + $offset;
					if ($offset >= $limit)
						$glyph = 0;
					else {
						$glyph = $this->get_ushort($offset);
						if ($glyph != 0)
						   $glyph = ($glyph + $idDelta[$n]) & 0xFFFF;
					}
				}
				$charToGlyph[$unichar] = $glyph;
				if ($unichar < 196608) { $this->maxUniChar = max($unichar,$this->maxUniChar); }
				$glyphToChar[$glyph][] = $unichar;
			}
		}

		// mPDF 5.4.05
		if ($this->unAGlyphs) {
		  if (isset($this->tables['post'])) {
			$this->seek_table("post");
			$formata = $this->read_ushort();
			$formatb = $this->read_ushort();
			// Only works on Format 2.0
			if ($formata != 2 || $formatb != 0) { die("Cannot set unAGlyphs for this font (".$file."). POST table must be in Format 2."); }
			$this->skip(28);
			$nGlyfs = $this->read_ushort();
			$glyphNameIndex = array();
			for ($i=0; $i<$nGlyfs; $i++) {
				$glyphNameIndex[($this->read_ushort())] = $i;
			}

			$opost = $this->get_table('post');
			$ptr = 34+($nGlyfs*2);
			for ($i=0; $i<$nGlyfs; $i++) {
				$len = ord(substr($opost,$ptr,1));
				$ptr++;
				$name = substr($opost,$ptr,$len);
				$gid = $glyphNameIndex[$i+258];
				// Select uni0600.xxx(x) - uni06FF.xxx(x)
				if (preg_match('/^uni(06[0-9a-f]{2})\.(fina|medi|init|fin|med|ini)$/i',$name,$m)) {
				  if (!isset($glyphToChar[$gid]) || (isset($glyphToChar[$gid]) && is_array($glyphToChar[$gid]) && count($glyphToChar[$gid])==1 && $glyphToChar[$gid][0]>57343 && $glyphToChar[$gid][0]<63489)) {	// if set in PUA private use area E000-F8FF, or NOT Unicode mapped
					$uni = hexdec($m[1]);
					$form = strtoupper(substr($m[2],0,1));
					// Assign new PUA Unicode between F500 - F7FF
					$bit = $uni & 0xFF;
					if ($form == 'I') { $bit += 0xF600; }
					else if ($form == 'M') { $bit += 0xF700; }
					else  { $bit += 0xF500; }
					// ADD TO CMAP
					$glyphToChar[$gid][] = $bit;
					$charToGlyph[$bit] = $gid;
				  }
				}
				// LAM with ALEF ligatures (Mandatory ligatures)
				else if (preg_match('/^uni064406(22|23|25|27)(\.fina|\.fin){0,1}$/i',$name,$m)) {
				  if ($m[1]=='22') {
					if ($m[2]) { $uni = hexdec('FEF6'); } else { $uni = hexdec('FEF5'); } 
				  }
				  else if ($m[1]=='23') {
					if ($m[2]) { $uni = hexdec('FEF8'); } else { $uni = hexdec('FEF7'); } 
				  }
				  else if ($m[1]=='25') {
					if ($m[2]) { $uni = hexdec('FEFA'); } else { $uni = hexdec('FEF9'); } 
				  }
				  else if ($m[1]=='27') {
					if ($m[2]) { $uni = hexdec('FEFC'); } else { $uni = hexdec('FEFB'); } 
				  }
				  if (!isset($glyphToChar[$gid]) || (isset($glyphToChar[$gid]) && is_array($glyphToChar[$gid]) && count($glyphToChar[$gid])==1 && $glyphToChar[$gid][0]>57343 && $glyphToChar[$gid][0]<63489)) {	// if set in PUA private use area E000-F8FF, or NOT Unicode mapped
					// ADD TO CMAP
					$glyphToChar[$gid][] = $uni;
					$charToGlyph[$uni] = $gid;
				  }
				}
				$ptr += $len;
			}
		  }
		}

	}


		// Put the TTF file together
	function endTTFile(&$stm) {
		$stm = '';
		$numTables = count($this->otables);
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $numTables) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 16;
		$rangeShift = $numTables * 16 - $searchRange;

		// Header
		if (_TTF_MAC_HEADER) {
			$stm .= (pack("Nnnnn", 0x74727565, $numTables, $searchRange, $entrySelector, $rangeShift));	// Mac
		}
		else {
			$stm .= (pack("Nnnnn", 0x00010000 , $numTables, $searchRange, $entrySelector, $rangeShift));	// Windows
		}

		// Table directory
		$tables = $this->otables;
		ksort ($tables); 
		$offset = 12 + $numTables * 16;
		foreach ($tables AS $tag=>$data) {
			if ($tag == 'head') { $head_start = $offset; }
			$stm .= $tag;
			$checksum = $this->calcChecksum($data);
			$stm .= pack("nn", $checksum[0],$checksum[1]);
			$stm .= pack("NN", $offset, strlen($data));
			$paddedLength = (strlen($data)+3)&~3;
			$offset = $offset + $paddedLength;
		}

		// Table data
		foreach ($tables AS $tag=>$data) {
			$data .= "\0\0\0";
			$stm .= substr($data,0,(strlen($data)&~3));
		}

		$checksum = $this->calcChecksum($stm);
		$checksum = $this->sub32(array(0xB1B0,0xAFBA), $checksum);
		$chk = pack("nn", $checksum[0],$checksum[1]);
		$stm = $this->splice($stm,($head_start + 8),$chk);
		return $stm ;
	}


	function repackageTTF($file, $TTCfontID=0, $debug=false, $unAGlyphs=false) {	// mPDF 5.4.05
		$this->unAGlyphs = $unAGlyphs;	// mPDF 5.4.05
		$this->filename = $file;
		$this->fh = fopen($file ,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->skip(4);
		$this->maxUni = 0;
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong();	// TTC Header version now
			if (!in_array($version, array(0x00010000,0x00020000)))
				die("ERROR - Error parsing TrueType Collection: version=".$version." - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
		}
		$this->readTableDirectory($debug);
		$tags = array ('OS/2', 'cmap', 'glyf', 'head', 'hhea', 'hmtx', 'loca', 'maxp', 'name', 'post', 'cvt ', 'fpgm', 'gasp', 'prep');
/*
Tables which require glyphIndex
hdmx
kern
LTSH

Tables which do NOT require glyphIndex
VDMX

GDEF
GPOS
GSUB
JSTF

DSIG
PCLT - not recommended
*/

		foreach($tags AS $tag) {
			if (isset($this->tables[$tag])) { $this->add($tag, $this->get_table($tag)); }
		}
		fclose($this->fh);
		$stm = '';
		$this->endTTFile($stm);
		return $stm ;
	}


}


?>