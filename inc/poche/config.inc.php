<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('POCHE_VERSION', '1.0-alpha');
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
define ('LANG', 'fr_FR.UTF8');
$storage_type = 'sqlite'; # sqlite, mysql, (file, not yet)

# /!\ Be careful if you change the lines below /!\
require_once './inc/poche/Tools.class.php';
require_once './inc/poche/Url.class.php';
require_once './inc/poche/Poche.class.php';
require_once './inc/3rdparty/Readability.php';
require_once './inc/3rdparty/Encoding.php';
require_once './inc/3rdparty/Session.class.php';
require_once './inc/store/store.class.php';
require_once './inc/store/' . $storage_type . '.class.php';
require_once './vendor/autoload.php';
require_once './inc/3rdparty/simple_html_dom.php';
require_once './inc/3rdparty/class.messages.php';

if (DOWNLOAD_PICTURES) {
    require_once './inc/poche/pochePictures.php';
}

$poche = new Poche($storage_type);
$poche->messages = new Messages();