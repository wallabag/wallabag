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

$notices = array();

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
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        if (Session::login($_SESSION['login'], $_SESSION['pass'], $_POST['login'], Tools::encodeString($_POST['password'] . $_POST['login']))) {
            Tools::logm('login successful');
            $notices['value'] = _('login successful');

            if (!empty($_POST['longlastingsession'])) {
                $_SESSION['longlastingsession'] = 31536000;
                $_SESSION['expires_on'] = time() + $_SESSION['longlastingsession'];
                session_set_cookie_params($_SESSION['longlastingsession']);
            } else {
                session_set_cookie_params(0);
            }
            session_regenerate_id(true);
            Tools::redirect($referer);
        }
        Tools::logm('login failed');
        $notices['value'] = _('Login failed !');
        Tools::redirect();
    } else {
        Tools::logm('login failed');
        Tools::redirect();
    }
}
elseif (isset($_GET['logout'])) {
    # see you soon !
    Tools::logm('logout');
    Session::logout();
    Tools::redirect();
}
elseif  (isset($_GET['config'])) {
    # Update password
    if (isset($_POST['password']) && isset($_POST['password_repeat'])) {
        if ($_POST['password'] == $_POST['password_repeat'] && $_POST['password'] != "") {
            if (!MODE_DEMO) {
                Tools::logm('password updated');
                $poche->store->updatePassword(Tools::encodeString($_POST['password'] . $_SESSION['login']));
                Session::logout();
                Tools::redirect();
            }
            else {
                Tools::logm('in demo mode, you can\'t do this');
            }
        }
    }
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
    'notices' => $notices,
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