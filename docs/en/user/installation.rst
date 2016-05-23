Install wallabag
================

Requirements
------------

wallabag is compatible with PHP >= 5.5, including PHP 7.

You'll need the following extensions for wallabag to work. Some of these may already activated in your version of PHP, so you may not have to install all corresponding packages.

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

- pdo_mysql
- pdo_sqlite
- pdo_pgsql

and it's corresponding database server.

Installation
------------

On a dedicated web server (recommended way)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

wallabag uses a big number of libraries in order to function. These libraries must be installed with a tool called Composer. You need to install it if you don't already have.

Install Composer:

::

    curl -s http://getcomposer.org/installer | php

You can find specific instructions `here <https://getcomposer.org/doc/00-intro.md>`__:

To install wallabag itself, you must run these two commands:

::

    git clone https://github.com/wallabag/wallabag.git
    cd wallabag
    git checkout 2.0.5
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console wallabag:install --env=prod

To start PHP's build-in server and test if everything did install correctly, you can do:

::

    php bin/console server:run --env=prod

And access wallabag at http://yourserverip:8000

.. tip::

    To define parameters with environment variables, you have to set these variables with ``SYMFONY__`` prefix. For example, ``SYMFONY__DATABASE_DRIVER``. You can have a look to the `Symfony documentation <http://symfony.com/doc/current/cookbook/configuration/external_parameters.html>`__.

On a shared hosting
~~~~~~~~~~~~~~~~~~~

We provide you a package with all dependancies inside.
The default configuration uses SQLite for the database. If you want to change these settings, please edit ``app/config/parameters.yml``.

We already created a user: login and password are ``wallabag``.

.. caution:: With this package, wallabag don't check mandatory extensions used in the application (theses checks are made during ``composer install`` when you have a dedicated web server, see above).

Execute this command to download and extract the latest package:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Now, read the following documentation to create your virtual host, then access to your wallabag.
If you changed the database configuration to use MySQL or PostgreSQL, you need to create a user via this command ``php bin/console wallabag:install --env=prod``.

Installation with Docker
------------------------

We provide you a Docker image to install wallabag easily. Have a look to our repository on `Docker Hub <https://hub.docker.com/r/wallabag/wallabag/>`__ to have more information.

Command to launch container
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

    docker pull wallabag/wallabag

Virtual hosts
-------------

Configuration on Apache
~~~~~~~~~~~~~~~~~~~~~~~

Assuming you install wallabag in the ``/var/www/wallabag`` folder and that you want to use PHP as an Apache module, here's a vhost for wallabag:

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

Configuration on Nginx
~~~~~~~~~~~~~~~~~~~~~~

Assuming you install wallabag in the ``/var/www/wallabag`` folder, here's the recipe for wallabag :

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

.. tip::

    When you want to import large file into wallabag, you need to add this line in your nginx configuration ``client_max_body_size XM; # allows file uploads up to X megabytes``.

Configuration on lighttpd
~~~~~~~~~~~~~~~~~~~~~~~~~

Assuming you install wallabag in the /var/www/wallabag folder, here's the recipe for wallabag (edit your ``lighttpd.conf`` file and paste this configuration into it):

::

    server.modules = (
        "mod_fastcgi",
        "mod_access",
        "mod_alias",
        "mod_compress",
        "mod_redirect",
        "mod_rewrite",
    )
    server.document-root = "/var/www/wallabag/web"
    server.upload-dirs = ( "/var/cache/lighttpd/uploads" )
    server.errorlog = "/var/log/lighttpd/error.log"
    server.pid-file = "/var/run/lighttpd.pid"
    server.username = "www-data"
    server.groupname = "www-data"
    server.port = 80
    server.follow-symlink = "enable"
    index-file.names = ( "index.php", "index.html", "index.lighttpd.html")
    url.access-deny = ( "~", ".inc" )
    static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )
    compress.cache-dir = "/var/cache/lighttpd/compress/"
    compress.filetype = ( "application/javascript", "text/css", "text/html", "text/plain" )
    include_shell "/usr/share/lighttpd/use-ipv6.pl " + server.port
    include_shell "/usr/share/lighttpd/create-mime.assign.pl"
    include_shell "/usr/share/lighttpd/include-conf-enabled.pl"
    dir-listing.activate = "disable"

    url.rewrite-if-not-file = (
        "^/([^?])(?:\?(.))?" => "/app.php?$1&$2",
        "^/([^?]*)" => "/app.php?=$1",
    )

Rights access to the folders of the project
-------------------------------------------

Test environment
~~~~~~~~~~~~~~~~

When we just want to test wallabag, we just run the command ``php bin/console server:run --env=prod`` to start our wallabag instance and everything will go smoothly because the user who started the project can access to the current folder naturally, without any problem.

Production environment
~~~~~~~~~~~~~~~~~~~~~~

As soon as we use Apache or Nginx to access to our wallabag instance, and not from the command  ``php bin/console server:run --env=prod`` to start it, we should take care to grant the good rights on the good folders to keep safe all the folders of the project.

To do so, the folder name, known as ``DocumentRoot`` (for apache) or ``root`` (for Nginx), has to be absolutely accessible by the Apache/Nginx user. Its name is generally ``www-data``, ``apache`` or ``nobody`` (depending on linux system used).

So the folder ``/var/www/wallabag/web`` has to be accessible by this last one. But this could be not enough if we just care about this folder, because we could meet a blank page or get an error 500 when trying to access to the homepage of the project.

This is due to the fact that we will need to grant the same rights access on the folder ``/var/www/wallabag/var`` like those we gave on the folder ``/var/www/wallabag/web``. Thus, we fix this problem with the following command:

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/var

It has to be the same for the following folders

* /var/www/wallabag/bin/
* /var/www/wallabag/app/config/
* /var/www/wallabag/vendor/
* /var/www/wallabag/data/

by entering

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/bin
   chown -R www-data:www-data /var/www/wallabag/app/config
   chown -R www-data:www-data /var/www/wallabag/vendor
   chown -R www-data:www-data /var/www/wallabag/data/

otherwise, sooner or later you will meet this error messages

.. code-block:: bash

    Unable to write to the "bin" directory.
    file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
    file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied
