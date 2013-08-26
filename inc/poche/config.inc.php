<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('SALT', '76cdae25a43a194ab30fad43f1962da1');
define ('LANG', 'en_EN.utf8');

define ('STORAGE', 'mysql'); # postgres, mysql, sqlite
define ('STORAGE_SERVER', 'localhost'); # leave blank for sqlite
define ('STORAGE_DB', 'poche'); # only for postgres & mysql
define ('STORAGE_SQLITE', ROOT . '/db/poche.sqlite'); # if you are using sqlite, where the database file is located
define ('STORAGE_USER', 'root'); # leave blank for sqlite
define ('STORAGE_PASSWORD', ''); # leave blank for sqlite

#################################################################################
# Do not trespass unless you know what you are doing
#################################################################################

define ('MODE_DEMO', FALSE);
define ('DEBUG_POCHE', true);
define ('CONVERT_LINKS_FOOTNOTES', FALSE);
define ('REVERT_FORCED_PARAGRAPH_ELEMENTS', FALSE);
define ('DOWNLOAD_PICTURES', FALSE);
define ('SHARE_TWITTER', TRUE);
define ('SHARE_MAIL', TRUE);
define ('SHARE_SHAARLI', FALSE);
define ('SHAARLI_URL', 'http://myshaarliurl.com');
define ('ABS_PATH', 'assets/');

define ('DEFAULT_THEME', 'default');

define ('THEME', ROOT . '/themes');
define ('LOCALE', ROOT . '/locale');
define ('CACHE', ROOT . '/cache');

define ('PAGINATION', '10');
//define ('THEME', 'light');

define ('POCHE_VERSION', '1.0-beta4');

define ('IMPORT_POCKET_FILE', ROOT . '/ril_export.html');
define ('IMPORT_READABILITY_FILE', ROOT . '/readability');
define ('IMPORT_INSTAPAPER_FILE', ROOT . '/instapaper-export.html');