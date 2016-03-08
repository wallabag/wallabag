Install wallabag
================

Requirements
------------

wallabag is compatible with php >= 5.5

You'll need the following extensions for wallabag to work. Some of these may already activated in your version of php, so you may not have to install all corresponding packages.

- php-session
- php-ctype
- php-dom
- php-hash
- php-simplexml
- php-json
- php-gd
- php-mbstring
- php-xml
- php-tidy
- php-iconv
- php-curl
- php-gettext
- php-tokenizer

wallabag uses PDO to connect to database, so you'll need one of:

- php-pdo_mysql
- php-pdo_sqlite
- php-pdo_pgsql

and it's corresponding database server.

Installation
------------

wallabag uses a big number of libraries in order to function. These libraries must be installed with a tool called Composer. You need to install it if you don't already have.

Install Composer:

::

    curl -s http://getcomposer.org/installer | php

You can find specific instructions here : __ https://getcomposer.org/doc/00-intro.md

To install wallabag itself, you must run these two commands:

::

    git clone git@github.com:wallabag/wallabag.git
    cd wallabag
    git checkout 2.0.0-beta.2
    SYMFONY_ENV=prod composer install --no-dev -o
    php bin/console wallabag:install --env=prod
    php bin/console server:run --env=prod

To start php's build-in server and test if everything did install correctly, you can do:

::

    php bin/console server:run --env=prod

And access wallabag at http://yourserverip:8000

Installing on Apache
--------------------

Assuming you install wallabag in the /var/www/wallabag folder and that you want to use php as an Apache module, here's a vhost for wallabag:

::

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/wallabag/web
        <Directory /var/www/wallabag/web>
            AllowOverride None
            Order Allow,Deny
            Allow from All

            <IfModule mod_rewrite.c>
                Options -MultiViews
                RewriteEngine On
                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteRule ^(.*)$ app.php [QSA,L]
            </IfModule>
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
        # <Directory /var/www/wallabag>
        #     Options FollowSymlinks
        # </Directory>

        # optionally disable the RewriteEngine for the asset directories
        # which will allow apache to simply reply with a 404 when files are
        # not found instead of passing the request into the full symfony stack
        <Directory /var/www/wallabag/web/bundles>
            <IfModule mod_rewrite.c>
                RewriteEngine Off
            </IfModule>
        </Directory>
        ErrorLog /var/log/apache2/wallabag_error.log
        CustomLog /var/log/apache2/wallabag_access.log combined
    </VirtualHost>

After reloading or restarting Apache, you should now be able to access wallabag at http://domain.tld.

Installing on Nginx
-------------------

Assuming you install wallabag in the /var/www/wallabag folder, here's the recipe for wallabag :

::

    server {
        server_name domain.tld www.domain.tld;
        root /var/www/wallabag/web;

        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app.php$is_args$args;
        }
        location ~ ^/app\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/app.php/some-path
            # Remove the internal directive to allow URIs like this
            internal;
        }

        error_log /var/log/nginx/wallabag_error.log;
        access_log /var/log/nginx/wallabag_access.log;
    }

After reloading or restarting nginx, you should now be able to access wallabag at http://domain.tld.
