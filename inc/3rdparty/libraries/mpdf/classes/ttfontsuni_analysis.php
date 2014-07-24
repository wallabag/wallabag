<?php

require_once(_MPDF_PATH.'classes/ttfontsuni.php');

class TTFontFile_Analysis EXTENDS TTFontFile {

	// Used to get font information from files in directory
	function extractCoreInfo($file, $TTCfontID=0) {
		$this->filename = $file;
		$this->fh = fopen($file,'rb');
		if (!$this->fh) { return ('ERROR - Can\'t open file ' . $file); }
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
		$this->version = $version = $this->read_ulong();
		$this->panose = array();	// mPDF 5.0
		if ($version==0x4F54544F) 
			return("ERROR - NOT ADDED as Postscript outlines are not supported - " . $file);
		if ($version==0x74746366) {
			if ($TTCfontID > 0) {
				$this->version = $version = $this->read_ulong();	// TTC Header version now
				if (!in_array($version, array(0x00010000,0x00020000)))
					return("ERROR - NOT ADDED as Error parsing TrueType Collection: version=".$version." - " . $file);
			}
			else return("ERROR - Error parsing TrueType Collection - " . $file);
			$this->numTTCFonts = $this->read_ulong();
			for ($i=1; $i<=$this->numTTCFonts; $i++) {
	      	      $this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong();	// TTFont version again now
			$this->readTableDirectory(false);
		}
		else {
			if (!in_array($version, array(0x00010000,0x74727565)))
				return("ERROR - NOT ADDED as Not a TrueType font: version=".$version." - " . $file);
			$this->readTableDirectory(false);
		}

/* Included for testing...
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$x[$i]['platformId'] = $this->read_ushort();
			$x[$i]['encodingId'] = $this->read_ushort();
			$x[$i]['offset'] = $this->read_ulong();
			$save_pos = $this->_pos;
			$x[$i]['format'] = $this->get_ushort($cmap_offset + $x[$i]['offset'] );
			$this->seek($save_pos );
		}
		print_r($x); exit;
*/
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////

/* Test purposes - displays table of names 
			$name_offset = $this->seek_table("name");
			$format = $this->read_ushort();
			if ($format != 0 && $format != 1)	// mPDF 5.3.73
				die("Unknown name table format ".$format);
			$numRecords = $this->read_ushort();
			$string_data_offset = $name_offset + $this->read_ushort();
			for ($i=0;$i<$numRecords; $i++) {
				$x[$i]['platformId'] = $this->read_ushort();
				$x[$i]['encodingId'] = $this->read_ushort();
				$x[$i]['languageId'] = $this->read_ushort();
				$x[$i]['nameId'] = $this->read_ushort();
				$x[$i]['length'] = $this->read_ushort();
				$x[$i]['offset'] = $this->read_ushort();

				$N = '';
				if ($x[$i]['platformId'] == 1 && $x[$i]['encodingId'] == 0 && $x[$i]['languageId'] == 0) { // Roman
					$opos = $this->_pos;
					$N = $this->get_chunk($string_data_offset + $x[$i]['offset'] , $x[$i]['length'] );
					$this->_pos = $opos;
					$this->seek($opos);
				}
				else { 	// Unicode
					$opos = $this->_pos;
					$this->seek($string_data_offset + $x[$i]['offset'] );
					$length = $x[$i]['length'] ;
					if ($length % 2 != 0)
						$length -= 1;
				//		die("PostScript name is UTF-16BE string of odd length");
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
				$x[$i]['names'][$nameId] = $N;
			}
			print_r($x); exit;
*/

			$name_offset = $this->seek_table("name");
			$format = $this->read_ushort();
			if ($format != 0 && $format != 1)	// mPDF 5.3.73
				return("ERROR - NOT ADDED as Unknown name table format ".$format." - " . $file);
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
						$length += 1;
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
				$psName = preg_replace('/ /','-',$names[6]);
			else if ($names[4])
				$psName = preg_replace('/ /','-',$names[4]);
			else if ($names[1])
				$psName = preg_replace('/ /','-',$names[1]);
			else
				$psName = '';
			if (!$names[1] && !$psName)
				return("ERROR - NOT ADDED as Could not find valid font name - " . $file);
			$this->name = $psName;
			if ($names[1]) { $this->familyName = $names[1]; } else { $this->familyName = $psName; }
			if ($names[2]) { $this->styleName = $names[2]; } else { $this->styleName = 'Regular'; }

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$ver_maj = $this->read_ushort();
		$ver_min = $this->read_ushort();
		if ($ver_maj != 1)
			return('ERROR - NOT ADDED as Unknown head table version '. $ver_maj .'.'. $ver_min." - " . $file);
		$this->fontRevision = $this->read_ushort() . $this->read_ushort();
		$this->skip(4);
		$magic = $this->read_ulong();
		if ($magic != 0x5F0F3CF5) 
			return('ERROR - NOT ADDED as Invalid head table magic ' .$magic." - " . $file);
		$this->skip(2);
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->skip(24);
		$macStyle = $this->read_short();
		$this->skip(4);
		$indexLocFormat = $this->read_short();

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		$sFamily = '';
		$panose = '';
		$fsSelection = '';
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$this->skip(30);
			$sF = $this->read_short();
			$sFamily = ($sF >> 8);
			$this->_pos += 10;  //PANOSE = 10 byte length
			$panose = fread($this->fh,10);
			$this->panose = array();
			for ($p=0;$p<strlen($panose);$p++) { $this->panose[] = ord($panose[$p]); }
			$this->skip(20); 
			$fsSelection = $this->read_short();
		}

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		$this->skip(4); 
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->skip(4);
		$isFixedPitch = $this->read_ulong();



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
				}
			}
			else if ((($platformID == 3 && $encodingID == 10) || $platformID == 0)) { // Microsoft, Unicode Format 12 table HKCS
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		if (!$unicode_cmap_offset)
			return('ERROR - Font ('.$this->filename .') NOT ADDED as it is not Unicode encoded, and cannot be used by mPDF');

		$rtl = false;
		$indic = false;
		$cjk = false;
		$sip = false;
		$smp = false;
		$pua = false;
		$puaag = false;
		$glyphToChar = array();
		$unAGlyphs = '';
		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12) {
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);
			$nGroups = $this->read_ulong();
			for($i=0; $i<$nGroups ; $i++) { 
				$startCharCode = $this->read_ulong(); 
				$endCharCode = $this->read_ulong(); 
				$startGlyphCode = $this->read_ulong(); 
				if (($endCharCode > 0x20000 && $endCharCode < 0x2A6DF) || ($endCharCode > 0x2F800 && $endCharCode < 0x2FA1F)) {
					$sip = true; 
				}
				if ($endCharCode > 0x10000 && $endCharCode < 0x1FFFF) {
					$smp = true; 
				}
				if (($endCharCode > 0x0590 && $endCharCode < 0x077F) || ($endCharCode > 0xFE70 && $endCharCode < 0xFEFF) || ($endCharCode > 0xFB50 && $endCharCode < 0xFDFF)) {
					$rtl = true; 
				}
				if ($endCharCode > 0x0900 && $endCharCode < 0x0DFF) {
					$indic = true; 
				}
				if ($endCharCode > 0xE000 && $endCharCode < 0xF8FF) {
					$pua = true; 
					if ($endCharCode > 0xF500 && $endCharCode < 0xF7FF) {
						$puaag = true; 
					}
				}
				if (($endCharCode > 0x2E80 && $endCharCode < 0x4DC0) || ($endCharCode > 0x4E00 && $endCharCode < 0xA4CF) || ($endCharCode > 0xAC00 && $endCharCode < 0xD7AF) || ($endCharCode > 0xF900 && $endCharCode < 0xFAFF) || ($endCharCode > 0xFE30 && $endCharCode < 0xFE4F)) {
					$cjk = true; 
				}

				$offset = 0;
				// Get each glyphToChar - only point if going to analyse un-mapped Arabic Glyphs
				if (isset($this->tables['post'])) {
				  for ($unichar=$startCharCode;$unichar<=$endCharCode;$unichar++) {
					$glyph = $startGlyphCode + $offset ;
					$offset++;
					$glyphToChar[$glyph][] = $unichar;
				  }
				}


			}
		}

		else {	// Format 4 CMap
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
			for($i=0; $i<$segCount; $i++) { $idDelta[] = $this->read_short(); }
			$idRangeOffset_start = $this->_pos;
			$idRangeOffset = array();
			for($i=0; $i<$segCount; $i++) { $idRangeOffset[] = $this->read_ushort(); }

			for ($n=0;$n<$segCount;$n++) {
				if (($endCount[$n] > 0x0590 && $endCount[$n] < 0x077F) || ($endCount[$n] > 0xFE70 && $endCount[$n] < 0xFEFF) || ($endCount[$n] > 0xFB50 && $endCount[$n] < 0xFDFF)) {
					$rtl = true; 
				}
				if ($endCount[$n] > 0x0900 && $endCount[$n] < 0x0DFF) {
					$indic = true; 
				}
				if (($endCount[$n] > 0x2E80 && $endCount[$n] < 0x4DC0) || ($endCount[$n] > 0x4E00 && $endCount[$n] < 0xA4CF) || ($endCount[$n] > 0xAC00 && $endCount[$n] < 0xD7AF) || ($endCount[$n] > 0xF900 && $endCount[$n] < 0xFAFF) || ($endCount[$n] > 0xFE30 && $endCount[$n] < 0xFE4F)) {
					$cjk = true; 
				}
				if ($endCount[$n] > 0xE000 && $endCount[$n] < 0xF8FF) {
					$pua = true; 
					if ($endCount[$n] > 0xF500 && $endCount[$n] < 0xF7FF) {
						$puaag = true; 
					}
				}
				// Get each glyphToChar - only point if going to analyse un-mapped Arabic Glyphs
				if (isset($this->tables['post'])) {
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
						$glyphToChar[$glyph][] = $unichar;
					}
				}

			}
		}
		// 'POST' table for un-mapped arabic glyphs
		if (isset($this->tables['post'])) {
			  $this->seek_table("post");
			  // Only works on Format 2.0
			  $formata = $this->read_ushort();
			  $formatb = $this->read_ushort();
			  if ($formata == 2 && $formatb == 0) {
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
						$unAGlyphs .= $gid;
						$name = 'uni'.strtoupper($m[1]).'.'.strtolower($m[2]);
						$unAGlyphs .= ' : '.$name;
						$unihexstr = $m[1];
						$unAGlyphs .= ' : '.$unihexstr;
						$unAGlyphs .= ' : '.$uni;
						$unAGlyphs .= ' : '.$form;
						// if already set in PUA private use area E000-F8FF
						if (isset($glyphToChar[$gid]) && $glyphToChar[$gid][0]>57343 && $glyphToChar[$gid][0]<63489) {
								$unAGlyphs .= ' : '.$glyphToChar[$gid][0].' {'.dechex($glyphToChar[$gid][0]).'}';
						}
						//else $unAGlyphs .= ':';
						$unAGlyphs .= ' : '.strtoupper(dechex($bit));
						$unAGlyphs .= '<br />';
					  }
					}
					$ptr += $len;
				}
				if ($unAGlyphs) { 
					$unAGlyphs = 'GID:Name:Unicode base Hex:Dec:Form:PUA Unicode<br />'.$unAGlyphs ; 
				}
			  }
		}



		$bold = false; 
		$italic = false; 
		$ftype = '';
		if ($macStyle & (1 << 0)) { $bold = true; }	// bit 0 bold
		else if ($fsSelection & (1 << 5)) { $bold = true; }	// 5 	BOLD 	Characters are emboldened

		if ($macStyle & (1 << 1)) { $italic = true; }	// bit 1 italic
		else if ($fsSelection & (1 << 0)) { $italic = true; }	// 0 	ITALIC 	Font contains Italic characters, otherwise they are upright
		else if ($this->italicAngle <> 0) { $italic = true; }

		if ($isFixedPitch ) { $ftype = 'mono'; }
		else if ($sFamily >0 && $sFamily <8) { $ftype = 'serif'; }
		else if ($sFamily ==8) { $ftype = 'sans'; }
		else if ($sFamily ==10) { $ftype = 'cursive'; }
		// Use PANOSE
		if ($panose) { 
			$bFamilyType=ord($panose[0]); 
			if ($bFamilyType==2) {
				$bSerifStyle=ord($panose[1]); 
				if (!$ftype) { 
					if ($bSerifStyle>1 && $bSerifStyle<11) { $ftype = 'serif'; }
					else if ($bSerifStyle>10) { $ftype = 'sans'; }
				}
				$bProportion=ord($panose[3]);
				if ($bProportion==9 || $bProportion==1) { $ftype = 'mono'; }	// ==1 i.e. No Fit needed for OCR-a and -b
			}
			else if ($bFamilyType==3) {
				$ftype = 'cursive'; 
			}
		}

		fclose($this->fh);
		return array($this->familyName, $bold, $italic, $ftype, $TTCfontID, $rtl, $indic, $cjk, $sip, $smp, $puaag, $pua, $unAGlyphs);
	}




}


?>