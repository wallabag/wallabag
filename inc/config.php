<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

if(!is_dir('db/')){mkdir('db/',0705);}
define ('DB_PATH', 'sqlite:./db/poche.sqlite');
define ('ABS_PATH', 'archiveImg/');

include 'db.php';
include 'functions.php';
require_once 'Readability.php';
require_once 'Encoding.php';
require_once 'rain.tpl.class.php';

$db = new db(DB_PATH);

raintpl::$tpl_dir   = './tpl/';
raintpl::$cache_dir = './cache/';
raintpl::$base_url  = get_poche_url();
raintpl::configure('path_replace', false);
raintpl::configure('debug', false);
$tpl = new raintpl();