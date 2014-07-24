<?php


$html = '
<h1>mPDF</h1>
<h2>Justification</h2>

<h4>Tables</h4>
<p>Text can be justified in table cells using in-line or stylesheet CSS. (Note that &lt;p&gt; tags are removed within cells along with any style definition or attributes.)</p>
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
<td style="text-align:justify;"><p>Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </p></td>
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

<h4>Testing Justification with Long Words</h4>
<p>http://www-950.ibm.com/software/globalization/icu/demo/converters?s=ALL&amp;snd=4356&amp;dnd=4356</p>
<h5>Should not split</h5>
<p>Maecenas feugiat pede vel risus. Nulla et lectus eleifend <i>verylongwordthatwontsplit</i> neque sit amet erat</p>
<p>Maecenas feugiat pede vel risus. Nulla et lectus eleifend et <i>verylongwordthatwontsplit</i> neque sit amet erat</p>

<h5>Non-breaking Space &amp;nbsp;</h5><p>The next example has a non-breaking space between <i>eleifend</i> and the very long word.</p><p>Maecenas feugiat pede vel risus. Nulla et lectus eleifend&nbsp;verylongwordthatwontsplitanywhere neque sit amet erat</p><p>Nbsp will only work in fonts that have a glyph to represent the character i.e. not in the CJK languages nor some Unicode fonts.</p>



<h4>Testing Justification with mixed Styles</h4>
<p>This is <s>strikethrough</s> in <b><s>block</s></b> and <small>small <s>strikethrough</s> in <i>small span</i></small> and <big>big <s>strikethrough</s> in big span</big> and then <u>underline</u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</p>
<p>This is a <font color="#008800">green reference<sup>32-47</sup></font> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> then <s>Strikethrough reference<sup>32-47</sup></s> and <s>strikethrough reference<sub>32-47</sub></s> and then more text.
</p> 
<p><big>Repeated in <u>BIG</u>: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</big>
</p> 
<p><small>Repeated in small: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</small> 
</p>

<p style="font-size:7pt;">This is <s>strikethrough</s> in block and <big>big <s>strikethrough</s> in big span</big> and then <u>underline</u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</p>
<p style="font-size:7pt;">This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> then <s>Strikethrough reference<sup>32-47</sup></s> and <s>strikethrough reference<sub>32-47</sub></s> then more text.
</p> 
<p></p>
<p style="font-size:7pt;">
<big>Repeated in BIG: This is reference<sup>32-47</sup> and <u>underlined reference<sup>32-47</sup></u> then reference<sub>32-47</sub> and <u>underlined reference<sub>32-47</sub></u> but out of span again but <font color="#000088">blue</font> font and <acronym>ACRONYM</acronym> text</big>
</p>
';

//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html);

// SPACING
$mpdf->WriteHTML("<h4>Spacing</h4><p>mPDF uses both letter- and word-spacing for text justification. The default is a mixture of both, set by the configurable values jSWord and jSmaxChar. (Only word spacing is used when cursive languages such as Arabic or Indic are detected.) </p>");

$mpdf->jSWord = 0;	// Proportion (/1) of space (when justifying margins) to allocate to Word vs. Character
$mpdf->jSmaxChar = 0;	// Maximum spacing to allocate to character spacing. (0 = no maximum)
$mpdf->WriteHTML("<h5>Character spacing</h5><p>Maecenas feugiat pede vel risus. Nulla et lectus eleifend <i>verylongwordthatwontsplitanywhere</i> neque sit amet erat</p>");

// Back to default settings
$mpdf->jSWord = 0.4;
$mpdf->jSmaxChar = 2;
$mpdf->WriteHTML("<h5>Word spacing</h5><p style=\"letter-spacing:0\">Maecenas feugiat pede vel risus. Nulla et lectus eleifend <i>verylongwordthatwontsplitanywhere</i> neque sit amet erat</p>");

$mpdf->WriteHTML("<h5>Mixed Character and Word spacing</h5><p>Maecenas feugiat pede vel risus. Nulla et lectus eleifend <i>verylongwordthatwontsplitanywhere</i> neque sit amet erat</p>");




$mpdf->Output();
exit;


?>