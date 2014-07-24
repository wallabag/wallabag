<?php

echo '<'.'!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<h2>Submitted data from PDF Form</h2>
<p style="font-size:0.9em;">(See formsubmit.php in the Examples folder for details)</p>
';


if (count($_POST)) {
  // To display HTML output from PDF form
  echo '<h4>HTML format data sent as POST</h4>';
  foreach($_POST AS $name=>$val) {
	$t =  mb_convert_encoding(PDFDocEncodingToWin1252($val), 'UTF-8', 'Windows-1252' );	// If from core fonts doc
	echo '<p>PDFDocEnc: '.$name.' => '.htmlspecialchars($t).'</p>';
  }
}
else if (count($_GET)) {
  // To display HTML output from PDF form
  echo '<h4>HTML format data sent as GET</h4>';
  foreach($_GET AS $name=>$val) {
	$t =  mb_convert_encoding(PDFDocEncodingToWin1252($val), 'UTF-8', 'Windows-1252' );	// If from core fonts doc
	echo '<p>PDFDocEnc: '.$name.' => '.htmlspecialchars($t).'</p>';
  }
}

else  {
 $postdata = file_get_contents("php://input");

 if ($postdata) {
  echo '<h4>XFDF format data detected</h4>';
  // To parse XFDF
  if (preg_match_all('/<field name="([^>]*)"\s*>\s*(<value\s*>(.*?)<\/value\s*>)\s*<\/field\s*>/s', $postdata, $m)) {
	for($i=0; $i<count($m[0]); $i++) {
		// if multiple values in response e.g. from multiple selected options
  		preg_match_all('/<value\s*>(.*?)<\/value\s*>/s', $m[2][$i], $v);
		if (count($v[0])>1) {
			$values = array();
			foreach($v[1] AS $val) { $values[] = $val; }
			//foreach($v[1] AS $val) { $values[] = htmlspecialchars_decode($val); }
			echo '<p>Field: '.$m[1][$i].' => [array of values] ('.implode(', ',$values).')</p>';
		}
		else {
			//echo '<p>Field: '.$m[1][$i].' => '.htmlspecialchars_decode($m[3][$i]).'</p>';
			echo '<p>Field: '.$m[1][$i].' => '.$m[3][$i].'</p>';
		}
	}
  }
  if (preg_match_all('/<field name="([^>]*)"\s*>\s*<value\s*\/\s*>\s*<\/field\s*>/s', $postdata, $m)) {
	for($i=0; $i<count($m[0]); $i++) {
		echo '<p>Field: '.$m[1][$i].' => [blank]</p>';
	}
  }
  if (preg_match_all('/<field name="([^>]*)"\s*\/\s*>/s', $postdata, $m)) {
	for($i=0; $i<count($m[0]); $i++) {
		echo '<p>Field: '.$m[1][$i].' => [no value]</p>';
	}
  }


  // To display whole XFDF
  //$postdata = preg_replace("/[\n\r]/", "", $postdata);
  //$postdata = preg_replace('/>\s*</', ">\n<", $postdata);
  //echo nl2br(htmlspecialchars($postdata)); 
 }
 else { echo "No form data detected"; }
}



echo '</body></html>';

exit;

function PDFDocEncodingToWin1252($txt) {
	$Win1252ToPDFDocEncoding = array(
		chr(0200) => chr(0240), chr(0214) => chr(0226), chr(0212) => chr(0227), chr(0237) => chr(0230), 
		chr(0225) => chr(0200), chr(0210) => chr(0032), chr(0206) => chr(0201), chr(0207) => chr(0202),
		chr(0205) => chr(0203), chr(0227) => chr(0204), chr(0226) => chr(0205), chr(0203) => chr(0206),
		chr(0213) => chr(0210), chr(0233) => chr(0211), chr(0211) => chr(0213), chr(0204) => chr(0214),
		chr(0223) => chr(0215), chr(0224) => chr(0216), chr(0221) => chr(0217), chr(0222) => chr(0220),
		chr(0202) => chr(0221), chr(0232) => chr(0235), chr(0230) => chr(0037), chr(0231) => chr(0222),
		chr(0216) => chr(0231)
	); 
	return strtr($txt, array_flip($Win1252ToPDFDocEncoding) );
}


?>