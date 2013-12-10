<?php
// Edit site config files for Full-Text RSS
// Author: Keyvan Minoukadeh
// Copyright (c) 2013 Keyvan Minoukadeh
// License: AGPLv3
// Date: 2013-02-25
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

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

require_once '../libraries/content-extractor/SiteConfig.php';

////////////////////////////////
// Load config file
////////////////////////////////
$admin_page = 'edit-pattern';
require_once('../config.php');
require_once('require_login.php');
require_once('template.php');
tpl_header('Edit site patterns');

$version = include('../site_config/standard/version.php');

function filter_only_text($filename) {
	return (strtolower(substr($filename, -4)) == '.txt');
}
function is_valid_hostname($host) {
	return preg_match('!^[a-z0-9_.-]+$!i', $host);
}

/////////////////////////////////
// Process changes
/////////////////////////////////
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// DELETE
	if (@$_POST['delete'] != '' && @$_POST['delete_dir'] != '') {
		if (is_valid_hostname($_POST['delete'])) {
			$delete = $_POST['delete'];
			if ($_POST['delete_dir'] == 'standard') {
				$delete = '../site_config/standard/'.$delete;
			} else {
				$delete = '../site_config/custom/'.$delete;
			}
			if (@unlink($delete)) {
				echo 'Deleted <strong>'.$delete.'</strong>';
			} else {
				echo 'Failed to delete <strong>'.$delete.'</strong>';
			}
		}
		exit;
	}
	
	// SAVE
	if (@$_POST['save'] != '' && isset($_POST['contents'])) {
		if (is_valid_hostname(trim($_POST['save']))) {
			$save = strtolower(trim($_POST['save']));
			if (@$_POST['save_dir'] == 'standard') {
				$savepath = '../site_config/standard/'.$save.'.txt';
			} else {
				$savepath = '../site_config/custom/'.$save.'.txt';
			}
			// TODO: check if file exists, if it does, prompt user whether to overwrite
			if (file_put_contents($savepath, $_POST['contents']) !== false) {
				echo '<p>Saved to <strong>'.$savepath.'</strong></p>';
				// check caching
				if ($options->caching) {
					echo '<p>Note: caching is enabled &mdash; you may have to disable caching or delete cache files to see changes.<p>';
				}
				if ($options->apc && function_exists('apc_delete') && function_exists('apc_cache_info')) {
					$_apc_data = apc_cache_info('user');
					foreach ($_apc_data['cache_list'] as $_apc_item) {
						if (substr($_apc_item['info'], 0, 3) == 'sc.') {
							apc_delete($_apc_item['info']);
						}
					}
					echo '<p>Cleared site config cache in APC.</p>';
				}
				SiteConfig::set_config_path(dirname($savepath));
				$sconfig = SiteConfig::build($save, $exact_host_match=true);
				if ($sconfig) {
					if (!empty($sconfig->test_url)) {
						echo '<h4>Test URLs</h4>';
						echo '<ul>';
						foreach ($sconfig->test_url as $test_url) {
							$ftr_test_url = $test_url;
							if (strtolower(substr($ftr_test_url, 0, 7)) == 'http://') {
								$ftr_test_url = substr($ftr_test_url, 7);
							}
							$ftr_test_url = '../makefulltextfeed.php?url='.urlencode($ftr_test_url);
							echo '<li>';
							echo '<a href="'.htmlspecialchars($test_url).'" target="_blank">'.htmlspecialchars($test_url).'</a>';
							echo ' | <a href="'.$ftr_test_url.'" target="_blank">Full-Text RSS result</a>';
							echo ' | <a href="'.$ftr_test_url.'&debug" target="_blank">Debug</a>';
							echo '</li>';
						}
						echo '</ul>';
					} else {
						echo '<p>No test URLs found in config, if you supply one we\'ll give you a link to test how Full-Text RSS will extract it</p>';
					}
				} else {
					echo '<p>Could not load/parse config file</p>';
				}
			} else {
				echo 'Failed to save <strong>'.$savepath.'</strong>. Make sure the directory is writable.';
			}
		}
		exit;
	}
}

