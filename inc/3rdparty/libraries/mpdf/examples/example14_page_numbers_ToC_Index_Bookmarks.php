<?php



//==============================================================
$lorem = "<p>Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </p><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin vel sem at odio varius pretium. Maecenas sed orci. Maecenas varius. Ut magna ipsum, tempus in, condimentum at, rutrum et, nisl. Vestibulum interdum luctus sapien. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Maecenas consectetuer eros quis massa. Mauris semper velit vehicula purus. Duis lacus. Aenean pretium consectetuer mauris. Ut purus sem, consequat ut, fermentum sit amet, ornare sit amet, ipsum. Donec non nunc. Maecenas fringilla. Curabitur libero. In dui massa, malesuada sit amet, hendrerit vitae, viverra nec, tortor. Donec varius. Ut ut dolor et tellus adipiscing adipiscing. </p><p>Proin aliquet lorem id felis. Curabitur vel libero at mauris nonummy tincidunt. Donec imperdiet. Vestibulum sem sem, lacinia vel, molestie et, laoreet eget, urna. Curabitur viverra faucibus pede. Morbi lobortis. Donec dapibus. Donec tempus. Ut arcu enim, rhoncus ac, venenatis eu, porttitor mollis, dui. Sed vitae risus. In elementum sem placerat dui. Nam tristique eros in nisl. Nulla cursus sapien non quam porta porttitor. Quisque dictum ipsum ornare tortor. Fusce ornare tempus enim. </p><p>Maecenas arcu justo, malesuada eu, dapibus ac, adipiscing vitae, turpis. Fusce mollis. Aliquam egestas. In purus dolor, facilisis at, fermentum nec, molestie et, metus. Vestibulum feugiat, orci at imperdiet tincidunt, mauris erat facilisis urna, sagittis ultricies dui nisl et lectus. Sed lacinia, lectus vitae dictum sodales, elit ipsum ultrices orci, non euismod arcu diam non metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In suscipit turpis vitae odio. Integer convallis dui at metus. Fusce magna. Sed sed lectus vitae enim tempor cursus. Cras eu erat vel libero sodales congue. Sed erat est, interdum nec, elementum eleifend, pretium at, nibh. Praesent massa diam, adipiscing id, mollis sed, posuere et, urna. Quisque ut leo. Aliquam interdum hendrerit tortor. Vestibulum elit. Vestibulum et arcu at diam mattis commodo. Nam ipsum sem, ultricies at, rutrum sit amet, posuere nec, velit. Sed molestie mollis dui. </p>";
//==============================================================
//==============================================================
//==============================================================


$html = '
<!-- defines the headers/footers -->

<!--mpdf

<htmlpageheader name="myHTMLHeader">
<div style="text-align: right; border-bottom: 1px solid #000000; font-family: serif; font-size: 8pt;">Odd Header</div>
</htmlpageheader>

<htmlpageheader name="myHTMLHeaderEven">
<div style="text-align: left; border-bottom: 1px solid #000000; font-family: serif; font-size: 8pt;">Even Header</div>
</htmlpageheader>

<htmlpagefooter name="myHTMLFooter">
<table width="100%" style="border-top: 1px solid #000000; vertical-align: top; font-family: sans; font-size: 8pt;"><tr>
<td width="33%">{DATE Y-m-d}</td>
<td width="33%" align="center"><span style="font-size:12pt;">{PAGENO}</span></td>
<td width="33%" style="text-align: right;">Odd Footer</td>
</tr></table>
</htmlpagefooter>

<htmlpagefooter name="myHTMLFooterEven">
<table width="100%" style="border-top: 1px solid #000000; vertical-align: top; font-family: sans; font-size: 8pt;"><tr>
<td width="33%">Even Footer</td>
<td width="33%" align="center"><span style="font-size:12pt;">{PAGENO}</span></td>
<td width="33%" style="text-align: right;">{DATE Y-m-d}</td>
</tr></table>
</htmlpagefooter>


<htmlpageheader name="tocHTMLHeader">
<div style="text-align: right; border-bottom: 1px solid #000000; font-family: serif; font-size: 8pt;">ToC Odd Header</div>
</htmlpageheader>

<htmlpageheader name="tocHTMLHeaderEven">
<div style="text-align: left; border-bottom: 1px solid #000000; font-family: serif; font-size: 8pt;">ToC Even Header</div>
</htmlpageheader>

