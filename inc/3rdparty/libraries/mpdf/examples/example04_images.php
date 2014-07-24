<?php



$html = '
<style>
table { border-collapse: collapse; margin-top: 0; text-align: center; }
td { padding: 0.5em; }
h1 { margin-bottom: 0; }z
</style>
<h1>mPDF Images</h1>

<table>
<tr>
<td>GIF</td>
<td>JPG</td>
<td>JPG (CMYK)</td>
<td>PNG</td>
<td>BMP</td>
<td>WMF</td>
<td>SVG</td>
</tr>
<tr>
<td><img style="vertical-align: top" src="tiger.gif" width="80" /></td>
<td><img style="vertical-align: top" src="tiger.jpg" width="80" /></td>
<td><img style="vertical-align: top" src="tigercmyk.jpg" width="80" /></td>
<td><img style="vertical-align: top" src="tiger.png" width="80" /></td>
<td><img style="vertical-align: top" src="tiger.bmp" width="80" /></td>
<td><img style="vertical-align: top" src="tiger2.wmf" width="80" /></td>
<td><img style="vertical-align: top" src="tiger.svg" width="80" /></td>
</tr>
</tr>
<tr>
<td colspan="7" style="text-align: left" ><h4>Opacity 50%</h4></td>
</tr>
<tr>
<tr>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger.gif" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger.jpg" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tigercmyk.jpg" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger.png" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger.bmp" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger2.wmf" width="80" /></td>
<td><img style="vertical-align: top; opacity: 0.5" src="tiger.svg" width="80" /></td>
</tr>
</table>

<h4>Alpha channel</h4>
<table>
<tr>
<td>PNG</td>
<td><img style="vertical-align: top" src="alpha.png" width="85" /></td>
<td style="background-color:#FFCCFF; "><img style="vertical-align: top" src="alpha.png" width="85" /></td>
<td style="background-color:#FFFFCC;"><img style="vertical-align: top" src="alpha.png" width="85" /></td>
<td style="background-color:#CCFFFF;"><img style="vertical-align: top" src="alpha.png" width="85" /></td>
<td style="background-color:#CCFFFF; background: transparent url(\'bg.jpg\') repeat scroll right top;"><img style="vertical-align: top" src="alpha.png" width="85" /></td>
</tr>
</table>
<h4>Transparency</h4>
<table><tr>
<td>PNG</td>
<td style="background-color:#FFCCFF; "><img style="vertical-align: top" src="tiger24trns.png" width="85" /></td>
<td style="background-color:#FFFFCC;"><img style="vertical-align: top" src="tiger24trns.png" width="85" /></td>
<td style="background-color:#CCFFFF;"><img style="vertical-align: top" src="tiger24trns.png" width="85" /></td>
<td style="background-color:#CCFFFF; background: transparent url(\'bg.jpg\') repeat scroll right top;"><img style="vertical-align: top" src="tiger24trns.png" width="85" /></td>
</tr><tr>
<td>GIF</td>
<td style="background-color:#FFCCFF;"><img style="vertical-align: top" src="tiger8trns.gif" width="85" /></td>
<td style="background-color:#FFFFCC;"><img style="vertical-align: top" src="tiger8trns.gif" width="85" /></td>
<td style="background-color:#CCFFFF;"><img style="vertical-align: top" src="tiger8trns.gif" width="85" /></td>
<td style="background-color:#CCFFFF; background: transparent url(\'bg.jpg\') repeat scroll right top;"><img style="vertical-align: top" src="tiger8trns.gif" width="85" /></td>
</tr><tr>
<td>WMF</td>
<td style="background-color:#FFCCFF;"><img style="vertical-align: top" src="tiger2.wmf" width="85" /></td>
<td style="background-color:#FFFFCC;"><img style="vertical-align: top" src="tiger2.wmf" width="85" /></td>
<td style="background-color:#CCFFFF;"><img style="vertical-align: top" src="tiger2.wmf" width="85" /></td>
<td style="background-color:#CCFFFF; background: transparent url(\'bg.jpg\') repeat scroll right top;"><img style="vertical-align: top" src="tiger2.wmf" width="85" /></td>
</tr><tr>
<td>SVG</td>
<td style="background-color:#FFCCFF;"><img style="vertical-align: top" src="tiger.svg" width="85" /></td>
<td style="background-color:#FFFFCC;"><img style="vertical-align: top" src="tiger.svg" width="85" /></td>
<td style="background-color:#CCFFFF;"><img style="vertical-align: top" src="tiger.svg" width="85" /></td>
<td style="background-color:#CCFFFF; background: transparent url(\'bg.jpg\') repeat scroll right top;"><img style="vertical-align: top" src="tiger.svg" width="85" /></td>
</tr></table>


