<?php

class wmf {

var $mpdf = null;
var $gdiObjectArray;

function wmf(&$mpdf) {
	$this->mpdf = $mpdf;
}


function _getWMFimage($data) {
	$k = _MPDFK;

		$this->gdiObjectArray = array();
		$a=unpack('stest',"\1\0");
		if ($a['test']!=1)
		return array(0, 'Error parsing WMF image - Big-endian architecture not supported'); 
		// check for Aldus placeable metafile header
		$key = unpack('Lmagic', substr($data, 0, 4));
		$p = 18;  // WMF header 
		if ($key['magic'] == (int)0x9AC6CDD7) { $p +=22; } // Aldus header
		// define some state variables
		$wo=null; // window origin
		$we=null; // window extent
		$polyFillMode = 0;
		$nullPen = false;
		$nullBrush = false;
		$endRecord = false;
		$wmfdata = '';
		while ($p < strlen($data) && !$endRecord) {
			$recordInfo = unpack('Lsize/Sfunc', substr($data, $p, 6));	$p += 6;
			// size of record given in WORDs (= 2 bytes)
			$size = $recordInfo['size'];
			// func is number of GDI function
			$func = $recordInfo['func'];
			if ($size > 3) {
				$parms = substr($data, $p, 2*($size-3));	$p += 2*($size-3);
			}
			switch ($func) {
				case 0x020b:  // SetWindowOrg
					// do not allow window origin to be changed
					// after drawing has begun
					if (!$wmfdata)
						$wo = array_reverse(unpack('s2', $parms));
					break;
				case 0x020c:  // SetWindowExt
					// do not allow window extent to be changed
					// after drawing has begun
					if (!$wmfdata)
						$we = array_reverse(unpack('s2', $parms));
					break;
				case 0x02fc:  // CreateBrushIndirect
					$brush = unpack('sstyle/Cr/Cg/Cb/Ca/Shatch', $parms);
					$brush['type'] = 'B';
					$this->_AddGDIObject($brush);
					break;
				case 0x02fa:  // CreatePenIndirect
					$pen = unpack('Sstyle/swidth/sdummy/Cr/Cg/Cb/Ca', $parms);
					// convert width from twips to user unit
					$pen['width'] /= (20 * $k);
					$pen['type'] = 'P';
					$this->_AddGDIObject($pen);
					break;

				// MUST create other GDI objects even if we don't handle them
				case 0x06fe: // CreateBitmap
				case 0x02fd: // CreateBitmapIndirect
				case 0x00f8: // CreateBrush
				case 0x02fb: // CreateFontIndirect
				case 0x00f7: // CreatePalette
				case 0x01f9: // CreatePatternBrush
				case 0x06ff: // CreateRegion
				case 0x0142: // DibCreatePatternBrush
					$dummyObject = array('type'=>'D');
					$this->_AddGDIObject($dummyObject);
					break;
				case 0x0106:  // SetPolyFillMode
					$polyFillMode = unpack('smode', $parms);
					$polyFillMode = $polyFillMode['mode'];
					break;
				case 0x01f0:  // DeleteObject
					$idx = unpack('Sidx', $parms);
					$idx = $idx['idx'];
					$this->_DeleteGDIObject($idx);
					break;
				case 0x012d:  // SelectObject
					$idx = unpack('Sidx', $parms);
					$idx = $idx['idx'];
					$obj = $this->_GetGDIObject($idx);
					switch ($obj['type']) {
						case 'B':
							$nullBrush = false;
							if ($obj['style'] == 1) { $nullBrush = true; }
							else {
								$wmfdata .= $this->mpdf->SetFColor($this->mpdf->ConvertColor('rgb('.$obj['r'].','.$obj['g'].','.$obj['b'].')'), true)."\n";	
							}
							break;
						case 'P':
							$nullPen = false;
							$dashArray = array(); 
							// dash parameters are custom
							switch ($obj['style']) {
								case 0: // PS_SOLID
									break;
								case 1: // PS_DASH
									$dashArray = array(3,1);
									break;
								case 2: // PS_DOT
									$dashArray = array(0.5,0.5);
									break;
								case 3: // PS_DASHDOT
									$dashArray = array(2,1,0.5,1);
									break;
								case 4: // PS_DASHDOTDOT
									$dashArray = array(2,1,0.5,1,0.5,1);
									break;
								case 5: // PS_NULL
									$nullPen = true;
									break;
							}
							if (!$nullPen) {
								$wmfdata .= $this->mpdf->SetDColor($this->mpdf->ConvertColor('rgb('.$obj['r'].','.$obj['g'].','.$obj['b'].')'), true)."\n";
								$wmfdata .= sprintf("%.3F w\n",$obj['width']*$k);
							}
							if (!empty($dashArray)) {
								$s = '[';
								for ($i=0; $i<count($dashArray);$i++) {
									$s .= $dashArray[$i] * $k;
									if ($i != count($dashArray)-1) { $s .= ' '; }
								}
								$s .= '] 0 d';
								$wmfdata .= $s."\n";
							}
							break;
					}
					break;
				case 0x0325: // Polyline
				case 0x0324: // Polygon
					$coords = unpack('s'.($size-3), $parms);
					$numpoints = $coords[1];
					for ($i = $numpoints; $i > 0; $i--) {
						$px = $coords[2*$i];
						$py = $coords[2*$i+1];

						if ($i < $numpoints) { $wmfdata .= $this->_LineTo($px, $py); }
					   else { $wmfdata .= $this->_MoveTo($px, $py); }
					}
					if ($func == 0x0325) { $op = 's'; }
					else if ($func == 0x0324) {
						if ($nullPen) {
							if ($nullBrush) { $op = 'n'; } // no op
							else { $op = 'f'; } // fill
						}
						else {
							if ($nullBrush) { $op = 's'; } // stroke
							else { $op = 'b'; } // stroke and fill
						}
						if ($polyFillMode==1 && ($op=='b' || $op=='f')) { $op .= '*'; } // use even-odd fill rule
					}
					$wmfdata .= $op."\n";
					break;
				case 0x0538: // PolyPolygon
					$coords = unpack('s'.($size-3), $parms);
					$numpolygons = $coords[1];
					$adjustment = $numpolygons;
					for ($j = 1; $j <= $numpolygons; $j++) {
						$numpoints = $coords[$j + 1];
						for ($i = $numpoints; $i > 0; $i--) {
							$px = $coords[2*$i   + $adjustment];
							$py = $coords[2*$i+1 + $adjustment];
							if ($i == $numpoints) { $wmfdata .= $this->_MoveTo($px, $py); }
							else { $wmfdata .= $this->_LineTo($px, $py); }
						}
						$adjustment += $numpoints * 2;
					}

					if ($nullPen) {
						if ($nullBrush) { $op = 'n'; } // no op
						else { $op = 'f'; } // fill
					}
					else {
						if ($nullBrush) { $op = 's'; } // stroke
						else { $op = 'b'; } // stroke and fill
					}
					if ($polyFillMode==1 && ($op=='b' || $op=='f')) { $op .= '*'; } // use even-odd fill rule
					$wmfdata .= $op."\n";
					break;
				case 0x0000:
					$endRecord = true;
					break;
			}
		}


	return array(1,$wmfdata,$wo,$we);
}


function _MoveTo($x, $y) {
	return "$x $y m\n";
}

// a line must have been started using _MoveTo() first
function _LineTo($x, $y) {
	return "$x $y l\n";
}

function _AddGDIObject($obj) {
	// find next available slot
	$idx = 0;
	if (!empty($this->gdiObjectArray)) {
		$empty = false;
		$i = 0;
		while (!$empty) {
			$empty = !isset($this->gdiObjectArray[$i]);
			$i++;
		}
		$idx = $i-1;
	}
	$this->gdiObjectArray[$idx] = $obj;
}

function _GetGDIObject($idx) {
	return $this->gdiObjectArray[$idx];
}

function _DeleteGDIObject($idx) {
	unset($this->gdiObjectArray[$idx]);
}


}

?>