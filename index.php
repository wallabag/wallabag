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

$tpl->assign('title', 'poche, a read it later open source system');
$tpl->assign('view', $view);
$tpl->assign('poche_url', get_poche_url());
$tpl->assign('entries', $entries);
$tpl->assign('load_all_js', 1);
$tpl->assign('token', $_SESSION['token_poche']);

$tpl->draw('head');
$tpl->draw('home');
$tpl->draw('entries');
$tpl->draw('js');
$tpl->draw('footer');
