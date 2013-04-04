<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

/**
 * TODO
 * gestion des erreurs sqlite (duplicate tout ça)
 * gérer si url vide
 * traiter les variables passées en get
 * récupérer le titre de la page pochée (cf readityourself.php)
 * actions archive, fav et delete à traiter
 * bookmarklet
 * améliorer présentation des liens
 * améliorer présentation d'un article
 * aligner verticalement les icones d'action
 * afficher liens mis en favoris et archivés
 * tri des liens
 */

try
{
    $db_handle = new PDO('sqlite:db/poche.sqlite');
    $db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
    die('database error : '.$e->getMessage());
}

$action = (isset ($_GET['action'])) ? htmlspecialchars($_GET['action']) : '';
$view = (isset ($_GET['view'])) ? htmlspecialchars($_GET['view']) : '';
$id = (isset ($_GET['id'])) ? htmlspecialchars($_GET['id']) : '';

switch ($action) {
    case 'add':
        $url = (isset ($_GET['url'])) ? htmlspecialchars($_GET['url']) : '';
        $title = $url;
        $query = $db_handle->prepare('INSERT INTO entries ( url, title ) VALUES (?, ?)');
        $query->execute(array($url, $title));
        break;
    case 'toggle_fav' :
        $sql_action = "UPDATE entries SET is_fav=~is_fav WHERE id=?";
        $params_action = array($id);
        break;
    case 'toggle_archive' :
        $sql_action = "UPDATE entries SET is_read=~is_read WHERE id=?";
        $params_action = array($id);
        break;
    case 'delete':
        break;
    default:
        break;
}

try
{
    # action query
    if (isset($sql_action)) {
        $query = $db_handle->prepare($sql_action);
        $query->execute($params_action);
    }
}
catch (Exception $e)
{
    die('query error : '.$e->getMessage());
}

switch ($view) {
    case 'archive':
        $sql = "SELECT * FROM entries WHERE is_read=?";
        $params = array(-1);
        break;
    case 'fav' :
        $sql = "SELECT * FROM entries WHERE is_fav=?";
        $params = array(-1);
        break;
    default:
        $sql = "SELECT * FROM entries WHERE is_read=?";
        $params = array(0);
        break;
}

# view query
try
{
    $query = $db_handle->prepare($sql);
    $query->execute($params);
    $entries = $query->fetchAll();
}
catch (Exception $e)
{
    die('query error : '.$e->getMessage());
}

function url() {
    $protocol = "http";
    if(isset($_SERVER['HTTPS'])) {
        if($_SERVER['HTTPS'] != "off") {
            $protocol = "https";
        }
    }

    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<!--[if lte IE 6]> <html class="no-js ie6 ie67 ie678" lang="en"> <![endif]-->
<!--[if lte IE 7]> <html class="no-js ie7 ie67 ie678" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="no-js ie8 ie678" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<html>
    <head>
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=10">
        <title>poche : queue</title>
        <link rel="stylesheet" href="css/knacss.css" media="all">
        <link rel="stylesheet" href="css/style.css" media="all">
    </head>
    <body>
        <header>
            <h1>poche, a read it later open source system</h1>
        </header>
        <div id="main" class="w800p">
            <ul id="links">
                <li><a href="index.php">home</a></li>
                <li><a href="?view=fav">favorites</a></li>
                <li><a href="?view=archive">archive</a></li>
                <li><a title="i am a bookmarklet, use me !" href="javascript:(function(){var%20url%20=%20location.href;var%20title%20=%20document.title%20||%20url;window.open('<?php echo url()?>?action=add&url='%20+%20encodeURIComponent(url),'_self');})();">poche it !</a></li>
            </ul>
            <ul id="entries">
                <?php
                foreach ($entries as $entry) {
                    echo '<li><a href="readityourself.php?url='.urlencode($entry['url']).'">' . $entry['title'] . '</a> <a href="?action=toggle_archive&id='.$entry['id'].'" title="toggle mark as read" class="tool">&#10003;</a> <a href="?action=toggle_fav&id='.$entry['id'].'" title="toggle favorite" class="tool">'.(($entry['is_fav'] == 0) ? '&#9734;' : '&#9733;' ).'</a> <a href="#" title="toggle delete" class="tool">&#10799;</a></li>';
                }
                ?>
            </ul>
        </div>
        <footer class="mr2 mt3">
            <p class="smaller"><a href="http://github.com/nicosomb/poche">poche</a> is a read it later open source system, based on <a href="http://www.memiks.fr/readityourself/">ReadItYourself</a>. poche is developed by <a href="http://nicolas.loeuillet.org">Nicolas Lœuillet</a> under the <a href="http://www.wtfpl.net/">Do What the Fuck You Want to Public License</a></p>
        </footer>
    </body>
</html>