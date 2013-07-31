<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('POCHE_VERSION', '0.3');

if (!is_dir('db/')) {
    @mkdir('db/',0705);
}

define ('MODE_DEMO', FALSE);
define ('ABS_PATH', 'assets/');
define ('CONVERT_LINKS_FOOTNOTES', TRUE);
define ('REVERT_FORCED_PARAGRAPH_ELEMENTS',FALSE);
define ('DOWNLOAD_PICTURES', TRUE);
define ('SALT', '464v54gLLw928uz4zUBqkRJeiPY68zCX');
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

$store     = new $storage_type();
# initialisation de RainTPL
raintpl::$tpl_dir   = './tpl/';
raintpl::$cache_dir = './cache/';
raintpl::$base_url  = get_poche_url();
raintpl::configure('path_replace', false);
raintpl::configure('debug', false);
$tpl = new raintpl();

if(!$store->isInstalled())
{
    logm('poche still not installed');
    $tpl->draw('install');
    if (isset($_GET['install'])) {
        if (($_POST['password'] == $_POST['password_repeat']) 
            && $_POST['password'] != "" && $_POST['login'] != "") {
            $store->install($_POST['login'], encode_string($_POST['password'] . $_POST['login']));
            Session::logout();
            MyTool::redirect();
        }
    }
    exit();
}

$_SESSION['login'] = (isset ($_SESSION['login'])) ? $_SESSION['login'] : $store->getLogin();
$_SESSION['pass']  = (isset ($_SESSION['pass'])) ? $_SESSION['pass'] : $store->getPassword();

$msg = new Messages();
$tpl->assign('msg', $msg);