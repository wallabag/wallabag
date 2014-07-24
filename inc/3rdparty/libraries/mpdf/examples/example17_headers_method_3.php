<?php



$html = '
<!-- defines the headers/footers - this must occur before the headers/footers are set -->

<!--mpdf
<pageheader name="odds" content-right="My document" header-style-right="color: #880000; font-style: italic;" line="1" />
<pageheader name="evens" content-right="{DATE j-m-Y}" content-center="{PAGENO}/{nb}" header-style="color: #880000; font-style: italic;" />
<pagefooter name="odds" content-right="Odd Footer" footer-style-right="color: #880000; font-style: italic;" line="1" />
<pagefooter name="evens" content-right="{DATE j-m-Y}" content-center="{PAGENO}/{nb}" footer-style="color: #880000; font-style: italic;" />

<pageheader name="display" content-center="New header called Display" header-style="color: #000088; font-weight: bold;" />
mpdf-->

<!-- set the headers/footers - they will occur from here on in the document -->
<!--mpdf
<setpageheader name="odds" page="odd" value="on" show-this-page="1" />
<setpageheader name="evens" page="even" value="1" />
<setpagefooter name="odds" page="O" value="on" />
<setpagefooter name="evens" page="E" value="1" />
mpdf-->

<h1>mPDF</h1>
<h2>Headers & Footers Method 3</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>
<pagebreak />

<h2>Headers & Footers Method 3</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>

<pagebreak odd-header-name="display" odd-header-value="1" even-header-name="display" even-header-value="1" />

<h2>Headers & Footers</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>
';

//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================


?>