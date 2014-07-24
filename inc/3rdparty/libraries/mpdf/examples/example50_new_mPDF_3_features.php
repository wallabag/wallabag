<?php

$html = '
<style>
.gradient {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
}
.radialgradient {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background-gradient: radial #00FFFF #FFFF00 0.5 0.5 0.5 0.5 0.65;
	margin: auto;
}
.rounded {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
	border-radius: 2mm;
	background-clip: border-box;
}
h4 {
	font-family: sans;
	font-weight: bold;
	margin-top: 1em;
	margin-bottom: 0.5em;
}
div {
	padding:1em; 
	margin-bottom: 1em;
	text-align:justify; 
}
.example pre {
	background-color: #d5d5d5; 
	margin: 1em 1cm;
	padding: 0 0.3cm;
}

pre { text-align:left }
pre.code { font-family: monospace }

table.html4colortable {margin:auto; width:80%; border:none }
table.html4colortable TD {border:none; padding:0}
td .colorsquare { display:block;width:16px;height:16px;border:2px solid black }

table.x11colortable td {text-align:center; background: white; }
table.x11colortable td.c { text-transform:uppercase }
table.x11colortable td:first-child, table.x11colortable td:first-child+td { border:1px solid black }
table.x11colortable th {text-align:center; background:black; color:white }

table.tprofile th.title {background:gray; color:white}
table.tprofile th { width:29%;padding:2px }
table.tprofile td { width:71%;padding:2px }

