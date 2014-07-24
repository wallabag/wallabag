<?php

ini_set("memory_limit","64M");

include("../mpdf.php");

$mpdf=new mPDF(''); 


//==============================================================

$html = '
<style>
.gradient {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
	box-shadow: 0.3em 0.3em #888888;
}
.rounded {
	border:0.1mm solid #220044; 
	background-color: #f0f2ff;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
	border-radius: 2mm;
	background-clip: border-box;
}
h4 {
	font-weight: bold;
	margin-top: 1em;
	margin-bottom: 0.3em;
	margin-top: 0;
}
div.text {
	padding:0.8em; 
	margin-bottom: 0.7em;
}
p { margin: 0.25em 0; }
.code {
	font-family: monospace;
	font-size: 9pt;
	background-color: #d5d5d5; 
	margin: 1em 1cm;
	padding: 0 0.3cm;
	border:0.2mm solid #000088; 
	box-shadow: 0.3em 0.3em #888888;
}
table {
	border:1px solid #000000;
	font-family: sans-serif;
	font-size: 10pt;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
}
td, th {
	border:1px solid #000000;
	text-align: left;
	font-weight: normal;
}
.shadowtitle { 
	height: 8mm; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.8em; 
	padding-left: 3em;
	font-family:sans;
	font-size: 26pt; 
	font-weight: bold;
	border: 0.2mm solid white;
	border-radius: 0.2em;
	box-shadow: 0 0 2em 0.5em rgba(0,0,255,0.9);
	color: #AAAACC;
	text-shadow: 0.03em 0.03em #666, 0.05em 0.05em rgba(127,127,127,0.5), -0.015em -0.015em white;
}
h3 { 
	margin: 3em 0 2em -15mm; 
	background-color: #EEDDFF; 
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;  
	padding: 0.5em; 
	padding-left: 3em;
	width: 50%;
	font-family:sans;
	font-size: 16pt; 
	font-weight: bold;
	border-left: none;
	border-radius: 0 2em 2em 0;
	box-shadow: 0 0 2em 0.5em rgba(255,0,0,1);
	text-shadow: 0.05em 0.04em rgba(127,127,127,0.5);
}
.css {
	font-family: arial;
	font-style: italic;
	color: #000088;
}
table.pop {
	border-collapse: collapse;
}
table.pop td {
	font-family: arial;
	font-size: 10px;
	border: 1px solid #888888;
}
meter.pop  {
	margin: 3px;
}
fieldset { border: 1px solid #000000; border-radius: 5px; padding: 10px; }
div.folder { 
	background: url(data:image/gif;base64,R0lGODlhEAAOALMAAOazToeHh0tLS/7LZv/0jvb29t/f3//Ub//ge8WSLf/rhf/3kdbW1mxsbP//mf///yH5BAAAAAAALAAAAAAQAA4AAARe8L1Ekyky67QZ1hLnjM5UUde0ECwLJoExKcppV0aCcGCmTIHEIUEqjgaORCMxIC6e0CcguWw6aFjsVMkkIr7g77ZKPJjPZqIyd7sJAgVGoEGv2xsBxqNgYPj/gAwXEQA7) no-repeat 4px center;
	padding: 5px 0 5px 25px;
	border: 1px solid #000000;
}
</style>
<body>


<div class="shadowtitle">New Features in mPDF v5.6</div>

<h3>HTML5 tags</h3>
<div class="gradient text">
<p>New tags introduced in HTML5 now have basic support in mPDF, and will thus support CSS style references.</p>
<p>The following are treated as block elements similar to &lt;div&gt;:</p>
<p class="code">&lt;article&gt; &lt;aside&gt; &lt;details&gt; &lt;figure&gt; &lt;figcaption&gt; &lt;footer&gt; &lt;header&gt; &lt;hgroup&gt; &lt;nav&gt; &lt;section&gt; &lt;summary&gt; </p>
<p>The following are treated as in-line elements:</p>
<p class="code">&lt;time&gt; &lt;mark&gt;</p>
<p>Mark is set by default to highlight in yellow in config.php using $defaultCSS e.g. <mark>mark</mark></p>
<p>Progress and meter are discussed below:</p>
</div>

<h3>&lt;progress&gt;</h3>
<div class="gradient text">
<p>Progress: accepts the attributes value and max. A progress element without a value is called an indeterminate progress bar.
Text between the opening and closing tags is not displayed.</p>
<p>CSS styles properties can be applied: display, visibility, margin, padding, border, vertical-align, width, height and opacity.
HTML attributes width and height are supported, although not officially part of the spec.</p>
<p>Example:</p>
<p class="code">&lt;progress value="5" max="10"&gt;50%&lt;/progress&gt;</p>

       <ul class="compact">
            <li>
              <label>Indeterminate</label>
              <progress max="100"></progress>
            </li>
            <li>
              <label>Progress: 0%</label>
              <progress max="10" value="0"></progress>
            </li>
            <li>
              <label>Progress: 100%</label>
              <progress max="3254" value="3254"></progress>
            </li>
            <li>
              <label>Progress: 57%</label>
              <progress max="0.7" value="0.4"></progress>
            </li>
          </ul>
</div>

<h3>&lt;meter&gt;</h3>
<div class="gradient text">
<p>Meter: accepts the attributes min, max, value, optimum, low, and high.
Text between the opening and closing tags is not displayed.</p>
<p>CSS styles properties can be applied: display, visibility, margin, padding, border, vertical-align, width, height and opacity.
HTML attributes width and height are supported, although not officially part of the spec.</p>
<p>Example:</p>
<p class="code">&lt;meter value="5" max="10" min="1" low="2" high="8" optimum="5.6"&gt;5&lt;/meter&gt;</p>



          <ul class="compact">
            <li>
              <label>Meter: full</label>
              <meter value="1"></meter>
            </li>
            <li>
              <label>Preferred usage</label>
              <meter min="1024" max="10240" low="2048" high="8192" value="1824" optimum="1024"></meter>
            </li>
            <li>
              <label>Too much traffic</label>
              <meter min="1024" max="10240" low="2048" high="8192" value="6216" optimum="1024"></meter>
            </li>
            <li>
              <label>Much too much traffic</label>
              <meter min="1024" max="10240" low="2048" high="8192" value="9216" optimum="1024"></meter>
            </li>
          </ul>
</div>

<div class="gradient text">
<h4>Custom appearances for &lt;meter&gt; and &lt;progress&gt;</h4>
<p>Meter (and to a lesser extent progress) can be used with custom appearances e.g. by using optimum to display the average, and low/high to indicate 90th centiles</p>

<p>Custom appearances can be written by editing the script in classes/meter.php  - Use a custom attribute of type="anyname" which is passed to the class as a variable e.g.</p>
<p class="code">&lt;meter type="2" value="612.7" optimum="580.4" min="517.0 " max="642.7" low="542" high="600"&gt;612.7&lt;/meter&gt;</p>
</div>


<table class="pop">
<tbody>
<tr>
<td><p><b>Domain</b></p></td>
<td><p><b>Indicator</b></p></td>
<td><p><b>LHB</b></p><p><b>number</b></p></td>
<td><p><b>LHB</b></p><p><b>Indicator</b></p>
<p><b>value</b></p>
</td>
<td><p><b>Wales</b></p><p><b>average</b></p>
</td>
<td><p><b>Wales range</b></p></td>
<td><p><b>Comparison</b></p></td>
</tr>

<tr>
<td rowspan="4"><p><b>Deaths</b></p></td>
<td><p>Death Rates per 100,000 population</p></td>
<td><p>3046</p></td>
<td><p><b>612.7</b><b></b></p></td>
<td><p>580.4</p></td>
<td><p>517.0 - 642.7</p></td>
<td><meter class="pop" type="2" value="612.7" optimum="580.4" min="517.0 " max="642.7" low="542" high="600">612.7</meter></td>
</tr>

<tr>
<td><p>Death Rates per 100,000 from cancer</p></td>
<td><p>789</p></td>
<td><p><b>178.2</b><b></b></p></td>
<td><p>172.7</p></td>
<td><p>159.5 - 182.2</p></td>
<td><meter class="pop" type="2" value="178.2" optimum="172.7" min="159.5 " max="182.2" low="162" high="180">178.2</meter></td>
</tr>

<tr>
<td><p>Death Rates per 100,000 from respiratory disease</p></td>
<td><p>505</p></td>
<td><p><b>60.5</b><b></b></p></td>
<td><p>72.11</p></td>
<td><p>54.41 - 95.5</p></td>
<td><meter class="pop" type="2" value="60.5" optimum="72.11" min="54.41 " max="95.5" low="68" high="80">60.5</meter></td>
</tr>

<tr>
<td><p>Death Rates per 100,000 from cardiovascular disease</p></td>
<td><p>913</p></td>
<td><p><b>178.2</b><b></b></p></td>
<td><p>165.0</p></td>
<td><p>151.8 - 179.9</p></td>
<td><meter class="pop" type="2" value="160.2" optimum="165" min="151.8 " max="179.9" low="158" high="170">160.2</meter></td>
</tr>

</tbody>
</table>


<h3>Fieldset and Legend</h3>
<form>
  <fieldset>
  <legend>Fieldset and legend</legend>
<p>Support for fieldset and legend was introduced in mPDF v5.5. Consider it experimental!</p>
    <label for="name">Username:</label>
    <input type="text" name="name" id="name" />
    <br />
    <label for="mail">E-mail:</label>
    <input type="text" name="mail" id="mail" />
  </fieldset>
</form>


<h3>CSS styles</h3>
<div class="gradient text">
<h4></h4>
<p><span class="css">min-height</span>, <span class="css">min-width</span>, <span class="css">max-height</span> and <span class="css">max-width</span> are now supported in CSS style sheets for &lt;img&gt; (only).</p>
<p><span class="css">background: url(data:image/gif;base64,...)</span> is now supported in CSS style sheets (gif, png and jpeg).</p>
</div>

<div class="folder">This &lt;div&gt; has the folder icon set as an embedded image in the CSS</div>
<p class="code">div.folder { 
	background: url(data:image/gif;base64,R0lGODlhEAAOALMAAOazToeHh0tLS/7LZv/0jvb29t/f3//Ub//ge8WSLf/rhf/3kdbW1mxsbP//mf///yH5BAAAAAAALAAAAAAQAA4AAARe8L1Ekyky67QZ1hLnjM5UUde0ECwLJoExKcppV0aCcGCmTIHEIUEqjgaORCMxIC6e0CcguWw6aFjsVMkkIr7g77ZKPJjPZqIyd7sJAgVGoEGv2xsBxqNgYPj/gAwXEQA7) no-repeat 4px center;
	padding: 5px 0 5px 25px;
	border: 1px solid #000000;
}
</p>



<h3>Arabic text</h3>



<br /><br />

<div class="gradient text">

<p>The script handling Arabic text (RTL) was rewritten in mPDF 5.5 with improved support for Pashto/Sindhi/Urdu/Kurdish, especially for joining characters and added new presentation forms.</p>
<p>Some characters in Pashto/Sindhi/Urdu/Kurdish do not have Unicode values for the final/initial/medial forms of the characters. However, some fonts include glyphs for these characters "un-mapped" to Unicode (including XB Zar and XB Riyaz, which are bundled with mPDF).</p>
<p>By editing config_fonts.php and adding:</p> 
<p class="code">
	\'unAGlyphs\' => true,
</p>
<p>to appropriate fonts, this will force mPDF to use unmapped glyphs. It requires the font file to include a Format 2.0 POST table which references the glyphs by name as e.g. uni067C.med or uni067C.medi</p>
<p>XB Riyaz, XB Zar, Arabic Typesetting (MS), Arial (MS) all contain this table. NB If you want to know if a font file is suitable, you can open a .ttf file in a text editor and search for "uni067C.med" - if it exists, it may work!</p>
<p>Using "unAGlyphs" forces subsetting of fonts, and will not work with SIP/SMP fonts (using characters beyond the Unicode BMP Plane).</p>
<p>mPDF maps these characters to part of the Private Use Area allocated by Unicode U+F500-F7FF. This could interfere with correct use
if the font already utilises these codes (unlikely).</p>
</div>

<pagebreak />
<p>Using Arial MS font:</p>
';
//==============================================================
// Test for all Arabic characters which may need joining
//==============================================================
$mpdf->cacheTables = true;
$html .='
<style>
.script-arabic { font-family: arial; font-size: 22pt; direction: rtl; padding: 0.1em 0.5em; text-align: center; }
.joined { color: #888888; }
</style>
<div dir="ltr">
';


$ranges = array(0=>array(0x0621, 0x063a), 1=>array(0x0640, 0x064a), 2=>array(0x0671, 0x0672), 3=>array(0x0674, 0x06d3));

foreach($ranges AS $r) {
	$html .= '<table border="1" style="border-collapse: collapse">';
	$html .= '<thead><tr>';
	$html .= '<td></td>';
	$html .= '<td style="text-align:center; padding: 0 0.5em;">Isolated</td>';
	$html .= '<td></td>';
	$html .= '<td style="text-align:center; padding: 0 0.5em;">Final</td>';
	$html .= '<td style="text-align:center; padding: 0 0.5em;">Medial</td>';
	$html .= '<td style="text-align:center; padding: 0 0.5em;">Initial</td>';
	$html .= '<td></td>';
	$html .= '</tr></thead><tbody>';
	for($n=$r[0];$n<=$r[1];$n++) {

		$html .= '<tr>';
		$html .= '<td>U+0'.strtoupper(dechex($n)) .'</td>';

		$html .= '<td class="script-arabic">&#x0'.dechex($n) .';</td>';
		$html .= '<td class="script-arabic joined">&#x626;&#x0'.dechex($n) .';</td>';
		$html .= '<td class="script-arabic">&#x640;&#x0'.dechex($n) .';</td>';
		$html .= '<td class="script-arabic">&#x640;&#x0'.dechex($n) .';&#x640;</td>';
		$html .= '<td class="script-arabic">&#x0'.dechex($n) .';&#x640;</td>';
		$html .= '<td class="script-arabic joined">&#x0'.dechex($n) .';&#x647;</td>';

		$html .= '</tr>';
	}
	$html .='</tbody></table>';
$html .='<br />';
}



$html .='</div>';
//==============================================================

$html .='
<br />


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

// OUTPUT
$mpdf->Output(); exit;


//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>