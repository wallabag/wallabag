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

$action = (isset ($_GET['action'])) ? htmlspecialchars($_GET['action']) : '';
$view   = (isset ($_GET['view'])) ? htmlspecialchars($_GET['view']) : '';
$id     = (isset ($_GET['id'])) ? htmlspecialchars($_GET['id']) : '';
$url    = (isset ($_GET['url'])) ? $_GET['url'] : '';

switch ($action)
{
    case 'add':
        if ($url == '')
            continue;

        $parametres_url = prepare_url($url);
        $sql_action     = 'INSERT INTO entries ( url, title, content ) VALUES (?, ?, ?)';
        $params_action  = array($url, $parametres_url['title'], $parametres_url['content']);
        break;
    case 'delete':
        $sql_action     = "DELETE FROM entries WHERE id=?";
        $params_action  = array($id);
        break;
    default:
        break;
}

try
{
    # action query
    if (isset($sql_action))
    {
        $query = $db->getHandle()->prepare($sql_action);
        $query->execute($params_action);
    }
}
catch (Exception $e)
{
    die('action query error : '.$e->getMessage());
}

switch ($view)
{
    case 'archive':
        $sql    = "SELECT * FROM entries WHERE is_read=? ORDER BY id desc";
        $params = array(-1);
        break;
    case 'fav' :
        $sql    = "SELECT * FROM entries WHERE is_fav=? ORDER BY id desc";
        $params = array(-1);
        break;
    default:
        $sql    = "SELECT * FROM entries WHERE is_read=? ORDER BY id desc";
        $params = array(0);
        $view = 'index';
        break;
}

# view query
try
{
    $query  = $db->getHandle()->prepare($sql);
    $query->execute($params);
    $entries = $query->fetchAll();
}
catch (Exception $e)
{
    die('view query error : '.$e->getMessage());
}

$tpl->assign('title', 'poche, a read it later open source system');
$tpl->assign('view', $view);
$tpl->assign('poche_url', get_poche_url());
$tpl->assign('entries', $entries);
$tpl->assign('load_all_js', 1);
$tpl->draw('home');