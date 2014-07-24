<?php

/* This script prints out the Unicode coverage of all TrueType font files
   in your font directory. 
   Point your browser to 
   http://your.domain/your_path_to _mpdf/utils/font_coverage.php
   By default this will examine the folder /ttfonts/ (or the default font
   directory defined by _MPDF_TTFONTPATH.
   You can optionally define an alternative folder to examine by setting 
   the variable below (must be a relative path, or filesystem path):
*/


$checkdir = '';


//////////////////////////////////
//////////////////////////////////
//////////////////////////////////


set_time_limit(600);
ini_set("memory_limit","256M");

//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('','A4-L','','',10,10,10,10); 
$mpdf->SetDisplayMode('fullpage');
$mpdf->useSubstitutions = true;
$mpdf->debug = true;
$mpdf->simpleTables = true;
if ($checkdir) { 
	$ttfdir = $checkdir;
}
else { $ttfdir = _MPDF_TTFONTPATH; }



$mqr=ini_get("magic_quotes_runtime");
if ($mqr) { set_magic_quotes_runtime(0); }
if (!class_exists('TTFontFile_Analysis', false)) { include(_MPDF_PATH .'classes/ttfontsuni_analysis.php'); }

//==============================================================
	$smp = true;
	$maxt = 131071;
//==============================================================
//==============================================================
$unifile = file('UnicodeData.txt');
$unichars = array();
foreach($unifile AS $line) {
	if (preg_match('/<control>/',$line,$m)) { 
	  $rangename = '';
	  continue;
	}
	else if (preg_match('/^([12]{0,1}[0-9A-Za-z]{4});<(.*?), Last>/',$line,$m)) { 
	  if ($rangename && $rangename == $m[2]) {
		$endrange = hexdec($m[1]);
		for ($i=$startrange;$i<=$endrange; $i++) {
			$unichars[$i] = $i;
		}
	  }
	  $rangename = '';
	}
	else if (preg_match('/^([12]{0,1}[0-9A-Za-z]{4});<(.*?), First>/',$line,$m)) { 
	  $startrange = hexdec($m[1]);
	  $rangename = $m[2];
	}
	else if (preg_match('/^([12]{0,1}[0-9A-Za-z]{4});/',$line,$m)) { 
	  $unichars[hexdec($m[1])] = hexdec($m[1]);
	  $rangename = '';
	}
}

// loads array $unicode_ranges
include('UnicodeRanges.php'); 
//==============================================================
$html = '<html><head><style>td { border: 0.1mm solid #555555; } 
body { font-weight: normal; font-family: helvetica;font-size:8pt; }
td { font-family: helvetica;font-size:8pt; vertical-align: top;}
</style></head><body>';

//==============================================================
$ff = scandir($ttfdir);
$tempfontdata = array();
foreach($ff AS $f) {
	$ttf = new TTFontFile_Analysis();
	$ret = array();
	$isTTC = false;
	if (strtolower(substr($f,-4,4))=='.ttf' || strtolower(substr($f,-4,4))=='.otf') {
		$ret[] = $ttf->extractCoreInfo($ttfdir.$f);
	}
	for ($i=0; $i<count($ret); $i++) {
	   if (is_array($ret[$i])) { 
		$tfname = $ret[$i][0];
		$bold = $ret[$i][1];
		$italic = $ret[$i][2];
		$fname = strtolower($tfname );
		$fname = preg_replace('/[ ()]/','',$fname );
		//$tempfonttrans[$tfname] = $fname;
		$style = '';
		if ($bold) { $style .= 'B'; }
		if ($italic) { $style .= 'I'; }
		if (!$style) { 
			$tempfontdata[$fname]['file'] = $f;
			if ($isTTC) { 
				$tempfontdata[$fname]['TTCfontID'] = $ret[$i][4];
			}
		}
	   }
	}
	unset($ttf);

}

$fullcovers = array();
$nearlycovers = array();
ksort($tempfontdata);
$ningroup = 14;
$nofgroups = ceil(count($unicode_ranges)/$ningroup);

//==============================================================


