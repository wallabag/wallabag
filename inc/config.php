<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */
define ('DB_PATH', 'sqlite:./db/poche.sqlite');

include 'db.php';
include 'functions.php';
require_once 'Readability.php';
require_once 'Encoding.php';
?>