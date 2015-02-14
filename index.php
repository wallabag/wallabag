<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define ('POCHE', '1.9.0');
require 'check_essentials.php';
require 'check_setup.php';
require_once 'inc/poche/global.inc.php';

// Start session
Session::$sessionName = 'wallabag';
Session::init();

// Let's rock !
$wallabag = new Poche();
$wallabag->run();
