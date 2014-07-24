<?php



$html = '
<!-- defines the headers/footers - this must occur before the headers/footers are set -->

<!--mpdf

<htmlpageheader name="myHTMLHeader1">
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%">Left header p <span style="font-size:14pt;">{PAGENO}</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;"><span style="font-weight: bold;">myHTMLHeader1</span></td>
</tr></table>
</htmlpageheader>

<htmlpageheader name="myHTMLHeader1Even">
<table width="100%" style="border-bottom: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
<td width="33%"><span style="font-weight: bold;">myHTMLHeader1Even</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;">Inner header p <span style="font-size:14pt;">{PAGENO}</span></td>
</tr></table>
</htmlpageheader>

<htmlpageheader name="myHTMLHeader2">
<table width="100%" style="border-bottom: 1px solid #880000; vertical-align: bottom; font-family: sans; font-size: 9pt; color: #880000;"><tr>
<td width="33%">myHTMLHeader2 p.<span style="font-size:14pt;">{PAGENO}</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;"><span style="font-weight: bold;">myHTMLHeader2</span></td>
</tr></table>
</htmlpageheader>

<htmlpageheader name="myHTMLHeader2Even">
<table width="100%" style="border-bottom: 1px solid #880000; vertical-align: bottom; font-family: sans; font-size: 9pt; color: #880000;"><tr>
<td width="33%"><span style="font-weight: bold;">myHTMLHeader2Even</span></td>
<td width="33%" align="center"><img src="sunset.jpg" width="126px" /></td>
<td width="33%" style="text-align: right;">Inner header p <span style="font-size:14pt;">{PAGENO}</span></td>
</tr></table>
</htmlpageheader>

mpdf-->

<!-- set the headers/footers - they will occur from here on in the document -->
<!--mpdf
<sethtmlpageheader name="myHTMLHeader1" page="O" value="on" show-this-page="1" />
<sethtmlpageheader name="myHTMLHeader1Even" page="E" value="on" />
mpdf-->

<h1>mPDF</h1>
<h2>Headers & Footers Method 4</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>
<pagebreak />

<h2>Headers & Footers Method 4</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>

<!-- Note the html_ prefix when referencing an HTML header using one of the pagebreaks -->
<pagebreak odd-header-name="html_myHTMLHeader2" odd-header-value="1" even-header-name="html_myHTMLHeader2Even" even-header-value="1" />

<h2>Headers & Footers Method 4</h2>
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>
';

//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,47,47,10,10); 

$mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================


?>