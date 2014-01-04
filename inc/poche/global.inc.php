<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
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

require_once INCLUDES . '/poche/Database.class.php';
require_once INCLUDES . '/3rdparty/simple_html_dom.php';
require_once INCLUDES . '/3rdparty/paginator.php';
require_once INCLUDES . '/3rdparty/Session.class.php';

require_once INCLUDES . '/3rdparty/libraries/feedwriter/FeedItem.php';
require_once INCLUDES . '/3rdparty/libraries/feedwriter/FeedWriter.php';
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