/////////////////////////////////
// Show list of site config files
/////////////////////////////////
if (!isset($_REQUEST['url']) || trim($_REQUEST['url']) == '') {
	$sc_files = array_merge(scandir('../site_config/standard/'), scandir('../site_config/custom/'));
	$sc_files = array_unique(array_filter($sc_files, 'filter_only_text'));
	?>
	<p><strong>Note:</strong> This feature is for advanced users familiar with XPath. It allows you to override automatic article extraction and specify what Full-Text RSS should extract from specific domains. If you're uncomfortable writing your own, you can <a href="http://fivefilters.org/content-only/custom_site_patterns.php">request one</a> from us.</p>
	<form class="well form-search" action="edit-pattern.php">
	<input id="search" type="text" name="url" class="span8" placeholder="Enter a host name or URL, e.g. http://www.example.org/article-123.html">
	<button type="submit" class="btn">Search or add</button>
	</form>
	<?php
	echo '<ul style="-webkit-column-count: 3; -moz-column-count: 3; column-count: 3" id="list">';
	foreach ($sc_files as $file) {
		$file = basename($file, '.txt');
		echo '<li><a href="edit-pattern.php?url='.urlencode($file).'">'.htmlspecialchars($file).'</a></li>';
	}
	echo '</ul>';
	// adapted from http://stackoverflow.com/a/11022738/407938 ...
	?>
	<script>
	$('input#search').bind('keyup',function(){
		var inputString = $(this).val();
		var items = $('ul#list li');
		items.hide();
		items.each(function(){
			var item = $(this).text().toString();
			if (item.indexOf(inputString)>=0)
				$(this).show();
		});
	});
	</script>
	<?php
	exit;
}

//////////////////////////////////
// Check if primary or secondary 
// folder specified
//////////////////////////////////
$lookin = null;
if (isset($_REQUEST['lookin']) && in_array($_REQUEST['lookin'], array('standard', 'custom'))) {
	$lookin = $_REQUEST['lookin'];
}

//////////////////////////////////
// Find file and display
//////////////////////////////////
$hostname = false;
if (is_valid_hostname($_REQUEST['url'])) {
	$hostname = $_REQUEST['url'];
} else {
	if ($_host = parse_url($_REQUEST['url'], PHP_URL_HOST)) {
		if (is_valid_hostname($_host)) {
			$hostname = $_host;
		}
	}
}
if (!$hostname) die('Invalid URL');
$hostname_base = ltrim($hostname, '.');
if (strtolower(substr($hostname, 0, 4)) == 'www.') {
	$hostname = substr($hostname, 4);
	$hostname_base = $hostname;
}
$check = array(
	'../site_config/standard/'.$hostname_base.'.txt',
	'../site_config/standard/.'.$hostname_base.'.txt',
	'../site_config/custom/'.$hostname_base.'.txt',
	'../site_config/custom/.'.$hostname_base.'.txt'
);
$related = array();
$matched = array();
$exact_match = false;
foreach ($check as $filename) {
	if (file_exists($filename)) {
		$related[$filename] = file_get_contents($filename);
		if ($lookin === null || strpos($filename, "/$lookin/") !== false) {
			$matched[$filename] = file_get_contents($filename);
			if (strpos($filename, "/$hostname") !== false) $exact_match = $filename;
		}
	}
}

if (empty($matched)) {
	$contents = "# No matching files found, you can write yours here\n\n# body: //div[@id='body']\n\n# test_url: http://...";
	echo '<p>No matching files found...</p>';
} elseif ($exact_match) {
	$contents = $matched[$exact_match];
	$file_location = $exact_match;
	echo '<p style="position: absolute;">Loaded <strong>'.htmlspecialchars($exact_match).'</strong></p>';
} else {
	$contents = end($matched);
	$file_location = array_pop(array_keys($matched));
	echo '<p style="position: absolute;">Loaded <strong>'.htmlspecialchars($file_location).'</strong></p>';
}

