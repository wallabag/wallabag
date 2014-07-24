<?php


$html = '
<head>
<style>
table {
	border-collapse: separate;
	border: 4px solid #880000;
	padding: 3px;
	margin: 0px 20px 0px 20px;
	empty-cells: hide;
	background-color:#FFFFCC;
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
}
table.inner {
	border-collapse: collapse;
	border: 2px solid #000088;
	padding: 3px;
	margin: 5px;
	empty-cells: show;
	background-color:#FFCCFF;
}
td {
	border: 1px solid #008800;
	padding: 0px;
	background-color:#ECFFDF;
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


</style>
</head>
<body>
<h1>mPDF</h1>
<h2>Tables - Nested</h2>


<div style="border: 2px solid #000088; background-color: #DDDDFF; padding: 2mm;">
Text before table

<div style="border: 2px solid #008888; background-color: #DCAFCF; padding: 2mm;">

<table cellSpacing="2" rotate="-90" align="center" autosize="1.5">
<tbody>
<tr>
<td>This is data</td>
<td>This is data</td>
<td>

<table cellSpacing="2">
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

<table cellSpacing="2">
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
<td style="background: transparent url(\'bg.jpg\') repeat scroll right top;" >Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus. Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat pulvinar dolor. Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus. Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod. Donec et nulla. Sed quis orci. </td>
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

<table cellSpacing="2">
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

<table cellSpacing="2">
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

<p>&nbsp;</p>


<div style="border: 1px solid #000088; background-color: #DDDDFF; padding: 5mm;">
Text before table

<table cellSpacing="2" class="separate">
<tbody>
<tr>
<td style="background-color:#FFCCFF;">Row 1</td>
<td>This is data</td>
<td>

NO NESTING </td>
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
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
</tr>

<tr>
<td>Row 4</td>
<td>This is data</td>
<td>This is data</td>
<td>This is data</td>
</tr>

</tbody></table>

</div>

</body>
';

//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c','A4','','',32,25,27,25,16,13); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

// LOAD a stylesheet
$stylesheet = file_get_contents('mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;


?>