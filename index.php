<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

include dirname(__FILE__).'/inc/config.php';

$action = (isset ($_REQUEST['action'])) ? htmlentities($_REQUEST['action']) : '';
$view   = (isset ($_GET['view'])) ? htmlentities($_GET['view']) : 'index';
$id     = (isset ($_REQUEST['id'])) ? htmlspecialchars($_REQUEST['id']) : '';
$url    = (isset ($_GET['url'])) ? $_GET['url'] : '';
$token    = (isset ($_POST['token'])) ? $_POST['token'] : '';

if ($action != '') {
    action_to_do($action, $id, $url, $token);
}

$entries = display_view($view);

$tpl->assign('title', 'poche, a read it later open source system');
$tpl->assign('view', $view);
$tpl->assign('poche_url', get_poche_url());
$tpl->assign('entries', $entries);
$tpl->assign('load_all_js', 1);
$tpl->assign('token', $_SESSION['token_poche']);
$tpl->draw('home');