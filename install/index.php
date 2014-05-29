<?php
$errors = array();
$successes = array();

/* Function taken from at http://php.net/manual/en/function.rmdir.php#110489
 * Idea : nbari at dalmp dot com
 * Rights unknown
 * Here in case of .gitignore files
 */
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }

if (isset($_GET['clean'])) {
    if (is_dir('install')){
    delTree('install');
    header('Location: index.php');      
    }
}

if (isset($_POST['download'])) {
    if (!file_put_contents("cache/vendor.zip", fopen("http://static.wallabag.org/files/vendor.zip", 'r'))) {
        $errors[] = 'Impossible to download vendor.zip. Please <a href="http://wllbg.org/vendor">download it manually</a> and unzip it in your wallabag folder.';
    }
    else {
        if (extension_loaded('zip')) {
            $zip = new ZipArchive();
            if ($zip->open("cache/vendor.zip") !== TRUE){
                $errors[] = 'Impossible to open cache/vendor.zip. Please unzip it manually in your wallabag folder.';
            }
            if ($zip->extractTo(realpath(''))) {
                @unlink("cache/vendor.zip");
                $successes[] = 'twig is now installed, you can install wallabag.';
            }
            else {
                $errors[] = 'Impossible to extract cache/vendor.zip. Please unzip it manually in your wallabag folder.';
            }
            $zip->close();
        }
        else {
            $errors[] = 'zip extension is not enabled in your PHP configuration. Please unzip cache/vendor.zip in your wallabag folder.';
        }
    }
}
else if (isset($_POST['install'])) {
    if (!is_dir('vendor')) {
        $errors[] = 'You must install twig before.';
    }
    else {
        $continue = true;
        // Create config.inc.php
        if (!copy('inc/poche/config.inc.default.php', 'inc/poche/config.inc.php')) {
            $errors[] = 'Installation aborted, impossible to create inc/poche/config.inc.php file. Maybe you don\'t have write access to create it.';
            $continue = false;
        }
        else {
            function generate_salt() {
                mt_srand(microtime(true)*100000 + memory_get_usage(true));
                return md5(uniqid(mt_rand(), true));
            }

            $content = file_get_contents('inc/poche/config.inc.php');
            $salt = generate_salt();
            $content = str_replace("define ('SALT', '');", "define ('SALT', '".$salt."');", $content);
            file_put_contents('inc/poche/config.inc.php', $content);
        }

        if ($continue) {

            // User informations
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $salted_password = sha1($password . $username . $salt);

            // Database informations
            if ($_POST['db_engine'] == 'sqlite') {
                if (!copy('install/poche.sqlite', 'db/poche.sqlite')) {
                    $errors[] = 'Impossible to create inc/poche/config.inc.php file.';
                    $continue = false;
                }
                else {
                    $db_path = 'sqlite:' . realpath('') . '/db/poche.sqlite';
                    $handle = new PDO($db_path);
                    $sql_structure = "";
                }
            }
            else {
                $content = file_get_contents('inc/poche/config.inc.php');

                if ($_POST['db_engine'] == 'mysql') {
                    $db_path = 'mysql:host=' . $_POST['mysql_server'] . ';dbname=' . $_POST['mysql_database'];
                    $content = str_replace("define ('STORAGE_SERVER', 'localhost');", "define ('STORAGE_SERVER', '".$_POST['mysql_server']."');", $content);
                    $content = str_replace("define ('STORAGE_DB', 'poche');", "define ('STORAGE_DB', '".$_POST['mysql_database']."');", $content);
                    $content = str_replace("define ('STORAGE_USER', 'poche');", "define ('STORAGE_USER', '".$_POST['mysql_user']."');", $content);
                    $content = str_replace("define ('STORAGE_PASSWORD', 'poche');", "define ('STORAGE_PASSWORD', '".$_POST['mysql_password']."');", $content);
                    $handle = new PDO($db_path, $_POST['mysql_user'], $_POST['mysql_password']); 

                    $sql_structure = file_get_contents('install/mysql.sql');
                }
                else if ($_POST['db_engine'] == 'postgres') {
                    $db_path = 'pgsql:host=' . $_POST['pg_server'] . ';dbname=' . $_POST['pg_database'];
                    $content = str_replace("define ('STORAGE_SERVER', 'localhost');", "define ('STORAGE_SERVER', '".$_POST['pg_server']."');", $content);
                    $content = str_replace("define ('STORAGE_DB', 'poche');", "define ('STORAGE_DB', '".$_POST['pg_database']."');", $content);
                    $content = str_replace("define ('STORAGE_USER', 'poche');", "define ('STORAGE_USER', '".$_POST['pg_user']."');", $content);
                    $content = str_replace("define ('STORAGE_PASSWORD', 'poche');", "define ('STORAGE_PASSWORD', '".$_POST['pg_password']."');", $content);
                    $handle = new PDO($db_path, $_POST['pg_user'], $_POST['pg_password']);

                    $sql_structure = file_get_contents('install/postgres.sql');
                }

                $content = str_replace("define ('STORAGE', 'sqlite');", "define ('STORAGE', '".$_POST['db_engine']."');", $content);
                file_put_contents('inc/poche/config.inc.php', $content);
            }

            if ($continue) {

                function executeQuery($handle, $sql, $params) {
                    try
                    {
                        $query = $handle->prepare($sql);
                        $query->execute($params);
                        return $query->fetchAll();
                    }
                    catch (Exception $e)
                    {
                        return FALSE;
                    }
                }

                // create database structure
                $query = executeQuery($handle, $sql_structure, array());

                // Create user
                $handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = 'INSERT INTO users (username, password, name) VALUES (?, ?, ?)';
                $params = array($username, $salted_password, $username);
                $query = executeQuery($handle, $sql, $params);

                $id_user = $handle->lastInsertId();

                $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
                $params = array($id_user, 'pager', '10');
                $query = executeQuery($handle, $sql, $params);

                $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
                $params = array($id_user, 'language', 'en_EN.UTF8');
                $query = executeQuery($handle, $sql, $params);

                $successes[] = 'wallabag is now installed. You can now <a href="index.php?clean=0">access it !</a>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0">
        <meta charset="utf-8">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=10">
        <![endif]-->
        <title>wallabag - installation</title>
        <link rel="shortcut icon" type="image/x-icon" href="themes/baggy/img/favicon.ico" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="themes/baggy/img/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="themes/baggy/img/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="themes/baggy/img/apple-touch-icon-precomposed.png">
        <link href='//fonts.googleapis.com/css?family=PT+Sans:700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="themes/baggy/css/ratatouille.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/font.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/main.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/messages.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/print.css" media="print">
        <script src="themes/default/js/jquery-2.0.3.min.js"></script>
        <script src="themes/baggy/js/init.js"></script>
    </head>
    <body>
        <header class="w600p center mbm">
            <h1 class="logo">
                <img width="100" height="100" src="themes/baggy/img/logo-w.png" alt="logo poche" />
            </h1>
        </header>
        <div id="main">
            <button id="menu" class="icon icon-menu desktopHide"><span>Menu</span></button>
            <ul id="links" class="links">
                <li><a href="http://www.wallabag.org/frequently-asked-questions/">FAQ</a></li>
                <li><a href="http://doc.wallabag.org/">doc</a></li>
                <li><a href="http://www.wallabag.org/help/">help</a></li>
                <li><a href="http://www.wallabag.org/">wallabag.org</a></li>
            </ul> 
            <?php if (!empty($errors)) : ?>
                <div class='messages error install'>
                    <p>Errors during installation:</p>
                    <p>
                        <ul>
                        <?php foreach($errors as $error) :?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </p>
                    <p><a href="index.php">Please reload</a> this page when you think you resolved these problems.</p>
                </div>
            <?php endif; ?>
            <?php if (!empty($successes)) : ?>
                <div class='messages success install'>
                    <p>
                        <ul>
                        <?php foreach($successes as $success) :?>
                            <li><?php echo $success; ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </p>
                </div>
            <?php else : ?>
                <?php if (file_exists('inc/poche/config.inc.php') && is_dir('vendor')) : ?>
                <div class='messages success install'>
                    <p>
                        wallabag seems already installed. If you want to update it, you only have to delete install folder, then <a href="index.php">reload this page</a>.
                    </p>
                </div>
                <?php endif; ?>    
            <?php endif; ?>
            <p>To install wallabag, you just have to fill the following fields. That's all.</p>
            <p>Don't forget to check your server compatibility <a href="wallabag_compatibility_test.php?from=install">here</a>.</p>
            <form method="post">
                <fieldset>
                    <legend><strong>Technical settings</strong></legend>
                    <?php if (!is_dir('vendor')) : ?>
                        <div class='messages notice install'>wallabag needs twig, a template engine (<a href="http://twig.sensiolabs.org/">?</a>). Two ways to install it:<br />
                        <ul>
                            <li>automatically download and extract vendor.zip into your wallabag folder. 
                            <p><input type="submit" name="download" value="Download vendor.zip" /></p>
                            <?php if (!extension_loaded('zip')) : ?>
                                <b>Be careful, zip extension is not enabled in your PHP configuration. You'll have to unzip vendor.zip manually.</b>
                            <?php endif; ?>
                                <em>This method is mainly recommended if you don't have a dedicated server.</em></li>
                            <li>use <a href="http://getcomposer.org/">Composer</a> :<pre><code>curl -s http://getcomposer.org/installer | php
php composer.phar install</code></pre></li>
                        </ul>
                        </div>
                    <?php endif; ?>
                    <p>
                        Database engine:
                        <ul>
                            <li><label for="sqlite">SQLite</label> <input name="db_engine" type="radio" checked="" id="sqlite" value="sqlite" />
                            <div id="pdo_sqlite" class='messages error install'>
                                <p>You have to enable <a href="http://php.net/manual/ref.pdo-sqlite.php">pdo_sqlite extension</a>.</p>
                            </div>
                            </li>
                            <li>
                                <label for="mysql">MySQL</label> <input name="db_engine" type="radio" id="mysql" value="mysql" />
                                <ul id="mysql_infos">
                                    <li><label for="mysql_server">Server</label> <input type="text" placeholder="localhost" id="mysql_server" name="mysql_server" /></li>
                                    <li><label for="mysql_database">Database</label> <input type="text" placeholder="wallabag" id="mysql_database" name="mysql_database" /></li>
                                    <li><label for="mysql_user">User</label> <input type="text" placeholder="user" id="mysql_user" name="mysql_user" /></li>
                                    <li><label for="mysql_password">Password</label> <input type="text" placeholder="p4ssw0rd" id="mysql_password" name="mysql_password" /></li>
                                </ul>
                            </li>
                            <li>
                                <label for="postgres">PostgreSQL</label> <input name="db_engine" type="radio" id="postgres" value="postgres" />
                                <ul id="pg_infos">
                                    <li><label for="pg_server">Server</label> <input type="text" placeholder="localhost" id="pg_server" name="pg_server" /></li>
                                    <li><label for="pg_database">Database</label> <input type="text" placeholder="wallabag" id="pg_database" name="pg_database" /></li>
                                    <li><label for="pg_user">User</label> <input type="text" placeholder="user" id="pg_user" name="pg_user" /></li>
                                    <li><label for="pg_password">Password</label> <input type="text" placeholder="p4ssw0rd" id="pg_password" name="pg_password" /></li>
                                </ul>
                            </li>
                        </ul>
                    </p>
                </fieldset>

                <fieldset>
                    <legend><strong>User settings</strong></legend>
                    <p>
                        <label for="username">Username</label>
                        <input type="text" required id="username" name="username" value="wallabag" />
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <input type="password" required id="password" name="password" value="wallabag" />
                    </p>
                    <p>
                        <label for="show">Show password:</label> <input name="show" id="show" type="checkbox" onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'">
                    </p>
                </fieldset>

                <input type="submit" id="install_button" value="Install wallabag" name="install" />
            </form>
        </div>
        <script>
            $("#mysql_infos").hide();
            $("#pg_infos").hide();

            <?php
            if (!extension_loaded('pdo_sqlite')) : ?>
            $("#install_button").hide();
            <?php
            else :
            ?>
            $("#pdo_sqlite").hide();
            <?php
            endif;
            ?>

            $("input[name=db_engine]").click(function() 
                {
                    if ( $("#mysql").prop('checked')) {
                        $("#mysql_infos").show();
                        $("#pg_infos").hide();
                        $("#pdo_sqlite").hide();
                        $("#install_button").show();
                    }
                    else {
                        if ( $("#postgres").prop('checked')) {
                            $("#mysql_infos").hide();
                            $("#pg_infos").show();
                            $("#pdo_sqlite").hide();
                            $("#install_button").show();
                        }
                        else {
                            $("#mysql_infos").hide();
                            $("#pg_infos").hide();
                            <?php
                            if (!extension_loaded('pdo_sqlite')) : ?>
                            $("#pdo_sqlite").show();
                            $("#install_button").hide();
                            <?php
                            endif;
                            ?>
                        }
                    }
                });
        </script>
    </body>
</html>