<?php

//==============================================================
//==============================================================
define("_JPGRAPH_PATH", '../../jpgraph_5/jpgraph/'); // must define this before including mpdf.php file
$JpgUseSVGFormat = true;

define('_MPDF_URI','../'); 	// must be  a relative or absolute URI - not a file system path
//==============================================================
//==============================================================


ini_set("memory_limit","64M");

$html = '
<html><head>
	<meta http-equiv="Content-Language" content="en-GB">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style>
		body { font-family:"Times New Roman"; font-size:10pt; }
		p.littlewomen { margin: 0; font-family: sans-serif; text-align: justify; }

		h1, h2, h3, h4, h5, h6 { font-family: DejaVuSansCondensed; }
		table {font-family: DejaVuSansCondensed; font-size: 9pt; line-height: 1.2;
			vertical-align: top; 
			margin-top: 2pt; margin-bottom: 5pt;
			border-collapse: collapse;  }

		thead {	font-weight: bold; vertical-align: bottom; }

		th {	font-weight: bold; 
			text-align:left; 
			padding-left: 2mm; 
			padding-right: 2mm; 
			padding-top: 0.5mm; 
			padding-bottom: 0.5mm; 
		 }

		td {	padding-left: 2mm; 
			text-align:left; 
			padding-right: 2mm; 
			padding-top: 0.5mm; 
			padding-bottom: 0.5mm;
		 }

		th p { text-align: left; margin:0pt;  }
		td p { text-align: left; margin:0pt;  }

		table.widecells td {
			padding-left: 5mm;
			padding-right: 5mm;
		}
		table.tallcells td {
			padding-top: 3mm;
			padding-bottom: 3mm;
		}	.sub td { vertical-align:top; border-top:0px; border-bottom:0px; padding:2px; padding-right:8px; 
			margin:0; font-size:9pt; }
		.sub { align:center; border:#888888 1px solid; }
		thead td { font-weight: bold; }

		table.nested {
			border-collapse: separate;
			border: 4px solid #880000;
			padding: 3px;
			margin: 0px 20px 0px 20px;
			empty-cells: hide;
			background-color:#FFFFCC;
		}
		table.nested td {
			border: 1px solid #008800;
			padding: 0px;
			background-color:#ECFFDF;
		}
		table.outer2 {
			border-collapse: separate;
			border: 4px solid #088000;
			padding: 3px;
			margin: 10px 0px;
			empty-cells: hide;
			background-color: yellow;
		}
		table.outer2 td {
			font-family: Times;
			border: 1px solid #008800;
			padding: 0px;
			background-color:#ECFFDF;
		}
		table.inner {
			border-collapse: collapse;
			border: 2px solid #000088;
			padding: 3px;
			margin: 5px;
			empty-cells: show;
			background-color:#FFCCFF;
		}
		table.inner td {
			border: 1px solid #000088;
			padding: 0px;
			font-family: monospace;
			font-style: italic;
			font-weight: bold;
			color: #880000;
			background-color:#FFECDF;
		}
		table.collapsed {
			border-collapse: collapse;
		}
		table.collapsed td {
			background-color:#EDFCFF;
		}
		.headerrow td, .headerrow th { background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;  }
		.footerrow td, .footerrow th { background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;  }

		.evenrow td, .evenrow th { background-color: #f5f8f5; } 
		.oddrow td, .oddrow th { background-color: #e3ece4; } 

		.bpmTopic {	background-color: #e3ece4; }
		.bpmTopicC { background-color: #e3ece4; }
		.bpmNoLines { background-color: #e3ece4; }
		.bpmNoLinesC { background-color: #e3ece4; }
		.bpmClear {		}
		.bpmClearC { text-align: center; }
		.bpmTopnTail { background-color: #e3ece4; topntail: 0.02cm solid #495b4a;}
		.bpmTopnTailC { background-color: #e3ece4; topntail: 0.02cm solid #495b4a;}
		.bpmTopnTailClear { topntail: 0.02cm solid #495b4a; }
		.bpmTopnTailClearC { topntail: 0.02cm solid #495b4a; }

		.bpmTopicC td, .bpmTopicC td p { text-align: center; }
		.bpmNoLinesC td, .bpmNoLinesC td p { text-align: center; }
		.bpmClearC td, .bpmClearC td p { text-align: center; }
		.bpmTopnTailC td, .bpmTopnTailC td p { text-align: center;  }
		.bpmTopnTailClearC td, .bpmTopnTailClearC td p {  text-align: center;  }

		.pmhMiddleCenter { text-align:center; vertical-align:middle; }
		.pmhMiddleRight {	text-align:right; vertical-align:middle; }
		.pmhBottomCenter { text-align:center; vertical-align:bottom; }
		.pmhBottomRight {	text-align:right; vertical-align:bottom; }
		.pmhTopCenter {	text-align:center; vertical-align:top; }
		.pmhTopRight {	text-align:right; vertical-align:top; }
		.pmhTopLeft {	text-align:left; vertical-align:top; }
		.pmhBottomLeft {	text-align:left; vertical-align:bottom; }
		.pmhMiddleLeft {	text-align:left; vertical-align:middle; }

		.bpmTopic td, .bpmTopic th  {	border-top: 1px solid #FFFFFF; }
		.bpmTopicC td, .bpmTopicC th  {	border-top: 1px solid #FFFFFF; }
		.bpmTopnTail td, .bpmTopnTail th  {	border-top: 1px solid #FFFFFF; }
		.bpmTopnTailC td, .bpmTopnTailC th  {	border-top: 1px solid #FFFFFF; }
		.lista { list-style-type: upper-roman; }
		.listb{ list-style-type: decimal; font-family: sans-serif; color: blue; font-weight: bold; font-style: italic; font-size: 19pt; }
		.listc{ list-style-type: upper-alpha; text-indent: 25mm; }
		.listd{ list-style-type: lower-alpha; color: teal; line-height: 2; }
		.liste{ list-style-type: disc; }

		.roundgradient {
			border:0.05mm solid #220044; 
			background-color: #f0f2ff;
			background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
			border-radius: 10mm / 10mm;
			background-clip: border-box;
			padding: 3.3mm;
		}
		.phpcode {
			border:1px solid #555555; 
			background-color: #DDDDDD; 
			padding: 1em; 
			font-size:8pt; 
			font-family: lucidaconsole, mono;
		}
	</style>
</head><body>

<!-- DEFINE HEADERS & FOOTERS -->
<htmlpageheader name="myHTMLHeaderOdd">
<div style="font-family:sans-serif; background-color:#BBEEFF" align="center"><b>mPDF Example File</b></div>
</htmlpageheader>
<htmlpageheader name="myHTMLHeaderEven">
<div style="font-family:sans-serif; background-color:#EFFBBE" align="center"><b><i>mPDF Example File</i></b></div>
</htmlpageheader>
<htmlpagefooter name="myHTMLFooterOdd" style="display:none">
<div style="font-family:sans-serif; background-color:#CFFFFC" align="center"><b>{PAGENO}/{nbpg}</b></div>
</htmlpagefooter>
<htmlpagefooter name="myHTMLFooterEven" style="display:none">
<div style="font-family:sans-serif; background-color:#FFCCFF" align="center"><b><i>{PAGENO}/{nbpg}</i></b></div>
</htmlpagefooter>

<pagefooter name="myFooter2Odd" content-left="" content-center="mPDF Example File" content-right="{PAGENO}/{nbpg}" footer-style="font-family:sans-serif; font-size:9pt; font-weight:bold; color:#000088;" footer-style-right="font-weight: bold;" line="on" />

<pagefooter name="myFooter2Even" content-left="{PAGENO}/{nbpg}" content-center="mPDF Example File" content-right="{DATE j-m-Y}" footer-style="font-family:sans-serif; font-size:10pt; color:#880000;" footer-style-left="font-weight:bold;" line="on" />


<!-- FRONT COVER -->
<div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;">
<img src="clematis.jpg" style="width: 210mm; height: 297mm; margin: 0;" />
</div>

<div style="position: absolute; left:32mm; right: 25mm; top: 70mm; width: 58%; margin-right: auto; margin-left:auto; ">
<div style="padding: 1em; font-family: Arial; font-weight: bold; font-size: 28pt; border: 3px solid #000044; border-radius: 5mm; background-clip: border-box; color: #000044; background-color: #FFFFFF;">
mPDF Example File
</div>
</div>

<pagebreak />

<p>The front cover can also be produced like this:</p>
<!-- EXAMPLE PHP CODE -->
<div class="phpcode">'. nl2br(htmlspecialchars('/* ALTERNATIVE PHP METHOD */
$mpdf->Image(\'clematis.jpg\',0,0,210,297,\'jpg\',\'\',true, false);
// the last "false" allows a full page picture

$mpdf->y = 70;
$mpdf->Shaded_box(\'mPDF Example File\', \'Trebuchet\', \'\', 28, \'70%\', \'DF\', 3, \'#FFFFFF\', \'#000044\', 10);
')) .'</div>
<!-- END EXAMPLE PHP CODE -->


<!-- TABLES OF CONTENTS -->
<tocpagebreak toc-preHTML="&lt;h2&gt;CONTENTS&lt;/h2&gt;" links="1" toc-bookmarkText="Contents" resetpagenum="1" pagenumstyle="1" 
odd-header-name="html_myHTMLHeaderOdd" odd-header-value="1" even-header-name="html_myHTMLHeaderEven" even-header-value="1" odd-footer-name="myFooter2Odd" odd-footer-value="1" even-footer-name="myFooter2Even" even-footer-value="1" />

<tocpagebreak name="Figures" toc-preHTML="&lt;h2&gt;FIGURES&lt;/h2&gt;" links="1" toc-bookmarkText="Figures" />

<tocpagebreak name="Tables" toc-preHTML="&lt;h2&gt;TABLES&lt;/h2&gt;" links="1"  toc-bookmarkText="Tables" />



<!-- SECTION 1 -->
<h1>(H1) mPDF</h1>
<h2>(H2) Section 1<bookmark content="Section 1" level="0" /></h2>
<h3>(H3) HTML Markup<bookmark content="HTML Markup" level="1" /><tocentry name="" content="HTML Markup" level="0" /><indexentry content="HTML Markup"  /></h3>

<tocentry name="" content="HTML Markup" level="1" />
<tocentry name="" content="HTML Markup" level="2" />

<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
<p>P: Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>

<hr />

<div>DIV: Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </div>
<div>DIV: Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </div>

<blockquote>Blockquote: Maecenas arcu justo, malesuada eu, dapibus ac, adipiscing vitae, turpis. Fusce mollis. Aliquam egestas. In purus dolor, facilisis at, fermentum nec, molestie et, metus. Maecenas arcu justo, malesuada eu, dapibus ac, adipiscing vitae, turpis. Fusce mollis. Aliquam egestas. In purus dolor, facilisis at, fermentum nec, molestie et, metus.</blockquote>

<address>Address: Vestibulum feugiat, orci at imperdiet tincidunt, mauris erat facilisis urna, sagittis ultricies dui nisl et lectus. Sed lacinia, lectus vitae dictum sodales, elit ipsum ultrices orci, non euismod arcu diam non metus.</address>

<pre>PRE: Cum sociis natoque penatibus et magnis dis parturient montes, 
nascetur ridiculus mus. In suscipit turpis vitae odio. Integer convallis 
dui at metus. Fusce magna. Sed sed lectus vitae enim tempor cursus. Cras 
sed, posuere et, urna. Quisque ut leo. Aliquam interdum hendrerit tortor. 
Vestibulum elit. Vestibulum et arcu at diam mattis commodo. Nam ipsum sem, 
ultricies at, rutrum sit amet, posuere nec, velit. Sed molestie mollis dui.</pre>

<div><a href="http://mpdf.bpm1.com/manual/">Hyperlink (&lt;a&gt;)</a></div>

<div>Styles - <tt>tt(teletype)</tt> <i>italic</i> <b>bold</b> <big>big</big> <small>small</small> <em>emphasis</em> <strong>strong</strong> <br />new lines<br>
<code>code</code> <samp>sample</samp> <kbd>keyboard</kbd> <var>variable</var> <cite>citation</cite> <abbr>abbr.</abbr> <acronym>ACRONYM</acronym> <sup>sup</sup> <sub>sub</sub> <strike>strike</strike> <s>strike-s</s> <u>underline</u> <del>delete</del> <ins>insert</ins> <q>To be or not to be</q> <font face="sans-serif" color="#880000" size="5">font changing face, size and color</font>
</div>

<p style="font-size:15pt; color:#440066">Paragraph using the in-line style to determine the font-size (15pt) and colour</p>


<h3>Testing BIG, SMALL, UNDERLINE, STRIKETHROUGH, FONT color, ACRONYM, SUPERSCRIPT and SUBSCRIPT</h3>
<p>This is <s>strikethrough</s> in <b><s>block</s></b> and <small>small <s>strikethrough</s> in <i>small span</i></small> and <big>big <s>strikethrough</s> in big span</big> and then <u>underline and <s>strikethrough and <sup>sup</sup></s></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</p>

<p>This is a <font color="#008800">green reference<sup>32-47</sup></font> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> then <s>Strikethrough reference<sup>32-47</sup></s> and <s>strikethrough reference<sub>32-47</sub></s></p> 

<p><big>Repeated in <u>BIG</u>: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</big></p> 

<p><small>Repeated in small: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</small></p>

<p>The above repeated, but starting with a paragraph with font-size specified (7pt)</p>

<p style="font-size:7pt;">This is <s>strikethrough</s> in block and <small>small <s>strikethrough</s> in small span</small> and then <u>underline</u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</p>

<p style="font-size:7pt;">This is <s>strikethrough</s> in block and <big>big <s>strikethrough</s> in big span</big> and then <u>underline</u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</p>

<p style="font-size:7pt;">This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> then <s>Strikethrough reference<sup>32-47</sup></s> and <s>strikethrough reference<sub>32-47</sub></s></p>

<p><small>This tests <u>underline</u> and <s>strikethrough</s> when they are <s><u>used together</u></s> as they both use text-decoration</small></p>


<p><small>Repeated in small: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</small></p> 

<p style="font-size:7pt;"><big>Repeated in BIG but with font-size set to 7pt by in-line css: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</big></p>

<ol>
<li>Item <b><u>1</u></b></li>
<li>Item 2<sup>32</sup></li>
<li><small>Item</small> 3</li>
<li>Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo. 
<ul>
<li>Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo. </li>
<li>Subitem 2
<ul>
<li>
Level 3 subitem
</li>
</ul>
</li>
</ul>
</li>
<li>Item 5</li>
</ol>

<p>Sed bibendum. Nunc eleifend ornare velit. Sed consectetuer urna in erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Mauris sodales semper metus. Maecenas justo libero, pretium at, malesuada eu, mollis et, arcu. Ut suscipit pede in nulla. Praesent elementum, dolor ac fringilla posuere, elit libero rutrum massa, vel tincidunt dui tellus a ante. Sed aliquet euismod dolor. Vestibulum sed dui. Duis lobortis hendrerit quam. Donec tempus orci ut libero. Pellentesque suscipit malesuada nisi. </p>
<tocentry name="Tables" content="Basic table" level="0" />
<table border="1" cellpadding="5">
<thead>
<tr>
<th>Data</th>
<th>Data</th>
<td>Data</td>
<td>Data<br />2nd line</td>
</tr>
</thead>
<tbody>
<tr>
<th>More Data</th>
<td>More Data</td>
<td>More Data</td>
<td>Data<br />2nd line</td>
</tr>
<tr>
<th>Data</th>
<td>Data</td>
<td>Data</td>
<td>Data<br />2nd line</td>
</tr>
<tr>
<th>Data</th>
<td>Data</td>
<td>Data</td>
<td>Data<br />2nd line</td>
</tr>
</tbody>
</table>

This paragraph has border-radius and background-gradient set. Minimum padding is recommended as 1/3rd of the border-radius. Or can use $mpdf->autoPadding.
<p class="roundgradient">Sed bibendum. Nunc eleifend ornare velit. Sed consectetuer urna in erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Mauris sodales semper metus. Maecenas justo libero, pretium at, malesuada eu, mollis et, arcu. Ut suscipit pede in nulla. Praesent elementum, dolor ac fringilla posuere, elit libero rutrum massa, vel tincidunt dui tellus a ante. Sed aliquet euismod dolor. Vestibulum sed dui. Duis lobortis hendrerit quam. Donec tempus orci ut libero. Pellentesque suscipit malesuada nisi. </p>



<!-- HYPHENATION -->
<pagebreak />
<h3>Hyphenation<bookmark content="Hyphenation" level="1" /><tocentry name="" content="Hyphenation" level="0" /><indexentry content="Hyphenation"  /></h3>

<h4>Little Women - Chapter One - Playing Pilgrims</h4>
<columns column-count="4" vAlign="J" column-gap="7" />

<p class="littlewomen"> Christmas won\'t be Christmas without any presents,  grumbled Jo, lying on the rug.</p><p class="littlewomen"> It\'s so dreadful to be poor!  sighed Meg, looking down at her old dress.</p><p class="littlewomen"> I don\'t think it\'s fair for some girls to have plenty of pretty things, and other girls nothing at all,  added little Amy, with an injured sniff.</p><p class="littlewomen"> We\'ve got Father and Mother, and each other,  said Beth contentedly from her corner.</p><p class="littlewomen">The four young faces on which the firelight shone brightened at the cheerful words, but darkened again as Jo said sadly,  We haven\'t got Father, and shall not have him for a long time.  She didn\'t say  perhaps never,  but each silently added it, thinking of Father far away, where the fighting was.</p><p class="littlewomen">Nobody spoke for a minute; then Meg said in an altered tone,  You know the reason Mother proposed not having any presents this Christmas was because it is going to be a hard winter for everyone; and she thinks we ought not to spend money for pleasure, when our men are suffering so in the army. We can\'t do much, but we can make our little sacrifices, and ought to do it gladly. But I am afraid I don\'t  And Meg shook her head, as she thought regretfully of all the pretty things she wanted.</p><p class="littlewomen"> But I don\'t think the little we should spend would do any good. We\'ve each got a dollar, and the army wouldn\'t be much helped by our giving that. I agree not to expect anything from Mother or you, but I do want to buy UNDINE AND SINTRAM for myself. I\'ve wanted it so long,  said Jo, who was a bookworm.</p><p class="littlewomen"> I planned to spend mine in new music,  said Beth, with a little sigh, which no one heard but the hearth brush and kettle holder.</p><p class="littlewomen"> I shall get a nice box of Faber\'s drawing pencils. I really need them,  said Amy decidedly.</p><p class="littlewomen"> Mother didn\'t say anything about our money, and she won\'t wish us to give up everything. Let\'s each buy what we want, and have a little fun. I\'m sure we work hard enough to earn it,  cried Jo, examining the heels of her shoes in a gentlemanly manner.</p><p class="littlewomen"> I know I do&mdash;teaching those tiresome children nearly all day, when I\'m longing to enjoy myself at home,  began Meg, in the complaining tone again.</p><p class="littlewomen"> You don\'t have half such a hard time as I do,  said Jo.  How would you like to be shut up for hours with a nervous, fussy old lady, who keeps you trotting, is never satisfied, and worries you till you you\'re ready to fly out the window or cry? </p><p class="littlewomen"> It\'s naughty to fret, but I do think washing dishes and keeping things tidy is the worst work in the world.  It makes me cross, and my hands get so stiff, I can\'t practice well at all.  And Beth looked at her rough hands with a sigh that any one could hear that time.</p><p class="littlewomen"> I don\'t believe any of you suffer as I do,  cried Amy,  for you don\'t have to go to school with impertinent girls, who plague you if you don\'t know your lessons, and laugh at your dresses, and label your father if he isn\'t rich, and insult you when your nose isn\'t nice. </p><p class="littlewomen"> If you mean libel, I\'d say so, and not talk about labels, as if Papa was a pickle bottle,  advised Jo, laughing.</p><p class="littlewomen"> I know what I mean, and you needn\'t be satirical about it. It\'s proper to use good words, and improve your vocabulary,  returned Amy, with dignity.</p><p class="littlewomen"> Don\'t peck at one another, children. Don\'t you wish we had the money Papa lost when we were little, Jo? Dear me! How happy and good we\'d be, if we had no worries!  said Meg, who could remember better times.</p><p class="littlewomen"> You said the other day you thought we were a deal happier than the King children, for they were fighting and fretting all the time, in spite of their money. </p><p class="littlewomen"> So I did, Beth. Well, I think we are. For though we do have to work, we make fun of ourselves, and are a pretty jolly set, as Jo would say. </p><p class="littlewomen"> Jo does use such slang words!   observed Amy, with a reproving look at the long figure stretched on the rug.</p><p class="littlewomen">Jo immediately sat up, put her hands in her pockets, and began to whistle.</p><p class="littlewomen"> Don\'t, Jo. It\'s so boyish! </p><p class="littlewomen"> That\'s why I do it. </p><p class="littlewomen"> I detest rude, unladylike girls! </p><p class="littlewomen"> I hate affected, niminy-piminy chits! </p><p class="littlewomen"> Birds in their little nests agree,  sang Beth, the peacemaker, with such a funny face that both sharp voices softened to a laugh, and the  pecking  ended for that time.</p><p class="littlewomen"> Really, girls, you are both to be blamed,  said Meg, beginning to lecture in her elder-sisterly fashion. You are old enough to leave off boyish tricks, and to behave better, Josephine. It didn\'t matter so much when you were a little girl, but now you are so tall, and turn up your hair, you should remember that you are a young lady. </p><p class="littlewomen"> I\'m not!  And if turning up my hair makes me one, I\'ll wear it in two tails till I\'m twenty,  cried Jo, pulling off her net, and shaking down a chestnut mane.   I hate to think I\'ve got to grow up, and be Miss March, and wear long gowns, and look as prim as a China Aster! It\'s bad enough to be a girl, anyway, when I like boy\'s games and work and manners! I can\'t get over my disappointment in not being a boy. And it\'s worse than ever now, for I\'m dying to go and fight with Papa. And I can only stay home and knit, like a poky old woman! </p><p class="littlewomen">And Jo shook the blue army sock till the needles rattled like castanets, and her ball bounded across the room.</p><p class="littlewomen"> Poor Jo! It\'s too bad, but it can\'t be helped. So you must try to be contented with making your name boyish, and playing brother to us girls,  said Beth, stroking the rough head with a hand that all the dish washing and dusting in the world could not make ungentle in its touch.</p><p class="littlewomen"> As for you, Amy,  continued Meg,  you are altogether to particular and prim. Your airs are funny now, but you\'ll grow up an affected little goose, if you don\'t take care. I I like your nice manners and refined ways of speaking, when you don\'t try to be elegant. But your absurd words are as bad as Jo\'s slang. </p><p class="littlewomen"> If Jo is a tomboy and Amy a goose, what am I, please?  asked Beth, ready to share the lecture.</p><p class="littlewomen"> You\'re a dear, and nothing else,  answered Meg warmly, and no one contradicted her, for the \'Mouse\' was the pet of the family.</p>

<columns column-count="1" />






<!-- LISTS -->
<pagebreak />
<h3>Lists<bookmark content="Lists" level="1" /><tocentry name="" content="Lists" level="0" /><indexentry content="Lists"  /></h3>
<div style="background-color:#ddccff; padding:0pt; border: 1px solid #555555;">
<ol class="lista">
<li>Text here lorem ipsum ibisque totum.</li>
<li><span style="color:green; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</span></li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum. Text here lorem ipsum ibisque totum. Text here lorem ipsum ibisque totum. Text here lorem ipsum ibisque totum. Text here lorem ipsum ibisque totum. Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.
<ol class="listb">
<li>Text here lorem ipsum ibisque totum.</li>
<li><span style="color:green; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</span></li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.
<ol class="listc">
<li>Big text indent 25mm: Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.
</li>
<li>Text here lorem ipsum ibisque totum.
<ol class="listd">
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.
<ol class="liste">
<li>Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.
<ol class="listc">
<li>Big text indent 25mm: Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.
<ol class="listd">
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.
<ol class="liste">
<li>Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.
<ol>
<li>No class specified. Text here lorem ipsum ibisque totum.</li>
<li style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</li>
</ol>
</li>
</ol>
</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem <span style="color:red; font-size:9pt; font-family:courier; font-weight: normal; font-style: normal;">ipsum</span> ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
<li>Text here lorem ipsum ibisque totum.</li>
</ol>
</div>

<!-- TABLES -->
<pagebreak />
<h3>Tables<bookmark content="Tables" level="1" /><tocentry name="" content="Tables" level="0" /><tocentry name="Tables" content="Tables - general" level="0" /><indexentry content="Tables"  /></h3>
<p>mPDF supports all in-line properties inside tables.</p>
<table border="1">
<tbody><tr><td>Row 1</td><td>This is data</td><td>This is data</td></tr>
<tr><td>Row 2</td>
<td>
<p>This is data p</p>
This is data out of p
<p style="font-weight:bold; font-size:20pt; background-color:#FFBBFF;">This is bold data p</p>
<b>This is bold data out of p</b><br />
This is normal data after br
<h3>Heading 3 inside a table</h3>
Text here lorem <i>ipsum</i> ibisque totum.<sup>32</sup>
<div>This is data div</div>
This is data out of div
<div style="font-weight:bold;">This is data div (bold)</div>
This is data out of div
</td>

<td>Also data</td></tr>
</tbody></table>

<p>This table has padding-top and -bottom set to 3mm i.e. padding within the cells. Also background-, border colour and style, font family and size are set by in-line <acronym>CSS</acronym>.</p>
<table style="border: 1px solid #880000; background-color: #BBCCDD; font-family: Mono; font-size: 7pt; " class="tallcells">
<tbody><tr><td>Row 1</td><td>This is data</td><td>This is data</td></tr>
<tr><td>Row 2</td><td><p>This is data p</p></td><td><p>More data</p></td></tr>
<tr><td><p>Row 3</p></td><td><p>This is long data</p></td><td>This is data</td></tr>
</tbody></table>


<h4>Tables<bookmark content="Table styles" level="2" /><tocentry name="Tables" content="Table styles" level="0" /><indexentry content="Table:styles"  /></h4>
<p>The style sheet used for these examples shows some of the table styles I use on my website. The property \'topntail\' defined by a border-type definition e.g. "1px solid #880000" puts a border at the top and bottom of the table, and also below a header row (thead) if defined. Note also that &lt;thead&gt; will automatically turn on the header-repeat i.e. reproduce the header row at the top of each page.</p>
<p>bpmTopic Class</p>
<table class="bpmTopic"><thead></thead><tbody>
<tr>
<td>Row 1</td>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr>
<td>Row 2</td>
<td>
<p>This is data p</p>
</td>
<td>
<p>More data</p>
</td>
</tr>
<tr>
<td>
<p>Row 3</p>
</td>
<td>
<p>This is long data</p>
</td>
<td>This is data</td>
</tr>
<tr>
<td>
<p>Row 4 &lt;td&gt; cell</p>
</td>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr>
<td>Row 5</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 6</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 7</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 8</td>
<td>Also data</td>
<td>Also data</td>
</tr>
</tbody></table>

<p>&nbsp;</p>

<p>bpmTopic<b>C</b> Class (centered) Odd and Even rows</p>
<table class="bpmTopicC"><thead>
<tr class="headerrow"><th>Col/Row Header</th>
<td>
<p>Second column header p</p>
</td>
<td>Third column header</td>
</tr>
</thead><tbody>
<tr class="oddrow"><th>Row header 1</th>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>Row header 2</th>
<td>
<p>This is data p</p>
</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr class="oddrow"><th>
<p>Row header 3</p>
</th>
<td>
<p>This is long data</p>
</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>
<p>Row header 4</p>
<p>&lt;th&gt; cell acting as header</p>
</th>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr class="oddrow"><th>Row header 5</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="evenrow"><th>Row header 6</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="oddrow"><th>Row header 7</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="evenrow"><th>Row header 8</th>
<td>Also data</td>
<td>Also data</td>
</tr>
</tbody></table>

<p>&nbsp;</p>

<p>bpmTopnTail Class </p>
<table class="bpmTopnTail"><thead></thead><tbody>
<tr>
<td>Row 1</td>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr>
<td>Row 2</td>
<td>
<p>This is data p</p>
</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr>
<td>
<p>Row 3</p>
</td>
<td>
<p>This is long data</p>
</td>
<td>This is data</td>
</tr>
<tr>
<td>
<p>Row 4 &lt;td&gt; cell</p>
</td>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr>
<td>Row 5</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 6</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 7</td>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr>
<td>Row 8</td>
<td>Also data</td>
<td>Also data</td>
</tr>
</tbody></table>
<p>&nbsp;</p>
<p>bpmTopnTail<b>C</b> Class (centered) Odd and Even rows</p>
<table class="bpmTopnTailC"><thead>
<tr class="headerrow"><th>Col/Row Header</th>
<td>
<p>Second column header p</p>
</td>
<td>Third column header</td>
</tr>
</thead><tbody>
<tr class="oddrow"><th>Row header 1</th>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>Row header 2</th>
<td>
<p>This is data p</p>
</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr class="oddrow"><th>
<p>Row header 3</p>
</th>
<td>
<p>This is long data</p>
</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>
<p>Row header 4</p>
<p>&lt;th&gt; cell acting as header</p>
</th>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr class="oddrow"><th>Row header 5</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="evenrow"><th>Row header 6</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="oddrow"><th>Row header 7</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr class="evenrow"><th>Row header 8</th>
<td>Also data</td>
<td>Also data</td>
</tr>
</tbody></table>

<p>&nbsp;</p>

<p>TopnTail Class</p>
<table class="bpmTopnTail"><thead>
<tr class="headerrow"><th>Col and Row Header</th>
<td>
<p>Second</p>
<p>column</p>
</td>
<td class="pmhTopRight">Top right align</td>
</tr>
</thead><tbody>
<tr class="oddrow"><th>
<p>Row header 1 p</p>
</th>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>Row header 2</th>
<td class="pmhBottomRight"><b><i>Bottom right align</i></b></td>
<td>
<p>This is data. Can use</p>
<p><b>bold</b> <i>italic </i><sub>sub</sub> or <sup>sup</sup> text</p>
</td>
</tr>
<tr class="oddrow"><th class="pmhBottomRight">
<p>Bottom right align</p>
</th>
<td class="pmhMiddleCenter" style="border: #000000 1px solid">
<p>This is data. This cell</p>
<p>uses Cell Styles to set</p>
<p>the borders.</p>
<p>All borders are collapsible</p>
<p>in mPDF.</p>
</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>Row header 4</th>
<td>
<p>This is data p</p>
</td>
<td>More data</td>
</tr>
<tr class="oddrow"><th>Row header 5</th>
<td colspan="2" class="pmhTopCenter">Also data merged and centered</td>
</tr>
</tbody></table>

<p>&nbsp;</p>

<h4>Lists in a Table<bookmark content="Lists in a table" level="2" /><tocentry name="Tables" content="Lists in a table" level="0" /><indexentry content="Table:lists inside"  /></h4>
<table class="bpmTopnTail"><thead>
<tr class="headerrow"><th>Col and Row Header</th>
<td>
<p>Second</p>
<p>column</p>
</td>
<td class="pmhTopRight">Top right align</td>
</tr>
</thead><tbody>
<tr class="oddrow"><th>
<p>Row header 1 p</p>
</th>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr class="evenrow"><th>Row header 2</th>
<td>
<ol>
<li>Item 1</li>
<li>Item 2
<ol type="a">
<li>Subitem of ordered list</li>
<li>Subitem 2
<ol type="i">
<li>Level 3 subitem</li>
<li>Level 3 subitem</li>
</ol>
</li>
</ol>
</li>
<li>Item 3</li>
<li>Another Item</li>
<li>Subitem
<ol>
<li>Level 3 subitem</li>
</ol>
</li>
<li>Another Item</li>
</ol>
</td>
<td>
Unordered list:
<ul>
<li>Item 1</li>
<li>Item 2
<ul>
<li>Subitem of unordered list</li>
<li>Subitem 2
<ul>
<li>Level 3 subitem</li>
<li>Level 3 subitem</li>
<li>Level 3 subitem</li>
</ul>
</li>
</ul>
</li>
<li>Item 3</li>
</ul>
</td>
</tr>
</tbody></table>
<p>&nbsp;</p>


<h4>Automatic Column Width<bookmark content="Automatic Column Width" level="2" /><tocentry name="Tables" content="Automatic column width" level="0" /><indexentry content="Table:automatic column width"  /></h4>
<table class="bpmTopnTail"><tbody>
<tr>
<td>Causes</td>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Ut magna ipsum, tempus in, condimentum at, rutrum et, nisl. Vestibulum interdum luctus sapien. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Maecenas consectetuer eros quis massa. Mauris semper velit vehicula purus. Duis lacus. Aenean pretium consectetuer mauris. Ut purus sem, consequat ut, fermentum sit amet, ornare sit amet, ipsum. Donec non nunc. Maecenas fringilla. Curabitur libero. In dui massa, malesuada sit amet, hendrerit vitae, viverra nec, tortor. Donec varius. Ut ut dolor et tellus adipiscing adipiscing.</td>
</tr>
</tbody></table>


<h4>Column span<bookmark content="Column span" level="2" /><tocentry name="Tables" content="Column span" level="0" /><indexentry content="Table:column span"  /></h4>
<table class="bpmTopnTail"><tbody>
<tr>
<td>Causes</td>
<td colspan="2">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. <br />
Ut a eros at ligula vehicula pretium; maecenas feugiat pede vel risus.<br />
Suspendisse potenti</td>
</tr>
<tr>
<td>Mechanisms</td>
<td>Fusce eleifend neque sit amet erat.<br />
Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus.</td>
<td>Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla.<br />
Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien.</td>
</tr>
</tbody></table>



<h4>Header & Footer Rows<bookmark content="Header Rows" level="2" /><tocentry name="Tables" content="Header rows" level="0" /><indexentry content="Table:header rows"  /></h4>
<p>A table using a header or footer row should repeat the header/footer row across pages:</p>
<p>bpmTopic<b>C</b> Class</p>
<table class="bpmTopicC">
<thead>
<tr class="headerrow"><th>Col and Row Header</th>
<td>
<p>Second column header</p>
</td>
<td>Third column header</td>
</tr>
</thead>
<tfoot>
<tr class="headerrow"><th>Col and Row Footer</th>
<td>
<p>Second column footer</p>
</td>
<td>Third column footer</td>
</tr>
</tfoot>
<tbody>
<tr><th>Row header 1</th>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr><th>Row header 2</th>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr><th>
<p>Row header 3</p>
</th>
<td>
<p>This is data</p>
</td>
<td>This is data</td>
</tr>
<tr><th>Row header 4</th>
<td>This is data</td>
<td>
<p>This is data</p>
</td>
</tr>
<tr><th>Row header 5</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr><th>Row header 6</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr><th>Row header 7</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr><th>Row header 8</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr><th>Row header 9</th>
<td>Also data</td>
<td>Also data</td>
</tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
<tr><th>Another Row header</th><td>Also data</td><td>Also data</td></tr>
</tbody></table>
<p>&nbsp;</p>

<h4>Autosizing Tables<bookmark content="Autosizing Tables" level="2" /><tocentry name="Tables" content="Autosizing tables" level="0" /><indexentry content="Table:autosizing"  /></h4>
<p>Periodic Table of elements. Tables are set by default to reduce font size if complete words will not fit inside each cell, to a maximum of 1/1.4 * the set font-size. This value can be changed by setting $mpdf->shrink_tables_to_fit=1.8 or using html attribute &lt;table autosize="1.8"&gt;.</p>

<h5>Periodic Table</h5>

<table style="border:1px solid #000000;" cellPadding="14"><thead>
<tr><th>1A</th><th>2A</th><th>3B</th><th>4B</th><th>5B</th><th>6B</th><th>7B</th><th>8B</th><th>8B</th><th>8B</th><th>1B</th><th>2B</th><th>3A</th><th>4A</th><th>5A</th><th>6A</th><th>7A</th><th>8A</th></tr></thead><tbody>
<tr>
<td colspan="18"></td>
</tr>
<tr>
<td>H </td><td colspan="16"></td><td>He </td>
</tr>
<tr>
<td>Li </td><td>Be </td><td colspan="10"></td><td>B </td><td>C </td><td>N </td><td>O </td><td>F </td><td>Ne </td>
</tr>
<tr>
<td>Na </td><td>Mg </td><td colspan="10"></td><td>Al </td><td>Si </td><td>P </td><td>S </td><td>Cl </td><td>Ar </td>
</tr>
<tr>
<td>K </td><td>Ca </td><td>Sc </td><td>Ti </td><td>V </td><td>Cr </td><td>Mn </td><td>Fe </td><td>Co </td><td>Ni </td>
<td>Cu </td><td>Zn </td><td>Ga </td><td>Ge </td><td>As </td><td>Se </td><td>Br </td><td>Kr </td>
</tr>
<tr>
<td>Rb </td><td>Sr </td><td>Y </td><td>Zr </td><td>Nb </td><td>Mo </td><td>Tc </td><td>Ru </td><td>Rh </td><td>Pd </td><td>Ag </td><td>Cd </td>
<td>In </td><td>Sn </td><td>Sb </td><td>Te </td><td>I </td><td>Xe </td>
</tr>
<tr>
<td>Cs </td><td>Ba </td><td>La </td><td>Hf </td><td>Ta </td><td>W </td><td>Re </td><td>Os </td><td>Ir </td><td>Pt </td><td>Au </td>
<td>Hg </td><td>Tl </td><td>Pb </td><td>Bi </td><td>Po </td><td>At </td><td>Rn </td>
</tr>
<tr>
<td>Fr </td><td>Ra </td><td>Ac </td><td colspan="15"></td>
</tr>
<tr>
<td colspan="18"></td></tr>
<tr>
<td colspan="3"></td><td>Ce </td><td>Pr </td><td>Nd </td><td>Pm </td><td>Sm </td><td>Eu </td><td>Gd </td><td>Tb </td>
<td>Dy </td><td>Ho </td><td>Er </td><td>Tm </td><td>Yb </td><td>Lu </td><td></td>
</tr>
<tr>
<td colspan="3"></td><td>Th </td><td>Pa </td><td>U </td><td>Np </td><td>Pu </td><td>Am </td><td>Cm </td><td>Bk </td><td>Cf </td>
<td>Es </td><td>Fm </td><td>Md </td><td>No </td><td>Lr </td><td></td>
</tr>
</tbody></table>

<pagebreak />

<h4>Rotated Tables<bookmark content="Rotated Tables" level="2" /><tocentry name="Tables" content="Rotated table" level="0" /><indexentry content="Table:rotated"  /></h4>
<p>This is set to rotate -90 degrees (counterclockwise).</p>

<h5>Periodic Table</h5>
<p>
<table rotate="-90" class="bpmClearC"><thead>
<tr><th>1A</th><th>2A</th><th>3B</th><th>4B</th><th>5B</th><th>6B</th><th>7B</th><th>8B</th><th>8B</th><th>8B</th><th>1B</th><th>2B</th><th>3A</th><th>4A</th><th>5A</th><th>6A</th><th>7A</th><th>8A</th></tr></thead><tbody>
<tr>
<td></td>
<td colspan="18"></td>
</tr>
<tr>
<td>H </td><td colspan="15"></td><td></td><td>He </td>
</tr>
<tr>
<td>Li </td><td>Be </td><td colspan="10"></td><td>B </td><td>C </td><td>N </td><td>O </td><td>F </td><td>Ne </td>
</tr>
<tr>
<td>Na </td><td>Mg </td><td colspan="10"></td><td>Al </td><td>Si </td><td>P </td><td>S </td><td>Cl </td><td>Ar </td>
</tr>
<tr>
<td>K </td><td>Ca </td><td>Sc </td><td>Ti </td><td>V </td><td>Cr </td><td>Mn </td><td>Fe </td><td>Co </td><td>Ni </td><td>Cu </td>
<td>Zn </td><td>Ga </td><td>Ge </td><td>As </td><td>Se </td><td>Br </td><td>Kr </td>
</tr>
<tr>
<td>Rb </td><td>Sr </td><td>Y </td><td>Zr </td><td>Nb </td><td>Mo </td><td>Tc </td><td>Ru </td><td>Rh </td><td>Pd </td>
<td>Ag </td><td>Cd </td><td>In </td><td>Sn </td><td>Sb </td><td>Te </td><td>I </td><td>Xe </td>
</tr>
<tr>
<td>Cs </td><td>Ba </td><td>La </td><td>Hf </td><td>Ta </td><td>W </td><td>Re </td><td>Os </td><td>Ir </td><td>Pt </td><td>Au </td>
<td>Hg </td><td>Tl </td><td>Pb </td><td>Bi </td><td>Po </td><td>At </td><td>Rn </td>
</tr>
<tr>
<td>Fr </td><td>Ra </td><td>Ac </td>
</tr>
<tr>
<td></td>
<td colspan="18"></td>
</tr>
<tr>
<td colspan="3"></td><td>Ce </td><td>Pr </td><td>Nd </td><td>Pm </td><td>Sm </td><td>Eu </td><td>Gd </td><td>Tb </td><td>Dy </td>
<td>Ho </td><td>Er </td><td>Tm </td><td>Yb </td><td>Lu </td><td></td>
</tr>
<tr>
<td colspan="3"></td><td>Th </td><td>Pa </td><td>U </td><td>Np </td><td>Pu </td><td>Am </td><td>Cm </td><td>Bk </td>
<td>Cf </td><td>Es </td><td>Fm </td><td>Md </td><td>No </td><td>Lr </td><td></td>
</tr>
</tbody></table>
<p>&nbsp;</p>

<pagebreak />
<h4>Rotated text in Tables<bookmark content="Rotated text in Tables" level="2" /><tocentry name="Tables" content="Rotated text in table" level="0" /><indexentry content="Table:rotated text"  /></h4>

<h5>Periodic Table</h5>
<table>
<thead>
<tr text-rotate="45">
<th><p>Element type 1A</p><p>Second line</p><th><p>Element type longer 2A</p></th>
<th>Element type 3B</th><th>Element type 4B</th><th>Element type 5B</th><th>Element type 6B</th><th>7B</th><th>8B</th>
<th>Element type 8B R</th><th>8B</th><th>Element <span>type</span> 1B</th><th>2B</th>
<th>Element type 3A</th><th>Element type 4A</th><th>Element type 5A</th><th>Element type 6A</th><th>7A</th><th>Element type 8A</th>
</tr>
</thead>

<tbody>
<tr>
<td>H</td><td colspan="15"></td><td></td><td>He </td>
</tr>
<tr>
<td>Li </td><td>Be </td><td colspan="10"></td><td>B </td><td>C </td><td>N </td><td>O </td><td>F </td><td>Ne </td>
</tr>
<tr>
<td>Na </td><td>Mg </td><td colspan="10"></td><td>Al </td><td>Si </td><td>P </td><td>S </td><td>Cl </td><td>Ar </td>
</tr>
<tr style="text-rotate: 45">
<td>K </td><td>Ca </td><td>Sc </td><td>Ti</td><td>Va</td><td>Cr</td><td>Mn</td><td>Fe</td><td>Co</td><td>Ni </td>
<td>Cu </td><td>Zn </td><td>Ga </td><td>Ge </td><td>As </td><td>Se </td><td>Br </td><td>Kr </td>
</tr>
<tr>
<td>Rb </td><td>Sr </td><td>Y </td><td>Zr </td><td>Nb </td><td>Mo </td><td>Tc </td><td>Ru </td>
<td style="text-align:right; ">Rh</td><td>Pd </td><td>Ag </td><td>Cd </td><td>In </td><td>Sn </td>
<td>Sb </td><td>Te </td><td>I </td><td>Xe </td>
</tr>
<tr>
<td>Cs </td><td>Ba </td><td>La </td><td>Hf </td><td>Ta </td><td>W </td><td>Re </td><td>Os </td><td>Ir </td>
<td>Pt </td><td>Au </td><td>Hg </td><td>Tl </td><td>Pb </td><td>Bi </td><td>Po </td><td>At </td><td>Rn </td>
</tr>
<tr>
<td>Fr </td><td>Ra </td><td colspan="16">Ac </td>
</tr>
<tr>
<td colspan="3"></td>
<td>Ce </td><td>Pr </td><td>Nd </td><td>Pm </td><td>Sm </td><td>Eu </td><td>Gd </td><td>Tb </td><td>Dy </td>
<td>Ho </td><td>Er </td><td>Tm </td><td>Yb </td><td>Lu </td><td></td>
</tr>
<tr>
<td colspan="3"></td>
<td>Th </td><td>Pa </td><td>U </td><td>Np </td><td>Pu </td><td>Am </td><td>Cm </td><td>Bk </td><td>Cf </td><td>Es </td>
<td>Fm </td><td>Md </td><td>No </td><td>Lr </td><td></td>
</tr>
</tbody></table>


<pagebreak />



<h4>Nested Tables<bookmark content="Nested Tables" level="2" /><tocentry name="Tables" content="Nested tables" level="0" /><indexentry content="Table:nested"  /></h4>

<div style="border: 2px solid #000088; background-color: #DDDDFF; padding: 2mm;">
Text before table

<div style="border: 2px solid #008888; background-color: #DCAFCF; padding: 2mm;">

<table cellSpacing="2" rotate="-90" align="center" autosize="1.5" class="nested" style="page-break-inside: avoid; ">
<tbody>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>
<table cellSpacing="2" class="nested">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id <a href="http://www.dummy.com">euismod auctor</a>, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>
<table cellSpacing="2" class="nested">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>
<table cellSpacing="2" class="nested">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>
<table cellSpacing="2" class="nested">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td></td>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
</tr>
</tbody></table>

</div>



<p>Text before table</p>

<table cellSpacing="2" class="outer2" autosize="3" style="page-break-inside:avoid">
<tbody>
<tr>
<td>Row 1</td>
<td>This is data</td>
<td style="text-align: right;">
Text before table
<table cellSpacing="2" class="inner" width="80%">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>C3</td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
<p>Text after table</p>
</td>
<td>This is data</td>
</tr>
<tr>
<td>Row 2</td>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
</tr>
<tr>
<td>Row 3</td>
<td style="text-align: center; vertical-align: middle;">
<table cellSpacing="2" class="inner" width="80%">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td style="text-align:center;"><img src="sunset.jpg" width="84" style="border:3px solid #44FF44; vertical-align:top; " /></td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>
<table cellSpacing="2">
<tbody>
<tr>
<td>F1</td>
<td>F2</td>
</tr>
<tr>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec et nulla. Sed quis orci.</td>
<td>G2</td>
</tr>
</tbody></table>
</td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td style="vertical-align: bottom; ">
<table cellSpacing="2" class="inner" align="right">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td>B3</td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>C3</td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
<tr>
<td>Row 4</td>
<td>This is data</td>
<td><table cellSpacing="2" class="inner">
<tbody>
<tr>
<td>Row A</td>
<td>A2</td>
<td>A3</td>
<td>A4</td>
</tr>
<tr>
<td>Row B</td>
<td>B2</td>
<td style="text-align:center;"><img src="sunset.jpg" width="84" style="border:3px solid #44FF44; vertical-align:top; " /></td>
<td>B4</td>
</tr>
<tr>
<td>Row C</td>
<td>C2</td>
<td>
<table cellSpacing="2">
<tbody>
<tr>
<td>F1</td>
<td>F2</td>
</tr>
<tr>
<td>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec et nulla. Sed quis orci.</td>
<td>G2</td>
</tr>
</tbody></table>
</td>
<td>C4</td>
</tr>
<tr>
<td>Row D</td>
<td>D2</td>
<td>D3</td>
<td>D4</td>
</tr>
</tbody></table>
</td>
<td>This is data</td>
</tr>
</tbody></table>


</div>


<!-- FORMS -->
<pagebreak />
<h3>Forms<bookmark content="Forms" level="1" /><tocentry name="" content="Forms" level="0" /><indexentry content="Forms" /></h3>
<form>
<b>Textarea</b>
<textarea name="authors" rows="5" cols="80" wrap="virtual">Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra.
Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. </textarea>
<br /><br />
<b>Select</b>
<select size="1" name="status"><option value="A">Active</option><option value="W" >New item from auto_manager: pending validation</option><option value="I" selected="selected">Incomplete record - pending</option><option value="X" >Flagged for Deletion</option> </select> followed by text
<br /><br />
<b>Input Radio</b>
<input type="radio" name="recommended" value="0" > No &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="1" > Keep &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="recommended" value="2"  checked="checked" > Choice 
<br /><br />
<b>Input Text</b>
<input type="text" size="190" name="doi" value="10.1258/jrsm.100.5.211"> 
<br /><br />
<b>Input Password</b>
<input type="password" size="40" name="password" value="secret"> 
<br /><br />
<input type="checkbox" name="QPC" value="ON" > Checkboxes<br>
<input type="checkbox" name="QPA" value="ON" > Not selected<br>
<input type="checkbox" name="QPA" value="ON" disabled="disabled"> Disabled<br>
<input type="checkbox" name="QLY" value="ON" checked="checked" > Selected
<br /><br />
<input type="submit" name="submit" value="Submit" /> 
<input type="image" name="submit" src="goto.gif" /> 
<input type="button" name="submit" value="Button" />
<input type="reset" name="submit" value="Reset" />
<br /><br />
</form>


<!-- ANNOTATIONS -->
<pagebreak />
<h3>Annotations<bookmark content="Annotations" level="1" /><tocentry name="" content="Annotations" level="0" /><indexentry content="Annotations"  /></h3>
<p>Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis metus aliquet vulputate.<annotation content="This is an annotation'."\n".'in the middle of the text" subject="My Subject" icon="Comment" color="#FE88EF" author="Ian Back" /> Donec luctus. Cras euismod tellus vel leo. Cras tellus. Fusce aliquet. Curabitur tincidunt viverra ligula. Fusce eget erat. Donec pede. Vestibulum id felis. Phasellus tincidunt ligula non pede. Morbi turpis. In vitae dui non erat placerat malesuada. Mauris adipiscing congue ante. Proin at erat. Aliquam mattis. </p>
<p>P: Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. <i>Fusce</i><annotation content="Fusce is a funny word!" subject="Idle Comments" icon="Note" author="Ian Back" pos-x="198" /> eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p>


<!-- GRAPH -->
<pagebreak />
<h3>Graphs<bookmark content="Graphs" level="1" /><tocentry name="" content="Graphs" level="0" /><indexentry content="Graphs"  /></h3>
<table id="tbl_1" class="sub"><tbody><tr><td></td><td align="right"><b>Female</b></td><td align="right"><b>Male</b></td></tr><tr><td>35 - 44</td><td align="right"><b>4</b></td><td align="right"><b>2</b></td></tr><tr><td>45 - 54</td><td align="right"><b>5</b></td><td align="right"><b>7</b></td></tr><tr><td>55 - 64</td><td align="right"><b>21</b></td><td align="right"><b>18</b></td></tr><tr><td>65 - 74</td><td align="right"><b>11</b></td><td align="right"><b>14</b></td></tr><tr><td>75 - 84</td><td align="right"><b>10</b></td><td align="right"><b>10</b></td></tr><tr><td>85 - 94</td><td align="right"><b>2</b></td><td align="right"><b>1</b></td></tr><tr><td>95 - 104</td><td align="right"><b>1</b></td><td align="right"><b></b></td></tr>
<tr><td>TOTAL</td><td align="right">54</td><td align="right">52</td></tr>
</tbody></table>

<h5>Subscriptions for 2008-09<tocentry name="Figures" content="Graph: Subscriptions for 2008-09" /></h5>
<jpgraph table="tbl_1" type="bar" stacked="0" dpi="300" title="New subscriptions" splines="1" bandw="0" antialias="1" label-y="% patients" label-x="Age group" axis-x="text" axis-y="lin" percent="0"  series="cols" data-col-begin="2" data-row-begin="2" data-col-end="0" data-row-end="-1" show-values="1" width="600" legend-overlap="1" hide-grid="1" hide-y-axis="1" />



<!-- FULL IMAGES & BARCODE -->
<pagebreak />
<h3>Full Images & Barcode<bookmark content="Full Images &amp; Barcode" level="1" /><tocentry name="Figures" content="Full size image & Barcode" level="0" /><indexentry content="Image:full-size"  /><tocentry name="" content="Barcode" level="0" /><indexentry content="Barcode"  /></h3>
<p>On the first and last page of this document, an image is reproduced full page size by placing it inside a DIV element with CSS "position:absolute". In all other situations, images are constrained to the width and height of the printable page (i.e. inside the margins). The image on the back page has CSS "opacity:0.5".</p>


<!-- EXAMPLE PHP CODE -->
<div class="phpcode">'. nl2br(htmlspecialchars('/* ALTERNATIVE PHP METHOD */
$mpdf->SetAlpha(0.5); 
$mpdf->Image(\'clematis.jpg\',0,0,210,297,\'jpg\',\'\',true, false);
// the last "false" allows a full page picture
$mpdf->SetAlpha(1);
')) .'</div>
<!-- END EXAMPLE PHP CODE -->

<p>The back cover also has an ISBN barcode</p>

<!-- EXAMPLE PHP CODE -->
<div class="phpcode">'. nl2br(htmlspecialchars('/* ALTERNATIVE PHP METHOD */
$mpdf->writeBarcode(\'978-0-9542246-0-8\', 1, 130, 230, 1,0, 3,3,4,4);	
')) .'</div>
<!-- END EXAMPLE PHP CODE -->

<p>But next is inserted the Index, which can also be done like this:</p>

<!-- EXAMPLE PHP CODE -->
<div class="phpcode">'. nl2br(htmlspecialchars('/* ALTERNATIVE PHP METHOD */
$mpdf->AddPage(\'\',NEXT-ODD\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',\'\',-1,-1,-1,-1);	
$mpdf->WriteHTML(\'<h2>Index<bookmark content="Index" /></h2>\');
$mpdf->WriteHTML(\'<indexinsert cols="2" font="serif" div-font="sans-serif" links="on" />\');
')) .'</div>
<!-- END EXAMPLE PHP CODE -->



<!-- INDEX -->
<pagebreak type="NEXT-ODD" odd-header-value="-1" even-header-value="-1" odd-footer-value="-1" even-footer-value="-1"  />
<h2>Index<bookmark content="Index" /></h2>
<indexinsert cols="2" font="serif" div-font="sans-serif" links="on" />



<!-- BACK COVER & BARCODE -->
<pagebreak type="NEXT-EVEN" />
<div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;">
<img src="clematis.jpg" style="width: 210mm; height: 297mm; margin: 0; opacity: 0.5;" />
</div>

<div style="position: absolute; right: 35mm; bottom: 35mm; ">
<barcode code="978-0-9542246-0" type="ISBN" style="padding: 2.5mm; border: 0.1mm solid #000000;" height="0.66" text="1" />
</div>



</body></html>';

//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");

$mpdf=new mPDF('s','A4','','',25,15,21,22,10,10); 
$mpdf->progbar_altHTML = '<html><body>
	<div style="margin-top: 5em; text-align: center; font-family: Verdana; font-size: 12px;"><img style="vertical-align: middle" src="loading.gif" /> Creating PDF file. Please wait...</div>';
$mpdf->StartProgressBarOutput();

$mpdf->mirrorMargins = 1;
$mpdf->SetDisplayMode('fullpage','two');
$mpdf->useGraphs = true;
$mpdf->list_number_suffix = ')';
$mpdf->hyphenate = true;

$mpdf->debug  = true;

$mpdf->WriteHTML($html);

$mpdf->Output();

exit;
//==============================================================
//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>