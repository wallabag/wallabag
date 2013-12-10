<?php
// Update site config files for Full-Text RSS
// Author: Keyvan Minoukadeh
// Copyright (c) 2012 Keyvan Minoukadeh
// License: AGPLv3
// Date: 2012-04-13
// More info: http://fivefilters.org/content-only/
// Help: http://help.fivefilters.org

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Usage
// -----
// Access this file in your browser and follow the instructions to update your site config files.

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
@set_time_limit(120);

////////////////////////////////
// Load config file
////////////////////////////////
$admin_page = 'update';
require_once('../config.php');
require_once('require_login.php');
require_once('template.php');
tpl_header('Update site patterns');

$version = include('../site_config/standard/version.php');

/////////////////////////////////
// Check for valid update key 
/////////////////////////////////
if (!isset($_REQUEST['key']) || trim($_REQUEST['key']) == '') {
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		header('Location: update.php');
		exit;
	}
	$auto = true;
	$no_auto_reasons = array();
	if (!class_exists('ZipArchive')) {
		$auto = false;
		$no_auto_reasons[] = 'zip support (PHP\'s <a href="http://php.net/manual/en/zip.requirements.php">ZipArchive</a> class) is missing';
	}
	if (!is_writable('../site_config')) {
		$auto = false;
		$no_auto_reasons[] = 'your <tt>site_config/</tt> folder is not writable - change permissions to 777 and try again.</p>';
	}
	if (!file_exists('../site_config/standard/version.php')) {
		die('Could not determine current version of your site pattern files (site_config/standard/version.php). Make sure you\'re using at least version 2.9.5 of Full-Text RSS.');
	}
	if (!@$options->registration_key) {
		$input_field = '<label for="key">Registration key</label><input type="password" name="key" id="key" />';
	} else {
		$reg_key = preg_replace('/[^a-z0-9-]/i', '', $options->registration_key);
		$input_field = '<input type="hidden" name="key" value="'.$reg_key.'" />';
	}
	?>
	<p>You have Full-Text RSS <strong><?php echo _FF_FTR_VERSION; ?></strong>
	(Site Patterns version: <strong><?php echo (isset($version) ? $version : 'Unknown'); ?></strong>)
	</p>
	<p>To see if you have the latest versions, <a href="http://fivefilters.org/content-only/latest_version.php?version=<?php echo urlencode(_FF_FTR_VERSION).'&site_config='.urlencode(@$version); ?>">check for updates</a>.</p>
	<?php
	$reg_key_info = '<h3>Registration key</h3><p>This update tool requires a registration key issued by FiveFilters.org. You do not need a registration key to use Full-Text RSS, and none of the regular funtionality is affected if you do not have one. The update tool is simply a convenience service we offer our customers.</p>';
	if ($auto) {
		echo '<p>This update tool will attempt to fetch the latest site patterns from FiveFilters.org and update yours.</p>';
		echo '<p><strong>Important: </strong>if you\'ve modified or added your own config files in the <tt>site_config/standard/</tt> folder, please move them to <tt>site_config/custom/</tt> &mdash; the update process will attempt to replace everything in <tt>site_config/standard/</tt> with our updated version.</p>';
		echo $reg_key_info;
		if (!isset($reg_key)) {
			echo '<p>Your registration key should be your PayPal or Avangate transaction ID. If you don\'t have a registration key, you will get one sent to you automatically when you <a href="http://fivefilters.org/content-only/">purchase Full-Text RSS</a> from FiveFilters.org.</p>';
		}
		echo '<form method="post" action="update.php" class="well">',$input_field,' <input type="submit" value="Update now" /></form>';
	} else {
		echo '<div class="notice">';
		echo '<p>We cannot automatically update your site pattern files because:</p>';
		echo '<ul>';
		foreach ($no_auto_reasons as $reason) {
			echo '<li>',$reason,'</li>';
		}
		echo '</ul>';
		echo '<p>You can still manually update by downloading the zip file and replacing everything in your <tt>site_config/standard/</tt> folder with the contents of the zip file.</p>';
		echo '</div>';
		echo $reg_key_info;
		if (!isset($reg_key)) {
			echo '<p>Enter your registration key below to download the latest version of the site config files from FiveFilters.org</p>';
			echo '<p>Your registration key should be your PayPal or Avangate transaction ID.</p>';
		}
		echo '<form method="post" class="well" action="http://fivefilters.org/content-only/update/get_site_config.php">',$input_field,' <input type="submit" value="Download site patterns" /></form>';
	}
	echo '<h3>Help</h3>';
	echo '<p>If you have any trouble, please contact us via our <a href="http://help.fivefilters.org">support site</a>.</p>';
	exit;
}

