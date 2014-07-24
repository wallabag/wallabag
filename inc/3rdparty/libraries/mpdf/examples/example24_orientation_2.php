<?php


$html = '
<h1>mPDF</h1>
<h2>Page Orientation</h2>

<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>


<p style="color:red; font-family:serif;">Sed bibendum. Nunc eleifend ornare velit. Sed consectetuer urna in erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Mauris sodales semper metus. Maecenas justo libero, pretium at, malesuada eu, mollis et, arcu. Ut suscipit pede in nulla. Praesent elementum, dolor ac fringilla posuere, elit libero rutrum massa, vel tincidunt dui tellus a ante. Sed aliquet euismod dolor. Vestibulum sed dui. Duis lobortis hendrerit quam. Donec tempus orci ut libero. Pellentesque suscipit malesuada nisi. </p>
<p style="color:orange; font-family:serif;">Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo. Cras tellus. Fusce aliquet. Curabitur tincidunt viverra ligula. Fusce eget erat. Donec pede. Vestibulum id felis. Phasellus tincidunt ligula non pede. Morbi turpis. In vitae dui non erat placerat malesuada. Mauris adipiscing congue ante. Proin at erat. Aliquam mattis. </p>
<p style="color:green; font-family:serif;">Integer feugiat venenatis metus. Integer lacinia ultrices ipsum. Proin et arcu. Quisque varius libero. Nullam id arcu. Aenean justo quam, accumsan nec, luctus id, pellentesque molestie, mi. Aliquam sollicitudin feugiat eros. Nunc nisi turpis, consequat id, aliquet et, semper a, augue. Integer nisl ipsum, blandit et, lobortis a, egestas nec, odio. Nulla dolor ligula, nonummy ac, vulputate a, sollicitudin id, orci. Donec laoreet nisl id magna. Curabitur mollis, quam eget fermentum malesuada, risus tortor ullamcorper dolor, nec placerat nisi urna non pede. Aliquam pretium, leo in interdum interdum, ipsum neque accumsan lectus, ac fringilla dui ipsum sed justo. In tincidunt risus convallis odio egestas luctus. Integer volutpat. Donec ultricies, leo in congue iaculis, dolor neque imperdiet nibh, vitae feugiat mi enim nec sapien. Aenean turpis lorem, consequat quis, varius in, posuere vel, eros. Nulla facilisi.</p>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>


';

$htmlL = '
<h6>Table in Landscape</h6>
<table class="bpmTopic">
<thead>
<tr style="text-rotate:45;">
<td>Type</td>
<td>Details</td>
<td>Notes</td>
</thead>
<tbody>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti. Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti. Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti. Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti. Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti. Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
</tr>
</tbody></table>
';

//==============================================================

$loremH = "<h4>Lectus facilisis</h4>
<p>Sed auctor viverra diam. In lacinia lectus.</p>
<p>Praesent tincidunt massa in dolor. Morbi viverra leo quis ipsum.&nbsp;In vitae velit. In aliquam nulla nec mi. Sed accumsan, justo id congue fringilla, diam mauris volutpat ligula, sed aliquet elit diam at felis. Quisque et velit sed eros convallis posuere.</p>
<h5>Nunc tincidunt</h5>
<p>Nunc diam ipsum, consectetuer nec, hendrerit vitae, malesuada a, ante. Nulla ornare aliquet ante. Maecenas in lectus. Morbi porttitor mauris. Praesent ut.</p>
<p>Pede quis ante tincidunt <a href=\"http://www.stlucia.org\">blandit</a>. Maecenas bibendum erat. Curabitur sit amet ante quis velit ultricies facilisis. Ut hendrerit dolor commodo magna. In nec ligula a purus tincidunt adipiscing. Etiam non ante. </p><div>Suspendisse potenti. <indexentry content=\"Inline indexentry &lt;B&gt;\" />Suspendisse accumsan euismod lectus. Nunc commodo pede et turpis. Pellentesque porta mauris sed lorem. Ut nec augue vitae elit eleifend eleifend. Quisque ornare feugiat diam. Duis nulla metus, tempus sit amet, scelerisque a, rutrum at, nisl. Nulla facilisi. Duis metus turpis, molestie nec, laoreet tincidunt, ultrices et, purus. Nullam faucibus aliquam nisi.</div><a href=\"http://www.stlucia.org\"><img zsrc=\"sunset.jpg\" /></a><p>Ut leo. Etiam tempus interdum tortor. Donec porta, arcu vel tincidunt placerat, lacus lorem iaculis diam, id sagittis sapien metus eu nunc. Morbi vitae nunc.<br />Mauris sapien. Phasellus elementum velit sed sapien. Nullam ante diam, consectetuer commodo, dignissim vitae, tempor vel, magna. Donec dictum. <i>Nullam</i> ultrices leo volutpat magna. Mauris blandit purus nec turpis. <a href=\"http://www.stlucia.org\">Curabitur</a> nunc. Aliquam condimentum eleifend<sup>32</sup> lectus. Praesent vitae nibh <b>et libero ullamcorper</b> scelerisque. Nullam auctor. Mauris ipsum nulla, malesuada id, aliquet at, feugiat vitae, eros.</p>

