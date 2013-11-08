<?php
require 'check_setup.php';
require 'vendor/password.php';
require 'vendor/PicoTools/Dependency_Injection.php';
require 'vendor/PicoTools/Translator.php';
require 'vendor/PicoDb/Database.php';
require 'vendor/PicoDb/Table.php';
require 'vendor/PicoFeed/Client.php';
require 'schema.php';
require 'model.php';

if (file_exists('config.php')) require 'config.php';

defined('APP_VERSION') or define('APP_VERSION', '2.0.0-alpha');
defined('HTTP_TIMEOUT') or define('HTTP_TIMEOUT', 20);
defined('DB_FILENAME') or define('DB_FILENAME', 'data/db.sqlite');
defined('DEBUG') or define('DEBUG', true);
defined('DEBUG_FILENAME') or define('DEBUG_FILENAME', 'data/debug.log');
defined('THEME_DIRECTORY') or define('THEME_DIRECTORY', 'themes');
defined('PLUGIN_DIRECTORY') or define('PLUGIN_DIRECTORY', 'plugins');
defined('SESSION_SAVE_PATH') or define('SESSION_SAVE_PATH', '');
defined('PROXY_HOSTNAME') or define('PROXY_HOSTNAME', '');
defined('PROXY_PORT') or define('PROXY_PORT', 3128);
defined('PROXY_USERNAME') or define('PROXY_USERNAME', '');
defined('PROXY_PASSWORD') or define('PROXY_PASSWORD', '');
defined('DEMO_MODE') or define('DEMO_MODE', false);

PicoFeed\Client::proxy(PROXY_HOSTNAME, PROXY_PORT, PROXY_USERNAME, PROXY_PASSWORD);

PicoTools\container('db', function() {

    $db = new PicoDb\Database(array(
        'driver' => 'sqlite',
        'filename' => DB_FILENAME
    ));

    if ($db->schema()->check(Model\DB_VERSION)) {
        return $db;
    }
    else {
        die('Unable to migrate database schema.');
    }
});
