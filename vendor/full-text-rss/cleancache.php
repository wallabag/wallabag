<?php
// Full-Text RSS: Clear Cache
// Author: Keyvan Minoukadeh
// Copyright (c) 2012 Keyvan Minoukadeh
// License: AGPLv3

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
// Set up your scheduler (e.g. cron) to request this file periodically.
// Note: this file must _not_ be named cleancache.php so please rename it.
// We ask you to do this to prevent others from initiating
// the cache cleanup process. It will not run if it's called cleancache.php.

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
@set_time_limit(120);

// check file name
if (basename(__FILE__) == 'cleancache.php') die('cleancache.php must be renamed');

// set include path
set_include_path(realpath(dirname(__FILE__).'/libraries').PATH_SEPARATOR.get_include_path());

// Autoloading of classes allows us to include files only when they're
// needed. If we've got a cached copy, for example, only Zend_Cache is loaded.
function __autoload($class_name) {
	static $mapping = array(
		'Zend_Cache' => 'Zend/Cache.php'
	);
	if (isset($mapping[$class_name])) {
		//echo "Loading $class_name\n<br />";
		require_once $mapping[$class_name];
		return true;
	} else {
		return false;
	}
}
require_once dirname(__FILE__).'/config.php';
if (!$options->caching) die('Caching is disabled');

// clean APC cache
if ($options->apc && function_exists('apc_delete')) {
	$_apc_data = apc_cache_info('user');
	foreach ($_apc_data['cache_list'] as $_apc_item) {
	  if ($_apc_item['ttl'] > 0 && ($_apc_item['ttl'] + $_apc_item['creation_time'] < time())) {
		apc_delete($_apc_item['info']);
	  }
	}
}

// clean rss (non-key) cache
$frontendOptions = array(
   'lifetime' => 20*60,
   'automatic_serialization' => false,
   'write_control' => false,
   'automatic_cleaning_factor' => 0,
   'ignore_user_abort' => false
);
$backendOptions = array(
	'cache_dir' => $options->cache_dir.'/rss/',
	'file_locking' => false,
	'read_control' => true,
	'read_control_type' => 'strlen',
	'hashed_directory_level' => $options->cache_directory_level,
	'hashed_directory_perm' => 0777,
	'cache_file_perm' => 0664,
	'file_name_prefix' => 'ff'
);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
$cache->clean(Zend_Cache::CLEANING_MODE_OLD);

// clean rss (key) cache
$frontendOptions = array(
   'lifetime' => 20*60,
   'automatic_serialization' => false,
   'write_control' => false,
   'automatic_cleaning_factor' => 0,
   'ignore_user_abort' => false
);
$backendOptions = array(
	'cache_dir' => $options->cache_dir.'/rss-with-key/',
	'file_locking' => false,
	'read_control' => true,
	'read_control_type' => 'strlen',
	'hashed_directory_level' => $options->cache_directory_level,
	'hashed_directory_perm' => 0777,
	'cache_file_perm' => 0664,
	'file_name_prefix' => 'ff'
);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
$cache->clean(Zend_Cache::CLEANING_MODE_OLD);

?>