<htmlpagefooter name="tocHTMLFooter">
<table width="100%" style="border-top: 1px solid #000000; vertical-align: top; font-family: sans; font-size: 8pt;"><tr>
<td width="33%">{DATE Y-m-d}</td>
<td width="33%" align="center"><span style="font-size:12pt;">{PAGENO}</span></td>
<td width="33%" style="text-align: right;">ToC Odd Footer</td>
</tr></table>
</htmlpagefooter>

<htmlpagefooter name="tocHTMLFooterEven">
<table width="100%" style="border-top: 1px solid #000000; vertical-align: top; font-family: sans; font-size: 8pt;"><tr>
<td width="33%">ToC Even Footer</td>
<td width="33%" align="center"><span style="font-size:12pt;">{PAGENO}</span></td>
<td width="33%" style="text-align: right;">{DATE Y-m-d}</td>
</tr></table>
</htmlpagefooter>

mpdf-->


<h1>mPDF</h1>
<h2>Table of Contents & Bookmarks</h2>

<!-- set the headers/footers - they will occur from here on in the document -->
<tocpagebreak paging="on" links="on" toc-odd-header-name="html_tocHTMLHeader" toc-even-header-name="html_tocHTMLHeaderEven" toc-odd-footer-name="html_tocHTMLFooter" toc-even-footer-name="html_tocHTMLFooterEven" toc-odd-header-value="on" toc-even-header-value="on" toc-odd-footer-value="on" toc-even-footer-value="on" toc-preHTML="&lt;h2&gt;Contents&lt;/h2&gt;" toc-bookmarkText="Content list" resetpagenum="1" pagenumstyle="A" odd-header-name="html_myHTMLHeader" odd-header-value="on" even-header-name="html_myHTMLHeaderEven" even-header-value="ON" odd-footer-name="html_myHTMLFooter" odd-footer-value="on" even-footer-name="html_myHTMLFooterEven" even-footer-value="on" outdent="2em" />

';

//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->mirrorMargins = 1;

$mpdf->SetDisplayMode('fullpage','two');

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyleA4.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html);


// Alternative ways to mark ToC entries and Bookmarks
// This will automatically generate entries from the <h4> tag
$mpdf->h2toc = array('H4'=>0);
$mpdf->h2bookmarks = array('H4'=>0);

//==============================================================
// CONTENT
for ($j = 1; $j<7; $j++) { 
   if ($j==2)	$mpdf->WriteHTML('<pagebreak resetpagenum="0" pagenumstyle="a" />',2);
   if ($j==3)	$mpdf->WriteHTML('<pagebreak resetpagenum="1" pagenumstyle="I" />',2);
   if ($j==4)	$mpdf->WriteHTML('<pagebreak resetpagenum="0" pagenumstyle="i" />',2);
   if ($j==5)	$mpdf->WriteHTML('<pagebreak resetpagenum="0" pagenumstyle="1" />',2);
   if ($j==6)	$mpdf->WriteHTML('<pagebreak resetpagenum="1" pagenumstyle="A" type="NEXT-ODD" /><div style="color:#AA0000">ODD</div>',2);
   for ($x = 1; $x<7; $x++) {

	// Alternative way to mark ToC entries and Bookmarks manually
//	$mpdf->WriteHTML('<h4>Section '.$j.'.'.$x.'<bookmark content="Section '.$j.'.'.$x.'" level="0" /><tocentry content="Section '.$j.'.'.$x.'" level="0" /></h4>',2);

	// Using Automatic generation from <h4> tag
	$mpdf->WriteHTML('<h4>Section '.$j.'.'.$x.'</h4>',2);

	$html = '';
	// Split $lorem into words
	$words = preg_split('/([\s,\.]+)/',$lorem,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($words as $i => $e) {
	   if($i%2==0) {
		$y =  rand(1,10); 	// every tenth word
		if (preg_match('/^[a-zA-Z]{4,99}$/',$e) && ($y > 8)) {
			// If it is just a word use it as an index entry
			$content = ucfirst(trim($e));
			$html .= '<indexentry content="'.$content.'" />';
			$html .= '<i>'.$e . '</i>';
		}
		else { $html .= $e; }
	   }
	   else { $html .= $e; }
	}
	$mpdf->WriteHTML($html);
   }
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// INDEX
$html = '<pagebreak type="next-odd" />
<h2>Index</h2>
<indexinsert cols="2" offset="5" usedivletters="on" div-font-size="15" gap="5" font="Trebuchet" div-font="sans-serif" links="on" />
';

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================


?>