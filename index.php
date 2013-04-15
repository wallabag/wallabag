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
$db = new db(DB_PATH);

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

        try
        {
            # insert query
            $query = $db->getHandle()->prepare('INSERT INTO entries ( url, title, content ) VALUES (?, ?, ?)');
            $query->execute(array($url, $parametres_url['title'], $parametres_url['content']));
        }
        catch (Exception $e)
        {
            error_log('insert query error : '.$e->getMessage());
        }

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
        <title>poche, a read it later open source system</title>
        <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="img/apple-touch-icon-precomposed.png">
        <link rel="stylesheet" href="css/knacss.css" media="all">
        <link rel="stylesheet" href="css/style.css" media="all">
    </head>
    <body>
        <header>
            <h1><img src="img/logo.png" alt="logo poche" />poche</h1>
        </header>
        <div id="main">
            <ul id="links">
                <li><a href="index.php" <?php echo (($view == 'index') ? 'class="current"' : ''); ?>>home</a></li>
                <li><a href="?view=fav" <?php echo (($view == 'fav') ? 'class="current"' : ''); ?>>favorites</a></li>
                <li><a href="?view=archive" <?php echo (($view == 'archive') ? 'class="current"' : ''); ?>>archive</a></li>
                <li><a style="cursor: move" title="i am a bookmarklet, use me !" href="javascript:(function(){var%20url%20=%20location.href;var%20title%20=%20document.title%20||%20url;window.open('<?php echo url()?>?action=add&url='%20+%20encodeURIComponent(url),'_self');})();">poche it !</a></li>
            </ul>
            <div id="content">
                <?php
                foreach ($entries as $entry)
                {
                    ?>
                    <div id="entry-<?php echo $entry['id']; ?>" class="entrie mb2">
                        <span class="content">
                            <h2 class="h6-like">
                                <a href="view.php?id=<?php echo $entry['id']; ?>"><?php echo $entry['title']; ?>
                            </h2>
                            <div class="tools">
                                <a title="toggle mark as read" class="tool archive <?php echo ( ($entry['is_read'] == '0') ? 'archive-off' : '' ); ?>" onclick="toggle_archive(this, <?php echo $entry['id']; ?>)"><span></span></a>
                                <a title="toggle favorite" class="tool fav <?php echo ( ($entry['is_fav'] == '0') ? 'fav-off' : '' ); ?>" onclick="toggle_favorite(this, <?php echo $entry['id']; ?>)"><span></span></a>
                                <a href="?action=delete&id=<?php echo $entry['id']; ?>" title="toggle delete" onclick="return confirm('Are you sure?')" class="tool delete"><span></span></a>
                            </div>
                        </span>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <footer class="mr2 mt3 smaller">
            <p>powered by <a href="http://inthepoche.com">poche</a><br />follow us on <a href="https://twitter.com/getpoche" title="follow us on twitter">twitter</a></p>
        </footer>
        <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.masonry.min.js"></script>
        <script type="text/javascript" src="js/poche.js"></script>
        <script type="text/javascript">
            $( window ).load( function()
            {
                var columns    = 3,
                    setColumns = function() { columns = $( window ).width() > 640 ? 3 : $( window ).width() > 320 ? 2 : 1; };

                setColumns();
                $( window ).resize( setColumns );

                $( '#content' ).masonry(
                {
                    itemSelector: '.entrie',
                    columnWidth:  function( containerWidth ) { return containerWidth / columns; }
                });
            });
        </script>
    </body>
</html>