<div style=\"background-color:#DDDDBB; text-align:center; padding:3px; border:1px solid #880000;  \">Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </div><p>Maecenas arcu justo, malesuada eu, dapibus ac, adipiscing vitae, turpis. Fusce mollis. Aliquam egestas. In purus dolor, facilisis at, fermentum nec, molestie et, metus. Vestibulum feugiat, orci at imperdiet tincidunt, mauris erat facilisis urna, sagittis ultricies dui nisl et lectus. Sed lacinia, lectus vitae dictum sodales, elit ipsum ultrices orci, non euismod arcu diam non metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In suscipit turpis vitae odio. Integer convallis dui at metus. Fusce magna. Sed sed lectus vitae enim tempor cursus. Cras eu erat vel libero sodales congue. Sed erat est, interdum nec, elementum eleifend, pretium at, nibh. Praesent massa diam, adipiscing id, mollis sed, posuere et, urna. Quisque ut leo. Aliquam interdum hendrerit tortor. Vestibulum elit. Vestibulum et arcu at diam mattis commodo. Nam ipsum sem, ultricies at, rutrum sit amet, posuere nec, velit. Sed molestie mollis dui. </p>
";

//==============================================================
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
$header = '<div align="center" style="background-color: #f0f2ff;background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;border-radius: 4mm;font-size:18pt;font-weight:bold;font-style:italic;">{DATE j-m-Y} &raquo; {PAGENO} &raquo; My document<br />My document<br />See <a href="http://mpdf1.com/manual/index.php">documentation manual</a><br />My document</div>';
$headerE = '<div align="center" style="background-color: #f0f2ff;background: transparent url(\'bg.jpg\') repeat scroll left top;border-radius: 4mm;font-size:18pt;font-weight:bold;font-style:italic;">Even page footer - {PAGENO} -<br />My document<br />My document<br />My document</div>';
$footer = '<div align="center" style="background-color: #f0f2ff;background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;border-radius: 4mm;font-size:18pt;font-weight:bold;font-style:italic;">{DATE j-m-Y} &raquo; {PAGENO} &raquo; My document<br />My document<br />See <a href="http://mpdf1.com/manual/index.php">documentation manual</a><br />My document</div>';
$footerE = '<div align="center" style="background-color: #f0f2ff;background: transparent url(\'bg.jpg\') repeat scroll right bottom;border-radius: 4mm;font-size:18pt;font-weight:bold;font-style:italic;">Even page footer - {PAGENO} -<br />My document<br />My document<br />My document</div>';

//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");


$mpdf=new mPDF('c','A4','','',42,15,57,57,20,17); 

$mpdf->displayDefaultOrientation = true;

$mpdf->forcePortraitHeaders = true;
$mpdf->forcePortraitMargins = true;

$mpdf->SetDisplayMode('fullpage','two');

$mpdf->mirrorMargins = 1;

$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($headerE,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footerE,'E');

$mpdf->WriteHTML($html);

$mpdf->AddPage('L');

$mpdf->WriteHTML($htmlL);
$mpdf->WriteHTML($htmlL);

// Columns
$mpdf->AddPage('L');
$mpdf->SetColumns(3,'J');
$mpdf->WriteHTML($loremH);

$mpdf->SetColumns(0);
$mpdf->WriteHTML('<hr />');


$mpdf->SetColumns(2,'J');
$mpdf->WriteHTML($loremH);
$mpdf->WriteHTML('<hr />');
$mpdf->SetColumns(0);

$mpdf->AddPage('L');

$mpdf->WriteHTML($htmlL);
$mpdf->WriteHTML($htmlL);

$mpdf->AddPage();

$mpdf->WriteHTML($html);
$mpdf->WriteHTML($html);

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================


?>