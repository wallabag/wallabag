<?php


$html = '
<h1>mPDF</h1>
<h2>Page Orientation</h2>

<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>


<p style="color:red; font-family:serif;">Sed bibendum. Nunc eleifend ornare velit. Sed consectetuer urna in erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Mauris sodales semper metus. Maecenas justo libero, pretium at, malesuada eu, mollis et, arcu. Ut suscipit pede in nulla. Praesent elementum, dolor ac fringilla posuere, elit libero rutrum massa, vel tincidunt dui tellus a ante. Sed aliquet euismod dolor. Vestibulum sed dui. Duis lobortis hendrerit quam. Donec tempus orci ut libero. Pellentesque suscipit malesuada nisi. </p>
<p style="color:orange; font-family:serif;">Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo. Cras tellus. Fusce aliquet. Curabitur tincidunt viverra ligula. Fusce eget erat. Donec pede. Vestibulum id felis. Phasellus tincidunt ligula non pede. Morbi turpis. In vitae dui non erat placerat malesuada. Mauris adipiscing congue ante. Proin at erat. Aliquam mattis. </p>
<p style="color:green; font-family:serif;">Integer feugiat venenatis metus. Integer lacinia ultrices ipsum. Proin et arcu. Quisque varius libero. Nullam id arcu. Aenean justo quam, accumsan nec, luctus id, pellentesque molestie, mi. Aliquam sollicitudin feugiat eros. Nunc nisi turpis, consequat id, aliquet et, semper a, augue. Integer nisl ipsum, blandit et, lobortis a, egestas nec, odio. Nulla dolor ligula, nonummy ac, vulputate a, sollicitudin id, orci. Donec laoreet nisl id magna. Curabitur mollis, quam eget fermentum malesuada, risus tortor ullamcorper dolor, nec placerat nisi urna non pede. Aliquam pretium, leo in interdum interdum, ipsum neque accumsan lectus, ac fringilla dui ipsum sed justo. In tincidunt risus convallis odio egestas luctus. Integer volutpat. Donec ultricies, leo in congue iaculis, dolor neque imperdiet nibh, vitae feugiat mi enim nec sapien. Aenean turpis lorem, consequat quis, varius in, posuere vel, eros. Nulla facilisi.</p>

<hr />

';

//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");


$mpdf=new mPDF('c','A4','','',42,15,67,67,20,15); 

$mpdf->SetDisplayMode('fullpage','two');

$mpdf->mirrorMargins = 1;


$header = '
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%">Left header p <span style="font-size:14pt;">{PAGENO}</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;"><span style="font-weight: bold;">Right header</span></td>
</tr></table>
';
$headerE = '
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%"><span style="font-weight: bold;">Outer header</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;">Inner header p <span style="font-size:14pt;">{PAGENO}</span></td>
</tr></table>
';
$longfooter = '
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%">Left footer p <span style="font-size:14pt;">{PAGENO}</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;"><span style="font-weight: bold;">Right footer</span></td>
</tr></table>
';
$longfooterE = '
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%"><span style="font-weight: bold;">Outer footer</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;">Inner footer p <span style="font-size:14pt;">{PAGENO}</span></td>
</tr></table>
';

$footer = '<div align="center" style="color:blue;font-family:mono;font-size:18pt;font-weight:bold;font-style:italic;">{DATE j-m-Y} &raquo; {PAGENO} &raquo; My document</div>';
$footerE = '<div align="center" style="color:green;font-family:mono;font-size:18pt;font-weight:bold;font-style:italic;">Even page footer - {PAGENO} -</div>';

$shortheader = '<div align="center" style="color:blue;font-family:mono;font-size:18pt;font-weight:bold;font-style:italic;">{DATE j-m-Y} &raquo; {PAGENO} &raquo; My document</div>';
$shortheaderE = '<div align="center" style="color:green;font-family:mono;font-size:18pt;font-weight:bold;font-style:italic;">Even page header - {PAGENO} -</div>';


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($headerE,'E');
$mpdf->setFooter('{PAGENO} of {nbpg} pages||{PAGENO} of {nbpg} pages') ;


$mpdf->WriteHTML($html);


$mpdf->setHeader();	// Clear headers before adding page
$mpdf->AddPage('L','','','','',25,25,55,45,18,12);

$mpdf->SetHTMLHeader($shortheader,'',true);	// New parameter in v1.4 to add the header to the new page
$mpdf->SetHTMLHeader($shortheaderE,'E',true);
$mpdf->SetHTMLFooter($longfooter);
$mpdf->SetHTMLFooter($longfooterE,'E');


$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);



$mpdf->setHeader('{PAGENO} of {nbpg} pages||{PAGENO} of {nbpg} pages') ;
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footerE,'E');


$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);


$mpdf->setHeader();	// Clear headers before adding page
$mpdf->AddPage('','','','','',42,15,67,67,20,15);	// Default is Portrait (because that was the document default)


$mpdf->SetHTMLHeader($shortheader,'',true);	// New parameter in v1.4 to add the header to the new page
$mpdf->SetHTMLHeader($shortheaderE,'E',true);
$mpdf->SetHTMLFooter($longfooter);
$mpdf->SetHTMLFooter($longfooterE,'E');


$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($headerE,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footerE,'E');


$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);


$mpdf->Output('mpdf.pdf','I');
exit;
//==============================================================
//==============================================================
//==============================================================


?>