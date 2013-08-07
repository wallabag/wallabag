# Installing poche

Get the [latest twig version](https://github.com/inthepoche/poche/archive/twig.zip) of poche on github. Unzip it and upload it on your server.

your datas can be stored on sqlite, postgres or mysql databases.

Edit /inc/poche/config.inc.php :

```php
define ('STORAGE','sqlite'); # postgres, mysql, sqlite
define ('STORAGE_SERVER', 'localhost'); # leave blank for sqlite
define ('STORAGE_DB', 'poche'); # only for postgres & mysql
define ('STORAGE_SQLITE', './db/poche.sqlite');
define ('STORAGE_USER', 'user'); # leave blank for sqlite
define ('STORAGE_PASSWORD', 'pass'); # leave blank for sqlite
```

poche must have write access on assets, cache and db directories.

[PHP cURL](http://www.php.net/manual/en/book.curl.php) & [tidy_parse_string](http://www.php.net/manual/en/tidy.parsestring.php) are recommended.

## storage in sqlite 
You have to install [sqlite for php](http://www.php.net/manual/en/book.sqlite.php) on your server.

Copy /install/poche.sqlite in /db

## storage in mysql
Execute /install/mysql.sql file in your database.

## storage in postgres 
Execute /install/postgres.sql file in your database.

Install composer in your project : 
```bash
curl -s http://getcomposer.org/installer | php
```
Install via composer : 
```bash
php composer.phar install
```

## updating from poche 0.3
With poche <= 0.3, all your datas were stored in a sqlite file. The structure of this file changed. 

You have to execute http://yourpoche/install/update_sqlite_from_0_to_1.php before using this new version.

That's all, you can use poche ! 