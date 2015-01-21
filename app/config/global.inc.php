<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define('ROOT', dirname(__FILE__) . '/../..');

require_once ROOT . '/vendor/autoload.php';

# system configuration
require_once __DIR__ . '/config.inc.php';
require_once __DIR__ . '/config.inc.default.php';

if (!ini_get('date.timezone') || !@date_default_timezone_set(ini_get('date.timezone'))) {
    date_default_timezone_set('UTC');
}

if (defined('ERROR_REPORTING')) {
    error_reporting(ERROR_REPORTING);
}