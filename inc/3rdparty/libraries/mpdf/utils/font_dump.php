<?php

/* 
   This script prints out all characters in a TrueType font file
   to a PDF document. Point your browser to 
   http://your.domain/your_path_to _mpdf/utils/font_dump.php
   The font file must be located in /ttfonts/ (or the default font
   directory defined by _MPDF_TTFONTPATH.
   By default this will examine the font dejavusanscondensed.
   You can optionally define an alternative font file to examine by setting 
   the variable below (must be a relative path, or filesystem path):
*/


$font = 'dejavusanscondensed';	// Use internal mPDF font-name

$showmissing = true;	// Show all missing unicode blocks / characters


//////////////////////////////////
//////////////////////////////////
//////////////////////////////////

set_time_limit(600);
ini_set("memory_limit","256M");

//==============================================================
//==============================================================
define('_MPDF_URI', '../');
include("../mpdf.php");

$mpdf=new mPDF(''); 
$mpdf->StartProgressBarOutput(2);

$mpdf->SetDisplayMode('fullpage');

$mpdf->useSubstitutions = true;
$mpdf->debug = true;
$mpdf->simpleTables = true;
// force fonts to be embedded whole i.e. NOT susbet
$mpdf->percentSubset = 0;

//==============================================================
//==============================================================
//==============================================================
//==============================================================

// This generates a .mtx.php file if not already generated
$mpdf->WriteHTML('<style>td { border: 0.1mm solid #555555; } body { font-weight: normal; }</style>');
$mpdf->WriteHTML('<h3 style="font-family:'.$font.'">'.strtoupper($font).'</h3>');	// Separate Paragraphs  defined by font
$html = '';
//==============================================================
//==============================================================
//==============================================================
//==============================================================
$unifile = file('UnicodeData.txt');
$unichars = array();

foreach($unifile AS $line) {
	if ($smp && preg_match('/^(1[0-9A-Za-z]{4});/',$line,$m)) { 
	  $unichars[hexdec($m[1])] = hexdec($m[1]);
	}
	else if (preg_match('/^([0-9A-Za-z]{4});/',$line,$m)) { 
	  $unichars[hexdec($m[1])] = hexdec($m[1]);
	}
}

// loads array $unicode_ranges
include('UnicodeRanges.php'); 
//==============================================================
//==============================================================



$cw = file_get_contents(_MPDF_TTFONTDATAPATH.$font.'.cw.dat');
if (!$cw) { die("Error - Must be able to read font metrics file: "._MPDF_TTFONTDATAPATH.$font.'.cw.dat'); }
$counter=0;


include(_MPDF_TTFONTDATAPATH.$font.'.mtx.php');

if ($smp) {
	$max = 131071;
}
else { 
	$max = 65535;
}


