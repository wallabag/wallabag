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
$db = new db(DB_PATH);

$action = (isset ($_GET['action'])) ? htmlspecialchars($_GET['action']) : '';
$id     = (isset ($_GET['id'])) ? htmlspecialchars($_GET['id']) : '';

switch ($action)
{
    case 'toggle_fav' :
        $sql_action     = "UPDATE entries SET is_fav=~is_fav WHERE id=?";
        $params_action  = array($id);
        break;
    case 'toggle_archive' :
        $sql_action     = "UPDATE entries SET is_read=~is_read WHERE id=?";
        $params_action  = array($id);
        break;
    default:
        break;
}

# action query
if (isset($sql_action))
{
    $query = $db->getHandle()->prepare($sql_action);
    $query->execute($params_action);
}
?>