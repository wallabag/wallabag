<?php
ini_set("memory_limit","384M");

// This is because changelog.txt contains over 100000 characters, and preg_* functions in mPDF won't work.
ini_set("pcre.backtrack_limit","200000");

include("../mpdf.php");

$mpdf=new mPDF(); 

$mpdf->tabSpaces = 6;

$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='windows-1252';


//==============================================================

$html = '
<h1>mPDF</h1>
<h2>ChangeLog</h2>
<div style="border:1px solid #555555; background-color: #DDDDDD; padding: 1em; font-size:8pt; font-family: lucidaconsole, mono;">
';
$lines = file('../CHANGELOG.txt');

$html .= '<pre>';
foreach($lines AS $line) {
	$html .= htmlspecialchars($line);
}
$html .= '</pre>';
$html .= '</div>';

//==============================================================

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;


?>