Images returned from tiger.php
<div>
GIF <img style="vertical-align: top" src="tiger.php?t=gif" width="85" />
JPG <img style="vertical-align: top" src="tiger.php?t=jpg" width="85" />
PNG <img style="vertical-align: top" src="tiger.php?t=png" width="85" />
WMF <img style="vertical-align: top" src="tiger.php?t=wmf" width="85" />
SVG <img style="vertical-align: top" src="tiger.php?t=svg" width="85" />
</div>

<pagebreak />


<h3>Image Alignment</h3>
<div>From mPDF version 4.2 onwards, in-line images can be individually aligned (vertically). Most of the values for "vertical-align" are supported: top, bottom, middle, baseline, text-top, and text-bottom. The default value for vertical alignment has been changed to baseline, and the default padding to 0, consistent with most browsers.
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: top;" />
are <img src="img2.png" style="vertical-align: top;" />
<b>top</b> <img src="img3.png" style="vertical-align: top;" />
aligned <img src="img4.png" style="vertical-align: middle;" />
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: text-top;" />
are <img src="img2.png" style="vertical-align: text-top;" />
<b>text-top</b> <img src="img3.png" style="vertical-align: text-top;" />
aligned <img src="img4.png" style="vertical-align: middle;" />
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: bottom;" />
are <img src="img2.png" style="vertical-align: bottom;" />
<b>bottom</b> <img src="img3.png" style="vertical-align: bottom;" />
aligned <img src="img4.png" style="vertical-align: middle;" />
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: text-bottom;" />
are <img src="img2.png" style="vertical-align: text-bottom;" />
<b>text-bottom</b> <img src="img3.png" style="vertical-align: text-bottom;" />
aligned <img src="img4.png" style="vertical-align: middle;" />
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: baseline;" />
are <img src="img2.png" style="vertical-align: baseline;" />
<b>baseline</b> <img src="img3.png" style="vertical-align: baseline;" />
aligned <img src="img4.png" style="vertical-align: middle;" />
</div>
<br />

<div style="background-color:#CCFFFF;">
These images <img src="img1.png" style="vertical-align: middle;" />
are <img src="img2.png" style="vertical-align: middle;" />
<b>middle</b> <img src="img3.png" style="vertical-align: middle;" />
aligned <img src="img5.png" style="vertical-align: bottom;" />
</div>
<br />

<h4>Mixed alignment</h4>
<div style="background-color:#CCFFFF;">
baseline: <img src="sunset.jpg" width="50" style="vertical-align: baseline;" />
text-bottom: <img src="sunset.jpg" width="30" style="vertical-align: text-bottom;" />
middle: <img src="sunset.jpg" width="30" style="vertical-align: middle;" />
bottom: <img src="sunset.jpg" width="80" style="vertical-align: bottom;" />
text-top: <img src="sunset.jpg" width="50" style="vertical-align: text-top;" />
top: <img src="sunset.jpg" width="100" style="vertical-align: top;" />
</div>

<h3>Image Border and padding</h3>
From mPDF v4.2, Image padding is supported as well as border and margin.
<img src="sunset.jpg" width="100" style="border:3px solid #44FF44; padding: 1em;" />

<h3>Rotated Images</h3>
<img src="tiger.png" width="100" /> 
<img src="tiger.png" rotate="90" width="100" /> 
<img src="tiger.png" rotate="180" width="100" /> 
<img src="tiger.png" rotate="-90" width="100" /> 
<br />
<img src="tiger.jpg" width="100" /> 
<img src="tiger.jpg" rotate="90" width="100" /> 
<img src="tiger.jpg" rotate="180" width="100" /> 
<img src="tiger.jpg" rotate="-90" width="100" /> 
<br />
<img src="tiger2.wmf" width="80" /> &nbsp; &nbsp; &nbsp;
<img src="tiger2.wmf" rotate="90" width="80" /> &nbsp; &nbsp; &nbsp;
<img src="tiger2.wmf" rotate="180" width="80" /> &nbsp; &nbsp; &nbsp;
<img src="tiger2.wmf" rotate="-90" width="80" />
<br />
<img src="tiger.svg" width="100" />&nbsp;
<img src="tiger.svg" rotate="90" width="85" />&nbsp;
<img src="tiger.svg" rotate="180" width="100" />&nbsp;
<img src="tiger.svg" rotate="-90" width="85" /> 
<br />

';
//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('c'); 

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>