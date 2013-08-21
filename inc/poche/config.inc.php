<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

require_once __DIR__ . '/../../inc/poche/define.inc.php';

# /!\ Be careful if you change the lines below /!\
if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    die('Twig does not seem installed. Have a look at <a href="http://inthepoche.com/?pages/Documentation">the documentation.</a>');
}

// if (file_exists(__DIR__ . '/../../inc/poche/myconfig.inc.php')) {
    // require_once __DIR__ . '/../../inc/poche/myconfig.inc.php';
// }
require_once __DIR__ . '/../../inc/poche/User.class.php';
require_once __DIR__ . '/../../inc/poche/Url.class.php';
require_once __DIR__ . '/../../inc/3rdparty/class.messages.php';
require_once __DIR__ . '/../../inc/poche/Poche.class.php';
require_once __DIR__ . '/../../inc/3rdparty/Readability.php';
require_once __DIR__ . '/../../inc/3rdparty/Encoding.php';
require_once __DIR__ . '/../../inc/poche/Database.class.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../inc/3rdparty/simple_html_dom.php';
require_once __DIR__ . '/../../inc/3rdparty/paginator.php';
require_once __DIR__ . '/../../inc/3rdparty/Session.class.php';

if (DOWNLOAD_PICTURES) {
    require_once __DIR__ . '/../../inc/poche/pochePictures.php';
}

$poche = new Poche();
#XSRF protection with token
// if (!empty($_POST)) {
//     if (!Session::isToken($_POST['token'])) {
//         die(_('Wrong token'));
//     }
//     unset($_SESSION['tokens']);
// }