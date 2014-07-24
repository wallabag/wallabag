<?php

$html = '
<style>
body { 	font-family: sans; }
h4, p {	margin: 0pt;
}
h5 { margin-bottom: 0; }
table.items {
	font-size: 9pt; 
	border-collapse: collapse;
	border: 3px solid #880000; 
	background-color: #FFFFFF;
}
td { vertical-align: top; 
}
table thead td { background-color: #EEEEEE;
	text-align: center;
}
table tfoot td { background-color: #AAFFEE;
	text-align: center;
}
.barcode {
	padding: 1.5mm;
	margin: 0;
	vertical-align: top;
	color: #000000;
}
.barcodecell {
	text-align: center;
	vertical-align: middle;
	padding: 0;
}

@page {
	background-gradient: linear #00FFFF #FFFF00 0 0.5 1 0.5;
	odd-header-name: html_myHTMLHeaderOdd;
	even-header-name: html_myHTMLHeaderEven;
	odd-footer-name: html_myHTMLFooterOdd;
	even-footer-name: html_myHTMLFooterEven;
}
#myfixed { 
	position: fixed; 
	overflow: auto; 
	height: 60mm;
	margin-left: auto; 
	right: 30mm; 
	top: 150mm; 
	border: 1px solid #880000; 
	background-color: #EEDDFF; 
	padding: 3em; 
	text-align: justify; 
	text-indent: 3em; 
	font-size: 10pt; 
	font-family:sans; 
	font-style: italic; 
	line-height: 1.8; 
	color: red;
}
.myfixed2 { position: absolute; 
	overflow: visible; 
	left: 0;
	right: 0;
	width: 100mm; 
	top: 40mm; 
	margin-left: auto; 
	margin-right: auto; 
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background: transparent url(\'bg.jpg\') repeat scroll right top; 
	padding: 1.5em; 
	font-family:sans; 
}
.myfixed3 { position: absolute; 
	overflow: visible; 
	right: 0; 
	bottom: 0; 
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 1.5em; 
	font-family:sans; 
}
.myfixed4 { position: absolute; 
	overflow: auto; 
	left: 150mm;
	right: 0;
	top: 100mm; 
	height: 10mm; 
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.5em; 
	font-family:sans; 
}
.myfixed5 { position: absolute; 
	overflow: visible; 
	left: 150mm;
	right: 0;
	top: 125mm; 
	height: 10mm; 
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.5em; 
	font-family:sans; 
}
.myfixed6 { position: absolute; 
	overflow: hidden; 
	right: 150mm;
	left: 0;
	top: 110mm; 
	height: 10mm; 
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.5em; 
	font-family:sans; 
}
.myfixed7 { position: absolute; 
	right: 140mm;
	top: 130mm; 
	width: auto;
	border: 1px solid #000088; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.5em; 
	font-family:sans; 
}
div.indic, div.arabic {
	font-size: 14pt;
}
div.indic h5, div.arabic h5  {
	margin: 0;
}
div.indic p, div.arabic p, div.arabic li {
	margin: 0;
	margin-botom: 1em;
	line-height: 1.8;
}
div.arabic p, div.arabic h5, div.arabic h4, div.arabic td   {
	text-align: right;
}
div.arabic td   {
	text-align: right;
	font-size: 14pt;
	padding: 1em;
}
h2 { color: #880000; margin-bottom: 0.2em; }
h4 { margin-bottom: 0.2em; }
</style>
<htmlpageheader name="myHTMLHeaderOdd" style="display:none">
<div style="background-color:#BBEEFF" align="center"><b>Page {PAGENO} of {nb}</b></div>
</htmlpageheader>
<htmlpageheader name="myHTMLHeaderEven" style="display:none">
<div style="background-color:#EFFBBE" align="center"><b><i>{PAGENO}</i></b></div>
</htmlpageheader>
<htmlpagefooter name="myHTMLFooterOdd" style="display:none">
<div style="background-color:#CFFFFC" align="center"><b>{PAGENO}</b></div>
</htmlpagefooter>
<htmlpagefooter name="myHTMLFooterEven" style="display:none">
<div style="background-color:#FFCCFF" align="center"><b><i>{PAGENO}</i></b></div>
</htmlpagefooter>


<h1>mPDF Version 4.0 New features</h1>
<ul><li>Ability to embed font subsets (much smaller files)</li>
<li>Support for Fixed position block elements</li>
<li>Support for Indic languages including consonant conjuncts</li>
<li>Much improved support for Arabic languages</li>
<li>New utility to help create your own fonts</li>
<li>Increased support for barcodes</li>
</ul>

<h2>Indic Fonts/Languages</h2>
<h5>From BBC World Service Hindi News (http://www.bbc.co.uk/hindi/)</h5>
<div class="indic">
<h4 lang="hi">&#x915;&#x941;&#x91b; &#x914;&#x930; &#x924;&#x925;&#x94d;&#x92f; &#x92a;&#x949;&#x92a;</h4>
<p lang="hi">&#x91c;&#x948;&#x915;&#x94d;&#x938;&#x928; &#x92e;&#x93e;&#x92e;&#x932;&#x947; &#x938;&#x947; &#x91c;&#x941;&#x921;&#x93c;&#x947; &#x928;&#x90f; &#x924;&#x925;&#x94d;&#x92f; &#x938;&#x93e;&#x92e;&#x928;&#x947; &#x906;&#x90f;.</p>
</div>


<h5>From BBC World Service Tamil News (http://www.bbc.co.uk/tamil/)</h5>
<div class="indic">
<h4 lang="ta">&#xb9a;&#xbc6;&#xbaf;&#xbcd;&#xba4;&#xbbf;&#xbaf;&#xbb0;&#xb99;&#xbcd;&#xb95;&#xbae;&#xbcd;</h4>
<p lang="ta">&#xb87;&#xbb2;&#xb99;&#xbcd;&#xb95;&#xbc8;&#xbaf;&#xbbf;&#xbb2;&#xbcd; &#xb9a;&#xbbf;&#xbb1;&#xbc1;&#xbaa;&#xbbe;&#xba9;&#xbcd;&#xbae;&#xbc8;&#xb95;&#xbcd; &#xb95;&#xb9f;&#xbcd;&#xb9a;&#xbbf;&#xb95;&#xbb3;&#xbc1;&#xb95;&#xbcd;&#xb95;&#xbbf;&#xb9f;&#xbc8;&#xbaf;&#xbbf;&#xbb2;&#xbcd; &#xbaa;&#xbca;&#xba4;&#xbc1; &#xb87;&#xba3;&#xb95;&#xbcd;&#xb95;&#xbaa;&#xbcd;&#xbaa;&#xbbe;&#xb9f;&#xbcd;&#xb9f;&#xbc8; &#xb8e;&#xb9f;&#xbcd;&#xb9f;&#xbc1;&#xbae;&#xbcd; &#xbae;&#xbc1;&#xbaf;&#xbb1;&#xbcd;&#xb9a;&#xbbf;&#xbaf;&#xbbf;&#xbb2;&#xbcd; ...</p>
</div>

<h5>From Yahoo Indian- Malayalam (http://in.malayalam.yahoo.com/)</h5>
<div class="indic">
<h4 lang="ml">&#xd2a;&#xd34;&#xd36;&#xd4d;&#xd36;&#xd3f;&#xd30;&#xd3e;&#xd1c; &#xd2e;&#xd46;&#xd17;&#xd3e;&#xd39;&#xd3f;&#xd31;&#xd4d;&#xd31;&#xd4d;; &#x2018;&#xd38;&#xd4d;&#xd35;.&#xd32;&#xd47;&#x2019; &#xd2e;&#xd41;&#xd28;&#xd4d;&#xd28;&#xd47;&#xd31;&#xd41;&#xd28;&#xd4d;&#xd28;&#xd41;</h4>
<p lang="ml">&#xd2a;&#xd34;&#xd36;&#xd4d;&#xd36;&#xd3f;&#xd30;&#xd3e;&#xd1c; &#xd2e;&#xd32;&#xd2f;&#xd3e;&#xd33; &#xd38;&#xd3f;&#xd28;&#xd3f;&#xd2e;&#xd2f;&#xd41;&#xd1f;&#xd46; &#xd1a;&#xd30;&#xd3f;&#xd24;&#xd4d;&#xd30;&#xd24;&#xd4d;&#xd24;&#xd3f;&#xd32;&#xd46; &#xd0f;&#xd31;&#xd4d;&#xd31;&#xd35;&#xd41;&#xd02; &#xd35;&#xd32;&#xd3f;&#xd2f; &#xd35;&#xd3f;&#xd1c;&#xd2f;&#xd2e;&#xd3e;&#xd15;&#xd41;&#xd15;&#xd2f;&#xd3e;&#xd23;&#xd4d;. 30 &#xd26;&#xd3f;&#xd35;&#xd38;&#xd19;&#xd4d;&#xd19;&#xd33;&#xd4d;&#x200d; &#xd2a;&#xd3f;&#xd28;&#xd4d;&#xd28;&#xd3f;&#xd1f;&#xd4d;&#xd1f;&#xd2a;&#xd4d;&#xd2a;&#xd4b;&#xd33;&#xd4d;&#x200d; &#xd1a;&#xd3f;&#xd24;&#xd4d;&#xd30;&#xd24;&#xd4d;&#xd24;&#xd3f;&#xd28;&#xd4d;&#x200d;&#xd31;&#xd46; &#xd15;&#xd33;&#xd15;&#xd4d;&#xd37;&#xd28;&#xd4d;&#x200d; 12 &#xd15;&#xd4b;&#xd1f;&#xd3f;...</p>
</div>

<h5>From Yahoo Indian- Punjabi (http://in.punjabi.yahoo.com/)</h5>
<div class="indic">
<h4 lang="pa">&#xa1a;&#xa3e;&#xa02;&#xa38; &#xa2a;&#xa47; &#xa21;&#xa3e;&#xa02;&#xa38; &#xa36;&#xa3e;&#xa39;&#xa3f;&#xa26; &#xa26;&#xa40; &#xa15;&#xa39;&#xa3e;&#xa23;&#xa40;</h4>
<p lang="pa">&#xa15;&#xa47;&#xa28; &#xa18;&#xa4b;&#xa36; &#xa26;&#xa40; &#xa5e;&#xa3f;&#xa32;&#xa2e; &#xa1a;&#xa3e;&#xa02;&#xa38; &#xa2a;&#xa47; &#xa21;&#xa3e;&#xa02;&#xa38; &#xa5e;&#xa3f;&#xa32;&#xa2e; &#xa35;&#xa3f;&#xa71;&#xa1a; &#xa36;&#xa3e;&#xa39;&#xa3f;&#xa26; &#xa15;&#xa2a;&#xa42;&#xa30; &#xa2e;&#xa41;&#xa71;&#xa16; &#xa2d;&#xa42;&#xa2e;&#xa3f;&#xa15;&#xa3e; &#xa35;&#xa3f;&#xa71;&#xa1a; &#xa39;&#xa28;&#x964; &#xa5e;&#xa3f;&#xa32;&#xa2e; &#xa26;&#xa47; &#xa2c;&#xa3e;&#xa30;&#xa47; &#xa35;&#xa3f;&#xa71;&#xa1a; &#xa15;&#xa3f;&#xa39;&#xa3e; &#xa1c;&#xa3e; &#xa38;&#xa15;&#xa26;&#xa3e; &#xa39;&#xa48; &#xa15;&#xa3f; &#xa07;&#xa39; &#xa06;&#xa2a; &#xa36;&#xa3e;&#xa39;&#xa3f;&#xa26; &#xa26;&#xa40; &#xa15;&#xa39;&#xa3e;&#xa23;&#xa40; &#xa39;&#xa48;&#x964; &#xa5e;&#xa3f;&#xa32;&#xa2e; &#xa26;&#xa3e; &#xa28;&#xa3e;&#xa07;&#xa15; &#xa2e;&#xa71;&#xa27;&#xa2e; &#xa2a;&#xa30;&#xa3f;&#xa35;&#xa3e;&#xa30; &#xa26;&#xa3e; &#xa26;&#xa71;&#xa38;&#xa3f;&#xa06; &#xa17;&#xa3f;&#xa06; &#xa39;&#xa48; &#xa05;&#xa24;&#xa47; &#xa15;&#xa08;...</p>
</div>

<h5>From Yahoo Indian- Gujarati (http://in.gujarati.yahoo.com/)</h5>
<div class="indic">
<h4 lang="gu">&#xab6;&#xabf;&#xab2;&#xacd;&#xaaa;&#xabe; &#xa85;&#xaa8;&#xac7; &#xab0;&#xabe;&#xa9c; &#xa86;&#xa9c;&#xac7; &#xab8;&#xabe;&#xaa4; &#xaab;&#xac7;&#xab0;&#xabe; &#xab2;&#xac7;&#xab6;&#xac7;</h4>
<p lang="gu">&#xaac;&#xacb;&#xab2;&#xac0;&#xab5;&#xac1;&#xaa1; &#xab8;&#xac1;&#xa82;&#xaa6;&#xab0;&#xac0; &#xab6;&#xabf;&#xab2;&#xacd;&#xaaa;&#xabe; &#xab6;&#xac7;&#xa9f;&#xacd;&#xa9f;&#xac0; &#xaaa;&#xacb;&#xaa4;&#xabe;&#xaa8;&#xabe; &#xaae;&#xa82;&#xa97;&#xac7;&#xaa4;&#xab0; &#xaad;&#xabe;&#xab0;&#xaa4;&#xac0;&#xaaf; &#xaae;&#xac2;&#xab3;&#xaa8;&#xabe; &#xaac;&#xacd;&#xab0;&#xabf;&#xa9f;&#xabf;&#xab6; &#xa89;&#xaa6;&#xacd;&#xaaf;&#xacb;&#xa97;&#xaaa;&#xaa4;&#xabf; &#xab0;&#xabe;&#xa9c; &#xa95;&#xac1;&#xa82;&#xaa6;&#xacd;&#xab0;&#xabe; &#xab8;&#xabe;&#xaa5;&#xac7; &#xa86;&#xa9c;&#xac7; &#xab2;&#xa97;&#xacd;&#xaa8; &#xaac;&#xa82;&#xaa7;&#xaa8;&#xaae;&#xabe;&#xa82; &#xaac;&#xa82;&#xaa7;&#xabe;&#xaaf; &#xa9c;&#xab6;&#xac7;. &#xab5;&#xabf;&#xab5;&#xabe;&#xab9; &#xab8;&#xaae;&#xabe;&#xab0;&#xa82;&#xaad; &#xab0;&#xabe;&#xa9c;&#xaa8;&#xabe; &#xaae;&#xabf;&#xaa4;&#xacd;&#xab0; &#xa95;&#xabf;&#xab0;&#xaa3; &#xaac;&#xabe;&#xab5;&#xabe;&#xaa8;&#xabe; &#xa96;&#xa82;&#xaa1;&#xabe;&#xab2;&#xabe;&#xaae;&#xabe;&#xa82; &#xa86;&#xab5;&#xac7;&#xab2; &#xaab;&#xabe;&#xab0;&#xacd;&#xaae;...</p>
</div>
<pagebreak />

<h2>Arabic Fonts/Languages</h2>
<h5>From BBC World Service Arabic News (http://www.bbc.co.uk/arabic/)</h5>
<div class="arabic">
<h4 lang="ar">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</h4>
<p lang="ar">&#x628;&#x64a;&#x639; &#x627;&#x644;&#x642;&#x641;&#x627;&#x632; &#x627;&#x644;&#x62c;&#x644;&#x62f;&#x64a; &#x627;&#x644;&#x645;&#x631;&#x635;&#x639; &#x627;&#x644;&#x630;&#x64a; &#x627;&#x631;&#x62a;&#x62f;&#x627;&#x647; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x644;&#x644;&#x645;&#x631;&#x629; &#x627;&#x644;&#x627;&#x648;&#x644;&#x649; &#x639;&#x627;&#x645; 1983 &#x62e;&#x644;&#x627;&#x644; &#x627;&#x648;&#x644; &#x62e;&#x637;&#x648;&#x629; &#x645;&#x646; &#x631;&#x642;&#x635;&#x62a;&#x647; &#x627;&#x644;&#x634;&#x647;&#x64a;&#x631;&#x629; "&#x627;&#x644;&#x633;&#x64a;&#x631; &#x639;&#x644;&#x649; &#x627;&#x644;&#x642;&#x645;&#x631; (&#x645;&#x648;&#x646; &#x648;&#x648;&#x643;)" &#x628;&#x633;&#x639;&#x631; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631; &#x627;&#x644;&#x633;&#x628;&#x62a; &#x641;&#x64a; &#x646;&#x64a;&#x648;&#x64a;&#x648;&#x631;&#x643; &#x62e;&#x644;&#x627;&#x644; &#x645;&#x632;&#x627;&#x62f; &#x644;&#x645;&#x642;&#x62a;&#x646;&#x64a;&#x627;&#x62a; &#x627;&#x644;&#x645;&#x63a;&#x646;&#x64a; &#x627;&#x644;&#x627;&#x645;&#x631;&#x64a;&#x643;&#x64a; &#x627;&#x644;&#x631;&#x627;&#x62d;&#x644;.</p>
</div>

<h5 style="text-align: right;">In alternative fonts (available with mPDF):</h5>
<div class="arabic">
<table border="1" style="border-collapse: collapse;" width="100%"> <tr> <td>
<p style="font-family: ar_1_002">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_1_003">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_1_004">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_1_005">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_1_006">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
</td><td>
<p style="font-family: ar_1_007">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_2_001">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_2_002">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_2_003">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
<p style="font-family: ar_2_004">&#x628;&#x64a;&#x639; &#x642;&#x641;&#x627;&#x632; &#x645;&#x627;&#x64a;&#x643;&#x644; &#x62c;&#x627;&#x643;&#x633;&#x648;&#x646; &#x628;&#x640; 350 &#x627;&#x644;&#x641; &#x62f;&#x648;&#x644;&#x627;&#x631;</p>
</td></tr></table>
</div>



<h5>From BBC World Service Persian News (http://www.bbc.co.uk/persian/)</h5>
<div class="arabic">
<h4 lang="fa">\'&#x637;&#x628;&#x642; &#x646;&#x638;&#x631;&#x633;&#x646;&#x62c;&#x6cc; &#x62f;&#x648;&#x644;&#x62a; &#x627;&#x646;&#x62a;&#x62e;&#x627;&#x628;&#x627;&#x62a; &#x628;&#x647; &#x62f;&#x648;&#x631; &#x62f;&#x648;&#x645; &#x6a9;&#x634;&#x6cc;&#x62f;&#x647; &#x645;&#x6cc; &#x634;&#x62f;\'</h4>
<p lang="fa">&#x639;&#x644;&#x6cc;&#x631;&#x636;&#x627; &#x632;&#x627;&#x6a9;&#x627;&#x646;&#x6cc; &#x646;&#x645;&#x627;&#x6cc;&#x646;&#x62f;&#x647; &#x62a;&#x647;&#x631;&#x627;&#x646; &#x62f;&#x631; &#x645;&#x62c;&#x644;&#x633; &#x6af;&#x641;&#x62a;&#x647; &#x6a9;&#x647; &#x6cc;&#x6a9; &#x631;&#x648;&#x632; &#x67e;&#x6cc;&#x634; &#x627;&#x632; &#x628;&#x631;&#x6af;&#x632;&#x627;&#x631;&#x6cc; &#x627;&#x646;&#x62a;&#x62e;&#x627;&#x628;&#x627;&#x62a; &#x631;&#x6cc;&#x627;&#x633;&#x62a; &#x62c;&#x645;&#x647;&#x648;&#x631;&#x6cc; &#x62f;&#x631; &#x627;&#x6cc;&#x631;&#x627;&#x646;&#x60c; &#x646;&#x638;&#x631;&#x633;&#x646;&#x62c;&#x6cc; &#x648;&#x632;&#x627;&#x631;&#x62a; &#x6a9;&#x634;&#x648;&#x631; &#x648; &#x648;&#x632;&#x627;&#x631;&#x62a; &#x627;&#x637;&#x644;&#x627;&#x639;&#x627;&#x62a; &#x627;&#x6cc;&#x631;&#x627;&#x646; &#x646;&#x634;&#x627;&#x646; &#x645;&#x6cc; &#x62f;&#x627;&#x62f; &#x6a9;&#x647; &#x627;&#x646;&#x62a;&#x62e;&#x627;&#x628;&#x627;&#x62a; &#x628;&#x647; &#x62f;&#x648;&#x631; &#x62f;&#x648;&#x645; &#x6a9;&#x634;&#x6cc;&#x62f;&#x647; &#x645;&#x6cc; &#x634;&#x648;&#x62f;.</p>
</div>


<h5>From BBC World Service Urdu News (http://www.bbc.co.uk/urdu/)</h5>
<div class="arabic">
<h4 lang="ur">&#x62c;&#x6cc;&#x6a9;&#x633;&#x646; &#x6a9;&#x627; &#x62f;&#x633;&#x62a;&#x627;&#x646;&#x6c1; 35 &#x644;&#x627;&#x6a9;&#x6be; &#x688;&#x627;&#x644;&#x631; &#x6a9;&#x627;</h4>
<p lang="ur">&#x627;&#x645;&#x631;&#x6cc;&#x6a9;&#x6cc; &#x67e;&#x627;&#x67e; &#x633;&#x646;&#x6af;&#x631; &#x645;&#x627;&#x626;&#x6cc;&#x6a9;&#x644; &#x62c;&#x6cc;&#x6a9;&#x633;&#x646; &#x6a9;&#x627; &#x62f;&#x633;&#x62a;&#x627;&#x646;&#x6c1; &#x62c;&#x633; &#x67e;&#x631; &#x646;&#x642;&#x644;&#x6cc; &#x6c1;&#x6cc;&#x631;&#x6d2; &#x62c;&#x691;&#x6d2; &#x6c1;&#x648;&#x626;&#x6d2; &#x62a;&#x6be;&#x6d2; &#x627;&#x648;&#x631; &#x62c;&#x648; &#x627;&#x646;&#x6be;&#x6cc;&#x6ba; &#x67e;&#x6c1;&#x644;&#x6cc; &#x645;&#x631;&#x62a;&#x628;&#x6c1; &#x2019;&#x645;&#x648;&#x646; &#x648;&#x627;&#x6a9;&#x2018; &#x67e;&#x6cc;&#x634; &#x6a9;&#x6cc;&#x626;&#x6d2; &#x62c;&#x627;&#x646;&#x6d2; &#x67e;&#x631; &#x645;&#x644;&#x627; &#x62a;&#x6be;&#x627; &#x67e;&#x6cc;&#x646;&#x62a;&#x6cc;&#x633; &#x644;&#x627;&#x6a9;&#x6be; &#x688;&#x627;&#x644;&#x631; &#x645;&#x6cc;&#x6ba; &#x646;&#x6cc;&#x644;&#x627;&#x645; &#x6c1;&#x648; &#x6af;&#x6cc;&#x627; &#x6c1;&#x6d2;&#x6d4;</p>
</div>


<h5>From BBC World Service Pashto News (http://www.bbc.co.uk/pashto/)</h5>
<div class="arabic">
<h4 lang="ps">&#x633;&#x62a;&#x627;&#x633;&#x64a; &#x67e;&#x64a;&#x63a;&#x627;&#x645;&#x648;&#x646;&#x647; &#x627;&#x648;&#x62f; &#x62e;&#x648;&#x69a;&#x649; &#x633;&#x646;&#x62f;&#x631;&#x6d0;</h4>
<p lang="ps">&#x62f; &#x645;&#x648;&#x633;&#x64a;&#x642;&#x6cd; &#x62f;&#x627;&#x62e;&#x67e;&#x631;&#x648;&#x646;&#x6d0; &#x67e;&#x647; &#x627;&#x641;&#x63a;&#x627;&#x646;&#x633;&#x62a;&#x627;&#x646; &#x6a9;&#x6d0; &#x62f;&#x627;&#x6d0;&#x641; &#x627;&#x6d0;&#x645; &#x67e;&#x647; &#x685;&#x67e;&#x648;&#x62f; &#x633;&#x647;&#x627;&#x631; &#x67e;&#x647; &#x644;&#x633;&#x648; &#x628;&#x62c;&#x648; &#x627;&#x648;&#x631;&#x64a;&#x62f;&#x644;&#x649; &#x634;&#x649;</p>
</div>


<pagebreak />
<h2>Fixed-position block elements</h2>
mPDF 4.0 supports fixed-position block elements (at least partially). This page has some examples of fixed-position elements.
<div id="myfixed">
<div style="border: 1px solid #000088; background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  background-color: #DDFFEE; padding: 0.5em;">#1. Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula eros vehicula pretium. Maecenas feugiat pede vel risus. <span title="Nulla is marked by a span">Nulla</span> et lectus. Fusce eleifend neque sit amet erat. Integer <a href="mailto:admin@bpm1.com">consectetuer</a> nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. 
<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula eros vehicula pretium. Maecenas feugiat pede vel risus. <span title="Nulla is marked by a span">Nulla</span> et lectus. Fusce eleifend neque sit amet erat. Integer <a href="mailto:admin@bpm1.com">consectetuer</a> nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. </p>
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt eros turpis, vel aliquam quam eros odio et sapien.
<div style="border: 1px solid #008800; background-color: #EEFFDD; text-align: left; padding: 0.5em;">
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt eros turpis, vel aliquam quam eros odio et sapien.
</div>
Mauris ante pede, eros auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </div>
</div>

<div class="myfixed2">#2. Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. <div style="border: 1px dotted green; padding: 1em; background-color: #FFEEFF; color: red">Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo.</div>Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo.</div>

<div class="myfixed3">#3. Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo.</div>

<div class="myfixed4">#4. overflow: auto<br />Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi.</div>

<div class="myfixed5">#5. overflow: visible<br />Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi.</div>

<div class="myfixed6">#6. overflow: hidden<br />Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi.</div>

<div class="myfixed7">#7. width: auto<br />Shrink-to-fit</div>

<pagebreak />
<h2>Barcodes</h2>
<p>NB <b>Quiet zones</b> - The barcode object includes space to the right/left or top/bottom only when the specification states a \'quiet zone\' or \'light margin\'. All the examples below also have CSS property set on the barcode object i.e. padding: 1.5mm; </p>

<h3>EAN-13 Barcodes (EAN-2 and EAN-5)</h3>
<p>NB EAN-13, UPC-A, UPC-E, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code (see below).</p>
<p>A nominal height and width for these barcodes is defined by the specification. \'size\' will scale both the height and width. Values between 0.8 and 2 are allowed (i.e. 80% to 200% of the nominal size). \'height\' can also be varied as a factor of 1; this is applied after the scaling factor used for \'size\'.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<!-- ITEMS HERE -->
<tr>
<td align="center">EAN13</td>
<td>Standard EAN-13 barcode. Accepts 12 or 13 characters (creating checksum digit if required). [0-9] numeric only.</td>
<td class="barcodecell"><barcode code="978-0-9542246-0" class="barcode" /></td>
</tr>
<tr>
<td align="center">ISBN</td>
<td>Standard EAN-13 barcode with \'ISBN\' number shown above [shown at height="0.66"]</td>
<td class="barcodecell"><barcode code="978-0-9542246-0" type="ISBN" class="barcode" height="0.66" text="1" /></td>
</tr>
<tr>
<td align="center">ISSN</td>
<td>Standard EAN-13 barcode with \'ISSN\' number shown above [shown at size="0.8"]</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8" type="ISSN" size="0.8" class="barcode" text="1" /></td>
</tr>
</tbody>
</table>

<h3>EAN-8, UPC-A and UPC-E Barcodes</h3>
<p>UPC-A, UPC-E, EAN-13, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code (see below).</p>
<p>A nominal height and width for these barcodes is defined by the specification. \'size\' will scale both the height and width. Values between 0.8 and 2 are allowed (i.e. 80% to 200% of the nominal size). \'height\' can also be varied as a factor of 1; this is applied after the scaling factor used for \'size\'.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<!-- ITEMS HERE -->
<tr>
<td align="center">UPCA</td>
<td>UPC-A barcode. This is a subset of the EAN-13. (098277211236) Accepts 11 or 12 characters (creating checksum digit if required). [0-9] numeric only</td>
<td class="barcodecell"><barcode code="09827721123" type="UPCA" class="barcode" /></td>
</tr>
<tr>
<td align="center">UPCE</td>
<td>UPC-E barcode. Requires the UPC-A code to be entered as above (e.g. 042100005264 to give 425261). NB mPDF will die with an error message if the code is not valid, as only some UPC-A codes can be converted into valid UPC-E codes. UPC-E doesn\'t have a check digit encoded explicity, rather the check digit is encoded in the parity of the other six characters. The check digit that is encoded is the check digit from the original UPC-A barcode.</td>
<td class="barcodecell"><barcode code="04210000526" type="UPCE" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN8</td>
<td>EAN-8 (5512345) Accepts 7 or 8 characters (creating checksum digit if required). [0-9] numeric only</td>
<td class="barcodecell"><barcode code="2468123" type="EAN8" class="barcode" /></td>
</tr>
</tbody>
</table>

<h3>EAN-2 and EAN-5 supplements, and combined forms</h3>
<p>UPC-A, UPC-E, EAN-13, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<!-- ITEMS HERE -->
<tr>
<td align="center">EAN2</td>
<td colspan="2">EAN-2 supplement barcode. mPDF does not generate EAN-5 barcode on its own; see supplements below. Used to denote an issue of a periodical. EAN-2 supplement accepts 2 digits [0-9] only, EAN-5 five.</td>
</tr>
<tr>
<td align="center">EAN5</td>
<td colspan="2">EAN-5 supplement barcode. mPDF does not generate EAN-5 barcode on its own; see supplements below. Usually used in conjunction with EAN-13 for the price of books. 90000 is the code for no price. </td>
</tr>
<tr>
<td align="center">EAN13P2</td>
<td>Standard EAN-13 barcode with 2-digit UPC supplement (07)</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 07" type="EAN13P2" class="barcode" /></td>
</tr>
<tr>
<td align="center">ISBNP2</td>
<td>Standard EAN-13 barcode with \'ISBN\' number shown above, and 2-digit EAN-2 supplement</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 07" type="ISBNP2" class="barcode" text="1" /></td>
</tr>
<tr>
<td align="center">ISSNP2</td>
<td>Standard EAN-13 barcode with \'ISSN\' number shown above, and 2-digit EAN-2 supplement</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 07" type="ISSNP2" class="barcode" text="1" /></td>
</tr>
<tr>
<td align="center">UPCAP2</td>
<td>UPC-A barcode with 2-digit EAN-2 supplement. This is a subset of the EAN-13. (075678164125 07)</td>
<td class="barcodecell"><barcode code="00633895260 24" type="UPCAP2" class="barcode" /></td>
</tr>
<tr>
<td align="center">UPCEP2</td>
<td>UPC-E barcode with 2-digit EAN-2 supplement. (042100005264 07)</td>
<td class="barcodecell"><barcode code="042100005264 07" type="UPCEP2" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN8P2</td>
<td>EAN-8 barcode with 2-digit EAN-2 supplement (55123457 07)</td>
<td class="barcodecell"><barcode code="55123457 07" type="EAN8P2" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN13P5</td>
<td>Standard EAN-13 barcode with 5-digit UPC supplement (90000)</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 90000" type="EAN13P5" class="barcode" /></td>
</tr>
<tr>
<td align="center">ISBNP5</td>
<td>Standard EAN-13 barcode with \'ISBN\' number shown above, and 5-digit EAN-5 supplement</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 90000" type="ISBNP5" class="barcode" text="1" /></td>
</tr>
<tr>
<td align="center">ISSNP5</td>
<td>Standard EAN-13 barcode with \'ISSN\' number shown above, and 5-digit EAN-5 supplement</td>
<td class="barcodecell"><barcode code="978-0-9542246-0-8 90000" type="ISSNP5" class="barcode" text="1" /></td>
</tr>
<tr>
<td align="center">UPCAP5</td>
<td>UPC-A barcode with 5-digit EAN-5 supplement. This is a subset of the EAN-13. (075678164125 90000)</td>
<td class="barcodecell"><barcode code="075678164125 90000" type="UPCAP5" class="barcode" /></td>
</tr>
<tr>
<td align="center">UPCEP5</td>
<td>UPC-E barcode with 5-digit EAN-5 supplement. (042100005264 90000)</td>
<td class="barcodecell"><barcode code="042100005264 90000" type="UPCEP5" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN8P5</td>
<td>EAN-8 barcode with 5-digit EAN-5 supplement (55123457 90000)</td>
<td class="barcodecell"><barcode code="55123457 90000" type="EAN8P5" class="barcode" /></td>
</tr>
</tbody>
</table>

<pagebreak />
<h3>Postcode Barcodes</h3>
<p>These all have sizes fixed by their specification. Although they can be altered using \'size\' it is not recommended. \'height\' is ignored.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<!-- ITEMS HERE -->
<tr>
<td align="center">IMB</td>
<td>Intelligent Mail Barcode - also known as: USPS OneCode 4-State Customer Barcode, OneCode 4CB, USPS 4CB, 4-CB, 4-State Customer Barcode, USPS OneCode Solution Barcode. (01234567094987654321-01234567891) Accepts: Up to 31 digits (required 20-digit Tracking Code, and up to 11-digit Routing Code; this may be 0, 5, 9, or 11 digits). If the Routing code is included, it should be spearated by a hyphen - like this example.</td>
<td class="barcodecell"><barcode code="01234567094987654321-01234567891" type="IMB" class="barcode" /></td>
</tr>
<tr>
<td align="center">RM4SCC</td>
<td>Royal Mail 4-state Customer barcode (SN34RD1A). Accepts: max. 9 characters. Valid characters: [A-Z,0-9] Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="SN34RD1A" type="RM4SCC" class="barcode" /></td>
</tr>
<tr>
<td align="center">KIX</td>
<td>Dutch KIX version of Royal Mail 4-state Customer barcode (SN34RD1A). Valid characters: [A-Z,0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="SN34RD1A" type="KIX" class="barcode" /></td>
</tr>
<tr>
<td align="center">POSTNET</td>
<td>POSTNET barcode. Accepts 5, 9 or 11 digits. Valid characters: [0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="123456789" type="POSTNET" class="barcode" /></td>
</tr>
<tr>
<td align="center">PLANET</td>
<td>PLANET barcode. Accepts 11 or 13 digits. Valid characters: [0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="00123456789" type="PLANET" class="barcode" /></td>
</tr>
</tbody>
</table>


<h3>Variable width Barcodes</h3>
<p>These barcodes are all of variable length depending on the code entered. There is no recommended maximum size for any of these specs, but all recommend a minimum X-dimension (width of narrowest bar) as 7.5mil (=0.19mm). The default used here is twice the minimum i.e. X-dim = 0.38mm.</p>
<p>The specifications give a minimum height of 15% of the barcode length (which can be variable). The bar height in mPDF is set to a default value of 10mm. </p>
<p>\'size\' will scale the barcode in both dimensions. mPDF will accept any number, but bear in mind that size="0.5" will set the bar width to the minimum. The \'height\' attribute further allows scaling - this factor is applied to already scaled barcode. Thus size="2" height="0.5" will give a barcode twice the default width (X-dim=0.76mm) and at the default height set in mPDF i.e. 10mm.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<tr>
<td align="center">C128A</td>
<td>CODE 128 A. Valid characters: [A-Z uppercase and control chars ASCII 0-31]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="CODE 128 A" type="C128A" class="barcode" /></td>
</tr>
<tr>
<td align="center">C128B</td>
<td>CODE 128 B. Valid characters: [Upper / Lower Case + All ASCII Printable Characters]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="ABC123abc@456" type="C128B" class="barcode" /></td>
</tr>
<tr>
<td align="center">C128C</td>
<td>CODE 128 C. Valid characters: [0-9]. Must be an even number of digits. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="0123456789" type="C128C" class="barcode" /></td>
</tr>

<tr>
<td align="center">EAN128C [A/B/C]</td>
<td>EAN128 (A, B, and C). Specified variant of Code 128, utilising an FNC1 start code. Also known as UCC/EAN-128 or GS1-128. Valid characters: [cf. Code 128]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="0112345678912343" type="EAN128C" class="barcode" /></td>
</tr>

<tr>
<td align="center">C39</td>
<td>CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9. Valid characters: [0-9 A-Z \'-\' . Space $/+%]</td>
<td class="barcodecell"><barcode code="TEC-IT" type="C39" class="barcode" /></td>
</tr>
<tr>
<td align="center">C39+</td>
<td>CODE 39 + CHECKSUM. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="39OR93" type="C39+" class="barcode" /></td>
</tr>
<tr>
<td align="center">C39E</td>
<td>CODE 39 EXTENDED. Valid characters: [ASCII-characters between 0..127]</td>
<td class="barcodecell"><barcode code="CODE 39 E" type="C39E" class="barcode" /></td>
</tr>
<tr>
<td align="center">C39E+</td>
<td>CODE 39 EXTENDED + CHECKSUM. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="CODE 39 E+" type="C39E+" class="barcode" /></td>
</tr>

<tr>
<td align="center">S25</td>
<td>Standard 2 of 5. Valid characters: [0-9]</td>
<td class="barcodecell"><barcode code="54321068" type="S25" class="barcode" /></td>
</tr>
<tr>
<td align="center">S25+</td>
<td>Standard 2 of 5 + CHECKSUM. Valid characters: [0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="54321068" type="S25+" class="barcode" /></td>
</tr>
<tr>
<td align="center">I25</td>
<td>Interleaved 2 of 5. Valid characters: [0-9]</td>
<td class="barcodecell"><barcode code="54321068" type="I25" class="barcode" /></td>
</tr>
<tr>
<td align="center">I25+</td>
<td>Interleaved 2 of 5 + CHECKSUM. Valid characters: [0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="54321068" type="I25+" class="barcode" /></td>
</tr>
<tr>
<td align="center">I25B</td>
<td>Interleaved 2 of 5 with bearer bars. Valid characters: [0-9]</td>
<td class="barcodecell"><barcode code="1234567" type="I25B" class="barcode" /></td>
</tr>
<tr>
<td align="center">I25B+</td>
<td>Interleaved 2 of 5 + CHECKSUM with bearer bars. Valid characters: [0-9]. Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="1234567" type="I25B+" class="barcode" /></td>
</tr>

<tr>
<td align="center">C93</td>
<td>CODE 93 - USS-93 (extended). Valid characters: [ASCII-characters between 0..127]. Checksum digits: automatic.</td>
<td class="barcodecell"><barcode code="39OR93" type="C93" class="barcode" /></td>
</tr>

<tr>
<td align="center">MSI</td>
<td>MSI. Modified Plessey. Valid characters: [0-9]</td>
<td class="barcodecell"><barcode code="01234567897" type="MSI" class="barcode" /></td>
</tr>
<tr>
<td align="center">MSI+</td>
<td>MSI + CHECKSUM (module 11). Checksum digit: automatic.</td>
<td class="barcodecell"><barcode code="0123456789" type="MSI+" class="barcode" /></td>
</tr>

<tr>
<td align="center">CODABAR</td>
<td>CODABAR. Valid characters: [0-9 \'-\' $:/.+ ABCD] ABCD are used as stop and start characters e.g. A34698735B</td>
<td class="barcodecell"><barcode code="A34698735B" type="CODABAR" class="barcode" /></td>
</tr>

<tr>
<td align="center">CODE11</td>
<td>CODE 11. Valid characters: [0-9 and \'-\']. Checksum digits: 1 (or 2 if length of code is > 10 characters) - automatic.</td>
<td class="barcodecell"><barcode code="123-456-789" type="CODE11" class="barcode" /></td>
</tr>


</tbody>
</table>

';


//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('s'); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->WriteHTML($html);

$mpdf->Output(); 

exit;

//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>