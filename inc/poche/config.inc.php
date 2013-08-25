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

require_once __DIR__ . '/../../inc/3rdparty/simplepie/SimplePieAutoloader.php';
require_once __DIR__ . '/../../inc/3rdparty/simplepie/SimplePie/Core.php';
require_once __DIR__ . '/../../inc/3rdparty/content-extractor/ContentExtractor.php';
require_once __DIR__ . '/../../inc/3rdparty/content-extractor/SiteConfig.php';
require_once __DIR__ . '/../../inc/3rdparty/humble-http-agent/HumbleHttpAgent.php';
require_once __DIR__ . '/../../inc/3rdparty/humble-http-agent/SimplePie_HumbleHttpAgent.php';
require_once __DIR__ . '/../../inc/3rdparty/humble-http-agent/CookieJar.php';
require_once __DIR__ . '/../../inc/3rdparty/feedwriter/FeedItem.php';
require_once __DIR__ . '/../../inc/3rdparty/feedwriter/FeedWriter.php';
require_once __DIR__ . '/../../inc/3rdparty/feedwriter/DummySingleItemFeed.php';

if (DOWNLOAD_PICTURES) {
    require_once __DIR__ . '/../../inc/poche/pochePictures.php';
}

if (!ini_get('date.timezone') || !@date_default_timezone_set(ini_get('date.timezone'))) {
    date_default_timezone_set('UTC');
}

$poche = new Poche();
#XSRF protection with token
// if (!empty($_POST)) {
//     if (!Session::isToken($_POST['token'])) {
//         die(_('Wrong token'));
//     }
//     unset($_SESSION['tokens']);
// }