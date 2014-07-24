<?php

$ff = scandir('./');

sort($ff);
$files = array();
foreach($ff AS $f) {
	if (preg_match('/example[0]{0,1}(\d+)_(.*?)\.php/',$f,$m)) {
		$num = intval($m[1]);
		$files[$num] = array(ucfirst(preg_replace('/_/',' ',$m[2])), $m[0]);
	}
}
echo '<html><body><h3>mPDF Example Files</h3>';

foreach($files AS $n=>$f) {
	echo '<p>'.$n.') '.$f[0].' &nbsp; <a href="'.$f[1].'">PDF</a> &nbsp;  <small><a href="show_code.php?filename='.$f[1].'">PHP</a></small></p>';
}

echo '</body></html>';
exit;


// For PHP4 compatability
if (!function_exists('scandir')) {
	function scandir($dir = './', $sort = 0) {
		$dir_open = @ opendir($dir);
		if (! $dir_open)
			return false;
		while (($dir_content = readdir($dir_open)) !== false)
			$files[] = $dir_content;
		if ($sort == 1)
			rsort($files, SORT_STRING);
		else
			sort($files, SORT_STRING);
		return $files;
	}
} 


?>