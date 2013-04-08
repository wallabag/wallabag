<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

require_once dirname(__FILE__).'/inc/Readability.php';
require_once dirname(__FILE__).'/inc/Encoding.php';
include dirname(__FILE__).'/inc/functions.php';

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

        $query = $db_handle->prepare('INSERT INTO entries ( url, title ) VALUES (?, ?)');
        $query->execute(array($url, $title));
        break;
    case 'toggle_fav' :
        $sql_action     = "UPDATE entries SET is_fav=~is_fav WHERE id=?";
        $params_action  = array($id);
        break;
    case 'toggle_archive' :
        $sql_action     = "UPDATE entries SET is_read=~is_read WHERE id=?";
        $params_action  = array($id);
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
        $query = $db_handle->prepare($sql_action);
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
        break;
}

# view query
try
{
    $query  = $db_handle->prepare($sql);
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
        <div id="main" class="w960p">
            <ul id="links">
                <li><a href="index.php">home</a></li>
                <li><a href="?view=fav">favorites</a></li>
                <li><a href="?view=archive">archive</a></li>
                <li><a style="cursor: move" title="i am a bookmarklet, use me !" href="javascript:(function(){var%20url%20=%20location.href;var%20title%20=%20document.title%20||%20url;window.open('<?php echo url()?>?action=add&url='%20+%20encodeURIComponent(url),'_self');})();">poche it !</a></li>
            </ul>
            <div id="entries">
                <?php
                $i = 0;
                foreach ($entries as $entry)
                {
                    if ($i == 0) {
                        echo '<section class="line grid3">';
                    }
                    echo '<aside class="mod entrie mb2"><h2 class="h6-like"><a href="readityourself.php?url='.urlencode($entry['url']).'">' . $entry['title'] . '</h2><div class="tools"><a href="?action=toggle_archive&id='.$entry['id'].'" title="toggle mark as read" class="tool">&#10003;</a> <a href="?action=toggle_fav&id='.$entry['id'].'" title="toggle favorite" class="tool">'.(($entry['is_fav'] == 0) ? '&#9734;' : '&#9733;' ).'</a> <a href="?action=delete&id='.$entry['id'].'" title="toggle delete"  onclick="return confirm(\'Are you sure?\')" class="tool">&#10799;</a></div></aside>';

                    $i++;
                    if ($i == 3) {
                        echo '</section>';
                        $i = 0;
                    }
                }
                ?>
            </div>
        </div>
        <footer class="mr2 mt3">
            <p class="smaller"><a href="http://github.com/nicosomb/poche">poche</a> is a read it later open source system, based on <a href="http://www.memiks.fr/readityourself/">ReadItYourself</a>. <a href="https://twitter.com/getpoche" title="follow us on twitter">@getpoche</a>. Logo by <a href="http://www.iconfinder.com/icondetails/43256/128/jeans_monotone_pocket_icon">Brightmix</a>. poche is developed by <a href="http://nicolas.loeuillet.org">Nicolas Lœuillet</a> under the <a href="http://www.wtfpl.net/">WTFPL</a>.</p>
        </footer>
    </body>
</html>