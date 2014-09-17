<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

@define ('SALT', ''); # put a strong string here
@define ('LANG', 'en_EN.utf8');

@define ('STORAGE', 'sqlite'); # postgres, mysql or sqlite

@define ('STORAGE_SQLITE', ROOT . '/db/poche.sqlite'); # if you are using sqlite, where the database file is located

# only for postgres & mysql
@define ('STORAGE_SERVER', 'localhost');
@define ('STORAGE_DB', 'poche');
@define ('STORAGE_USER', 'poche');
@define ('STORAGE_PASSWORD', 'poche');

#################################################################################
# Do not trespass unless you know what you are doing
#################################################################################
// Change this if http is running on nonstandard port - i.e is behind cache proxy
@define ('HTTP_PORT', 80);

// Change this if not using the standart port for SSL - i.e you server is behind sslh
@define ('SSL_PORT', 443);

@define ('MODE_DEMO', FALSE);
@define ('DEBUG_POCHE', FALSE);

//default level of error reporting in application. Developers should override it in their config.inc.php: set to E_ALL.
@define ('ERROR_REPORTING', E_ALL & ~E_NOTICE);

@define ('DOWNLOAD_PICTURES', FALSE); # This can slow down the process of adding articles
@define ('REGENERATE_PICTURES_QUALITY', 75);
@define ('CONVERT_LINKS_FOOTNOTES', FALSE);
@define ('REVERT_FORCED_PARAGRAPH_ELEMENTS', FALSE);
@define ('SHARE_TWITTER', TRUE);
@define ('SHARE_MAIL', TRUE);
@define ('SHARE_SHAARLI', FALSE);
@define ('SHAARLI_URL', 'http://myshaarliurl.com');
@define ('FLATTR', TRUE);
@define ('FLATTR_API', 'https://api.flattr.com/rest/v2/things/lookup/?url=');
@define ('NOT_FLATTRABLE', '0');
@define ('FLATTRABLE', '1');
@define ('FLATTRED', '2');
// display or not print link in article view
@define ('SHOW_PRINTLINK', '1');
// display or not percent of read in article view. Affects only default theme.
@define ('SHOW_READPERCENT', '1');
@define ('ABS_PATH', 'assets/');

@define ('DEFAULT_THEME', 'baggy');

@define ('THEME', ROOT . '/themes');
@define ('LOCALE', ROOT . '/locale');
@define ('CACHE', ROOT . '/cache');

@define ('PAGINATION', '10');

//limit for download of articles during import
@define ('IMPORT_LIMIT', 5);
//delay between downloads (in sec)
@define ('IMPORT_DELAY', 5);

