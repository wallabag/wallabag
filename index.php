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
    $db_handle = new PDO('sqlite:poche.sqlite');
    $db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
    die('error : '.$e->getMessage());
}

$action = (isset ($_GET['action'])) ? htmlspecialchars($_GET['action']) : '';

switch ($action) {
    case 'add':
        $url = (isset ($_GET['url'])) ? htmlspecialchars($_GET['url']) : '';
        $title = $url;
        $query = $db_handle->prepare('INSERT INTO entries ( url, title ) VALUES (?, ?)');
        $query->execute(array($url, $title));
        break;
    case 'archive':
        break;
    case 'fav' :
        break;
    case 'delete':
        break;
    default:
        break;
}

function url(){
  $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
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
				<li><a href="#">favorites</a></li>
				<li><a href="#">archive</a></li>
                <li><a href="javascript:(function(){var%20url%20=%20location.href;var%20title%20=%20document.title%20||%20url;window.open('<?php echo url()?>index.php?action=add&url='%20+%20encodeURIComponent(url),'_self');})();">poche it !</a></li>
			</ul>
		<?php
			$query = $db_handle->prepare("SELECT * FROM entries WHERE read=?");
			$query->execute(array('FALSE'));
			$entries = $query->fetchAll();
		?>
			<ul id="entries">
				<?php
				foreach ($entries as $entry) {
					echo '<li><a href="readityourself.php?url='.urlencode($entry['url']).'">' . $entry['title'] . '</a> <a href="#" title="toggle delete" class="tool">&#10003;</a> <a href="#" title="toggle favorite" class="tool">&#9734;</a> <a href="#" title="toggle mark as read" class="tool">&#10799;</a></li>';
				}
				?>
			</ul>
		</div>
		<footer class="mr2 mt3">
            <p class="smaller"><a href="http://github.com/nicosomb/poche">poche</a> is a read it later open source system, based on <a href="http://www.memiks.fr/readityourself/">ReadItYourself</a>. poche is developed by <a href="http://nicolas.loeuillet.org">Nicolas Lœuillet</a> under the <a href="http://www.wtfpl.net/">Do What the Fuck You Want to Public License</a></p>
        </footer>
	</body>
</html>