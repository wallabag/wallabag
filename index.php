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
$view   = (isset ($_GET['view'])) ? htmlspecialchars($_GET['view']) : '';
$id     = (isset ($_GET['id'])) ? htmlspecialchars($_GET['id']) : '';

switch ($action)
{
    case 'add':
        $url = (isset ($_GET['url'])) ? $_GET['url'] : '';
        if ($url == '')
            continue;

        $url    = html_entity_decode(trim($url));

        // We remove the annoying parameters added by FeedBurner and GoogleFeedProxy (?utm_source=...)
        // from shaarli, by sebsauvage
        $i=strpos($url,'&utm_source='); if ($i!==false) $url=substr($url,0,$i);
        $i=strpos($url,'?utm_source='); if ($i!==false) $url=substr($url,0,$i);
        $i=strpos($url,'#xtor=RSS-'); if ($i!==false) $url=substr($url,0,$i);

        $title  = $url;
        if (!preg_match('!^https?://!i', $url))
            $url = 'http://' . $url;

        $html = Encoding::toUTF8(get_external_file($url,15));
        if (isset($html) and strlen($html) > 0)
        {
            $r = new Readability($html, $url);
            if($r->init())
            {
                $title = $r->articleTitle->innerHTML;
            }
        }

        $query = $db->getHandle()->prepare('INSERT INTO entries ( url, title ) VALUES (?, ?)');
        $query->execute(array($url, $title));
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
    die('query error : '.$e->getMessage());
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
    die('query error : '.$e->getMessage());
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
                <ul id="entries">
                <?php
                foreach ($entries as $entry)
                {
                    ?>
                    <li id="entry-<?php echo $entry['id']; ?>" class="entrie mb2">
                        <span class="content">
                            <h2 class="h6-like">
                                <a href="readityourself.php?url=<?php echo urlencode($entry['url']); ?>"><?php echo $entry['title']; ?>
                            </h2>
                            <div class="tools">
                                <a title="toggle mark as read" class="tool archive <?php echo ( ($entry['is_read'] == '0') ? 'archive-off' : '' ); ?>" onclick="toggle_archive(<?php echo $entry['id']; ?>)"><span></span></a>
                                <a title="toggle favorite" class="tool fav <?php echo ( ($entry['is_fav'] == '0') ? 'fav-off' : '' ); ?>" onclick="toggle_favorite(this, <?php echo $entry['id']; ?>)"><span></span></a>
                                <a href="?action=delete&id=<?php echo $entry['id']; ?>" title="toggle delete" onclick="return confirm('Are you sure?')" class="tool delete"><span></span></a>
                            </div>
                        </span>
                    </li>
                <?php
                }
                ?>
                </ul>
            </div>
        </div>
        <footer class="mr2 mt3 smaller">
            <p>download poche on <a href="http://github.com/nicosomb/github">github</a><br />follow us on <a href="https://twitter.com/getpoche" title="follow us on twitter">twitter</a></p>
        </footer>
        <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="js/poche.js"></script>
    </body>
</html>
