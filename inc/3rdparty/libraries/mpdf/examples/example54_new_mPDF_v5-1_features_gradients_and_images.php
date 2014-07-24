<?php


include("../mpdf.php");

$mpdf=new mPDF(''); 

//==============================================================
$html = '
<style>
body {
	font-family: sans-serif;
	font-size: 10pt;
}
h4 {
	font-variant: small-caps; 
}
h5 {
	margin-bottom: 0;
	color: #110044;
}
p { margin-top: 0; }
dl {
	margin: 0;
}
table {
	border-spacing: 0.5em;
	border: 7px dashed teal;
}
.table1 { 
	background-image: -moz-linear-gradient(left, #07cdde 20%, #00f200 ); 
}
.table1 tr.thisrow1 { 
	background-image-resolution: 300dpi;
	background: transparent url(\'bayeux1.jpg\') repeat scroll left top;
}
.table1 tr.thisrow1 td { 
	height: 28mm;
}
.table1 tr.thisrow2 { 
	background-image: none; 
	background: -moz-linear-gradient(left, #c7Fdde 20%, #FF0000 ); 
	background: -webkit-gradient(linear, left bottom, left top, color-stop(0.29, rgb(90,83,12)), color-stop(0.65, rgb(117,117,39)), color-stop(0.83, rgb(153,153,67)));
}
.table3 { 
	border-collapse: collapse;
	/* background-gradient: linear #07cdde #00f200 1 0 0.5 1; */
	background: -moz-linear-gradient(left, #07cdde 20%, #00f200 ); 
}
tr.thisrow { 
	border: 3px dashed red;
	background: transparent url(\'bayeux1.jpg\') repeat scroll left top;
}
.table3 tr.thisrow { 
	border: 3px dashed orange;
	background: transparent url(\'bgrock.jpg\') repeat scroll left top;
}
tfoot tr { 
	border: 5px dashed blue;
	/* background-gradient: linear #c7Fdde #FF0000 1 0 0.5 0; */
	background: -moz-linear-gradient(left, #c7Fdde 20%, #FF0000 ); 
}
.gradient {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background: linear-gradient(top, #c7cdde, #f0f2ff);
}
.rounded {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background: linear-gradient(top, #c7cdde, #f0f2ff);
	border-radius: 2mm;
	background-clip: border-box;
}
div.text {
	padding:1em; 
	margin: 1em 0;
	text-align:justify; 
}
.code {
	font-family: mono;
	font-size: 9pt;
	background-color: #d5d5d5; 
	margin: 1em 1cm;
	padding: 0 0.3cm;
}
</style>
<body style="background:  -moz-repeating-radial-gradient(rgba(255,0,0,0.1), rgba(0,0,255,0.1) 40px, rgba(255,0,0,0.1) 80px)">

<div style="position:fixed; top: 0; right: 0"><img src="tux.svg" width="110" /></div>

<h1></a>mPDF</h1>
<h2>New features in mPDF Version 5.1</h2>

<div class="rounded text">
<ul>
<li>CSS background (images, colours or gradients) on &lt;TR&gt; and &lt;TABLE&gt;</li>
<li>CSS border on &lt;TR&gt; (only in border-collapsed mode)</li>
<li>support for Mozilla and CSS3 gradient syntax:
<ul>
<li>-moz-linear-gradient, linear-gradient</li>
<li>-moz-radial-gradient, radial-gradient</li>
<li>-moz-repeating-linear-gradient, linear-repeating-gradient</li>
<li>-moz-repeating-radial-gradient, radial-repeating-gradient</li>
</ul>
</li>
<li>expanded support for gradients (including in SVG images):
<ul>
<li>multiple colour \'stops\'</li>
<li>opacity (transparency)</li>
<li>angle and/or position can be specified</li>
</ul>
</li>
<li>gradient can be used as an image mask (custom mPDF styles: gradient-mask)</li>
<li>CSS3 image-orientation supported for &lt;IMG&gt; (similar to existing custom mPDF attribute: rotate)</li>
<li>CSS3 image-resolution supported for &lt;IMG&gt;</li>
<li>background-image-resolution (custom mPDF CSS-type style) to define resolution of background images</li>
<li>improved support for SVG images</li>
<li>SVG and WMF images supported in background-image</li>
<li>file attachments (embedded in PDF file) &rarr; &rarr; &rarr; &rarr; &rarr; <annotation file="tiger.jpg" content="This is a file attachment (embedded file)
Double-click to open attached file
Right-click to save file on your computer" icon="Paperclip" title="Attached File: tiger.jpg" pos-x="150" /></li>
</ul>
</div>

<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Tables: borders, background images & gradients</div>

<div class="rounded text">Background images or gradients can be set on whole tables or table rows (as well as individual table cells)</div>

<table class="table1">
<tbody><tr><td>Row 1</td><td>This is data</td><td>This is data</td></tr>
<tr class="thisrow1"><td>This row has</td><td>a background-image</td><td>of the bayeux tapestry</td></tr>
<tr><td><p>Row 3</p></td><td><p>This is long data</p></td><td>This is data</td></tr>
<tr class="thisrow2"><td>This row has</td><td>a gradient set</td><td>which spans all 3 cells</td></tr>
<tr><td>Row 5</td><td>Also data</td><td>Also data</td></tr>
</tbody></table>


<div class="rounded text">Border can be set on table rows (only when border-collapse is set to collapse)</div>

<table class="table3" border="1">
<tbody><tr><td>Row 1</td><td>This is data</td><td>This is data</td></tr>
<tr class="thisrow"><td>Row 2</td><td>This is data<br />This is data<br />This is data<br />This is data</td><td>Also data</td></tr>
<tr><td><p>Row 3</p></td><td><p>This is long data</p></td><td>This is data</td></tr>
</tbody></table>


<!-- ============================================================== -->


<div style="margin-top: 2em; height: 2mm; background-image: -moz-linear-gradient(45deg, red, blue);"> </div>

<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Mozilla and CSS3 gradient syntax</div>

<div style="height: 2mm; background-image: -moz-linear-gradient(45deg, red, blue);"> </div>
<h2>Linear gradients</h2>
<h4> Angle set AND points e.g. -moz-linear-gradient(34% 84% 30deg, red, orange, yellow...</h4>
<table style="border-collapse: collapse; repeat scroll left top; border: none;">
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(top right 210deg, red, orange, yellow, green, blue, indigo, violet);">top right 210 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(top right 210deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(30% 80% 60deg, red, orange, yellow, green, blue, indigo, violet);">30% 80% 60 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(30% 80% 60deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(10px 40px 325deg, red, orange, yellow, green, blue, indigo, violet);">10px 40px 325 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(10px 40px 325deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(bottom left 135deg, red, orange, yellow, green, blue, indigo, violet);">bottom left 135deg&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(bottom left 135deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
</table>


<h4> Points set only e.g. -moz-linear-gradient(bottom left, red, orange, yellow...</h4>

<table style="border-collapse: collapse; repeat scroll left top; border: none;">
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(bottom right, red, orange, yellow, green, blue, indigo, violet);">bottom right</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(bottom right, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(top, red, orange, yellow, green, blue, indigo, violet);">top</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(top, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(10px 40px, red, orange, yellow, green, blue, indigo, violet);">10px 40px</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(10px 40px, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(30% 10%, red, orange, yellow, green, blue, indigo, violet);">30% 10%</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(30% 10%, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
</table>

<pagebreak />

<h4> Angle set but no points e.g. -moz-linear-gradient(30deg, red, orange, yellow...</h4>

<table style="border-collapse: collapse; repeat scroll left top; border: none;">
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(90deg, red, orange, yellow, green, blue, indigo, violet);">90 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(90deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(120deg, red, orange, yellow, green, blue, indigo, violet);">120 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(120deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(180deg, red, orange, yellow, green, blue, indigo, violet);">180 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(180deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
<tr>
<td style="height: 25mm; width: 135mm; background-image: -moz-linear-gradient(210deg, red, orange, yellow, green, blue, indigo, violet);">210 degrees&nbsp;</td>
<td>&nbsp;</td>
<td style="height: 25mm; width: 10mm; background-image: -moz-linear-gradient(210deg, red, orange, yellow, green, blue, indigo, violet);">&nbsp;</td>
</tr>
</table>



<div class="rounded text">
<p>Linear and radial gradients are not specified in the CSS2 specification. The CSS3 draft specification gives a way of outputting gradients, but currently this is not supported by any browser.</p>
<p>Mozilla (Firefox) has developed its own way of producing gradients, which approximates to the CSS3 draft specification: </p>
<ul>
<li><i>-moz-linear-gradient</i> </li>
<li><i>-moz-repeating-linear-gradient</i></li> 
<li><i>-moz-radial-gradient</i> and </li>
<li><i>-moz-repeating-radial-gradient</i></li>
</ul>
<p>WebKit (Safari, Chrome etc.) have a separate way of defining gradients using <i>-webkit-gradient</i></p>
<p>Microsoft (IE) does not support any such method of specifying gradients, but does have a function <i>filter: progid:DXImageTransform.Microsoft.gradient()</i> </p>
<p>When writing HTML for cross-browser compatibility, it is common to see something like this in a stylesheet:</p>
<p class="code">
background: #999999; /* for non-css3 browsers */<br />
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#cccccc\', endColorstr=\'#000000\'); /* for IE */<br />
background: -webkit-gradient(linear, left top, left bottom, from(#cccccc), to(#000000)); /* for webkit browsers */<br />
background: -moz-linear-gradient(top,  #cccccc,  #000000); /* for firefox 3.6+ */<br />
</p>



<p>mPDF versions <= 5.0 supported a custom style property <i>background-gradient</i> which accepted both linear and radial gradients. These continue to be supported (and both old and new forms can be used together); note the differences:</p>
<ul>
<li>mPDF background-gradients are output underneath background-images, and both can be specified; whereas the new CSS3/Mozilla-type gradients are defined as a type of background-image</li>
<li>CSS3/Mozilla gradients support multiple colour-stops, opacity, repeating-gradients, and a greater number of options for defining the gradient axis (linear gradients) or shape and extent (radial gradients)</li>
</ul>

<p>mPDF will attempt to parse a CSS stylesheet written for cross-browser compatibility:</p>
<ul>
<li>parse and support <i>-moz</i> type gradients</li>
<li>parse and support CSS3 gradient syntax</li>
<li>ignore <i>-webkit</i> syntax gradients</li>
</ul>


<p>More details can be found at:</p>
<ul>
<li>Mozilla linear - <a href="https://developer.mozilla.org/en/CSS/-moz-linear-gradient">https://developer.mozilla.org/en/CSS/-moz-linear-gradient</a></li>
<li>Mozilla radial - <a href="https://developer.mozilla.org/en/CSS/-moz-radial-gradient">https://developer.mozilla.org/en/CSS/-moz-radial-gradient</a></li>
<li>Mozilla gradients use - <a href="https://developer.mozilla.org/en/Using_gradients">https://developer.mozilla.org/en/Using_gradients</a></li>
<li>CSS3 linear gradients - <a href="http://dev.w3.org/csswg/css3-images/#linear-gradients">http://dev.w3.org/csswg/css3-images/#linear-gradients</a></li>
<li>CSS3 radial gradients - <a href="http://dev.w3.org/csswg/css3-images/#radial-gradients">http://dev.w3.org/csswg/css3-images/#radial-gradients</a></li>
<li>WebKit gradients - <a href="http://webkit.org/blog/175/introducing-css-gradients/">http://webkit.org/blog/175/introducing-css-gradients/</a></li>
</ul>
</div>



<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Repeating gradients</div>

<p class="code">
background: repeating-linear-gradient(red, blue 20px, red 40px);
<br /> <br />
background: -moz-repeating-linear-gradient(top left -45deg, red, red 10px, rgba(255,255,255,0) 10px, rgba(255,255,255,0) 20px);
</p>

<div  style="float: right; width: 250px; height: 150px; background: -moz-repeating-linear-gradient(top left -45deg, red, red 10px, rgba(255,255,255,0) 10px, rgba(255,255,255,0) 20px);">&nbsp;</div>

<div  style="float: left; width: 250px; height: 150px; background: repeating-linear-gradient(red, blue 20px, red 40px);">&nbsp;</div>
<br style="clear: both;" />
<br />
<br />

<p class="code">
background: repeating-radial-gradient(20px 30px, circle farthest-side, red, yellow, green 10px, yellow 15px, red 20px);
<br /> <br />
background: repeating-radial-gradient(red, blue 20px, red 40px);
</p>
<div  style="float: right; width: 250px; height: 150px; background: repeating-radial-gradient(red, blue 20px, red 40px);">&nbsp;</div>

<div  style="float: left; width: 250px; height: 150px; background: repeating-radial-gradient(20px 30px, circle farthest-side, red, yellow, green 10px, yellow 15px, red 20px);">&nbsp;</div>
<br style="clear: both;" />


<pagebreak />


<h2>Radial gradients</h2>

<div  style="float: right; width: 250px; height: 150px; padding: 15px; background: #F56991; color: #E8F3F8; 
border-radius: 155px / 100px;
-moz-border-radius: 155px / 100px;
box-shadow: 10px 10px 25px #CCC;
-moz-box-shadow: 5px 5px 25px #CCC;
background-image: -moz-radial-gradient(70% 30%, ellipse , #ffffff 0%, #F56991 50%, #8A2624 100%);">&nbsp;</div>


<div  style="float: left; width: 150px; height: 150px; padding: 15px; background: #F56991; color: #E8F3F8;
border-radius: 100px;
-moz-border-radius: 100px;
box-shadow: 10px 10px 25px #CCC;
-moz-box-shadow: 5px 5px 25px #CCC;
background-image: -moz-radial-gradient(70% 30%, circle , #ffffff 0%, #E56991 50%, #8A2624 100%);">&nbsp;</div>
<br style="clear: both;" />


<div  style="float: right; width: 150px; height: 150px; border: 0.2mm solid black;
background: radial-gradient(bottom left, farthest-side, red, blue 50px, pink);
background-image: -moz-radial-gradient(red, yellow, #1E90FF);
background: -webkit-gradient(linear, left bottom, left top, color-stop(0.48, rgb(107,14,86)), color-stop(0.74, rgb(140,41,112)), color-stop(0.87, rgb(168,70,146)));">&nbsp;</div>

<div  style="float: left; width: 150px; height: 150px; border: 0.2mm solid black; background-image: -moz-radial-gradient(red 5%, yellow 25%, #1E90FF 50%);">&nbsp;</div>
<br style="clear: both;" />

<div style="float: right; width: 300px; height: 150px; border: 0.2mm solid black; background-image: -moz-radial-gradient(bottom left, circle, red, yellow, #1E90FF);">&nbsp;</div>

<div style="float: left; width: 300px; height: 150px; border: 0.2mm solid black; background-image: -moz-radial-gradient(bottom left, ellipse, red, yellow, #1E90FF);">&nbsp;</div>
<br style="clear: both;" />

<div style="float: right; width: 300px; height: 150px; border: 0.2mm solid black; background-image: -moz-radial-gradient(ellipse closest-side, red, yellow 10%, #1E90FF 50%, white);">&nbsp;</div>

<div style="float: left; width: 300px; height: 150px; border: 0.2mm solid black; background-image: -moz-radial-gradient(ellipse farthest-corner, red, yellow 10%, #1E90FF 50%, white);">&nbsp;</div>
<br style="clear: both;" />


<p style="background-image: -moz-radial-gradient(center , red, orange, yellow, green, blue, indigo, violet);">&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p>

<p style="background-image: -moz-radial-gradient(center , circle closest-side, blue 0%, red 100%);">&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p>




<pagebreak />


<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Gradient Image mask</div>

<div class="rounded text">Gradients (linear or radial) can also be used to produce \'masks\' for images. The same syntax is used as for background gradients (e.g. -moz-linear-gradient) but is set using a custom mPDF style: <i>gradient-mask</i>. The rgba() method for defining colours is used: colours are ignored, but the opacity value is used to mask the image.</div>

<p class="code">&lt;img src="windmill.jpg" style="gradient-mask: -moz-radial-gradient(center, ellipse closest-side, rgba(255,255,255,1), rgba(255,255,255,1) 30%, rgba(255,255,255,0) 90%, rgba(255,255,255,0));" /&gt;
<br /><br />&lt;img src="windmill.jpg" style="gradient-mask: -moz-radial-gradient(center, ellipse closest-side, rgba(255,255,255,1), rgba(255,255,255,1) 70%, rgba(255,255,255,0) 90%, rgba(255,255,255,0));" /&gt;
<br /><br />&lt;img src="windmill.jpg" style="gradient-mask: -moz-linear-gradient(left, rgba(0,0,0,0) , rgba(0,0,0,1) 50% , rgba(0,0,0,0) 100%);" /&gt;
</p>

<img src="windmill.jpg" style="gradient-mask: -moz-radial-gradient(center, ellipse closest-side, rgba(255,255,255,1), rgba(255,255,255,1) 30%, rgba(255,255,255,0) 90%, rgba(255,255,255,0));" />
<img src="windmill.jpg" style="gradient-mask: -moz-radial-gradient(center, ellipse closest-side, rgba(255,255,255,1), rgba(255,255,255,1) 70%, rgba(255,255,255,0) 90%, rgba(255,255,255,0));" />
<img src="windmill.jpg" style="gradient-mask: -moz-linear-gradient(left, rgba(0,0,0,0) , rgba(0,0,0,1) 50% , rgba(0,0,0,0) 100%);" />


<br />




<pagebreak />

<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Image orientation</div>

<div class="rounded text">Images can be rotated using a custom mPDF HTML attribute: rotate. mPDF now also supports the draft CSS3 property of image-orientation. Rotation can be expressed in degrees, radians or grad units; it is corrected if necessary to an orthogonal rotation i.e. 90, 180 or 270 degrees. NB This does not work on background-images.</div>
<p class="code">&lt;img src="tiger2.png" style="image-orientation: -90deg" width="100" /&gt;
<br />
&lt;img src="tiger2.png" style="image-orientation: 3.14159rad" width="100" /&gt;
</p>

<img src="tiger2.png" width="100" /> 
<img src="tiger2.png" style="image-orientation: 75deg;" width="100" /> 
<img src="tiger2.png" style="image-orientation: 180deg; image-resolution: 300dpi; " width="100" /> 
<img src="tiger2.png" style="image-orientation: -90deg" width="100" /> 


<br />

<br />
<br />
<br />

<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Image resolution</div>


<div class="rounded text">Image files (which do not have an output width or height specified) are displayed in mPDF at the default resolution set by the variable $mpdf->img_dpi. This can be overridden using the draft CSS3 property \'image-resolution\', which can be applied to &lt;IMG&gt; or background-images.
<br />
The next 3 image files are identical (300px x 300px) but they have been saved with a different specified resolution: the first at 96dpi, the second at 300dpi.
<br />
NB When used in combination with \'from-image\', a specified resolution is only used if the image does not have an intrinsic resolution. Only JPG, PNG and BMP files store a specified DPI resolution  in the file.</div>

<p class="code">&lt;img src="tiger300px300dpi.png" style="image-resolution: from-image;" /&gt;
<br />&lt;img src="tiger300px300dpi.png" style="image-resolution: 150dpi;" /&gt;
<br />&lt;img src="tiger300px96dpi.png" style="image-resolution: from-image;" /&gt;</p>

<img src="tiger300px300dpi.png" style="image-resolution: from-image;" /> 
<img src="tiger300px300dpi.png" style="image-resolution: 150dpi;" /> 
<img src="tiger300px96dpi.png" style="image-resolution: from-image;" /> 
<br /> <br />



<pagebreak />

<div class="rounded text">Image resolution can also be applied to a background-image. This can be used as an alternative to the custom mPDF style property - \'background-image-resize\'</div>

<p class="code">&lt;div height="300px" width="300px" style="background: #FFCCEE url(tiger300px96dpi.png); background-image-resolution: from-image; border: 0.2mm solid black;"&gt;</p>
<div height="300px" width="300px" style="background: #FFCCEE url(tiger300px96dpi.png); background-image-resolution: from-image; border: 0.2mm solid black;">Hallo<br />world
</div>

<br />

<p class="code">&lt;div height="300px" width="300px" style="background-image: url(tiger300px300dpi.png); background-image-resolution: from-image; border: 0.2mm solid black;"&gt;</p>
<div height="300px" width="300px" style="background-image: url(tiger300px300dpi.png); background-image-resolution: from-image; border: 0.2mm solid black;">
</div>



<br />


<pagebreak />



<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Mixed effects</div>


<div style="padding: 15px; background: url(flowers-pattern.jpg) repeat right; border-radius: 90px;background-color: #00f200 ;  ">
<div style="padding: 15px; background: -moz-linear-gradient(top right, red, orange, yellow, green, blue, indigo, violet); border-radius: 75px; ">
<div style="padding: 15px; background-gradient: linear #07cdde #00f200 0 0 0.5 1; border-radius: 60px; ">
<div style="padding: 15px; background: url(flowers-pattern.jpg) repeat right; border-radius: 45px; background-image-resolution: 180dpi; ">
<div style="padding: 15px; background: -moz-linear-gradient(left, red, orange, yellow, green, blue, indigo, violet); border-radius: 30px; ">
<div style="padding: 15px; background: url(alpha3.png) repeat top left; border-radius: 15px; background-image-resolution: 180dpi; ">
Hallo World
</div>
<div style="padding: 15px; background: url(alpha3.png) repeat top left; border-radius: 15px; background-image-resolution: 360dpi; ">
Hallo World
</div>
</div>
</div>
</div>
</div>
</div>

<br />

<div style="background-color:#FF0000 ; width:180px; background-image: -moz-radial-gradient(center, ellipse closest-side, rgba(255,255,255,1), rgba(255,255,255,1) 70%, rgba(255,255,255,0) 90%, rgba(255,255,255,0));">
<img src="tux.svg" width="180" />
</div>

';

//==============================================================
if ($_REQUEST['html']) { echo $html; exit; }
if ($_REQUEST['source']) { 
	$file = __FILE__;
	header("Content-Type: text/plain");
	header("Content-Length: ". filesize($file));
	header("Content-Disposition: attachment; filename='".$file."'");
	readfile($file);
	exit; 
}
//==============================================================
$mpdf->WriteHTML($html);

//==============================================================
//==============================================================
// OUTPUT
$mpdf->Output(); exit;


//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>