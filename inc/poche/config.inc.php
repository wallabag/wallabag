<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

define ('SALT', 'strong'); # put a strong string here
define ('LANG', 'en_EN.utf8');

define ('STORAGE', 'sqlite'); # postgres, mysql or sqlite

define ('STORAGE_SQLITE', ROOT . '/db/poche.sqlite'); # if you are using sqlite, where the database file is located

# only for postgres & mysql
define ('STORAGE_SERVER', 'localhost');
define ('STORAGE_DB', 'poche');
define ('STORAGE_USER', 'poche');
define ('STORAGE_PASSWORD', 'poche');

#################################################################################
# Do not trespass unless you know what you are doing
#################################################################################

define ('MODE_DEMO', FALSE);
define ('DEBUG_POCHE', true);
define ('DOWNLOAD_PICTURES', FALSE);
define ('CONVERT_LINKS_FOOTNOTES', FALSE);
define ('REVERT_FORCED_PARAGRAPH_ELEMENTS', FALSE);
define ('SHARE_TWITTER', TRUE);
define ('SHARE_MAIL', TRUE);
define ('SHARE_SHAARLI', FALSE);
define ('SHAARLI_URL', 'http://myshaarliurl.com');
define ('FLATTR', TRUE);
define ('FLATTR_API', 'https://api.flattr.com/rest/v2/things/lookup/?url=');
define ('NOT_FLATTRABLE', '0');
define ('FLATTRABLE', '1');
define ('FLATTRED', '2');
define ('ABS_PATH', 'assets/');

define ('DEFAULT_THEME', 'default');

define ('THEME', ROOT . '/themes');
define ('LOCALE', ROOT . '/locale');
define ('CACHE', ROOT . '/cache');

define ('PAGINATION', '10');

define ('POCHE_VERSION', '1.0-beta4');

define ('IMPORT_POCKET_FILE', ROOT . '/ril_export.html');
define ('IMPORT_READABILITY_FILE', ROOT . '/readability');
define ('IMPORT_INSTAPAPER_FILE', ROOT . '/instapaper-export.html');