//////////////////////////////////
// Check for updates
//////////////////////////////////
$ff_version = (float)@file_get_contents('http://fivefilters.org/content-only/site_config/standard/version.txt');
if (version_compare($version, $ff_version) != -1) {
	die('Your site config files are up to date! If you have trouble extracting from a particular site, please email us: help@fivefilters.org');
} else {
	println("Updated site patterns are available at FiveFilters.org (version $ff_version)...");
}

//////////////////////////////////
// Prepare
//////////////////////////////////
$latest_remote = 'http://fivefilters.org/content-only/update/get_site_config.php?key='.urlencode($_REQUEST['key']);
$tmp_latest_local = '../site_config/latest_site_config.zip';
$tmp_latest_local_dir = '../site_config/standard_latest';
$tmp_old_local_dir = '../site_config/standard_old';
if (file_exists($tmp_latest_local)) unlink($tmp_latest_local);
if (file_exists($tmp_latest_local_dir)) rrmdir($tmp_latest_local_dir);
if (file_exists($tmp_old_local_dir)) {
	rrmdir($tmp_old_local_dir);
}
$standard_local_dir = '../site_config/standard/';
//@copy($latest_remote, $tmp_latest_local);
//copy() does not appear to fill $http_response_header in certain environments
@file_put_contents($tmp_latest_local, @file_get_contents($latest_remote));
$headers = implode("\n", $http_response_header);
//var_dump($headers); exit;
if (strpos($headers, 'HTTP/1.1 403') !== false) {
	println("Invalid registration key supplied");
	exit;
} elseif (strpos($headers, 'HTTP/1.1 200') === false) {
	println("Sorry, something went wrong. We're looking into it. Please contact us if the problem persists.");
	exit;
}
if (class_exists('ZipArchive') && file_exists($tmp_latest_local)) {
	println("Downloaded latest copy of the site pattern files to $tmp_latest_local");
	$zip = new ZipArchive;
	if ($zip->open($tmp_latest_local) === TRUE) {
		$zip->extractTo($tmp_latest_local_dir);
		$zip->close();
		@unlink($tmp_latest_local);
		if (file_exists($tmp_latest_local_dir)) {
			println("Unzipped contents to $tmp_latest_local_dir");
			if (!file_exists($tmp_latest_local_dir.'/version.php')) {
				println("There was a problem extracting the latest site patterns archive - your current site patterns remain untouched.");
				println("Please <a href=\"$latest_remote\">update manually</a>.");
				exit;
			}
			rename($standard_local_dir, $tmp_old_local_dir);
			if (file_exists($tmp_old_local_dir)) println("Renamed $standard_local_dir to $tmp_old_local_dir");
			rename($tmp_latest_local_dir, $standard_local_dir);
			if (file_exists($standard_local_dir)) println("Renamed $tmp_latest_local_dir to $standard_local_dir");
			println("<strong style=\"color: darkgreen;\">All done!</strong> Your old site config files are in $tmp_old_local_dir &mdash; these will be removed next time you go through the update process.");
		} else {
			if (file_exists($tmp_latest_local)) @unlink($tmp_latest_local);
			println("Failed to unzip to $tmp_latest_local_dir - your current site patterns remain untouched");
		}
	} else {
		if (file_exists($tmp_latest_local)) @unlink($tmp_latest_local);
		println("Failed to extract from $tmp_latest_local - your current site patterns remain untouched");
	}
} else {
	println("Could not download the latest site config files. Please <a href=\"$latest_remote\">update manually</a> - your current site patterns remain untouched.");
}

function println($txt) {
	echo $txt,"<br />\n";
	ob_end_flush(); 
    ob_flush(); 
    flush(); 
}

function rrmdir($dir) {
    foreach(glob($dir . '/{*.txt,*.php,.*.txt,.*.php}', GLOB_BRACE|GLOB_NOSORT) as $file) {
        if(is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
		}
    }
    rmdir($dir);
}
?>