<?php

include("../mpdf.php");

$mpdf=new mPDF('','','','',15,15,57,16,9,9); 
$mpdf->SetImportUse();	

$mpdf->SetDisplayMode('fullpage');

$mpdf->SetCompression(false);

// Add First page
$pagecount = $mpdf->SetSourceFile('sample_basic.pdf');

$crop_x = 50;
$crop_y = 50;
$crop_w = 100;
$crop_h = 100;

$tplIdx = $mpdf->ImportPage(2, $crop_x, $crop_y, $crop_w, $crop_h);

$x = 50;
$y = 50;
$w = 100;
$h = 100;

$mpdf->UseTemplate($tplIdx, $x, $y, $w, $h);

$mpdf->Rect($x, $y, $w, $h);

$mpdf->Output('newpdf.pdf', 'I');

exit;


?>