<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

include dirname(__FILE__).'/inc/poche/config.inc.php';

# XSRF protection with token
// if (!empty($_POST)) {
//     if (!Session::isToken($_POST['token'])) {
//         die(_('Wrong token'));
//         // TODO remettre le test
//     }
//     unset($_SESSION['tokens']);
// }

$referer = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

if (isset($_GET['login'])) {
    # hello you
    $poche->login($referer);
}
elseif (isset($_GET['logout'])) {
    # see you soon !
    $poche->logout();
}
elseif (isset($_GET['config'])) {
    # Update password
    $poche->updatePassword();
}
elseif (isset($_GET['import'])) {
    $poche->import($_GET['from']);
}

# Aaaaaaand action !
$view = (isset ($_REQUEST['view'])) ? htmlentities($_REQUEST['view']) : 'home';
$full_head = (isset ($_REQUEST['full_head'])) ? htmlentities($_REQUEST['full_head']) : 'yes';
$action = (isset ($_REQUEST['action'])) ? htmlentities($_REQUEST['action']) : '';
$_SESSION['sort'] = (isset ($_REQUEST['sort'])) ? htmlentities($_REQUEST['sort']) : 'id';
$id = (isset ($_REQUEST['id'])) ? htmlspecialchars($_REQUEST['id']) : '';

$url = new Url((isset ($_GET['url'])) ? $_GET['url'] : '');

$tpl_vars = array(
    'referer' => $referer,
    'view' => $view,
    'poche_url' => Tools::getPocheUrl(),
    'demo' => MODE_DEMO,
    'title' => _('poche, a read it later open source system'),
    'token' => Session::getToken(),
);

if (Session::isLogged()) {
    $poche->action($action, $url, $id);
    $tpl_file = Tools::getTplFile($view);
    $tpl_vars = array_merge($tpl_vars, $poche->displayView($view, $id));
}
else {
    $tpl_file = 'login.twig';
}

echo $poche->tpl->render($tpl_file, $tpl_vars);