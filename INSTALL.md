# Installing poche

## requirements
* PHP 5.2.0 or higher
* XML ([?](http://php.net/xml))
* PCRE ([?](http://php.net/pcre))
* Data filtering ([?](http://uk.php.net/manual/en/book.filter.php))
* Tidy ([?](http://php.net/tidy))
* cURL ([?](http://php.net/curl))
* Parallel URL fetching
* allow_url_fopen ([?](http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen))

To see if your server is ok to run poche, execute http://yourpoche/poche_compatibility_test.php. 

## you don't want to install twig (the template engine) by yourself

Download this file http://static.inthepoche.com/files/poche-1.0-latest-with-twig.zip

Extract this file on your server.

## you want to install twig by yourself 

Download the latest version here : http://www.inthepoche.com/?pages/T%C3%A9l%C3%A9charger-poche

Extract this file on your server.

```php
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

### using sqlite

Copy / paste install/poche.sqlite in db folder.

### using mysql or postgresql

Execute the sql file in /install (mysql.sql or postgres.sql)

Then, go to step 3.

# Upgrading poche

Replace all the files except **db/poche.sqlite**. Also remember to edit the file /inc/poche/config.inc.php.

## Upgrading from poche <= 0.3

You have to execute http://yourpoche/install/update_sqlite_from_0_to_1.php

Then, go to step 3.

## Upgrading from poche >= 1.0 beta1

Nothing to do here. 

Then, go to step 3.

# Here is the step 3

You must have write access on assets, cache and db directories. These directories may not exist, you'll have to create them.

You can use poche ! Enjoy.

# Some problems you may encounter

## Blank page

Be sure to have write access on assets, cache and db directories.

## PHP Fatal error:  Call to a member function fetchAll() on a non-object in /var/www/poche/inc/poche/Database.class.php on line 42

If you want to install poche, delete the db/poche.sqlite file and copy / paste the install/poche.sqlite in /db. Be sure to have write access.