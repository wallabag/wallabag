Installer wallabag
==================

Pré-requis
------------

wallabag est compatible avec php >= 5.5

Vous aurez besoin des extensions suivantes pour que wallabag fonctionne. Il est possible que certaines de ces extensions soient déjà activées dans votre version de php, donc vous n'avez pas forcément besoin d'installer tous les paquets correspondants.

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

wallabag utilise PDO afin de se connecter à une base de données, donc vous aurez besoin d'une extension et d'un système de bases de données parmi :

- php-pdo_mysql
- php-pdo_sqlite
- php-pdo_pgsql

Installation
------------

wallabag utilise un grand nombre de bibliothèques pour fonctionner. Ces bibliothèques doivent être installées à l'aide d'un outil nommé Composer. Vous devez l'installer si ce n'est déjà fait.

Installation de Composer :

::

    curl -s http://getcomposer.org/installer | php

Vous pouvez trouver des instructions spécifiques ici (en anglais) : __ https://getcomposer.org/doc/00-intro.md

Pour installer wallabag, vous devez exécuter ces deux commandes :

::

    SYMFONY_ENV=prod composer create-project wallabag/wallabag wallabag "2.0.*@alpha" --no-dev
    php bin/console wallabag:install --env=prod

Pour démarrer le serveur interne à php et vérifier que tout s'est installé correctement, vous pouvez exécuter :

::

    php bin/console server:run --env=prod

Et accéder wallabag à l'adresse http://lipdevotreserveur:8000

Installation avec Apache
------------------------

En imaginant que vous vouliez installer wallabag dans le dossier /var/www/wallabag et que vous utilisiez php comme un module Apache, voici un vhost pour wallabag :

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

Après que vous ayez rechargé/redémarré Apache, vous devriez pouvoir avoir accès à wallabag à l'adresse http://domain.tld.

Installation avec Nginx
-----------------------

En imaginant que vous vouliez installer wallabag dans le dossier /var/www/wallabag, voici un fichier de configuration Nginx pour wallabag :

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

Après que vous ayez rechargé/redémarré Nginx, vous devriez pouvoir avoir accès à wallabag à l'adresse http://domain.tld.
