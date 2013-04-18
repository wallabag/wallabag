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

$entries = get_entries($view);

$tpl->assign('title', 'poche, a read it later open source system');
$tpl->assign('entries', $entries);
$tpl->assign('load_all_js', 1);

$tpl->draw('head');
if (Session::isLogged()) {
    $tpl->draw('home');
    $tpl->draw('entries');
    $tpl->draw('js');
}
else {
    $tpl->draw('login');
}
$tpl->draw('footer');