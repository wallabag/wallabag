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

require_once INCLUDES . '/poche/Tools.class.php';
require_once INCLUDES . '/poche/User.class.php';
require_once INCLUDES . '/poche/Url.class.php';
require_once INCLUDES . '/3rdparty/class.messages.php';
require_once INCLUDES . '/poche/Poche.class.php';

require_once INCLUDES . '/3rdparty/Readability.php';
require_once INCLUDES . '/poche/PocheReadability.php';

require_once INCLUDES . '/3rdparty/Encoding.php';
require_once INCLUDES . '/poche/Database.class.php';
require_once INCLUDES . '/3rdparty/simple_html_dom.php';
require_once INCLUDES . '/3rdparty/paginator.php';
require_once INCLUDES . '/3rdparty/Session.class.php';

require_once INCLUDES . '/3rdparty/simplepie/SimplePieAutoloader.php';
require_once INCLUDES . '/3rdparty/simplepie/SimplePie/Core.php';
require_once INCLUDES . '/3rdparty/content-extractor/ContentExtractor.php';
require_once INCLUDES . '/3rdparty/content-extractor/SiteConfig.php';
require_once INCLUDES . '/3rdparty/humble-http-agent/HumbleHttpAgent.php';
require_once INCLUDES . '/3rdparty/humble-http-agent/SimplePie_HumbleHttpAgent.php';
require_once INCLUDES . '/3rdparty/humble-http-agent/CookieJar.php';
require_once INCLUDES . '/3rdparty/feedwriter/FeedItem.php';
require_once INCLUDES . '/3rdparty/feedwriter/FeedWriter.php';
require_once INCLUDES . '/3rdparty/feedwriter/DummySingleItemFeed.php';
require_once INCLUDES . '/3rdparty/FlattrItem.class.php';

# Composer its autoloader for automatically loading Twig
if (! file_exists(ROOT . '/vendor/autoload.php')) {
    Poche::$canRenderTemplates = false;
} else {
    require_once ROOT . '/vendor/autoload.php';
}

# system configuration; database credentials et cetera
if (! file_exists(INCLUDES . '/poche/config.inc.php')) {
    Poche::$configFileAvailable = false;
} else {
    require_once INCLUDES . '/poche/config.inc.php';
}

if (Poche::$configFileAvailable && DOWNLOAD_PICTURES) {
    require_once  INCLUDES . '/poche/pochePictures.php';
}

if (!ini_get('date.timezone') || !@date_default_timezone_set(ini_get('date.timezone'))) {
    date_default_timezone_set('UTC');
}

#XSRF protection with token
if (!empty($_POST)) {
    if (!Session::isToken($_POST['token'])) {
        die(_('Wrong token'));
    }
    unset($_SESSION['token']);
}