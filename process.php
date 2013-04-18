<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

include dirname(__FILE__).'/inc/config.php';

$entries = display_view($view);
$tpl->assign('token', $_SESSION['token_poche']);
$tpl->assign('entries', $entries);
$tpl->draw('entries');