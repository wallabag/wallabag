<?php

if ($_REQUEST['filename']) { $filename = $_REQUEST['filename']; }
else { die("No file specified"); }

include("../mpdf.php");

$mpdf=new mPDF('utf-8-s'); 
$mpdf->debug=true;
$mpdf->tabSpaces = 6;

$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='windows-1252';

//==============================================================
preg_match('/example[0]{0,1}(\d+)_(.*?)\.php/',$filename,$m);
$num = intval($m[1]);
$title = ucfirst(preg_replace('/_/',' ',$m[2]));

if (!$num || !$title) { die("Invalid file"); }
if (preg_match('/\//', $filename) || !preg_match('/\.php$/',$filename)) { die("Hacking attempt"); }

$html = '
<h1>mPDF</h1>
<h2>Example '.$num.'. '.$title.'</h2>
<div style="border:1px solid #555555; background-color: #DDDDDD; padding: 1em; font-size:8pt; font-family: lucidaconsole, mono;">
';
$lines = file($filename);

$html .= '<pre>';
foreach($lines AS $line) {
	$html .= htmlspecialchars($line);
}
$html .= '</pre>';
$html .= '</div>';

$mpdf->WriteHTML($html,2);	// The 2 is important to prevent <style etc.  being parsed

$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>