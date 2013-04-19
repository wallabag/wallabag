<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

if (!is_dir('db/')) {
    @mkdir('db/',0705);
}



define ('DB_PATH', 'sqlite:./db/poche.sqlite');
define ('ABS_PATH', 'assets/');
define ('CONFIG_PATH', 'db/user_config.php');
define ('CONVERT_LINKS_FOOTNOTES', TRUE);
define ('DOWNLOAD_PICTURES', TRUE);

include 'db.php';
include 'functions.php';
@include CONFIG_PATH;
require_once 'Readability.php';
require_once 'Encoding.php';
require_once 'rain.tpl.class.php';
require_once 'MyTool.class.php';
require_once 'Session.class.php';


$db = new db(DB_PATH);

# initialisation de RainTPL
raintpl::$tpl_dir   = './tpl/';
raintpl::$cache_dir = './cache/';
raintpl::$base_url  = get_poche_url();
raintpl::configure('path_replace', false);
raintpl::configure('debug', false);
$tpl = new raintpl();

# initialize session
Session::init();
# XSRF protection with token
if (!empty($_POST)) {
    if (!Session::isToken($_POST['token'])) {
        die('Wrong token.');
    }
    unset($_SESSION['tokens']);
}

$ref = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

if (isset($_GET['login'])) {
    // Login
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        if (Session::login(LOGIN, HASH, $_POST['login'], sha1($_POST['password'].$_POST['login'].SALT))) {
            if (!empty($_POST['longlastingsession'])) {
                $_SESSION['longlastingsession'] = 31536000;
                $_SESSION['expires_on'] = time() + $_SESSION['longlastingsession'];
                session_set_cookie_params($_SESSION['longlastingsession']);
            } else {
                session_set_cookie_params(0); // when browser closes
            }
            session_regenerate_id(true);

            MyTool::redirect();
        }
        logm('login failed');
        die("Login failed !");
    } else {
        logm('login successful');
    }
}
elseif (isset($_GET['logout'])) {
    logm('logout');
    Session::logout();
    MyTool::redirect();
}

# Traitement des paramètres et déclenchement des actions
$view               = (isset ($_REQUEST['view'])) ? htmlentities($_REQUEST['view']) : 'index';
$action             = (isset ($_REQUEST['action'])) ? htmlentities($_REQUEST['action']) : '';
$_SESSION['sort']   = (isset ($_REQUEST['sort'])) ? htmlentities($_REQUEST['sort']) : 'id';
$id                 = (isset ($_REQUEST['id'])) ? htmlspecialchars($_REQUEST['id']) : '';
$url                = (isset ($_GET['url'])) ? $_GET['url'] : '';

$tpl->assign('isLogged', Session::isLogged());
$tpl->assign('referer', $ref);
$tpl->assign('view', $view);
$tpl->assign('poche_url', get_poche_url());

if ($action != '') {
    action_to_do($action, $url, $id);
}


/**
 * Installation
 */
function install()
{
    if (Session::isInstall(CONFIG_PATH)) die('You are not authorized to alter config.');
    if (!empty($_POST['setlogin']) && !empty($_POST['setpassword']))
    {
       if(!Session::writeConfig(CONFIG_PATH,$_POST['setlogin'],$_POST['setpassword']))die('Poche could not create the config file. Please make sure Poche has the right to write in the folder is it installed in.');
       MyTool::redirect();
    }   
}

