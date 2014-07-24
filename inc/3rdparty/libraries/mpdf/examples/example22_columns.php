<?php



$html = '
<h1>mPDF</h1>
<h2>Columns</h2>
';
//==============================================================

$loremH = "<h4>Lectus facilisis</h4>
<p>Sed auctor viverra diam. In lacinia lectus.</p>
<p>Praesent tincidunt massa in dolor. Morbi viverra leo quis ipsum.&nbsp;In vitae velit. In aliquam nulla nec mi. Sed accumsan, justo id congue fringilla, diam mauris volutpat ligula, sed aliquet elit diam at felis. Quisque et velit sed eros convallis posuere.</p>
<h5>Nunc tincidunt</h5>
<p>Nunc diam ipsum, consectetuer nec, hendrerit vitae, malesuada a, ante. Nulla ornare aliquet ante. Maecenas in lectus. Morbi porttitor mauris. Praesent ut.</p>
<p>Pede quis ante tincidunt <a href=\"http://www.stlucia.org\">blandit</a>. Maecenas bibendum erat. Curabitur sit amet ante quis velit ultricies facilisis. Ut hendrerit dolor commodo magna. In nec ligula a purus tincidunt adipiscing. Etiam non ante. </p><div>Suspendisse potenti. <indexentry content=\"Inline indexentry &lt;B&gt;\" />Suspendisse accumsan euismod lectus. Nunc commodo pede et turpis. Pellentesque porta mauris sed lorem. Ut nec augue vitae elit eleifend eleifend.Quisque ornare feugiat diam. Duis nulla metus, tempus sit amet, scelerisque a, rutrum at, nisl. Nulla facilisi. Duis metus turpis, molestie nec, laoreet tincidunt, ultrices et, purus. Nullam faucibus aliquam nisi.</div><a href=\"http://www.stlucia.org\"><img src=\"sunset.jpg\" /></a><p>Ut leo. Etiam tempus interdum tortor. Donec porta, arcu vel tincidunt placerat, lacus lorem iaculis diam, id sagittis sapien metus eu nunc. Morbi vitae nunc.<br />Mauris sapien. Phasellus elementum velit sed sapien. Nullam ante diam, consectetuer commodo, dignissim vitae, tempor vel, magna. Donec dictum. <i>Nullam</i> ultrices leo volutpat magna. Mauris blandit purus nec turpis. <a href=\"http://www.stlucia.org\">Curabitur</a> nunc. Aliquam condimentum eleifend<sup>32</sup> lectus. Praesent vitae nibh <b>et libero ullamcorper</b> scelerisque. Nullam auctor. Mauris ipsum nulla, malesuada id, aliquet at, feugiat vitae, eros.</p>

<div style=\"background-color:#DDDDBB; text-align:center; padding:3px; border:1px solid #880000;  \">Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede.
<div style=\"background-color:#ADDBBF; text-align:center; padding:3px; border:1px solid #880000;  \">Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </div>
 Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </div>
<p>Maecenas arcu justo, malesuada eu, dapibus ac, adipiscing vitae, turpis. Fusce mollis. Aliquam egestas. In purus dolor, facilisis at, fermentum nec, molestie et, metus. Vestibulum feugiat, orci at imperdiet tincidunt, mauris erat facilisis urna, sagittis ultricies dui nisl et lectus. Sed lacinia, lectus vitae dictum sodales, elit ipsum ultrices orci, non euismod arcu diam non metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In suscipit turpis vitae odio. Integer convallis dui at metus. Fusce magna. Sed sed lectus vitae enim tempor cursus. Cras eu erat vel libero sodales congue. Sed erat est, interdum nec, elementum eleifend, pretium at, nibh. Praesent massa diam, adipiscing id, mollis sed, posuere et, urna. Quisque ut leo. Aliquam interdum hendrerit tortor. Vestibulum elit. Vestibulum et arcu at diam mattis commodo. Nam ipsum sem, ultricies at, rutrum sit amet, posuere nec, velit. Sed molestie mollis dui. </p>
";



//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

$stylesheet = file_get_contents('mpdfstyleA4.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

// Bullets in columns are probably best not indented
$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

$mpdf->max_colH_correction = 1.1;


	$mpdf->WriteHTML($html,2);
	$mpdf->WriteHTML($loremH,2);

	// consider reducing lineheight when using columns - especially if vAligned justify
	$mpdf->SetDefaultBodyCSS('line-height', 1.2);

	$mpdf->SetColumns(3,'J');
	$mpdf->WriteHTML($loremH,2);

	$mpdf->SetColumns(0);
	$mpdf->WriteHTML('<hr />');


	$mpdf->SetColumns(2,'J');
	$mpdf->WriteHTML($loremH,2);
	$mpdf->WriteHTML('<hr />');
	$mpdf->SetColumns(0);
	$mpdf->WriteHTML('<hr />');

	$mpdf->SetColumns(3,'J');
	$mpdf->WriteHTML($loremH,2);

	$mpdf->SetColumns(0);
	$mpdf->WriteHTML('<hr />');
	$mpdf->SetColumns(2,'J');
	$mpdf->WriteHTML($loremH,2);



$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================


?>