for ($urgp = 0; $urgp < $nofgroups; $urgp++) {


   $html .= '<table cellpadding="2" cellspacing="0" style="page-break-inside:avoid; text-align:center; border-collapse: collapse; ">';
   $html .= '<thead><tr><td></td>';

   foreach($unicode_ranges AS $urk => $ur) {
	   if ($urk >= ($urgp*$ningroup) && $urk < (($urgp+1)*$ningroup)) {
				$rangekey = $urk;
				$range = $ur['range'];
				$rangestart = $ur['starthex'];
				$rangeend = $ur['endhex'];
				$html .= '<td style="font-family:helvetica;font-size:8pt;font-weight:bold;">'.strtoupper($range).' (U+'.$rangestart .'-U+'.$rangeend.')</td>';
	   }
   }
   $html .= '</tr></thead>';


  foreach ($tempfontdata AS $fname => $v) {
	$cw = '';
	if (file_exists((_MPDF_TTFONTDATAPATH.$fname.'.cw.dat'))) { $cw = file_get_contents(_MPDF_TTFONTDATAPATH.$fname.'.cw.dat'); }
	else {
		$mpdf->fontdata[$fname]['R'] = $tempfontdata[$fname]['file']; 
		$mpdf->AddFont($fname);
		$cw = file_get_contents(_MPDF_TTFONTDATAPATH.$fname.'.cw.dat'); 
	}
	if (!$cw) {
		continue;
		die("Font data not available for $fname");
	}

	$counter=0;
	$max = $maxt;

	// create HTML content
	$html .= '<tr>';
	$html .= '<td>'.$fname.'</td>';

	foreach($unicode_ranges AS $urk => $ur) {
	   if ($urk >= ($urgp*$ningroup) && $urk < (($urgp+1)*$ningroup)) {
	    if ($ur['pua'] || $ur['reserved'] || $ur['control']) {
		$html .= '<td style="background-color: #000000;"></td>'; 
	    }
	    else {
		$rangekey = $urk;
		$range = $ur['range'];
		$rangestart = $ur['starthex'];
		$rangeend = $ur['endhex'];
		$rangestartdec = $ur['startdec'];
		$rangeenddec = $ur['enddec'];
		$uniinrange = 0;
		$fontinrange = 0;
		for ($i=$rangestartdec; $i<=$rangeenddec; $i++) {
			//if (isset($cw[$i])) { $fontinrange++; }
			if ($mpdf->_charDefined($cw, $i)) { $fontinrange++; }
			if (isset($unichars[$i])) { $uniinrange++; }
		}
		if ($uniinrange) {
			if ($fontinrange) {
				$pc = ($fontinrange/$uniinrange);
				$str = '('.$fontinrange.'/'.$uniinrange.')';
				if ($pc==1) { 
					$fullcovers[$urk][] = $fname;
					$html .= '<td style="background-color: #00FF00;"></td>'; 
				}
				else if ($pc>1) { 
					$fullcovers[$urk][] = $fname;
					$html .= '<td style="background-color: #00FF00;">'.$str.'</td>'; 
				}
				else if ($pc>=0.9) { 
					$html .= '<td style="background-color: #AAFFAA;">'.$str.'</td>'; 
					$nearlycovers[$urk][] = $fname;
				}
				else if ($pc>0.75) { $html .= '<td style="background-color: #00FFAA;">'.$str.'</td>'; }
				else if ($pc>0.5) { $html .= '<td style="background-color: #AAAAFF;">'.$str.'</td>'; }
				else if ($pc>0.25) { $html .= '<td style="background-color: #FFFFAA;">'.$str.'</td>'; }
				else { $html .= '<td style="background-color: #FFAAAA;">'.$str.'</td>'; }
			}
			else { $html .= '<td style="background-color: #555555;">(0/0)</td>'; }
		}
		else { $html .= '<td style="background-color: #000000;"></td>'; }
	    }
	   }
	}


	$html .= '</tr>';

   }
//==============================================================
  $html .= '</table><pagebreak />';
}

$html .= '<h4>Fonts with full coverage of Unicode Ranges</h4>';
$html .= '<table>';
//$html .= '<tr><td></td><td></td></tr>';
foreach($unicode_ranges AS $urk => $ur) {
	if ($ur['pua'] || $ur['reserved'] || $ur['control']) { continue; }
	$rangekey = $urk;
	$range = $ur['range'];
	$rangestart = $ur['starthex'];
	$rangeend = $ur['endhex'];
	$ext = $ext2 = '';
	if ($ur['combining']) { $ext = 'background-color:#DDDDFF;'; $ext2 = '<br /><span style="color:#AA0000">Special positioning required</span>'; }
	if ($ur['vertical']) { $ext = 'background-color:#FFDDDD;'; $ext2 = '<br /><span style="color:#AA0000">Vertical positioning required</span>'; }
	if ($ur['special']) { $ext = 'background-color:#FFDDDD;'; $ext2 = '<br /><span style="color:#AA0000">Special processing required</span>'; }


	$html .= '<tr><td style="font-family:helvetica;font-size:8pt;font-weight:bold;'.$ext.'">'.strtoupper($range).' (U+'.$rangestart .'-U+'.$rangeend.')'.$ext2.'</td>';
	$arr = $fullcovers[$urk];
	$narr = $nearlycovers[$urk];
	if (is_array($arr)) { $html .= '<td>'. implode(', ',$arr). '</td></tr>'; }
	else if (is_array($narr)) { $html .= '<td style="background-color: #AAAAAA;">'. implode(', ',$narr). ' (>90%)</td></tr>'; }
	else { $html .= '<td style="background-color: #555555;"> </td></tr>'; }
}
$html.= '</table>';


//==============================================================
echo $html;

exit;

//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>