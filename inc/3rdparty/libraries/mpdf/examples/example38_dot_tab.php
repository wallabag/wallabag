<?php


$html = '
<style>
dottab.menu {
	outdent: 4em;
}
p.menu {
	text-align: left;
	padding-right: 4em;
}
</style>

<h3>Menu</h3>

<div style="border: 0.2mm solid #000088; padding: 1em;">
<p class="menu">Nulla felis erat, imperdiet eu, ullamcorper non, nonummy quis, elit. Suspendisse potenti. Ut a eros at ligula vehicula pretium. Maecenas feugiat pede vel risus. Nulla et lectus <dottab class="menu" />&nbsp;&pound;37.00</p>

<p class="menu">Fusce eleifend neque sit amet erat. Integer consectetuer nulla non orci. Morbi feugiat <dottab class="menu" />&nbsp;&pound;3700.00</p>

<p class="menu">Cras odio. Donec mattis, nisi id euismod auctor, neque metus pellentesque risus, at eleifend lacus sapien et risus <dottab class="menu" />&nbsp;&pound;27.00</p>

<p class="menu">Phasellus metus. Phasellus feugiat, lectus ac aliquam molestie, leo lacus tincidunt turpis, vel aliquam quam odio et sapien. Mauris ante pede, auctor ac, suscipit quis, malesuada sed, nulla. Integer sit amet odio sit amet lectus luctus euismod <dottab class="menu" />&nbsp;&pound;7.00</p>

<p class="menu">Donec et nulla. Sed quis orci <dottab class="menu" />&nbsp;&pound;1137.00</p>
</div>
';


include("../mpdf.php");

$mpdf=new mPDF(); 

$mpdf->WriteHTML($html);

$mpdf->Output(); 

exit;



?>