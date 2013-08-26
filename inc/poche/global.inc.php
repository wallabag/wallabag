<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

# the poche system root directory (/inc)
define('INCLUDES', dirname(__FILE__) . '/..');

# the poche root directory
define('ROOT', INCLUDES . '/..');

# system configuration; database credentials et cetera
require_once INCLUDES . '/poche/config.inc.php';

# Composer its autoloader for automatically loading Twig
require_once ROOT . '/vendor/autoload.php';
//if (! file_exists(__DIR__ . '/../../vendor/autoload.php')) {
//    die('Twig does not seem installed. Have a look at <a href="http://inthepoche.com/?pages/Documentation">the documentation.</a>');
//}

// if (file_exists(__DIR__ . '/../../inc/poche/myconfig.inc.php')) {
    // require_once __DIR__ . '/../../inc/poche/myconfig.inc.php';
// }

require_once INCLUDES . '/poche/Tools.class.php';
require_once INCLUDES . '/poche/User.class.php';
require_once INCLUDES . '/poche/Url.class.php';
require_once INCLUDES . '/3rdparty/class.messages.php';
require_once INCLUDES . '/poche/Poche.class.php';
require_once INCLUDES . '/3rdparty/Readability.php';
require_once INCLUDES . '/3rdparty/Encoding.php';
require_once INCLUDES . '/poche/Database.class.php';
require_once INCLUDES . '/3rdparty/simple_html_dom.php';
require_once INCLUDES . '/3rdparty/paginator.php';
require_once INCLUDES . '/3rdparty/Session.class.php';

if (DOWNLOAD_PICTURES) {
    require_once  INCLUDES . '/poche/pochePictures.php';
}
#XSRF protection with token
// if (!empty($_POST)) {
//     if (!Session::isToken($_POST['token'])) {
//         die(_('Wrong token'));
//     }
//     unset($_SESSION['tokens']);
// }