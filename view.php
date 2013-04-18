<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

include dirname(__FILE__).'/inc/config.php';

$id = (isset ($_GET['id'])) ? htmlspecialchars($_GET['id']) : '';

if(!empty($id)) {

    $entry = get_article($id);

    if ($entry != NULL) {
        $tpl->assign('id', $entry[0]['id']);
        $tpl->assign('url', $entry[0]['url']);
        $tpl->assign('title', $entry[0]['title']);
        $tpl->assign('content', $entry[0]['content']);
        $tpl->assign('is_fav', $entry[0]['is_fav']);
        $tpl->assign('is_read', $entry[0]['is_read']);
        $tpl->assign('load_all_js', 0);
        $tpl->draw('view');
    }
    else {
        logm('error in view call : entry is NULL');
    }
}
else {
    logm('error in view call : id is empty');
}