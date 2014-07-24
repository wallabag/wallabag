<?php

if ($_GET['t']=='png') { $filename = 'tiger.png'; $mime = 'png'; }
else if ($_GET['t']=='gif') { $filename = 'tiger.gif'; $mime = 'gif'; }
else if ($_GET['t']=='jpg') { $filename = 'tiger.jpg'; $mime = 'jpeg'; }
else if ($_GET['t']=='jpeg') { $filename = 'tiger.jpg'; $mime = 'jpeg'; }
else if ($_GET['t']=='wmf') { $filename = 'tiger2.wmf'; $mime = 'wmf'; }
else if ($_GET['t']=='svg') { $filename = 'tiger.svg'; $mime = 'svg'; }
else { exit; }


$fp = fopen($filename, 'rb');
header("Content-Type: image/".$mime);
header("Content-Length: " . filesize($filename));
fpassthru($fp);
fclose($fp);
exit;
?>