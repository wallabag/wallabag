<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

# storage
define ('STORAGE','sqlite'); # postgres, mysql, sqlite
define ('STORAGE_SERVER', 'localhost'); # leave blank for sqlite
define ('STORAGE_DB', 'poche'); # only for postgres & mysql
define ('STORAGE_SQLITE', './db/poche.sqlite');
define ('STORAGE_USER', 'postgres'); # leave blank for sqlite
define ('STORAGE_PASSWORD', 'postgres'); # leave blank for sqlite

define ('POCHE_VERSION', '1.0-beta2');
define ('MODE_DEMO', FALSE);
define ('DEBUG_POCHE', FALSE);
define ('CONVERT_LINKS_FOOTNOTES', FALSE);
define ('REVERT_FORCED_PARAGRAPH_ELEMENTS', FALSE);
define ('DOWNLOAD_PICTURES', FALSE);
define ('SHARE_TWITTER', TRUE);
define ('SHARE_MAIL', TRUE);
define ('SALT', '464v54gLLw928uz4zUBqkRJeiPY68zCX');
define ('ABS_PATH', 'assets/');
define ('TPL', './tpl');
define ('LOCALE', './locale');
define ('CACHE', './cache');
define ('LANG', 'en_EN.UTF8');
define ('PAGINATION', '10');
define ('THEME', 'light');

# /!\ Be careful if you change the lines below /!\
require_once './inc/poche/User.class.php';
require_once './inc/poche/Tools.class.php';
require_once './inc/poche/Url.class.php';
require_once './inc/3rdparty/class.messages.php';
require_once './inc/poche/Poche.class.php';
require_once './inc/3rdparty/Readability.php';
require_once './inc/3rdparty/Encoding.php';
require_once './inc/poche/Database.class.php';
require_once './vendor/autoload.php';
require_once './inc/3rdparty/simple_html_dom.php';
require_once './inc/3rdparty/paginator.php';
require_once './inc/3rdparty/Session.class.php';

if (DOWNLOAD_PICTURES) {
    require_once './inc/poche/pochePictures.php';
}

$poche = new Poche();
#XSRF protection with token
// if (!empty($_POST)) {
//     if (!Session::isToken($_POST['token'])) {
//         die(_('Wrong token'));
//     }
//     unset($_SESSION['tokens']);
// }