$justfinishedblank = false;
$justfinishedblankinvalid = false;

    		foreach($unicode_ranges AS $urk => $ur) {
			if (0 >= $ur['startdec'] && 0 <= $ur['enddec']) {
				$rangekey = $urk;
				$range = $ur['range'];
				$rangestart = $ur['starthex'];
				$rangeend = $ur['endhex'];
				break;
			}
   		}
	  $lastrange  = $range ;
    // create HTML content
    $html .= '<table cellpadding="2" cellspacing="0" style="font-family:'.$font.';text-align:center; border-collapse: collapse; ">';
    $html .= '<tr><td colspan="18" style="font-family:helvetica;font-weight:bold">'.strtoupper($font).'</td></tr>';
    $html .= '<tr><td colspan="18" style="font-family:helvetica;font-size:8pt;font-weight:bold">'.strtoupper($range).' (U+'.$rangestart .'-U+'.$rangeend.')</td></tr>';
    $html .= '<tr><td></td>';

    $html .= '<td></td>';
    for ($i = 0; $i < 16; $i++) {
            $html .= '<td><b>-'.sprintf('%X', $i).'</b></td>';
    }


    // print each character
    for ($i = 32; $i < $max; ++$i) {
        if (($i > 0) AND (($i % 16) == 0)) {
		$notthisline = true;
		while($notthisline) {
	    	   for ($j = 0; $j < 16; $j++) {
			if ($mpdf->_charDefined($cw, ($i + $j))) {
			//if (isset($cw[($i+$j)])) { 
				$notthisline = false; 
			}
		   }
		   if ($notthisline) { 
		    if ($showmissing) {
			$range = '';
	    		foreach($unicode_ranges AS $urk => $ur) {
				if ($i >= $ur['startdec'] && $i <= $ur['enddec']) {
					$rangekey = $urk;
					$range = $ur['range'];
					$rangestart = $ur['starthex'];
					$rangeend = $ur['endhex'];
					break;
				}
	   		}
			$anyvalid = false;
	    	   	for ($j = 0; $j < 16; $j++) {
				if (isset($unichars[$i+$j])) { $anyvalid = true; break; }
			}
			if ($range && $range == $lastrange) {
    				if (!$anyvalid) { 
					if (!$justfinishedblankinvalid) { 
						$html .= '<tr><td colspan="18" style="background-color:#555555; font-size: 4pt;">&nbsp;</td></tr>'; 
					}
					$justfinishedblankinvalid = true;
				}
    				else if (!$justfinishedblank ) { 
					$html .= '<tr><td colspan="18" style="background-color:#FFAAAA; font-size: 4pt;">&nbsp;</td></tr>'; 
					$justfinishedblank = true;
				}
			}
			else if($range) {
				$html .= '</tr></table><br />';
				$mpdf->WriteHTML($html); $html = '';
				$html .= '<table cellpadding="2" cellspacing="0" style="font-family:'.$font.';text-align:center; border-collapse: collapse; ">';
    				$html .= '<tr><td colspan="18" style="font-family:helvetica;font-size:8pt;font-weight:bold">'.strtoupper($range).' (U+'.$rangestart.'-U+'.$rangeend.')</td></tr>';
				$html .= '<tr><td></td>';
    				$html .= '<td></td>';
				for ($k = 0; $k < 16; $k++) {
      			      $html .= '<td><b>-'.sprintf('%X', $k).'</b></td>';
				}
				$justfinishedblank = false;
				$justfinishedblankinvalid = false;
			}
	  		$lastrange = $range ;
		    }
		    $i +=16; 
		    if ($i > $max) { break 2; }
		   }
		}
    		foreach($unicode_ranges AS $urk => $ur) {
			if ($i >= $ur['startdec'] && $i <= $ur['enddec']) {
				$rangekey = $urk;
				$range = $ur['range'];
				$rangestart = $ur['starthex'];
				$rangeend = $ur['endhex'];
				break;
			}
   		}

        	if ($i > 0 && ($i % 16) == 0 && ($range != $lastrange)) {
			$html .= '</tr></table><br />';
			$mpdf->WriteHTML($html); $html = '';
			$html .= '<table cellpadding="2" cellspacing="0" style="font-family:'.$font.';text-align:center; border-collapse: collapse; ">';
    			$html .= '<tr><td colspan="18" style="font-family:helvetica;font-size:8pt;font-weight:bold">'.strtoupper($range).' (U+'.$rangestart.'-U+'.$rangeend.')</td></tr>';
			$html .= '<tr><td></td>';
    			$html .= '<td></td>';
			for ($k = 0; $k < 16; $k++) {
      		      $html .= '<td><b>-'.sprintf('%X', $k).'</b></td>';
			}
		}
	  	$lastrange  = $range ;
		$justfinishedblank = false;
		$justfinishedblankinvalid = false;
            $html .= '</tr><tr><td><i>'.(floor($i / 16)*16).'</i></td>';
            $html .= '<td><b>'.sprintf('%03X', floor($i / 16)).'-</b></td>';
        }
	  if ($mpdf->_charDefined($cw, $i)) { $html .= '<td>&#'.$i.';</td>'; $counter++; }
	  else if (isset($unichars[$i])) { $html .= '<td style="background-color: #FFAAAA;"></td>'; }
	  else { $html .= '<td style="background-color: #555555;"></td>'; }
    }

    if (($i % 16) > 0) {
	for ($j = ($i % 16); $j < 16; ++$j) { $html .= '<td style="background-color: #555555;"></td>'; }
    }
    $html .= '</tr></table><br />';
//==============================================================
//==============================================================
$mpdf->WriteHTML($html);	// Separate Paragraphs  defined by font

$mpdf->Output(); 
exit;

//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>