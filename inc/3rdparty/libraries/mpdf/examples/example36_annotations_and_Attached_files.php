<?php



$html = '
<h1>mPDF</h1>
<h2>Annotations</h2>
<h5>Heading 5<annotation content="This is an annotation'."\n".'in the middle of the text" subject="My Subject" icon="Comment" color="#FE88EF" author="Ian Back" /></h5>
<h6>Heading 6</h6>
<p>P: Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. <i>Fusce</i><annotation content="Fusce is a funny word!" subject="Idle Comments" icon="Note" author="Ian Back" pos-x="195" /> eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at <span title="This annotation was automatically defined from the title attribute of a span element">eleifend</span> lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada<annotation file="tiger.jpg" content="This is a file attachment (embedded file)
Double-click to open attached file
Right-click to save file on your computer" icon="Graph" title="Attached File: tiger.jpg" pos-x="195" />  sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>

';
//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c'); 

$mpdf->title2annots = true;

$mpdf->WriteHTML($html);

$mpdf->Output();

exit;
//==============================================================
//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>