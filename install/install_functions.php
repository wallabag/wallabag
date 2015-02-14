<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

function status() {
$app_name = 'wallabag';

$php_ok = (function_exists('version_compare') && version_compare(phpversion(), '5.3.3', '>='));
$pdo_ok = class_exists('PDO');
$pcre_ok = extension_loaded('pcre');
$zlib_ok = extension_loaded('zlib');
$mbstring_ok = extension_loaded('mbstring');
$dom_ok = extension_loaded('DOM');
$iconv_ok = extension_loaded('iconv');
$tidy_ok = function_exists('tidy_parse_string');
$curl_ok = function_exists('curl_exec');
$parse_ini_ok = function_exists('parse_ini_file');
$parallel_ok = ((extension_loaded('http') && class_exists('HttpRequestPool')) || ($curl_ok && function_exists('curl_multi_init')));
$allow_url_fopen_ok = (bool)ini_get('allow_url_fopen');
$filter_ok = extension_loaded('filter');
$gettext_ok = function_exists("gettext");
$gd_ok = extension_loaded('gd');
$pdo_drivers = pdoDrivers();
$pdo_drivers_passing = $pdo_drivers['sqlite'] || $pdo_drivers['mysql'] || $pdo_drivers['postgres'];
$urlfetching = $curl_ok || $allow_url_fopen_ok;



if (extension_loaded('xmlreader')) {
	$xml_ok = true;
} elseif (extension_loaded('xml')) {
	$parser_check = xml_parser_create();
	xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
	xml_parser_free($parser_check);
	$xml_ok = isset($values[0]['value']);
} else {
	$xml_ok = false;
}

$status = array('app_name' => $app_name, 'php' => $php_ok, 'pdo' => $pdo_ok, 
  'pdo_drivers_passing' => $pdo_drivers_passing, 'xml' => $xml_ok, 'pcre' => $pcre_ok,
  'zlib' => $zlib_ok, 'mbstring' => $mbstring_ok, 'dom' => $dom_ok, 'iconv' => $iconv_ok, 'tidy' => $tidy_ok, 'curl' => $curl_ok,
   'parse_ini' => $parse_ini_ok, 'parallel' => $parallel_ok, 'allow_url_fopen' => $allow_url_fopen_ok, 'filter' => $filter_ok,
    'gettext' => $gettext_ok, 'gd' => $gd_ok, 'urlfetching' => $urlfetching);

return $status;
}
function isOkay() {
	return !in_array(false, status());
}

function isPassing() {
	$status = status();
	unset($status['curl'], $status['parallel'], $status['tidy'], $status['gd'], $status['filter'], $status['allow_url_fopen']);
	return !in_array(false, $status);
}

function pdoDrivers() {
    $pdo_driver_sqlite = extension_loaded('pdo_sqlite');
    $pdo_driver_mysql = extension_loaded('pdo_mysql');
    $pdo_driver_postgres = extension_loaded('pdo_pgsql');

    $pdo_drivers = array('sqlite' => $pdo_driver_sqlite, 'mysql' => $pdo_driver_mysql,
        'postgres' => $pdo_driver_postgres);

    return $pdo_drivers;
}

/* Function taken from at http://php.net/manual/en/function.rmdir.php#110489
 * Idea : nbari at dalmp dot com
 * Rights unknown
 * Here in case of .gitignore files
 */

function delTree($dir, $withdirectory="true") {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    if ($withdirectory) {
        return rmdir($dir);
    } else {
        return true;
    }
  }

function generate_salt() {
	mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return md5(uniqid(mt_rand(), true));
}

function executeQuery($handle, $sql, $params) {
    try
    {
        $query = $handle->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
    catch (Exception $e)
    {
        return FALSE;
    }
}

?>