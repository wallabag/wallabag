<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define ('WALLABAG', '2.0.0-alpha');

require_once '../app/check_essentials.php';
require_once '../app/check_setup.php';
require_once '../app/config/global.inc.php';

// Check if /cache is writeable
if (! is_writable(CACHE)) {
    die('The directory ' . CACHE . ' must be writeable by your web server user');
}

Session::$sessionName = 'wallabag';
Session::init();

// Let's rock !
$wallabag = new Wallabag\Wallabag\Wallabag();
$wallabag->run();