<?php
require_once dirname(__FILE__).'/../inc/poche/Tools.class.php';
include dirname(__FILE__).'/../inc/poche/define.inc.php';
require_once __DIR__ . '/../inc/poche/Database.class.php';
$store = new Database();
$old_salt = '464v54gLLw928uz4zUBqkRJeiPY68zCX';
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
        <h1>update poche to 1.0-beta3</h1>

        <h2>Changelog</h2>
        <p>
            <ul>
                <li>this awesome updating step</li>
                <li>error message when install folder exists</li>
                <li>more tests before installation (write access, etc.)</li>
                <li>updated README to make installation easier</li>
                <li>german language thanks to HLFH</li>
                <li>spanish language thanks to Nitche</li>
                <li>new file ./inc/poche/myconfig.inc.php created to store language and salt</li>
                <li><a href="https://github.com/inthepoche/poche/issues/119">#119</a>: salt is now created when installing poche</li>
                <li><a href="https://github.com/inthepoche/poche/issues/130">#130</a>: robots.txt added</li>
                <li><a href="https://github.com/inthepoche/poche/issues/136">#136</a>: error during readability import</li>
                <li><a href="https://github.com/inthepoche/poche/issues/137">#137</a>: mixed content alert in https</li>
                <li><a href="https://github.com/inthepoche/poche/issues/138">#138</a>: change pattern to parse url with #</li>
            </ul>
        </p>
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
        $user = $store->login($_POST['login'], sha1($_POST['password'] . $_POST['login'] . $old_salt));
        if ($user != array()) {
            $new_salt = md5(time() . $_SERVER['SCRIPT_FILENAME'] . rand());
            $myconfig_file = '../inc/poche/myconfig.inc.php';
            if (!is_writable('../inc/poche/')) {
                die('You don\'t have write access to create ./inc/poche/myconfig.inc.php.');
            }

            if (!file_exists($myconfig_file))
            {
                $fp = fopen($myconfig_file, 'w');
                
                fwrite($fp, '<?php'."\r\n");
                fwrite($fp, "define ('POCHE_VERSION', '1.0-beta3');" . "\r\n");
                fwrite($fp, "define ('SALT', '" . $new_salt . "');" . "\r\n");
                fwrite($fp, "define ('LANG', 'en_EN.utf8');" . "\r\n");
                fclose($fp);
            }
            # faire une mise à jour de la table users en prenant en compte le nouveau SALT généré
            $store->updatePassword($user['id'], sha1($_POST['password'] . $_POST['login'] . $new_salt));
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