table.hslexample { background: #808080; padding:1em; margin:0; float:left; }
table.hslexample td,table.hslexample th { font-size:smaller;width:3em }
</style>
<!-- TEST FLOAT -->
<body style="background-gradient: linear #88FFFF #FFFF44 0 0.5 1 0.5;">
<h2>mPDF Version 3.0</h2>
<h1>New Features</h1>

<div style="border:0.1mm solid #220044; padding:1em 2em; background-color:#ffffcc; ">
<h4>Page background</h4>
<div class="gradient">
The background colour can now be set by CSS styles on the &lt;body&gt; tag. This will set the background for the whole page. In this document, the background has been set as a gradient (see below).
</div>

<h4>Background Gradients</h4>
<div class="gradient">
Background can be set as a linear or radial gradient between two colours. The background has been set on this &lt;div&gt; element to a linear gradient. CSS style used here is:<br />
<span style="font-family: mono; font-size: 9pt;">background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;</span><br />
The four numbers are coordinates in the form (x1, y1, x2, y2) which defines the gradient vector. x and y are values from 0 to 1, where  1 represents the height or width of the box as it is printed.
<br />
<br />
Background gradients can be set on all block elements e.g. P, DIV, H1-H6, as well as on BODY.
</div>
<div class="radialgradient">
The background has been set on this &lt;div&gt; element to a radial gradient. CSS style used here is:<br />
<span style="font-family: mono; font-size: 9pt;">background-gradient: radial #00FFFF #FFFF00 0.5 0.5 0.5 0.5 0.65;</span><br />
The five numbers are coordinates in the form (x1, y1, x2, y2, r) where (x1, y1) is the starting point of the gradient with color1, 
(x2, y2) is the center of the circle with color2, and r is the radius of the circle.
(x1, y1) should be inside the circle, otherwise some areas will not be defined.
<br />
<br />
Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec mattis lacus ac purus feugiat semper. Donec aliquet nunc odio, vitae pellentesque diam. Pellentesque sed velit lacus. Duis quis dui quis sem consectetur sollicitudin. Cras dolor quam, dapibus et pretium sit amet, elementum vel arcu. Duis rhoncus facilisis erat nec mattis. In hac habitasse platea dictumst. Vivamus hendrerit sem in justo aliquet a pellentesque lorem scelerisque. Suspendisse a augue sed urna rhoncus elementum. Aliquam erat volutpat. 
</div>

<h4>Background Images</h4>
<div style="border:0.1mm solid #880000; background: transparent url(bg.jpg) repeat fixed right top; background-color:#ccffff; ">
The CSS properties background-image, background-position, and background-repeat are supported as defined in CSS2, as well as the shorthand form "background".
<br />
The background has been set on this &lt;div&gt; element to:<br />
<span style="font-family: mono; font-size: 9pt;">background: transparent url(\'bg.jpg\') repeat fixed right top;</span><br />
Background gradients can be set on all block elements e.g. P, DIV, H1-H6, as well as on BODY.
</div>

<h4>Rounded Borders</h4>
<div class="rounded">
Rounded corners to borders can be added using border-radius as defined in the draft spec. of <a href="http://www.w3.org/TR/2008/WD-css3-background-20080910/#layering">CSS3</a>. <br />

The two length values of the border-*-radius properties define the radii of a quarter ellipse that defines the shape of the corner of the outer border edge.
The first value is the horizontal radius. <br />
<span style="font-family: mono; font-size: 9pt;">border-top-left-radius: 55pt 25pt;</span>  55pt is radius of curve from top end of left border starting to go round to the top.<br />

If the second length is omitted it is equal to the first (and the corner is thus a quarter circle). If either length is zero, the corner is square, not rounded.<br />

The border-radius shorthand sets all four border-*-radius properties. If values are given before and after a slash, then the values before the slash set the horizontal radius and the values after the slash set the vertical radius. If there is no slash, then the values set both radii equally. The four values for each radii are given in the order top-left, top-right, bottom-right, bottom-left. If bottom-left is omitted it is the same as top-right. If bottom-right is omitted it is the same as top-left. If top-right is omitted it is the same as top-left.
</div>
<div class="rounded">
<span style="font-family: mono; font-size: 9pt;">border-radius: 4em;</span><br />

would be equivalent to<br />

<span style="font-family: mono; font-size: 9pt;">border-top-left-radius:     4em;<br />
border-top-right-radius:    4em;<br />
border-bottom-right-radius: 4em;<br />
border-bottom-left-radius:  4em;</span><br />
<br />
and<br />
<span style="font-family: mono; font-size: 9pt;">border-radius: 2em 1em 4em / 0.5em 3em;</span><br />
would be equivalent to<br />
<span style="font-family: mono; font-size: 9pt;">border-top-left-radius:     2em 0.5em;<br />
border-top-right-radius:    1em 3em;<br />
border-bottom-right-radius: 4em 0.5em;<br />
border-bottom-left-radius:  1em 3em;</span>
</div>

<h4>Interlaced and alpha-channel-set PNG images supported</h4>
<div class="rounded">
Prior to version 3.0, mPDF has not supported PNG images which are interlaced, or images with transparency - now it does.
<div style="float: left; width: 45%; background-color: #CCFFFF; margin-bottom: 0pt; ">With a blue background-color set, this PNG image with transparency appears like this:
<br /><img src="alpha.png" width="70" />
</div>
<div style="float: right; width: 45%; background-color: #FFFFCC; margin-bottom: 0pt; ">With a different background-color set, the same image appears like this:
<br /><img src="alpha.png" width="70" />
</div>
<div style="clear: both; margin-bottom: 0pt; ">This will not work with background-gradient or background-image (the background-color is used to paint the transparent parts of the image).</div>
</div>


</div>


<h4>CSS "Float"</h4>
<div>
Block elements can be positioned alongside each other using the CSS property float: left or right. The clear property can also be used, set as left|right|both. Float is only supported on block elements (i.e. not SPAN etc.) and is not fully compliant with the CSS specification. 
Float only works properly if a width is set for the float, otherwise the width is set to the maximum available (full width, or less if floats already set).
<br />
Margin-right can still be set for a float:right and vice-versa.
<br />
A block element next to a float has the padding adjusted so that content fits in the remaining width. Text next to a float should wrap correctly, but backgrounds and borders will overlap and/or lie under the floats in a mess.
<br />
NB The width that is set defines the width of the content-box. So if you have two floats with width=50% and either of them has padding, margin or border, they will not fit together on the page.
</div>
<div class="gradient" style="float: right; width: 28%; margin-bottom: 0pt; ">
This is text in a &lt;div&gt; element that is set to float:right and width:28%.
</div>
<div class="gradient" style="float: left; width: 54%; margin-bottom: 0pt; ">
This is text in a &lt;div&gt; element that is set to float:left and width:54%.
</div>

<div style="clear: both; margin: 0pt; padding: 0pt; "></div>
This is text that follows a &lt;div&gt; element that is set to clear:both.



<h4>HTML Headers now support hyperlinks</h4>
<div class="gradient">
Hyperlinks can now be included in HTML headers and footers. See the link to the documentation manual in the footer of this document.
</div>


contd...

<pagebreak resetpagenum="20" />
<h4>Resetting Page Numbering</h4>
<div class="gradient">
Page numbers can now be reset to any value (rather than just 1) during the document i.e. in any function/tag that supports resetting the numbering: AddPage(), &lt;pagebreak&gt; etc.
<br />
Note that the page number has been reset to 20 from this page.
</div>

<h4>Page Numbering - additional text</h4>
<div class="gradient">
Text can be defined to appear before and after page numbers ($pagenumPrefix; $pagenumSuffix; $nbpgPrefix; $nbpgSuffix;)
<br />
This document has a non-HTML header defined with the right content as \'{PAGENO}{<span>nbpg</span>}\'.<br />
The following values have been set:<br />
<span style="font-family: mono; font-size: 9pt;">$mpdf->pagenumPrefix = \'Page \';<br />
$mpdf->pagenumSuffix = \'\';<br />
$mpdf->nbpgPrefix = \' of \';<br />
$mpdf->nbpgSuffix = \' pages.\';</span>
<br />
<br />

This is only recommended in non-HTML headers and footers. Although the text is added correctly in HTML headers & footers, the text alignment is not readjusted after substitution e.g. if it used in the right margin.
</div>

<h4>Other New features</h4>
<div style="border:0.1mm solid #555555; background-color: #DDDDDD; padding: 1em; font-size:8pt; font-family: mono;">
- internal links supported in Indexes (parameter added to CreateIndex() and CreateReference()<br />&nbsp; &nbsp; $useLinking=true;)<br />
- improved handling of &lt;br>, block elements, and text lines inside tables<br />
- borders of block-level elements & table cell borders supported (partially) in columns<br />
- optional error reporting for problems with Images ($showImageErrors=true;)<br />
- ToC will word-wrap long entries<br />
- internal links (Bookmarks, IndexEntry and ToCEntry) rewritten to give more accurate positioning<br />&nbsp; &nbsp; (when used as &lt;tag>)<br />
- autofont algorithm improved for CJK languages<br />
</div>


  <h4>All SVG color keywords supported</h4>
  <p style="margin: 0pt;">This table provides a list of all the named colors supported by mPDF. The
   list is precisely the same as the <a href="http://www.w3.org/TR/SVG/types.html#ColorKeywords">SVG 1.0 color
   keyword names</a>. 
   The two color swatches on the left illustrate setting the background color
   of a table cell in two ways: The first column uses the named color value,
   and the second column uses the respective numeric color value.
  </p>
<table align="center" class="x11colortable" style="background-color: white" >
   <tbody>
    <tr>
     <th style="background: black ">Named
     </th><th>Numeric
     </th><th>Color&nbsp;name

     </th><th>Hex&nbsp;rgb

     </th><th>Decimal

    </th></tr><tr>
     <td class="c" style="background: aliceblue ">&nbsp;

     </td><td class="c" style="background: rgb(240, 248, 255) ">&nbsp;

     </td><td>aliceblue

     </td><td class="c" style="background-color: #FFFFFF; ">#f0f8ff

     </td><td class="c" style="background-color: #FFFFFF; ">240,248,255

    </td></tr><tr>
     <td class="c" style="background: antiquewhite ">&nbsp;

     </td><td class="c" style="background: rgb(250, 235, 215) ">&nbsp;

     </td><td>antiquewhite

     </td><td class="c" style="background-color: #FFFFFF; ">#faebd7

     </td><td class="c" style="background-color: #FFFFFF; ">250,235,215

    </td></tr><tr>
     <td class="c" style="background: aqua ">&nbsp;

     </td><td class="c" style="background: rgb(0, 255, 255) ">&nbsp;

     </td><td>aqua

     </td><td class="c" style="background-color: #FFFFFF; ">#00ffff

     </td><td class="c" style="background-color: #FFFFFF; ">0,255,255

    </td></tr><tr>
     <td class="c" style="background: aquamarine ">&nbsp;

     </td><td class="c" style="background: rgb(127, 255, 212) ">&nbsp;

     </td><td>aquamarine

     </td><td class="c" style="background-color: #FFFFFF; ">#7fffd4

     </td><td class="c" style="background-color: #FFFFFF; ">127,255,212

    </td></tr><tr>
     <td class="c" style="background: azure ">&nbsp;

     </td><td class="c" style="background: rgb(240, 255, 255) ">&nbsp;

     </td><td>azure

     </td><td class="c" style="background-color: #FFFFFF; ">#f0ffff

     </td><td class="c" style="background-color: #FFFFFF; ">240,255,255

    </td></tr><tr>
     <td class="c" style="background: beige ">&nbsp;

     </td><td class="c" style="background: rgb(245, 245, 220) ">&nbsp;

     </td><td>beige

     </td><td class="c" style="background-color: #FFFFFF; ">#f5f5dc

     </td><td class="c" style="background-color: #FFFFFF; ">245,245,220

    </td></tr><tr>
     <td class="c" style="background: bisque ">&nbsp;

     </td><td class="c" style="background: rgb(255, 228, 196) ">&nbsp;

     </td><td>bisque

     </td><td class="c" style="background-color: #FFFFFF; ">#ffe4c4

     </td><td class="c" style="background-color: #FFFFFF; ">255,228,196

    </td></tr><tr>
     <td class="c" style="background: black ">&nbsp;

     </td><td class="c" style="background: rgb(0, 0, 0) ">&nbsp;

     </td><td>black

     </td><td class="c" style="background-color: #FFFFFF; ">#000000

     </td><td class="c" style="background-color: #FFFFFF; ">0,0,0

    </td></tr><tr>
     <td class="c" style="background: blanchedalmond ">&nbsp;

     </td><td class="c" style="background: rgb(255, 235, 205) ">&nbsp;

     </td><td>blanchedalmond

     </td><td class="c" style="background-color: #FFFFFF; ">#ffebcd

     </td><td class="c" style="background-color: #FFFFFF; ">255,235,205

    </td></tr><tr>
     <td class="c" style="background: blue ">&nbsp;

     </td><td class="c" style="background: rgb(0, 0, 255) ">&nbsp;

     </td><td>blue

     </td><td class="c" style="background-color: #FFFFFF; ">#0000ff

     </td><td class="c" style="background-color: #FFFFFF; ">0,0,255

    </td></tr><tr>
     <td class="c" style="background: blueviolet ">&nbsp;

     </td><td class="c" style="background: rgb(138, 43, 226) ">&nbsp;

     </td><td>blueviolet

     </td><td class="c" style="background-color: #FFFFFF; ">#8a2be2

     </td><td class="c" style="background-color: #FFFFFF; ">138,43,226

    </td></tr><tr>
     <td class="c" style="background: brown ">&nbsp;

     </td><td class="c" style="background: rgb(165, 42, 42) ">&nbsp;

     </td><td>brown

     </td><td class="c" style="background-color: #FFFFFF; ">#a52a2a

     </td><td class="c" style="background-color: #FFFFFF; ">165,42,42

    </td></tr><tr>
     <td class="c" style="background: burlywood ">&nbsp;

     </td><td class="c" style="background: rgb(222, 184, 135) ">&nbsp;

     </td><td>burlywood

     </td><td class="c" style="background-color: #FFFFFF; ">#deb887

     </td><td class="c" style="background-color: #FFFFFF; ">222,184,135

    </td></tr><tr>
     <td class="c" style="background: cadetblue ">&nbsp;

     </td><td class="c" style="background: rgb(95, 158, 160) ">&nbsp;

     </td><td>cadetblue

     </td><td class="c" style="background-color: #FFFFFF; ">#5f9ea0

     </td><td class="c" style="background-color: #FFFFFF; ">95,158,160

    </td></tr><tr>
     <td class="c" style="background: chartreuse ">&nbsp;

     </td><td class="c" style="background: rgb(127, 255, 0) ">&nbsp;

     </td><td>chartreuse

     </td><td class="c" style="background-color: #FFFFFF; ">#7fff00

     </td><td class="c" style="background-color: #FFFFFF; ">127,255,0

    </td></tr><tr>
     <td class="c" style="background: chocolate ">&nbsp;

     </td><td class="c" style="background: rgb(210, 105, 30) ">&nbsp;

     </td><td>chocolate

     </td><td class="c" style="background-color: #FFFFFF; ">#d2691e

     </td><td class="c" style="background-color: #FFFFFF; ">210,105,30

    </td></tr><tr>
     <td class="c" style="background: coral ">&nbsp;

     </td><td class="c" style="background: rgb(255, 127, 80) ">&nbsp;

     </td><td>coral

     </td><td class="c" style="background-color: #FFFFFF; ">#ff7f50

     </td><td class="c" style="background-color: #FFFFFF; ">255,127,80

    </td></tr><tr>
     <td class="c" style="background: cornflowerblue ">&nbsp;

     </td><td class="c" style="background: rgb(100, 149, 237) ">&nbsp;

     </td><td>cornflowerblue

     </td><td class="c" style="background-color: #FFFFFF; ">#6495ed

     </td><td class="c" style="background-color: #FFFFFF; ">100,149,237

    </td></tr><tr>
     <td class="c" style="background: cornsilk ">&nbsp;

     </td><td class="c" style="background: rgb(255, 248, 220) ">&nbsp;

     </td><td>cornsilk

     </td><td class="c" style="background-color: #FFFFFF; ">#fff8dc

     </td><td class="c" style="background-color: #FFFFFF; ">255,248,220

    </td></tr><tr>
     <td class="c" style="background: crimson ">&nbsp;

     </td><td class="c" style="background: rgb(220, 20, 60) ">&nbsp;

     </td><td>crimson

     </td><td class="c" style="background-color: #FFFFFF; ">#dc143c

     </td><td class="c" style="background-color: #FFFFFF; ">220,20,60

    </td></tr><tr>
     <td class="c" style="background: cyan ">&nbsp;

     </td><td class="c" style="background: rgb(0, 255, 255) ">&nbsp;

     </td><td>cyan

     </td><td class="c" style="background-color: #FFFFFF; ">#00ffff

     </td><td class="c" style="background-color: #FFFFFF; ">0,255,255

    </td></tr><tr>
     <td class="c" style="background: darkblue ">&nbsp;

     </td><td class="c" style="background: rgb(0, 0, 139) ">&nbsp;

     </td><td>darkblue

     </td><td class="c" style="background-color: #FFFFFF; ">#00008b

     </td><td class="c" style="background-color: #FFFFFF; ">0,0,139

    </td></tr><tr>
     <td class="c" style="background: darkcyan ">&nbsp;

     </td><td class="c" style="background: rgb(0, 139, 139) ">&nbsp;

     </td><td>darkcyan

     </td><td class="c" style="background-color: #FFFFFF; ">#008b8b

     </td><td class="c" style="background-color: #FFFFFF; ">0,139,139

    </td></tr><tr>
     <td class="c" style="background: darkgoldenrod ">&nbsp;

     </td><td class="c" style="background: rgb(184, 134, 11) ">&nbsp;

     </td><td>darkgoldenrod

     </td><td class="c" style="background-color: #FFFFFF; ">#b8860b

     </td><td class="c" style="background-color: #FFFFFF; ">184,134,11

    </td></tr><tr>
     <td class="c" style="background: darkgray ">&nbsp;

     </td><td class="c" style="background: rgb(169, 169, 169) ">&nbsp;

     </td><td>darkgray

     </td><td class="c" style="background-color: #FFFFFF; ">#a9a9a9

     </td><td class="c" style="background-color: #FFFFFF; ">169,169,169

    </td></tr><tr>
     <td class="c" style="background: darkgreen ">&nbsp;

     </td><td class="c" style="background: rgb(0, 100, 0) ">&nbsp;

     </td><td>darkgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#006400

     </td><td class="c" style="background-color: #FFFFFF; ">0,100,0

    </td></tr><tr>
     <td class="c" style="background: darkgrey ">&nbsp;

     </td><td class="c" style="background: rgb(169, 169, 169) ">&nbsp;

     </td><td>darkgrey

     </td><td class="c" style="background-color: #FFFFFF; ">#a9a9a9

     </td><td class="c" style="background-color: #FFFFFF; ">169,169,169

    </td></tr><tr>
     <td class="c" style="background: darkkhaki ">&nbsp;

     </td><td class="c" style="background: rgb(189, 183, 107) ">&nbsp;

     </td><td>darkkhaki

     </td><td class="c" style="background-color: #FFFFFF; ">#bdb76b

     </td><td class="c" style="background-color: #FFFFFF; ">189,183,107

    </td></tr><tr>
     <td class="c" style="background: darkmagenta ">&nbsp;

     </td><td class="c" style="background: rgb(139, 0, 139) ">&nbsp;

     </td><td>darkmagenta

     </td><td class="c" style="background-color: #FFFFFF; ">#8b008b

     </td><td class="c" style="background-color: #FFFFFF; ">139,0,139

    </td></tr><tr>
     <td class="c" style="background: darkolivegreen ">&nbsp;

     </td><td class="c" style="background: rgb(85, 107, 47) ">&nbsp;

     </td><td>darkolivegreen

     </td><td class="c" style="background-color: #FFFFFF; ">#556b2f

     </td><td class="c" style="background-color: #FFFFFF; ">85,107,47

    </td></tr><tr>
     <td class="c" style="background: darkorange ">&nbsp;

     </td><td class="c" style="background: rgb(255, 140, 0) ">&nbsp;

     </td><td>darkorange

     </td><td class="c" style="background-color: #FFFFFF; ">#ff8c00

     </td><td class="c" style="background-color: #FFFFFF; ">255,140,0

    </td></tr><tr>
     <td class="c" style="background: darkorchid ">&nbsp;

     </td><td class="c" style="background: rgb(153, 50, 204) ">&nbsp;

     </td><td>darkorchid

     </td><td class="c" style="background-color: #FFFFFF; ">#9932cc

     </td><td class="c" style="background-color: #FFFFFF; ">153,50,204

    </td></tr><tr>
     <td class="c" style="background: darkred ">&nbsp;

     </td><td class="c" style="background: rgb(139, 0, 0) ">&nbsp;

     </td><td>darkred

     </td><td class="c" style="background-color: #FFFFFF; ">#8b0000

     </td><td class="c" style="background-color: #FFFFFF; ">139,0,0

    </td></tr><tr>
     <td class="c" style="background: darksalmon ">&nbsp;

     </td><td class="c" style="background: rgb(233, 150, 122) ">&nbsp;

     </td><td>darksalmon

     </td><td class="c" style="background-color: #FFFFFF; ">#e9967a

     </td><td class="c" style="background-color: #FFFFFF; ">233,150,122

    </td></tr><tr>
     <td class="c" style="background: darkseagreen ">&nbsp;

     </td><td class="c" style="background: rgb(143, 188, 143) ">&nbsp;

     </td><td>darkseagreen

     </td><td class="c" style="background-color: #FFFFFF; ">#8fbc8f

     </td><td class="c" style="background-color: #FFFFFF; ">143,188,143

    </td></tr><tr>
     <td class="c" style="background: darkslateblue ">&nbsp;

     </td><td class="c" style="background: rgb(72, 61, 139) ">&nbsp;

     </td><td>darkslateblue

     </td><td class="c" style="background-color: #FFFFFF; ">#483d8b

     </td><td class="c" style="background-color: #FFFFFF; ">72,61,139

    </td></tr><tr>
     <td class="c" style="background: darkslategray ">&nbsp;

     </td><td class="c" style="background: rgb(47, 79, 79) ">&nbsp;

     </td><td>darkslategray

     </td><td class="c" style="background-color: #FFFFFF; ">#2f4f4f

     </td><td class="c" style="background-color: #FFFFFF; ">47,79,79

    </td></tr><tr>
     <td class="c" style="background: darkslategrey ">&nbsp;

     </td><td class="c" style="background: rgb(47, 79, 79) ">&nbsp;

     </td><td>darkslategrey

     </td><td class="c" style="background-color: #FFFFFF; ">#2f4f4f

     </td><td class="c" style="background-color: #FFFFFF; ">47,79,79

    </td></tr><tr>
     <td class="c" style="background: darkturquoise ">&nbsp;

     </td><td class="c" style="background: rgb(0, 206, 209) ">&nbsp;

     </td><td>darkturquoise

     </td><td class="c" style="background-color: #FFFFFF; ">#00ced1

     </td><td class="c" style="background-color: #FFFFFF; ">0,206,209

    </td></tr><tr>
     <td class="c" style="background: darkviolet ">&nbsp;

     </td><td class="c" style="background: rgb(148, 0, 211) ">&nbsp;

     </td><td>darkviolet

     </td><td class="c" style="background-color: #FFFFFF; ">#9400d3

     </td><td class="c" style="background-color: #FFFFFF; ">148,0,211

    </td></tr><tr>
     <td class="c" style="background: deeppink ">&nbsp;

     </td><td class="c" style="background: rgb(255, 20, 147) ">&nbsp;

     </td><td>deeppink

     </td><td class="c" style="background-color: #FFFFFF; ">#ff1493

     </td><td class="c" style="background-color: #FFFFFF; ">255,20,147

    </td></tr><tr>
     <td class="c" style="background: deepskyblue ">&nbsp;

     </td><td class="c" style="background: rgb(0, 191, 255) ">&nbsp;

     </td><td>deepskyblue

     </td><td class="c" style="background-color: #FFFFFF; ">#00bfff

     </td><td class="c" style="background-color: #FFFFFF; ">0,191,255

    </td></tr><tr>
     <td class="c" style="background: dimgray ">&nbsp;

     </td><td class="c" style="background: rgb(105, 105, 105) ">&nbsp;

     </td><td>dimgray

     </td><td class="c" style="background-color: #FFFFFF; ">#696969

     </td><td class="c" style="background-color: #FFFFFF; ">105,105,105

    </td></tr><tr>
     <td class="c" style="background: dimgrey ">&nbsp;

     </td><td class="c" style="background: rgb(105, 105, 105) ">&nbsp;

     </td><td>dimgrey

     </td><td class="c" style="background-color: #FFFFFF; ">#696969

     </td><td class="c" style="background-color: #FFFFFF; ">105,105,105

    </td></tr><tr>
     <td class="c" style="background: dodgerblue ">&nbsp;

     </td><td class="c" style="background: rgb(30, 144, 255) ">&nbsp;

     </td><td>dodgerblue

     </td><td class="c" style="background-color: #FFFFFF; ">#1e90ff

     </td><td class="c" style="background-color: #FFFFFF; ">30,144,255

    </td></tr><tr>
     <td class="c" style="background: firebrick ">&nbsp;

     </td><td class="c" style="background: rgb(178, 34, 34) ">&nbsp;

     </td><td>firebrick

     </td><td class="c" style="background-color: #FFFFFF; ">#b22222

     </td><td class="c" style="background-color: #FFFFFF; ">178,34,34

    </td></tr><tr>
     <td class="c" style="background: floralwhite ">&nbsp;

     </td><td class="c" style="background: rgb(255, 250, 240) ">&nbsp;

     </td><td>floralwhite

     </td><td class="c" style="background-color: #FFFFFF; ">#fffaf0

     </td><td class="c" style="background-color: #FFFFFF; ">255,250,240

    </td></tr><tr>
     <td class="c" style="background: forestgreen ">&nbsp;

     </td><td class="c" style="background: rgb(34, 139, 34) ">&nbsp;

     </td><td>forestgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#228b22

     </td><td class="c" style="background-color: #FFFFFF; ">34,139,34

    </td></tr><tr>
     <td class="c" style="background: fuchsia ">&nbsp;

     </td><td class="c" style="background: rgb(255, 0, 255) ">&nbsp;

     </td><td>fuchsia

     </td><td class="c" style="background-color: #FFFFFF; ">#ff00ff

     </td><td class="c" style="background-color: #FFFFFF; ">255,0,255

    </td></tr><tr>
     <td class="c" style="background: gainsboro ">&nbsp;

     </td><td class="c" style="background: rgb(220, 220, 220) ">&nbsp;

     </td><td>gainsboro

     </td><td class="c" style="background-color: #FFFFFF; ">#dcdcdc

     </td><td class="c" style="background-color: #FFFFFF; ">220,220,220

    </td></tr><tr>
     <td class="c" style="background: ghostwhite ">&nbsp;

     </td><td class="c" style="background: rgb(248, 248, 255) ">&nbsp;

     </td><td>ghostwhite

     </td><td class="c" style="background-color: #FFFFFF; ">#f8f8ff

     </td><td class="c" style="background-color: #FFFFFF; ">248,248,255

    </td></tr><tr>
     <td class="c" style="background: gold ">&nbsp;

     </td><td class="c" style="background: rgb(255, 215, 0) ">&nbsp;

     </td><td>gold

     </td><td class="c" style="background-color: #FFFFFF; ">#ffd700

     </td><td class="c" style="background-color: #FFFFFF; ">255,215,0

    </td></tr><tr>
     <td class="c" style="background: goldenrod ">&nbsp;

     </td><td class="c" style="background: rgb(218, 165, 32) ">&nbsp;

     </td><td>goldenrod

     </td><td class="c" style="background-color: #FFFFFF; ">#daa520

     </td><td class="c" style="background-color: #FFFFFF; ">218,165,32

    </td></tr><tr>
     <td class="c" style="background: gray ">&nbsp;

     </td><td class="c" style="background: rgb(128, 128, 128) ">&nbsp;

     </td><td>gray

     </td><td class="c" style="background-color: #FFFFFF; ">#808080

     </td><td class="c" style="background-color: #FFFFFF; ">128,128,128

    </td></tr><tr>
     <td class="c" style="background: green ">&nbsp;

     </td><td class="c" style="background: rgb(0, 128, 0) ">&nbsp;

     </td><td>green

     </td><td class="c" style="background-color: #FFFFFF; ">#008000

     </td><td class="c" style="background-color: #FFFFFF; ">0,128,0

    </td></tr><tr>
     <td class="c" style="background: greenyellow ">&nbsp;

     </td><td class="c" style="background: rgb(173, 255, 47) ">&nbsp;

     </td><td>greenyellow

     </td><td class="c" style="background-color: #FFFFFF; ">#adff2f

     </td><td class="c" style="background-color: #FFFFFF; ">173,255,47

    </td></tr><tr>
     <td class="c" style="background: grey ">&nbsp;

     </td><td class="c" style="background: rgb(128, 128, 128) ">&nbsp;

     </td><td>grey

     </td><td class="c" style="background-color: #FFFFFF; ">#808080

     </td><td class="c" style="background-color: #FFFFFF; ">128,128,128

    </td></tr><tr>
     <td class="c" style="background: honeydew ">&nbsp;

     </td><td class="c" style="background: rgb(240, 255, 240) ">&nbsp;

     </td><td>honeydew

     </td><td class="c" style="background-color: #FFFFFF; ">#f0fff0

     </td><td class="c" style="background-color: #FFFFFF; ">240,255,240

    </td></tr><tr>
     <td class="c" style="background: hotpink ">&nbsp;

     </td><td class="c" style="background: rgb(255, 105, 180) ">&nbsp;

     </td><td>hotpink

     </td><td class="c" style="background-color: #FFFFFF; ">#ff69b4

     </td><td class="c" style="background-color: #FFFFFF; ">255,105,180

    </td></tr><tr>
     <td class="c" style="background: indianred ">&nbsp;

     </td><td class="c" style="background: rgb(205, 92, 92) ">&nbsp;

     </td><td>indianred

     </td><td class="c" style="background-color: #FFFFFF; ">#cd5c5c

     </td><td class="c" style="background-color: #FFFFFF; ">205,92,92

    </td></tr><tr>
     <td class="c" style="background: indigo ">&nbsp;

     </td><td class="c" style="background: rgb(75, 0, 130) ">&nbsp;

     </td><td>indigo

     </td><td class="c" style="background-color: #FFFFFF; ">#4b0082

     </td><td class="c" style="background-color: #FFFFFF; ">75,0,130

    </td></tr><tr>
     <td class="c" style="background: ivory ">&nbsp;

     </td><td class="c" style="background: rgb(255, 255, 240) ">&nbsp;

     </td><td>ivory

     </td><td class="c" style="background-color: #FFFFFF; ">#fffff0

     </td><td class="c" style="background-color: #FFFFFF; ">255,255,240

    </td></tr><tr>
     <td class="c" style="background: khaki ">&nbsp;

     </td><td class="c" style="background: rgb(240, 230, 140) ">&nbsp;

     </td><td>khaki

     </td><td class="c" style="background-color: #FFFFFF; ">#f0e68c

     </td><td class="c" style="background-color: #FFFFFF; ">240,230,140

    </td></tr><tr>
     <td class="c" style="background: lavender ">&nbsp;

     </td><td class="c" style="background: rgb(230, 230, 250) ">&nbsp;

     </td><td>lavender

     </td><td class="c" style="background-color: #FFFFFF; ">#e6e6fa

     </td><td class="c" style="background-color: #FFFFFF; ">230,230,250

    </td></tr><tr>
     <td class="c" style="background: lavenderblush ">&nbsp;

     </td><td class="c" style="background: rgb(255, 240, 245) ">&nbsp;

     </td><td>lavenderblush

     </td><td class="c" style="background-color: #FFFFFF; ">#fff0f5

     </td><td class="c" style="background-color: #FFFFFF; ">255,240,245

    </td></tr><tr>
     <td class="c" style="background: lawngreen ">&nbsp;

     </td><td class="c" style="background: rgb(124, 252, 0) ">&nbsp;

     </td><td>lawngreen

     </td><td class="c" style="background-color: #FFFFFF; ">#7cfc00

     </td><td class="c" style="background-color: #FFFFFF; ">124,252,0

    </td></tr><tr>
     <td class="c" style="background: lemonchiffon ">&nbsp;

     </td><td class="c" style="background: rgb(255, 250, 205) ">&nbsp;

     </td><td>lemonchiffon

     </td><td class="c" style="background-color: #FFFFFF; ">#fffacd

     </td><td class="c" style="background-color: #FFFFFF; ">255,250,205

    </td></tr><tr>
     <td class="c" style="background: lightblue ">&nbsp;

     </td><td class="c" style="background: rgb(173, 216, 230) ">&nbsp;

     </td><td>lightblue

     </td><td class="c" style="background-color: #FFFFFF; ">#add8e6

     </td><td class="c" style="background-color: #FFFFFF; ">173,216,230

    </td></tr><tr>
     <td class="c" style="background: lightcoral ">&nbsp;

     </td><td class="c" style="background: rgb(240, 128, 128) ">&nbsp;

     </td><td>lightcoral

     </td><td class="c" style="background-color: #FFFFFF; ">#f08080

     </td><td class="c" style="background-color: #FFFFFF; ">240,128,128

    </td></tr><tr>
     <td class="c" style="background: lightcyan ">&nbsp;

     </td><td class="c" style="background: rgb(224, 255, 255) ">&nbsp;

     </td><td>lightcyan

     </td><td class="c" style="background-color: #FFFFFF; ">#e0ffff

     </td><td class="c" style="background-color: #FFFFFF; ">224,255,255

    </td></tr><tr>
     <td class="c" style="background: lightgoldenrodyellow ">&nbsp;

     </td><td class="c" style="background: rgb(250, 250, 210) ">&nbsp;

     </td><td>lightgoldenrodyellow

     </td><td class="c" style="background-color: #FFFFFF; ">#fafad2

     </td><td class="c" style="background-color: #FFFFFF; ">250,250,210

    </td></tr><tr>
     <td class="c" style="background: lightgray ">&nbsp;

     </td><td class="c" style="background: rgb(211, 211, 211) ">&nbsp;

     </td><td>lightgray

     </td><td class="c" style="background-color: #FFFFFF; ">#d3d3d3

     </td><td class="c" style="background-color: #FFFFFF; ">211,211,211

    </td></tr><tr>
     <td class="c" style="background: lightgreen ">&nbsp;

     </td><td class="c" style="background: rgb(144, 238, 144) ">&nbsp;

     </td><td>lightgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#90ee90

     </td><td class="c" style="background-color: #FFFFFF; ">144,238,144

    </td></tr><tr>
     <td class="c" style="background: lightgrey ">&nbsp;

     </td><td class="c" style="background: rgb(211, 211, 211) ">&nbsp;

     </td><td>lightgrey

     </td><td class="c" style="background-color: #FFFFFF; ">#d3d3d3

     </td><td class="c" style="background-color: #FFFFFF; ">211,211,211

    </td></tr><tr>
     <td class="c" style="background: lightpink ">&nbsp;

     </td><td class="c" style="background: rgb(255, 182, 193) ">&nbsp;

     </td><td>lightpink

     </td><td class="c" style="background-color: #FFFFFF; ">#ffb6c1

     </td><td class="c" style="background-color: #FFFFFF; ">255,182,193

    </td></tr><tr>
     <td class="c" style="background: lightsalmon ">&nbsp;

     </td><td class="c" style="background: rgb(255, 160, 122) ">&nbsp;

     </td><td>lightsalmon

     </td><td class="c" style="background-color: #FFFFFF; ">#ffa07a

     </td><td class="c" style="background-color: #FFFFFF; ">255,160,122

    </td></tr><tr>
     <td class="c" style="background: lightseagreen ">&nbsp;

     </td><td class="c" style="background: rgb(32, 178, 170) ">&nbsp;

     </td><td>lightseagreen

     </td><td class="c" style="background-color: #FFFFFF; ">#20b2aa

     </td><td class="c" style="background-color: #FFFFFF; ">32,178,170

    </td></tr><tr>
     <td class="c" style="background: lightskyblue ">&nbsp;

     </td><td class="c" style="background: rgb(135, 206, 250) ">&nbsp;

     </td><td>lightskyblue

     </td><td class="c" style="background-color: #FFFFFF; ">#87cefa

     </td><td class="c" style="background-color: #FFFFFF; ">135,206,250

    </td></tr><tr>
     <td class="c" style="background: lightslategray ">&nbsp;

     </td><td class="c" style="background: rgb(119, 136, 153) ">&nbsp;

     </td><td>lightslategray

     </td><td class="c" style="background-color: #FFFFFF; ">#778899

     </td><td class="c" style="background-color: #FFFFFF; ">119,136,153

    </td></tr><tr>
     <td class="c" style="background: lightslategrey ">&nbsp;

     </td><td class="c" style="background: rgb(119, 136, 153) ">&nbsp;

     </td><td>lightslategrey

     </td><td class="c" style="background-color: #FFFFFF; ">#778899

     </td><td class="c" style="background-color: #FFFFFF; ">119,136,153

    </td></tr><tr>
     <td class="c" style="background: lightsteelblue ">&nbsp;

     </td><td class="c" style="background: rgb(176, 196, 222) ">&nbsp;

     </td><td>lightsteelblue

     </td><td class="c" style="background-color: #FFFFFF; ">#b0c4de

     </td><td class="c" style="background-color: #FFFFFF; ">176,196,222

    </td></tr><tr>
     <td class="c" style="background: lightyellow ">&nbsp;

     </td><td class="c" style="background: rgb(255, 255, 224) ">&nbsp;

     </td><td>lightyellow

     </td><td class="c" style="background-color: #FFFFFF; ">#ffffe0

     </td><td class="c" style="background-color: #FFFFFF; ">255,255,224

    </td></tr><tr>
     <td class="c" style="background: lime ">&nbsp;

     </td><td class="c" style="background: rgb(0, 255, 0) ">&nbsp;

     </td><td>lime

     </td><td class="c" style="background-color: #FFFFFF; ">#00ff00

     </td><td class="c" style="background-color: #FFFFFF; ">0,255,0

    </td></tr><tr>
     <td class="c" style="background: limegreen ">&nbsp;

     </td><td class="c" style="background: rgb(50, 205, 50) ">&nbsp;

     </td><td>limegreen

     </td><td class="c" style="background-color: #FFFFFF; ">#32cd32

     </td><td class="c" style="background-color: #FFFFFF; ">50,205,50

    </td></tr><tr>
     <td class="c" style="background: linen ">&nbsp;

     </td><td class="c" style="background: rgb(250, 240, 230) ">&nbsp;

     </td><td>linen

     </td><td class="c" style="background-color: #FFFFFF; ">#faf0e6

     </td><td class="c" style="background-color: #FFFFFF; ">250,240,230

    </td></tr><tr>
     <td class="c" style="background: magenta ">&nbsp;

     </td><td class="c" style="background: rgb(255, 0, 255) ">&nbsp;

     </td><td>magenta

     </td><td class="c" style="background-color: #FFFFFF; ">#ff00ff

     </td><td class="c" style="background-color: #FFFFFF; ">255,0,255

    </td></tr><tr>
     <td class="c" style="background: maroon ">&nbsp;

     </td><td class="c" style="background: rgb(128, 0, 0) ">&nbsp;

     </td><td>maroon

     </td><td class="c" style="background-color: #FFFFFF; ">#800000

     </td><td class="c" style="background-color: #FFFFFF; ">128,0,0

    </td></tr><tr>
     <td class="c" style="background: mediumaquamarine ">&nbsp;

     </td><td class="c" style="background: rgb(102, 205, 170) ">&nbsp;

     </td><td>mediumaquamarine

     </td><td class="c" style="background-color: #FFFFFF; ">#66cdaa

     </td><td class="c" style="background-color: #FFFFFF; ">102,205,170

    </td></tr><tr>
     <td class="c" style="background: mediumblue ">&nbsp;

     </td><td class="c" style="background: rgb(0, 0, 205) ">&nbsp;

     </td><td>mediumblue

     </td><td class="c" style="background-color: #FFFFFF; ">#0000cd

     </td><td class="c" style="background-color: #FFFFFF; ">0,0,205

    </td></tr><tr>
     <td class="c" style="background: mediumorchid ">&nbsp;

     </td><td class="c" style="background: rgb(186, 85, 211) ">&nbsp;

     </td><td>mediumorchid

     </td><td class="c" style="background-color: #FFFFFF; ">#ba55d3

     </td><td class="c" style="background-color: #FFFFFF; ">186,85,211

    </td></tr><tr>
     <td class="c" style="background: mediumpurple ">&nbsp;

     </td><td class="c" style="background: rgb(147, 112, 219) ">&nbsp;

     </td><td>mediumpurple

     </td><td class="c" style="background-color: #FFFFFF; ">#9370db

     </td><td class="c" style="background-color: #FFFFFF; ">147,112,219

    </td></tr><tr>
     <td class="c" style="background: mediumseagreen ">&nbsp;

     </td><td class="c" style="background: rgb(60, 179, 113) ">&nbsp;

     </td><td>mediumseagreen

     </td><td class="c" style="background-color: #FFFFFF; ">#3cb371

     </td><td class="c" style="background-color: #FFFFFF; ">60,179,113

    </td></tr><tr>
     <td class="c" style="background: mediumslateblue ">&nbsp;

     </td><td class="c" style="background: rgb(123, 104, 238) ">&nbsp;

     </td><td>mediumslateblue

     </td><td class="c" style="background-color: #FFFFFF; ">#7b68ee

     </td><td class="c" style="background-color: #FFFFFF; ">123,104,238

    </td></tr><tr>
     <td class="c" style="background: mediumspringgreen ">&nbsp;

     </td><td class="c" style="background: rgb(0, 250, 154) ">&nbsp;

     </td><td>mediumspringgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#00fa9a

     </td><td class="c" style="background-color: #FFFFFF; ">0,250,154

    </td></tr><tr>
     <td class="c" style="background: mediumturquoise ">&nbsp;

     </td><td class="c" style="background: rgb(72, 209, 204) ">&nbsp;

     </td><td>mediumturquoise

     </td><td class="c" style="background-color: #FFFFFF; ">#48d1cc

     </td><td class="c" style="background-color: #FFFFFF; ">72,209,204

    </td></tr><tr>
     <td class="c" style="background: mediumvioletred ">&nbsp;

     </td><td class="c" style="background: rgb(199, 21, 133) ">&nbsp;

     </td><td>mediumvioletred

     </td><td class="c" style="background-color: #FFFFFF; ">#c71585

     </td><td class="c" style="background-color: #FFFFFF; ">199,21,133

    </td></tr><tr>
     <td class="c" style="background: midnightblue ">&nbsp;

     </td><td class="c" style="background: rgb(25, 25, 112) ">&nbsp;

     </td><td>midnightblue

     </td><td class="c" style="background-color: #FFFFFF; ">#191970

     </td><td class="c" style="background-color: #FFFFFF; ">25,25,112

    </td></tr><tr>
     <td class="c" style="background: mintcream ">&nbsp;

     </td><td class="c" style="background: rgb(245, 255, 250) ">&nbsp;

     </td><td>mintcream

     </td><td class="c" style="background-color: #FFFFFF; ">#f5fffa

     </td><td class="c" style="background-color: #FFFFFF; ">245,255,250

    </td></tr><tr>
     <td class="c" style="background: mistyrose ">&nbsp;

     </td><td class="c" style="background: rgb(255, 228, 225) ">&nbsp;

     </td><td>mistyrose

     </td><td class="c" style="background-color: #FFFFFF; ">#ffe4e1

     </td><td class="c" style="background-color: #FFFFFF; ">255,228,225

    </td></tr><tr>
     <td class="c" style="background: moccasin ">&nbsp;

     </td><td class="c" style="background: rgb(255, 228, 181) ">&nbsp;

     </td><td>moccasin

     </td><td class="c" style="background-color: #FFFFFF; ">#ffe4b5

     </td><td class="c" style="background-color: #FFFFFF; ">255,228,181

    </td></tr><tr>
     <td class="c" style="background: navajowhite ">&nbsp;

     </td><td class="c" style="background: rgb(255, 222, 173) ">&nbsp;

     </td><td>navajowhite

     </td><td class="c" style="background-color: #FFFFFF; ">#ffdead

     </td><td class="c" style="background-color: #FFFFFF; ">255,222,173

    </td></tr><tr>
     <td class="c" style="background: navy ">&nbsp;

     </td><td class="c" style="background: rgb(0, 0, 128) ">&nbsp;

     </td><td>navy

     </td><td class="c" style="background-color: #FFFFFF; ">#000080

     </td><td class="c" style="background-color: #FFFFFF; ">0,0,128

    </td></tr><tr>
     <td class="c" style="background: oldlace ">&nbsp;

     </td><td class="c" style="background: rgb(253, 245, 230) ">&nbsp;

     </td><td>oldlace

     </td><td class="c" style="background-color: #FFFFFF; ">#fdf5e6

     </td><td class="c" style="background-color: #FFFFFF; ">253,245,230

    </td></tr><tr>
     <td class="c" style="background: olive ">&nbsp;

     </td><td class="c" style="background: rgb(128, 128, 0) ">&nbsp;

     </td><td>olive

     </td><td class="c" style="background-color: #FFFFFF; ">#808000

     </td><td class="c" style="background-color: #FFFFFF; ">128,128,0

    </td></tr><tr>
     <td class="c" style="background: olivedrab ">&nbsp;

     </td><td class="c" style="background: rgb(107, 142, 35) ">&nbsp;

     </td><td>olivedrab

     </td><td class="c" style="background-color: #FFFFFF; ">#6b8e23

     </td><td class="c" style="background-color: #FFFFFF; ">107,142,35

    </td></tr><tr>
     <td class="c" style="background: orange ">&nbsp;

     </td><td class="c" style="background: rgb(255, 165, 0) ">&nbsp;

     </td><td>orange

     </td><td class="c" style="background-color: #FFFFFF; ">#ffa500

     </td><td class="c" style="background-color: #FFFFFF; ">255,165,0

    </td></tr><tr>
     <td class="c" style="background: orangered ">&nbsp;

     </td><td class="c" style="background: rgb(255, 69, 0) ">&nbsp;

     </td><td>orangered

     </td><td class="c" style="background-color: #FFFFFF; ">#ff4500

     </td><td class="c" style="background-color: #FFFFFF; ">255,69,0

    </td></tr><tr>
     <td class="c" style="background: orchid ">&nbsp;

     </td><td class="c" style="background: rgb(218, 112, 214) ">&nbsp;

     </td><td>orchid

     </td><td class="c" style="background-color: #FFFFFF; ">#da70d6

     </td><td class="c" style="background-color: #FFFFFF; ">218,112,214

    </td></tr><tr>
     <td class="c" style="background: palegoldenrod ">&nbsp;

     </td><td class="c" style="background: rgb(238, 232, 170) ">&nbsp;

     </td><td>palegoldenrod

     </td><td class="c" style="background-color: #FFFFFF; ">#eee8aa

     </td><td class="c" style="background-color: #FFFFFF; ">238,232,170

    </td></tr><tr>
     <td class="c" style="background: palegreen ">&nbsp;

     </td><td class="c" style="background: rgb(152, 251, 152) ">&nbsp;

     </td><td>palegreen

     </td><td class="c" style="background-color: #FFFFFF; ">#98fb98

     </td><td class="c" style="background-color: #FFFFFF; ">152,251,152

    </td></tr><tr>
     <td class="c" style="background: paleturquoise ">&nbsp;

     </td><td class="c" style="background: rgb(175, 238, 238) ">&nbsp;

     </td><td>paleturquoise

     </td><td class="c" style="background-color: #FFFFFF; ">#afeeee

     </td><td class="c" style="background-color: #FFFFFF; ">175,238,238

    </td></tr><tr>
     <td class="c" style="background: palevioletred ">&nbsp;

     </td><td class="c" style="background: rgb(219, 112, 147) ">&nbsp;

     </td><td>palevioletred

     </td><td class="c" style="background-color: #FFFFFF; ">#db7093

     </td><td class="c" style="background-color: #FFFFFF; ">219,112,147

    </td></tr><tr>
     <td class="c" style="background: papayawhip ">&nbsp;

     </td><td class="c" style="background: rgb(255, 239, 213) ">&nbsp;

     </td><td>papayawhip

     </td><td class="c" style="background-color: #FFFFFF; ">#ffefd5

     </td><td class="c" style="background-color: #FFFFFF; ">255,239,213

    </td></tr><tr>
     <td class="c" style="background: peachpuff ">&nbsp;

     </td><td class="c" style="background: rgb(255, 218, 185) ">&nbsp;

     </td><td>peachpuff

     </td><td class="c" style="background-color: #FFFFFF; ">#ffdab9

     </td><td class="c" style="background-color: #FFFFFF; ">255,218,185

    </td></tr><tr>
     <td class="c" style="background: peru ">&nbsp;

     </td><td class="c" style="background: rgb(205, 133, 63) ">&nbsp;

     </td><td>peru

     </td><td class="c" style="background-color: #FFFFFF; ">#cd853f

     </td><td class="c" style="background-color: #FFFFFF; ">205,133,63

    </td></tr><tr>
     <td class="c" style="background: pink ">&nbsp;

     </td><td class="c" style="background: rgb(255, 192, 203) ">&nbsp;

     </td><td>pink

     </td><td class="c" style="background-color: #FFFFFF; ">#ffc0cb

     </td><td class="c" style="background-color: #FFFFFF; ">255,192,203

    </td></tr><tr>
     <td class="c" style="background: plum ">&nbsp;

     </td><td class="c" style="background: rgb(221, 160, 221) ">&nbsp;

     </td><td>plum

     </td><td class="c" style="background-color: #FFFFFF; ">#dda0dd

     </td><td class="c" style="background-color: #FFFFFF; ">221,160,221

    </td></tr><tr>
     <td class="c" style="background: powderblue ">&nbsp;

     </td><td class="c" style="background: rgb(176, 224, 230) ">&nbsp;

     </td><td>powderblue

     </td><td class="c" style="background-color: #FFFFFF; ">#b0e0e6

     </td><td class="c" style="background-color: #FFFFFF; ">176,224,230

    </td></tr><tr>
     <td class="c" style="background: purple ">&nbsp;

     </td><td class="c" style="background: rgb(128, 0, 128) ">&nbsp;

     </td><td>purple

     </td><td class="c" style="background-color: #FFFFFF; ">#800080

     </td><td class="c" style="background-color: #FFFFFF; ">128,0,128

    </td></tr><tr>
     <td class="c" style="background: red ">&nbsp;

     </td><td class="c" style="background: rgb(255, 0, 0) ">&nbsp;

     </td><td>red

     </td><td class="c" style="background-color: #FFFFFF; ">#ff0000

     </td><td class="c" style="background-color: #FFFFFF; ">255,0,0

    </td></tr><tr>
     <td class="c" style="background: rosybrown ">&nbsp;

     </td><td class="c" style="background: rgb(188, 143, 143) ">&nbsp;

     </td><td>rosybrown

     </td><td class="c" style="background-color: #FFFFFF; ">#bc8f8f

     </td><td class="c" style="background-color: #FFFFFF; ">188,143,143

    </td></tr><tr>
     <td class="c" style="background: royalblue ">&nbsp;

     </td><td class="c" style="background: rgb(65, 105, 225) ">&nbsp;

     </td><td>royalblue

     </td><td class="c" style="background-color: #FFFFFF; ">#4169e1

     </td><td class="c" style="background-color: #FFFFFF; ">65,105,225

    </td></tr><tr>
     <td class="c" style="background: saddlebrown ">&nbsp;

     </td><td class="c" style="background: rgb(139, 69, 19) ">&nbsp;

     </td><td>saddlebrown

     </td><td class="c" style="background-color: #FFFFFF; ">#8b4513

     </td><td class="c" style="background-color: #FFFFFF; ">139,69,19

    </td></tr><tr>
     <td class="c" style="background: salmon ">&nbsp;

     </td><td class="c" style="background: rgb(250, 128, 114) ">&nbsp;

     </td><td>salmon

     </td><td class="c" style="background-color: #FFFFFF; ">#fa8072

     </td><td class="c" style="background-color: #FFFFFF; ">250,128,114

    </td></tr><tr>
     <td class="c" style="background: sandybrown ">&nbsp;

     </td><td class="c" style="background: rgb(244, 164, 96) ">&nbsp;

     </td><td>sandybrown

     </td><td class="c" style="background-color: #FFFFFF; ">#f4a460

     </td><td class="c" style="background-color: #FFFFFF; ">244,164,96

    </td></tr><tr>
     <td class="c" style="background: seagreen ">&nbsp;

     </td><td class="c" style="background: rgb(46, 139, 87) ">&nbsp;

     </td><td>seagreen

     </td><td class="c" style="background-color: #FFFFFF; ">#2e8b57

     </td><td class="c" style="background-color: #FFFFFF; ">46,139,87

    </td></tr><tr>
     <td class="c" style="background: seashell ">&nbsp;

     </td><td class="c" style="background: rgb(255, 245, 238) ">&nbsp;

     </td><td>seashell

     </td><td class="c" style="background-color: #FFFFFF; ">#fff5ee

     </td><td class="c" style="background-color: #FFFFFF; ">255,245,238

    </td></tr><tr>
     <td class="c" style="background: sienna ">&nbsp;

     </td><td class="c" style="background: rgb(160, 82, 45) ">&nbsp;

     </td><td>sienna

     </td><td class="c" style="background-color: #FFFFFF; ">#a0522d

     </td><td class="c" style="background-color: #FFFFFF; ">160,82,45

    </td></tr><tr>
     <td class="c" style="background: silver; ">&nbsp;

     </td><td class="c" style="background: rgb(192, 192, 192) ">&nbsp;

     </td><td>silver

     </td><td class="c" style="background-color: #FFFFFF; ">#c0c0c0

     </td><td class="c" style="background-color: #FFFFFF; ">192,192,192

    </td></tr><tr>
     <td class="c" style="background: skyblue ">&nbsp;

     </td><td class="c" style="background: rgb(135, 206, 235) ">&nbsp;

     </td><td>skyblue

     </td><td class="c" style="background-color: #FFFFFF; ">#87ceeb

     </td><td class="c" style="background-color: #FFFFFF; ">135,206,235

    </td></tr><tr>
     <td class="c" style="background: slateblue ">&nbsp;

     </td><td class="c" style="background: rgb(106, 90, 205) ">&nbsp;

     </td><td>slateblue

     </td><td class="c" style="background-color: #FFFFFF; ">#6a5acd

     </td><td class="c" style="background-color: #FFFFFF; ">106,90,205

    </td></tr><tr>
     <td class="c" style="background: slategray ">&nbsp;

     </td><td class="c" style="background: rgb(112, 128, 144) ">&nbsp;

     </td><td>slategray

     </td><td class="c" style="background-color: #FFFFFF; ">#708090

     </td><td class="c" style="background-color: #FFFFFF; ">112,128,144

    </td></tr><tr>
     <td class="c" style="background: slategrey ">&nbsp;

     </td><td class="c" style="background: rgb(112, 128, 144) ">&nbsp;

     </td><td>slategrey

     </td><td class="c" style="background-color: #FFFFFF; ">#708090

     </td><td class="c" style="background-color: #FFFFFF; ">112,128,144

    </td></tr><tr>
     <td class="c" style="background: snow ">&nbsp;

     </td><td class="c" style="background: rgb(255, 250, 250) ">&nbsp;

     </td><td>snow

     </td><td class="c" style="background-color: #FFFFFF; ">#fffafa

     </td><td class="c" style="background-color: #FFFFFF; ">255,250,250

    </td></tr><tr>
     <td class="c" style="background: springgreen ">&nbsp;

     </td><td class="c" style="background: rgb(0, 255, 127) ">&nbsp;

     </td><td>springgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#00ff7f

     </td><td class="c" style="background-color: #FFFFFF; ">0,255,127

    </td></tr><tr>
     <td class="c" style="background: steelblue ">&nbsp;

     </td><td class="c" style="background: rgb(70, 130, 180) ">&nbsp;

     </td><td>steelblue

     </td><td class="c" style="background-color: #FFFFFF; ">#4682b4

     </td><td class="c" style="background-color: #FFFFFF; ">70,130,180

    </td></tr><tr>
     <td class="c" style="background: tan ">&nbsp;

     </td><td class="c" style="background: rgb(210, 180, 140) ">&nbsp;

     </td><td>tan

     </td><td class="c" style="background-color: #FFFFFF; ">#d2b48c

     </td><td class="c" style="background-color: #FFFFFF; ">210,180,140

    </td></tr><tr>
     <td class="c" style="background: teal ">&nbsp;

     </td><td class="c" style="background: rgb(0, 128, 128) ">&nbsp;

     </td><td>teal

     </td><td class="c" style="background-color: #FFFFFF; ">#008080

     </td><td class="c" style="background-color: #FFFFFF; ">0,128,128

    </td></tr><tr>
     <td class="c" style="background: thistle ">&nbsp;

     </td><td class="c" style="background: rgb(216, 191, 216) ">&nbsp;

     </td><td>thistle

     </td><td class="c" style="background-color: #FFFFFF; ">#d8bfd8

     </td><td class="c" style="background-color: #FFFFFF; ">216,191,216

    </td></tr><tr>
     <td class="c" style="background: tomato ">&nbsp;

     </td><td class="c" style="background: rgb(255, 99, 71) ">&nbsp;

     </td><td>tomato

     </td><td class="c" style="background-color: #FFFFFF; ">#ff6347

     </td><td class="c" style="background-color: #FFFFFF; ">255,99,71

    </td></tr><tr>
     <td class="c" style="background: turquoise ">&nbsp;

     </td><td class="c" style="background: rgb(64, 224, 208) ">&nbsp;

     </td><td>turquoise

     </td><td class="c" style="background-color: #FFFFFF; ">#40e0d0

     </td><td class="c" style="background-color: #FFFFFF; ">64,224,208

    </td></tr><tr>
     <td class="c" style="background: violet ">&nbsp;

     </td><td class="c" style="background: rgb(238, 130, 238) ">&nbsp;

     </td><td>violet

     </td><td class="c" style="background-color: #FFFFFF; ">#ee82ee

     </td><td class="c" style="background-color: #FFFFFF; ">238,130,238

    </td></tr><tr>
     <td class="c" style="background: wheat ">&nbsp;

     </td><td class="c" style="background: rgb(245, 222, 179) ">&nbsp;

     </td><td>wheat

     </td><td class="c" style="background-color: #FFFFFF; ">#f5deb3

     </td><td class="c" style="background-color: #FFFFFF; ">245,222,179

    </td></tr><tr>
     <td class="c" style="background: white ">&nbsp;

     </td><td class="c" style="background: rgb(255, 255, 255) ">&nbsp;

     </td><td>white

     </td><td class="c" style="background-color: #FFFFFF; ">#ffffff

     </td><td class="c" style="background-color: #FFFFFF; ">255,255,255

    </td></tr><tr>
     <td class="c" style="background: whitesmoke ">&nbsp;

     </td><td class="c" style="background: rgb(245, 245, 245) ">&nbsp;

     </td><td>whitesmoke

     </td><td class="c" style="background-color: #FFFFFF; ">#f5f5f5

     </td><td class="c" style="background-color: #FFFFFF; ">245,245,245

    </td></tr><tr>
     <td class="c" style="background: yellow ">&nbsp;

     </td><td class="c" style="background: rgb(255, 255, 0) ">&nbsp;

     </td><td>yellow

     </td><td class="c" style="background-color: #FFFFFF; ">#ffff00

     </td><td class="c" style="background-color: #FFFFFF; ">255,255,0

    </td></tr><tr>
     <td class="c" style="background: yellowgreen ">&nbsp;

     </td><td class="c" style="background: rgb(154, 205, 50) ">&nbsp;

     </td><td>yellowgreen

     </td><td class="c" style="background-color: #FFFFFF; ">#9acd32

     </td><td class="c" style="background-color: #FFFFFF; ">154,205,50
  </td></tr></tbody></table>


';


//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");


$mpdf=new mPDF('c','A4','','',15,15,20,20,5,5); 

//==============================================================

$mpdf->pagenumPrefix = 'Page ';
$mpdf->pagenumSuffix = '';
$mpdf->nbpgPrefix = ' of ';
$mpdf->nbpgSuffix = ' pages.';
$header = array(
	'L' => array(
	),
	'C' => array(
	),
	'R' => array(
		'content' => '{PAGENO}{nbpg}',
		'font-family' => 'sans',
		'font-style' => '',
		'font-size' => '9',	/* gives default */
	),
	'line' => 1,
);
$footer = '
<table width="100%" style="border-top: 0.1mm solid #000000; vertical-align: top; font-family: sans; font-size: 9pt; color: #000055;"><tr>
<td width="50%"></td>
<td width="50%" align="right">See <a href="http://mpdf1.com/manual/index.php">documentation manual</a> for further details</td>
</tr></table>
';

$mpdf->SetHeader($header,'O');
$mpdf->SetHTMLFooter($footer);
//==============================================================

$mpdf->SetDisplayMode('fullpage');

$mpdf->WriteHTML($html);

$mpdf->Output(); 
exit;

//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>