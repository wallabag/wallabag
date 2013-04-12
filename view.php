<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

header('Content-type:text/html; charset=utf-8');

include dirname(__FILE__).'/inc/config.php';
require_once dirname(__FILE__).'/inc/rain.tpl.class.php';
$db = new db(DB_PATH);

if(isset($_GET['id']) && $_GET['id'] != '') {

    $sql    = "SELECT * FROM entries WHERE id=?";
    $params = array(intval($_GET['id']));

    # view article query
    try
    {
        $query  = $db->getHandle()->prepare($sql);
        $query->execute($params);
        $entry = $query->fetchAll();
    }
    catch (Exception $e)
    {
        die('query error : '.$e->getMessage());
    }

    generate_page($entry[0]['url'], $entry[0]['title'], $entry[0]['content']);
}