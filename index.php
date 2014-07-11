<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

define ('POCHE', '1.8.0');
require 'check_setup.php';
require_once 'inc/poche/global.inc.php';

if (defined('ERROR_REPORTING')) {
	error_reporting(ERROR_REPORTING);
}

// Start session
Session::$sessionName = 'wallabag';
Session::init();

// Let's rock !
$wallabag = new Poche();
$wallabag->run();
