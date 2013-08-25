<?php
require_once dirname(__FILE__).'/../inc/poche/Tools.class.php';
include dirname(__FILE__).'/../inc/poche/define.inc.php';
include dirname(__FILE__).'/../inc/poche/myconfig.inc.php';
require_once __DIR__ . '/../inc/poche/Database.class.php';
$store = new Database();
?>
<!DOCTYPE html>
<!--[if lte IE 6]> <html class="no-js ie6 ie67 ie678" lang="en"> <![endif]-->
<!--[if lte IE 7]> <html class="no-js ie7 ie67 ie678" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="no-js ie8 ie678" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <title>updating poche</title>
    </head>
    <body>
        <h1>update poche to 1.0-beta4</h1>

        <h2>Changelog</h2>
        <ul>
            <li>enhancement: updating and installing poche are more easier</li>
            <li>enhancement: poche now uses Full Text RSS to fetch content</li>
            <li>enhancement: css and twig files are more clean, thanks to NumEricR and nicofrand</li>
            <li>enhancement: updated german translation, thanks to HLFH</li>
            <li>enhancement: add db/, cache/ and assets/ directories in versioning</li>
            <li>enhancement: display messages when error with import, thanks to EliasZ</li>
            <li>enhancement: poche compatibility test file</li>
            <li>enhancement: <a href="https://github.com/inthepoche/poche/issues/112">#112</a>: link with shaarli</li>
            <li>enhancement: <a href="https://github.com/inthepoche/poche/issues/162">#162</a>: links to firefox / chrome / android apps in config screen</li>
            <li>bug: encode url to share with twitter / email / shaarli</li>
            <li>bug: Add IPv4 url support (and others beginning by a digit)</li>
            <li>bug: title page in article view was wrong</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/148">#148</a>: use of undefined constant POCHE_VERSION</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/148">#149</a>: can't poche theguardian.com</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/150">#150</a>: default title for untitled articles</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/150">#151</a>: HTML entities in titles are encoded twice</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/169">#169</a>: entries height with short description</li>
            <li>bug: <a href="https://github.com/inthepoche/poche/issues/175">#175</a>: IP addresses do not appear in "view original"</li>
        </ul>
        <p>To update your poche, please fill the following fields.</p>
        <p>
        <form name="update" method="post">
            <div><label for="login">login:</label> <input type="text" name="login" id="login" /></div>
            <div><label for="password">password:</label> <input type="password" name="password" id="password" /></div>
            <div><input type="hidden" name="go" value="ok" /><input type="submit" value="update" /></div>
        </form>
        </p>
<?php
if (isset($_POST['go'])) {
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        $user = $store->login($_POST['login'], sha1($_POST['password'] . $_POST['login'] . SALT));
        if ($user != array()) {
            $myconfig_file = '../inc/poche/myconfig.inc.php';
            # just change version number in config file
            
            if (!is_writable('../inc/poche/myconfig.inc.php')) {
                die('You don\'t have write access to open ./inc/poche/myconfig.inc.php.');
            }

            if (file_exists($myconfig_file))
            {
                $content = str_replace('1.0-beta3', '1.0-beta4', file_get_contents($myconfig_file));
                file_put_contents($myconfig_file, $content);
            }
?>
        <p><span style="color: green;">your poche is up to date!</span></p>
        <p><span style="color: red;">don't forget to delete ./install/ folder after the update.</span></p>
        <p><a href="../">go back to your poche</a></p>
<?php
        }
    }
}
?>
    </body>
</html>