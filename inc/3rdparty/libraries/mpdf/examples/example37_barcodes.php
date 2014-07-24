<?php


$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
	font-size: 9pt;
	background: transparent url(\'bgbarcode.png\') repeat-y scroll left top;
}
h5, p {	margin: 0pt;
}
table.items {
	font-size: 9pt; 
	border-collapse: collapse;
	border: 3px solid #880000; 
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
</style>
</head>
<body>

<!--mpdf
<htmlpagefooter name="myfooter">
<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
Page {PAGENO} of {nb}
</div>
</htmlpagefooter>

<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<h1>mPDF</h1>
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
<td class="barcodecell"><barcode code="978-0-9542246-0" text="1" class="barcode" /></td>
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
<td>EAN-8. Accepts 7 or 8 characters (creating checksum digit if required). [0-9] numeric only</td>
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
<td class="barcodecell"><barcode code="978-0-9542246-0 07" type="EAN13P2" class="barcode" /></td>
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
<td>UPC-A barcode with 2-digit EAN-2 supplement. This is a subset of the EAN-13. </td>
<td class="barcodecell"><barcode code="00633895260 24" type="UPCAP2" class="barcode" /></td>
</tr>
<tr>
<td align="center">UPCEP2</td>
<td>UPC-E barcode with 2-digit EAN-2 supplement. </td>
<td class="barcodecell"><barcode code="042100005264 07" type="UPCEP2" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN8P2</td>
<td>EAN-8 barcode with 2-digit EAN-2 supplement</td>
<td class="barcodecell"><barcode code="5512345 07" type="EAN8P2" class="barcode" /></td>
</tr>
<tr>
<td align="center">EAN13P5</td>
<td>Standard EAN-13 barcode with 5-digit UPC supplement (90000)</td>
<td class="barcodecell"><barcode code="978-0-9542246-0 90000" type="EAN13P5" class="barcode" /></td>
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
<td>UPC-A barcode with 5-digit EAN-5 supplement. This is a subset of the EAN-13</td>
<td class="barcodecell"><barcode code="07567816412 90000" type="UPCAP5" class="barcode" /></td>
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

<!-- QR-CODE 
<h3>2D Code (QR-code)</h3>
<p>A nominal height and width for these barcodes is set as 25mm. \'size\' will scale both the height and width.</p>
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
<td align="center">QR-code</td>
<td>QR-code. Accepts numeric, alphanumeric or binary input, with specified error level: L, M, Q, H.</td>
<td class="barcodecell"><barcode code="Your message here" type="QR" class="barcode" size="0.8" error="M" /></td>
</tr>
</tbody>
</table>
-->


<br />

<div>
<h5>Useful links</h5>
<p><a href="http://www.adams1.com">http://www.adams1.com</a></p>
<p><a href="http://www.tec-it.com/Download/PDF/Barcode_Reference_EN.pdf">http://www.tec-it.com/Download/PDF/Barcode_Reference_EN.pdf</a></p>
<p><a href="http://www.tec-it.com/en/support/knowbase/symbologies/barcode-overview/linear/Default.aspx">http://www.tec-it.com/en/support/knowbase/symbologies/barcode-overview/linear/Default.aspx</a></p>
<p><a href="http://www.gs1uk.org/downloads/bar_code/Bar%20coding%20getting%20it%20right.pdf">http://www.gs1uk.org/downloads/bar_code/Bar%20coding%20getting%20it%20right.pdf</a></p>
<p><a href="http://web.archive.org/web/19990501035133/http://www.uc-council.org/d36-d.htm">http://web.archive.org/web/19990501035133/http://www.uc-council.org/d36-d.htm (EAN2 and EAN5)</a></p>
<p><a href="http://www.barcodeisland.com/ean13.phtml">http://www.barcodeisland.com/ean13.phtml (UPC-A)</a></p>
<p><a href="http://www.idautomation.com/fonts/postnet/#Specifications">http://www.idautomation.com/fonts/postnet/#Specifications</a></p>
<p><a href="http://www.outputlinks.com/sites/AFP/ibm_bcocafaq.pdf">http://www.outputlinks.com/sites/AFP/ibm_bcocafaq.pdf</a></p>
<p><a href="https://ribbs.usps.gov/intelligentmail_mailpieces/documents/tech_guides/USPSIMB_Tech_Resource_Guide.pdf">https://ribbs.usps.gov/intelligentmail_mailpieces/documents/tech_guides/USPSIMB_Tech_Resource_Guide.pdf (Intelligent Mail)</a></p>
<p><a href="http://www.mailsorttechnical.com/downloads_mailsort_user_guide.cfm">http://www.mailsorttechnical.com/downloads_mailsort_user_guide.cfm</a></p>

<p><a href="http://www.mailsorttechnical.com/docs/mug_jun_2009/MUG_10_2008_Mailsort_700.pdf">http://www.mailsorttechnical.com/docs/mug_jun_2009/MUG_10_2008_Mailsort_700.pdf</a>  page 20</p>
 

</div>

<pagebreak />
<div>
<h3>Human-readable text</h3>
Human-readable text is only produced as part of the barcode object in EAN-13, ISBN, ISSN, EAN-8, UPC-A and UPC-E. Here is an example to add text to a barcode:
</div>

<div style="border:1px solid #555555; background-color: #DDDDDD; padding: 1em; font-size:8pt; font-family: lucidaconsole, mono;">

&lt;div style="position:fixed; right: 50mm; top: 60mm; border: 0.2mm solid #000000; text-align: center; padding: 0.5mm; padding-top: 2mm;"&gt;<br />
&lt;barcode code="00034698735346987355" type="EAN128C" /&gt;&lt;br /&gt;<br />
&lt;div style="font-family: ocrb;"&gt;(00) 0346987 35346987 355&lt;/div&gt;<br />
&lt;/div&gt;

</div>

<div style="position:fixed; right: 50mm; top: 60mm; border: 0.2mm solid #000000; text-align: center; padding: 0.5mm; padding-top: 2mm;">
<barcode code="00034698735346987355" type="EAN128C" /><br />
<div style="font-family: ocrb;">(00) 0346987 35346987 355</div>
</div>

</body>
</html>
';
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('','','','',20,15,25,25,10,10); 
$mpdf->WriteHTML($html);
$mpdf->Output(); 

exit;

?>