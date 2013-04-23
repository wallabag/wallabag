<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('POCHE_VERSION', '0.2.1');

if (!is_dir('db/')) {
    @mkdir('db/',0705);
}

define ('ABS_PATH', 'assets/');
define ('CONVERT_LINKS_FOOTNOTES', TRUE);
define ('DOWNLOAD_PICTURES', TRUE);
define ('REVERT_FORCED_PARAGRAPH_ELEMENTS',FALSE);

$storage_type = 'sqlite'; # sqlite or file

include 'functions.php';
require_once 'Readability.php';
require_once 'Encoding.php';
require_once 'rain.tpl.class.php';
require_once 'MyTool.class.php';
require_once 'Session.class.php';
require_once 'store/store.class.php';
require_once 'store/sqlite.class.php';
require_once 'store/file.class.php';
require_once 'class.messages.php';

Session::init();

$store 	= new $storage_type();
$msg 	= new Messages();

# initialisation de RainTPL
raintpl::$tpl_dir   = './tpl/';
raintpl::$cache_dir = './cache/';
raintpl::$base_url  = get_poche_url();
raintpl::configure('path_replace', false);
raintpl::configure('debug', false);
$tpl = new raintpl();
$tpl->assign('msg', $msg);