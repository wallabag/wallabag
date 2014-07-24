<?php



$hhtml = '
<htmlpageheader name="myHTMLHeaderOdd" style="display:none">
<div style="background-color:#BBEEFF" align="center"><b>&nbsp;{PAGENO}&nbsp;</b></div>
</htmlpageheader>
<htmlpagefooter name="myHTMLFooterOdd" style="display:none">
<div style="background-color:#CFFFFC" align="center"><b>&nbsp;{PAGENO}&nbsp;</b></div>
</htmlpagefooter>
<sethtmlpageheader name="myHTMLHeaderOdd" page="O" value="on" show-this-page="1" />
<sethtmlpagefooter name="myHTMLFooterOdd" page="O" value="on" show-this-page="1" />
';

//==============================================================
$html = '
<h1>mPDF Page Sizes</h1>
<h3>Changing page (sheet) sizes within the document</h3>
';
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c','A4'); 

$mpdf->WriteHTML($hhtml);

$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<p>This should print on an A4 (portrait) sheet</p>');

$mpdf->WriteHTML('<tocpagebreak sheet-size="A4-L" toc-sheet-size="A5" toc-preHTML="This ToC should print on an A5 sheet" />');
$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<tocentry content="A4 landscape" /><p>This page appears just after the ToC and should print on an A4 (landscape) sheet</p>');

$mpdf->WriteHTML('<pagebreak sheet-size="A5-L" />');
$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<tocentry content="A5 landscape" /><p>This should print on an A5 (landscape) sheet</p>');

$mpdf->WriteHTML('<pagebreak sheet-size="Letter" />');
$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<tocentry content="Letter portrait" /><p>This should print on an Letter sheet</p>');

$mpdf->WriteHTML('<pagebreak sheet-size="150mm 150mm" />');
$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<tocentry content="150mm square" /><p>This should print on a sheet 150mm x 150mm</p>');

$mpdf->WriteHTML('<pagebreak sheet-size="11.69in 8.27in" />');
$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<tocentry content="A4 landscape (ins)" /><p>This should print on a sheet 11.69in x 8.27in = A4 landscape</p>');


$mpdf->Output();
exit;
//==============================================================
//==============================================================


?>