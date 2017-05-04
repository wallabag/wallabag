Install wallabag
================

Requirements
------------

wallabag is compatible with **PHP >= 5.6**, including PHP 7.

.. note::

    To install wallabag easily, we provide a ``Makefile``, so you need to have the ``make`` tool.

wallabag uses a large number of PHP libraries in order to function. These libraries must be installed with a tool called Composer. You need to install it if you have not already done so and be sure to use the 1.2 version (if you already have Composer, run a ``composer selfupdate``).

Install Composer:

::

    curl -s https://getcomposer.org/installer | php

You can find specific instructions `here <https://getcomposer.org/doc/00-intro.md>`__.

You'll also need the following extensions for wallabag to work. Some of these may already activated in your version of PHP, so you may not have to install all corresponding packages.

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
- php-bcmath

wallabag uses PDO to connect to the database, so you'll need one of the following:

- pdo_mysql
- pdo_sqlite
- pdo_pgsql

and its corresponding database server.

Installation
------------

On a dedicated web server (recommended way)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To install wallabag itself, you must run the following commands:

::

    git clone https://github.com/wallabag/wallabag.git
    cd wallabag && make install

To start PHP's build-in server and test if everything did install correctly, you can do:

::

    make run

And access wallabag at http://yourserverip:8000

.. tip::

    To define parameters with environment variables, you have to set these variables with ``SYMFONY__`` prefix. For example, ``SYMFONY__DATABASE_DRIVER``. You can have a look at `Symfony documentation <http://symfony.com/doc/current/cookbook/configuration/external_parameters.html>`__.

On a shared hosting
~~~~~~~~~~~~~~~~~~~

We provide a package with all dependencies inside.
The default configuration uses SQLite for the database. If you want to change these settings, please edit ``app/config/parameters.yml``.

We already created a user: login and password are ``wallabag``.

.. caution:: With this package, wallabag doesn't check for mandatory extensions used in the application (theses checks are made during ``composer install`` when you have a dedicated web server, see above).

Execute this command to download and extract the latest package:

.. code-block:: bash

    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package

You will find the `md5 hash of the latest package on our website <https://static.wallabag.org/releases/>`_.

Now, read the following documentation to create your virtual host, then access your wallabag.
If you changed the database configuration to use MySQL or PostgreSQL, you need to create a user via this command ``php bin/console wallabag:install --env=prod``.

Installation with Docker
~~~~~~~~~~~~~~~~~~~~~~~~

We provide you a Docker image to install wallabag easily. Have a look at our repository on `Docker Hub <https://hub.docker.com/r/wallabag/wallabag/>`__ for more information.

Command to launch container
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

    docker pull wallabag/wallabag

Installation on Cloudron
~~~~~~~~~~~~~~~~~~~~~~~~

Cloudron provides an easy way to install webapps on your server with a focus on sysadmin automation and keeping apps updated.
wallabag is packaged as a Cloudron app and available to install directly from the store.

`Install wallabag on your Cloudron <https://cloudron.io/store/org.wallabag.cloudronapp.html>`__

Virtual hosts
-------------

Configuration on Apache
~~~~~~~~~~~~~~~~~~~~~~~

Do not forget to active the *rewrite* mod of Apache

.. code-block:: bash

    a2enmod rewrite && systemctl reload apache2

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


.. tip:: Note for Apache 2.4, in the section `<Directory /var/www/wallabag/web>` you have to replace the directives :

::

    AllowOverride None
    Order Allow,Deny
    Allow from All


by

::

    Require All granted


After reloading or restarting Apache, you should now be able to access wallabag at http://domain.tld.

Configuration on Nginx
~~~~~~~~~~~~~~~~~~~~~~

Assuming you installed wallabag in the ``/var/www/wallabag`` folder, here's the recipe for wallabag :

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

        # return 404 for all other php files not matching the front controller
        # this prevents access to other php files you don't want to be accessible.
        location ~ \.php$ {
            return 404;
        }

        error_log /var/log/nginx/wallabag_error.log;
        access_log /var/log/nginx/wallabag_access.log;
    }

After reloading or restarting nginx, you should now be able to access wallabag at http://domain.tld.

.. tip::

    When you want to import large files into wallabag, you need to add this line in your nginx configuration ``client_max_body_size XM; # allows file uploads up to X megabytes``.

Configuration on lighttpd
~~~~~~~~~~~~~~~~~~~~~~~~~

Assuming you install wallabag in the ``/var/www/wallabag`` folder, here's the recipe for wallabag (edit your ``lighttpd.conf`` file and paste this configuration into it):

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
        "^/([^?]*)(?:\?(.*))?" => "/app.php?$1&$2",
        "^/([^?]*)" => "/app.php?=$1",
    )

Rights access to the folders of the project
-------------------------------------------

Test environment
~~~~~~~~~~~~~~~~

When we just want to test wallabag, we just run the command ``make run`` to start our wallabag instance and everything will go smoothly because the user who started the project can access to the current folder naturally, without any problem.

Production environment
~~~~~~~~~~~~~~~~~~~~~~

As soon as we use Apache or Nginx to access to our wallabag instance, and not from the command  ``make run`` to start it, we should take care to grant the good rights on the good folders to keep safe all the folders of the project.

To do so, the folder name, known as ``DocumentRoot`` (for apache) or ``root`` (for Nginx), has to be absolutely accessible by the Apache/Nginx user. Its name is generally ``www-data``, ``apache`` or ``nobody`` (depending on linux system used).

So the folder ``/var/www/wallabag/web`` has to be accessible by this last one. But this may not be enough if we just care about this folder, because we could meet a blank page or get an error 500 when trying to access to the homepage of the project.

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

otherwise, sooner or later you will see these error messages:

.. code-block:: bash

    Unable to write to the "bin" directory.
    file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
    file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied

Additional rules for SELinux
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If SELinux is enabled on your system, you will need to configure additional contexts in order for wallabag to function properly. To check if SELinux is enabled, simply enter the following:

``getenforce``

This will return ``Enforcing`` if SELinux is enabled. Creating a new context involves the following syntax:

``semanage fcontext -a -t <context type> <full path>``

For example:

``semanage fcontext -a -t httpd_sys_content_t "/var/www/wallabag(/.*)?"``

This will recursively apply the httpd_sys_content_t context to the wallabag directory and all underlying files and folders. The following rules are needed:

+-----------------------------------+----------------------------+
| Full path                         | Context                    |
+===================================+============================+
| /var/www/wallabag(/.*)?           | ``httpd_sys_content_t``    |
+-----------------------------------+----------------------------+
| /var/www/wallabag/data(/.*)?      | ``httpd_sys_rw_content_t`` |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/logs(/.*)?  | ``httpd_log_t``            |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/cache(/.*)? | ``httpd_cache_t``          |
+-----------------------------------+----------------------------+

After creating these contexts, enter the following in order to apply your rules:

``restorecon -R -v /var/www/wallabag``

You can check contexts in a directory by typing ``ls -lZ`` and you can see all of your current rules with ``semanage fcontext -l -C``.

If you're installing the preconfigured latest-v2-package, then an additional rule is needed during the initial setup:

``semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/wallabag/var"``

After you successfully access your wallabag and complete the initial setup, this context can be removed:

::

    semanage fcontext -d -t httpd_sys_rw_content_t "/var/www/wallabag/var"
    retorecon -R -v /var/www/wallabag/var