if (isset($file_location)) unset($related[$file_location]);

$save_locations = array(
	'custom' => 'custom (recommended)',
	'standard' => 'standard'
);
echo '<form method="POST" action="edit-pattern.php">';
echo '<div style="text-align: right; margin-top: 10px; margin-bottom: 5px;"><a href="http://help.fivefilters.org/customer/portal/articles/223153-site-patterns" target="_blank">Need help?</a></div>';
echo '<textarea name="contents" class="span8" style="height: 400px;" id="config">'.htmlspecialchars($contents).'</textarea>';
echo '<div style="margin-top: 8px; margin-bottom: 4px;">';
echo '<label>Save as</label> <input type="text" name="save" value="'.htmlspecialchars($hostname).'" />.txt';
echo '<br />';
echo '<label>In directory</label> ';
echo '<select name="save_dir">';
foreach ($save_locations as $_sl_val => $_sl_display) {
	echo "<option value=\"$_sl_val\">$_sl_display</option>";
}
echo '</select>';
echo '</div>';
echo '<input type="submit" class="btn btn-primary" value="Save" /> ';
echo 'or <a href="edit-pattern.php" class="btn" >Cancel and return to listing</a>';
echo '</form>';

// DELETE option
if (!empty($matched)) {
	echo '<hr /><h3>Delete file?</h3>';
	echo '<p>Delete <strong>'.htmlspecialchars($file_location).'</strong></p>';
	echo '<form method="POST" action="edit-pattern.php" onsubmit="return confirm(\'Are you sure?\');">';
	echo '<input type="hidden" name="delete" value="'.htmlspecialchars(basename($file_location)).'" />';
	echo '<input type="hidden" name="delete_dir" value="'.(strpos($file_location, '/standard/') ? 'standard' : 'custom').'" />';
	echo '<input type="submit" value="Delete" class="btn btn-danger" />';
	echo '</form>';
}

// TEST URLs
if (!empty($matched)) {
	if ($sconfig = SiteConfig::build_from_array(explode("\n", $contents))) {
		if (!empty($sconfig->test_url)) {
			echo '<hr /><h3>Test URLs</h3>';
			echo '<ul>';
			foreach ($sconfig->test_url as $test_url) {
				$ftr_test_url = $test_url;
				if (strtolower(substr($ftr_test_url, 0, 7)) == 'http://') {
					$ftr_test_url = substr($ftr_test_url, 7);
				}
				$ftr_test_url = '../makefulltextfeed.php?url='.urlencode($ftr_test_url);
				echo '<li>';
				echo '<a href="'.htmlspecialchars($test_url).'" target="_blank">'.htmlspecialchars($test_url).'</a>';
				echo ' | <a href="'.$ftr_test_url.'" target="_blank">Full-Text RSS result</a>';
				echo ' | <a href="'.$ftr_test_url.'&debug" target="_blank">Debug</a>';
				echo '</li>';
			}
			echo '</ul>';
		}
	}
}

// RELATED files
if (!empty($related)) {
	echo '<hr /><h3>Related files</h3>';
	echo '<ul>';
	foreach (array_keys($related) as $_m_file) {
		preg_match('!/(standard|custom)/(.+?)\.txt$!', $_m_file, $_m);
		echo '<li><a href="edit-pattern.php?lookin='.$_m[1].'&url='.urlencode($_m[2]).'">'.htmlspecialchars($_m_file).'</a></li>';
	}
	echo '</ul>';
}
?>
<script>
var editor = CodeMirror.fromTextArea(document.getElementById("config"), {
	lineNumbers: true,
	theme: 'default',
	lineWrapping: true
});
</script>