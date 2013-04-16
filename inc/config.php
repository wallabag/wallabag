<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */
define ('DB_PATH', 'sqlite:./db/poche.sqlite');

include 'db.php';
include 'functions.php';
require_once 'Readability.php';
require_once 'Encoding.php';
require_once 'rain.tpl.class.php';

$db = new db(DB_PATH);

# Initialisation de RainTPL
raintpl::$tpl_dir   = './tpl/';
raintpl::$cache_dir = './cache/';
raintpl::$base_url  = get_poche_url();
raintpl::configure('path_replace', false);
raintpl::configure('debug', false);
$tpl = new raintpl();

# Démarrage session et initialisation du jeton de sécurité
session_start();

if (!isset($_SESSION['token_poche'])) {
    $token = md5(uniqid(rand(), TRUE));
    $_SESSION['token_poche'] = $token;
    $_SESSION['token_time_poche'] = time();
}

# Traitement des paramètres et déclenchement des actions
$view               = (isset ($_REQUEST['view'])) ? htmlentities($_REQUEST['view']) : 'index';
$action             = (isset ($_REQUEST['action'])) ? htmlentities($_REQUEST['action']) : '';
$_SESSION['sort']   = (isset ($_REQUEST['sort'])) ? htmlentities($_REQUEST['sort']) : 'id';
$id                 = (isset ($_REQUEST['id'])) ? htmlspecialchars($_REQUEST['id']) : '';
$url                = (isset ($_GET['url'])) ? $_GET['url'] : '';
$token              = (isset ($_REQUEST['token'])) ? $_REQUEST['token'] : '';

if ($action != '') {
    action_to_do($action, $id, $url, $token);
}