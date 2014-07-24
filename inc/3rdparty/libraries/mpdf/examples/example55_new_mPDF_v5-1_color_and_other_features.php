<?php


include("../mpdf.php");

$mpdf=new mPDF(''); 

$mpdf->useKerning=true;

$mpdf->restrictColorSpace=3; 	// forces everything to convert to CMYK colors

$mpdf->AddSpotColor('PANTONE 534 EC',85,65,47,9);

//==============================================================
$html = '
<style>
@page {
		sheet-size: A4;
		size: 17cm 25cm;
		margin: 10%;	/* % of page-box width for LR, height for TB */
		margin-header: 5mm;
		margin-footer: 5mm;
		margin-left: 2cm;
		margin-right: 1cm;
		marks: cross crop;
		background-image:  -moz-repeating-radial-gradient(rgba(255,0,0,0.1), rgba(0,0,255,0.1) 40px, rgba(255,0,0,0.1) 80px);
}
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
<body>

<div style="position:fixed; top: 0; right: 0"><img src="tux.svg" width="110" /></div>

<h1></a>mPDF</h1>
<h2>Other new features in mPDF Version 5.1</h2>

<div class="rounded text">
<ul>
<li>Kerning</li>
<li>Letter- and word-spacing</li>
<li>Small-caps improved to work with justified text, and now with kerning, letter- and word-spacing</li>
<li>Bleed area on @page media</li>
<li>Colorspace and colour conversion (almost everything except BMP images)</li>
<li>Spot colours</li>
<li>PDF/X files</li>
<li>dir="rtl"</li>
<li>numeric list-styles for arabic and indic</li>
</ul>
</div>


<!-- ============================================================== -->
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Kerning</div>

<div class="rounded text">Font kerning is supported. This corrects the inter-character spacing between specific pairs of letters. It is dependent on kerning information being available in the original font file.
<br />
You need to set $mpdf-&gt;useKerning=true; either in the config.php configuration file, or at runtime. This causes the kerning information to be loaded when fonts are accessed (and will therefore increase memory usage).
<br />
You can then set kerning on or off using the draft CSS3 style property "font-kerning". Values of normal or auto will turn kerning on; "none" will turn kerning off.
</div>

<div style="border: 0.2mm solid black; font-family: arial; font-size: 40pt;">
Off: AWAY To War.
</div>

<div style="border: 0.2mm solid black; font-family: arial; font-size: 40pt; font-kerning: auto;">
On: AWAY To War.
</div>

<!-- ============================================================== -->
<pagebreak />
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Letter and word spacing & text justification</div>

<div class="rounded text">Letter- and word-spacing can be set on almost all block and in-line style elements, using the CSS properties letter-spacing and word-spacing. Values of normal or a length can be specified (em or ex recommended).
Note that setting the letter-spacing value (including setting it to zero) will prevent any additional letter-spacing to be added when full-justifying text. The word-spacing value, however, is a <i>minimum</i> value, and can be increased in order to justify text.
<br />
<br />
Text-align: justify - no longer uses configurable variable $jSpacing= C | W | \'\'
<br />
The default value is for mixed letter- and word-spacing, set by jSWord and jSmaxChar
<br />
If a line contains a cursive script (RTL or Indic [devanagari, punjabi, bengali]) then it prevents letter-spacing
for justification on that line - effectively the same as setting letter-spacing:0
<br />
Spacing values have been removed from the config_cp.php configuration file, so the "lang" property 
(in config_cp) no longer determines justification behaviour (this includes the use of Autofont()).
<br />
When using RTL or Indic [devanagari, punjabi, bengali] scripts, you should set CSS letter-spacing:0
whenever you use text-align:justify.
</div>

<p style="border: 0.2mm solid black; padding: 0.3em;">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. <span style="letter-spacing: 0.2em; color: red;">Letter spacing set at 0.2em. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci.</span> Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. <span style="word-spacing: 1em; color: teal;">Word spacing set at 1em. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</span> Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>


<!-- ============================================================== -->
<pagebreak />
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Colours</div>

