<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('POCHE', '1.7.1');
require 'check_setup.php';
require_once 'inc/poche/global.inc.php';

# Set error reporting level
if (defined('ERROR_REPORTING')) {
	error_reporting(ERROR_REPORTING);
}

# Start session
Session::$sessionName = 'poche';
Session::init();

# Start Poche
$poche = new Poche();
$notInstalledMessage = $poche -> getNotInstalledMessage();

# Parse GET & REFERER vars
$referer = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
$view = Tools::checkVar('view', 'home');
$action = Tools::checkVar('action');
$id = Tools::checkVar('id');
$_SESSION['sort'] = Tools::checkVar('sort', 'id');
$url = new Url((isset ($_GET['url'])) ? $_GET['url'] : '');

# vars to _always_ send to templates
$tpl_vars = array(
    'referer' => $referer,
    'view' => $view,
    'poche_url' => Tools::getPocheUrl(),
    'title' => _('wallabag, a read it later open source system'),
    'token' => Session::getToken(),
    'theme' => $poche->getTheme()
);

if (! empty($notInstalledMessage)) {
    if (! Poche::$canRenderTemplates || ! Poche::$configFileAvailable) {
        # We cannot use Twig to display the error message
        echo '<h1>Errors</h1><ol>';
        foreach ($notInstalledMessage as $message) {
            echo '<li>' . $message . '</li>';
        }
        echo '</ol>';
        die();
    } else {
        # Twig is installed, put the error message in the template
        $tpl_file = Tools::getTplFile('error');
        $tpl_vars = array_merge($tpl_vars, array('msg' => $poche->getNotInstalledMessage()));
        echo $poche->tpl->render($tpl_file, $tpl_vars);
        exit;
    }
}

# poche actions
if (isset($_GET['login'])) {
    # hello you
    $poche->login($referer);
} elseif (isset($_GET['logout'])) {
    # see you soon !
    $poche->logout();
} elseif (isset($_GET['config'])) {
    # Update password
    $poche->updatePassword();
} elseif (isset($_GET['newuser'])) {
    $poche->createNewUser();
} elseif (isset($_GET['deluser'])) {
    $poche->deleteUser();
} elseif (isset($_GET['epub'])) {
    $poche->createEpub();
} elseif (isset($_GET['import'])) {
    $import = $poche->import();
    $tpl_vars = array_merge($tpl_vars, $import);
} elseif (isset($_GET['download'])) {
    Tools::download_db();
} elseif (isset($_GET['empty-cache'])) {
    $poche->emptyCache();
} elseif (isset($_GET['export'])) {
    $poche->export();
} elseif (isset($_GET['updatetheme'])) {
    $poche->updateTheme();
} elseif (isset($_GET['updatelanguage'])) {
    $poche->updateLanguage();
} elseif (isset($_GET['uploadfile'])) {
    $poche->uploadFile();
} elseif (isset($_GET['feed'])) {
    if (isset($_GET['action']) && $_GET['action'] == 'generate') {
        $poche->generateToken();
    }
    else {
        $tag_id = (isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0);
        $poche->generateFeeds($_GET['token'], filter_var($_GET['user_id'],FILTER_SANITIZE_NUMBER_INT), $tag_id, $_GET['type']);
    }
}

elseif (isset($_GET['plainurl']) && !empty($_GET['plainurl'])) {
    $plain_url = new Url(base64_encode($_GET['plainurl']));
    $poche->action('add', $plain_url);
}

if (Session::isLogged()) {
    $poche->action($action, $url, $id);
    $tpl_file = Tools::getTplFile($view);
    $tpl_vars = array_merge($tpl_vars, $poche->displayView($view, $id));
} elseif(isset($_SERVER['PHP_AUTH_USER'])) {
    if($poche->store->userExists($_SERVER['PHP_AUTH_USER'])) {
        $poche->login($referer);
    } else {
        $poche->messages->add('e', _('login failed: user doesn\'t exist'));
        Tools::logm('user doesn\'t exist');
        $tpl_file = Tools::getTplFile('login');
        $tpl_vars['http_auth'] = 1;
    }
} elseif(isset($_SERVER['REMOTE_USER'])) {
    if($poche->store->userExists($_SERVER['REMOTE_USER'])) {
        $poche->login($referer);
    } else {
        $poche->messages->add('e', _('login failed: user doesn\'t exist'));
        Tools::logm('user doesn\'t exist');
        $tpl_file = Tools::getTplFile('login');
        $tpl_vars['http_auth'] = 1;
    }
} else {
    $tpl_file = Tools::getTplFile('login');
    $tpl_vars['http_auth'] = 0;
    Session::logout();
}

# because messages can be added in $poche->action(), we have to add this entry now (we can add it before)
$messages = $poche->messages->display('all', FALSE);
$tpl_vars = array_merge($tpl_vars, array('messages' => $messages));

# display poche
echo $poche->tpl->render($tpl_file, $tpl_vars);