<div class="rounded text">Whenever a colour can be specified in a style, additional formats are now supported: rgb(), rgba(), hsl(), hsla(), cmyk(), cmyka(), or spot().
<br />
Spot colours need to be defined at the start of the script using e.g. $mpdf-&gt;AddSpotColor(\'PANTONE 534 EC\',85,65,47,9);
<br />
The four values define the CMYK values used when the spot colour is not available. A tint % can be specified when using the spot colour in the document.
</div>

<div style="border: 0.2mm solid black; background-color: rgba(150,150,255, 0.5); color: rgb(0,150,150);">background-color: rgba(150,150,255, 0.5); color: rgb(0,150,150);</div>
<div style="border: 0.2mm solid black; background-color: rgba(60%,60%,100%, 0.5); color: rgb(0,60%,60%);">background-color: rgba(60%,60%,100%, 0.5); color: rgb(0,60%,60%);</div>
<div style="border: 0.2mm solid black; background-color: hsla(180,30%,25%, 0.5); color: hsl(360,100%,50%);">background-color: hsla(180,30%,25%, 0.5); color: hsl(360,100%,50%);</div>
<div style="border: 0.2mm solid black; background-color: cmyka(0,100,0,30, 0.3); color: spot(PANTONE 534 EC,90%);">background-color: cmyka(85,65,0,30, 0.3); color: spot(PANTONE 300 EC,80%);</div>
<br />
<br />

<!-- ============================================================== -->
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">@page media</div>

<div class="rounded text">When using @page to create a print publication with page-size less than sheet-size, the bleed margin is now configurable.
Backgrounds/gradients/images now use the bleed box as their "container box", rather than the whole page. (See this document as an example.)
<br />
Crop- and cross-marks can now both be used together, and are more configurable.
Also, background-image-opacity and background-image-resize have been extended to work with @page CSS.
<br />
The following values can be set in the configuration file, config.php:
$this-&gt;bleedMargin<br />
$this-&gt;crossMarkMargin<br />
$this-&gt;cropMarkMargin<br />
$this-&gt;cropMarkLength<br />
$this-&gt;nonPrintMargin<br />
</div>

<!-- ============================================================== -->
<pagebreak />
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">Colorspace and colour conversion </div>

<div class="rounded text">PDF files can contain objects using different colorSpaces e.g. Grayscale, RGB and CMYK. By default,
mPDF creates PDF files using the colours as they are specified: font colour may be set (e.g. #880000) as an RGB colour, and the 
file may contain JPG images in RGB or CMYK format.
<br />
In some circumstances, you may wish to create a PDF file with restricted colorSpaces e.g. printers will often want files 
which contain only CMYK, spot colours, or grayscale, but <i>not</i> RGB.
<br />
Additional methods for defining colours can be used (see above), but alternatively you can set mPDF to restrict the colorSpace by setting 
the value for $mpdf-&gt;restrictColorSpace:
<br />
1 - allow GRAYSCALE only [converts CMYK/RGB->gray]
<br />
2 - allow RGB / SPOT COLORS / Grayscale [converts CMYK->RGB]
<br />
3 - allow CMYK / SPOT COLORS / Grayscale [converts RGB->CMYK]
<br />
This will attempt to convert every colour value used in the document to the permitted colorSpace(s). Almost everything including images
will be converted (except BMP images), and the conversion of images may take significant time.
<br />
This example file is set to (3) CMYK; compare the appearance of the Tux penguin in this file and in the previous example file (RGB).
</div>
<br />
<br />

<!-- ============================================================== -->
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">PDF/A and PDF/X files</div>

<div class="rounded text">mPDF can produce files which (attempt to) meet the PDF/A and PDF/X specifications. In addition to restricted colorSpace,
PDF/A and /X files cannot contain images or colour values with "transparency".
<br />
Please note that full compliance with the PDF/A or /X specification is not guaranteed.
</div>

<!-- ============================================================== -->
<pagebreak />
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">RTL (right-to-left) text</div>

<div class="rounded text"><div>Handling of RTL (right-to-left) languages has been significantly rewritten, and is likely to cause 
changes to the resulting files if you have previously been using mPDF. The changes have made mPDF
act more like a browser, respecting the HTML/CSS rules.
Changes include:</div>
<ul>
<li>the document now has a baseline direction; this determines the 
	<ul>
	<li>behaviour of blocks for which text-align has not been specifically set</li>
	<li>layout of mirrored page-margins, columns, ToC and Indexes, headers / footers</li>
	<li>base direction can be set by any of:
		<ul>
		<li>$mpdf-&gt;SetDirectionality(\'rtl\');</li>
		<li>&lt;html dir="rtl" or style="direction: rtl;"&gt;</li>
		<li>&lt;body dir="rtl" or style="direction: rtl;"&gt;</li>
		</ul></li>
	<li>base direction is an inherited CSS property, so will affect all content, unless...</li>
	</ul></li>
<li>direction can be set for all HTML block elements e.g. &lt;DIV&gt;&lt;P&gt;&lt;TABLE&gt;&lt;UL&gt; etc using
	<ul>
	<li>CSS property &lt;style="direction: rtl;"&gt; </li>
	<li>direction can only be set on the top-level element of nested lists</li>
	<li>direction can only be set on &lt;TABLE&gt;, NOT on THEAD, TBODY, TD etc.</li>
	<li>nested tables CAN have different directions</li>
	</ul></li>
<li>NOTE that block/table margins/paddings are NOT reversed by direction</li>
<li>language (either CSS "lang", using Autofont, or through initial set-up e.g. $mpdf = new mPDF(\'ar\') )
	no longer affects direction in any way.<br />
	NB config_cp.php has been changed as a result; any values of "dir" set here are now ineffective</li>
<li>default text-align is now as per CSS spec: "a nameless value which is dependent on direction"<br /> 
	NB default text-align removed in default stylesheet in config.php </li>
<li>once text-align is specified, it is respected and inherited<br />
	NB mPDF &lt;5.1 reversed the text-align property for all blocks when RTL set.</li>
<li>the configurable value $rtlcss is depracated, as it is no longer required</li>
<li>improved algorithm for dtermining text direction
	<ul>
	<li>english word blocks are handled in text reversal as one block i.e. dir="rtl"<br />
	[arabic text] this will not be reversed [arabic text]</li>
	<li>arabic numerals 0-9 handled correctly</li>
	</ul></li>
</ul>
Although the control of direction for block elements is now more configurable, the control of 
text direction (RTL arabic characters) remains fully automatic and unconfigurable. 
&lt;BDO&gt; etc has no effect. Enclosing text in silent tags can sometimes help e.g.:
	content&lt;span&gt;[arabic text]&lt;/span&gt;content
</div>


<!-- ============================================================== -->
<pagebreak />
<div style="font-family: Arial; font-size: 18pt; color: rgb(49,124,209)">List styles</div>
<div class="rounded text">Additional numerical list-styles are supported. All of these (except Tamil) are consistent with the draft CSS3 specification:<br />
<b>list-style</b>: arabic-indic | bengali | devanagari | gujarati | gurmukhi | kannada | malayalam | oriya | persian | telugu | thai | urdu | tamil

</div>

<style>
ul.arabic { font-family:\'XB Zar\'; text-align: right; direction: rtl; }
ol.arabic { font-family:\'XB Zar\'; list-style: arabic-indic; text-align: right; direction: rtl; }
ol.persian { font-family:\'XB Zar\'; list-style: persian; text-align: right; direction: rtl; }
ol.urdu { font-family:\'XB Zar\'; list-style: urdu; text-align: right; direction: rtl; }
ol.bengali { font-family: ind_bn_1_001; list-style: bengali; }
ol.devanagari { font-family: ind_hi_1_001; list-style: devanagari; }
ol.gujarati { font-family: ind_gu_1_001; list-style: gujarati; }
ol.gurmukhi { font-family: ind_pa_1_001; list-style: gurmukhi; }
ol.kannada { font-family: ind_kn_1_001; list-style: kannada; }
ol.malayalam { font-family: ind_ml_1_001; list-style: malayalam ; }
ol.oriya { font-family: ind_or_1_001; list-style: oriya ; }
ol.tamil { font-family: ind_ta_1_001; list-style: tamil ; }
ol.telugu { font-family: ind_te_1_001; list-style: telugu ; }
</style>

<ul class="arabic">


<li>Arabic
<ol class="arabic">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
<li>Six</li>
</ol>
</li>
<li>Persian
<ol class="persian">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
<li>Six</li>
</ol>
</li>
<li>Urdu
<ol class="urdu">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
<li>Six</li>
</ol>
</li>

</ul>

<ul>

<li>Bengali
<ol class="bengali">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Devanagari
<ol class="devanagari">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Gujarati
<ol class="gujarati">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Gurmukhi
<ol class="gurmukhi">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Kannada
<ol class="kannada">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Malayalam
<ol class="malayalam">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Oriya
<ol class="oriya">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Tamil
<ol class="tamil">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>
<li>Telugu
<ol class="telugu">
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
</li>

</ul>

';

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