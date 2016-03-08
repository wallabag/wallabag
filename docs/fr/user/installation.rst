Installer wallabag
==================

Pré-requis
------------

wallabag est compatible avec PHP >= 5.5, PHP 7 inclus.

Vous aurez besoin des extensions suivantes pour que wallabag fonctionne. Il est possible que certaines de ces extensions soient déjà activées dans votre version de PHP, donc vous n'avez pas forcément besoin d'installer tous les paquets correspondants.

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

- pdo_mysql
- pdo_sqlite
- pdo_pgsql

Installation
------------

Sur un serveur dédié (méthode conseillée)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

wallabag utilise un grand nombre de bibliothèques pour fonctionner. Ces bibliothèques doivent être installées à l'aide d'un outil nommé Composer. Vous devez l'installer si ce n'est déjà fait.

Installation de Composer :

::

    curl -s http://getcomposer.org/installer | php

Vous pouvez trouver des instructions spécifiques `ici (en anglais) <https://getcomposer.org/doc/00-intro.md>`__ :

Pour installer wallabag, vous devez exécuter ces deux commandes :

::

    git clone https://github.com/wallabag/wallabag.git
    cd wallabag
    git checkout 2.0.5
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console wallabag:install --env=prod

Pour démarrer le serveur interne à php et vérifier que tout s'est installé correctement, vous pouvez exécuter :

::

    php bin/console server:run --env=prod

Et accéder wallabag à l'adresse http://lipdevotreserveur:8000

.. tip::
    Pour définir des paramètres via des variables d'environnement, vous pouvez les spécifier avec le préfixe ``SYMFONY__``. Par exemple, ``SYMFONY__DATABASE_DRIVER``. Vous pouvez lire `documentation Symfony <http://symfony.com/doc/current/cookbook/configuration/external_parameters.html>`__ pour en savoir plus.

Sur un serveur mutualisé
~~~~~~~~~~~~~~~~~~~~~~~~

Nous mettons à votre disposition une archive avec toutes les dépendances à l'intérieur.
La configuration par défaut utilise SQLite pour la base de données. Si vous souhaitez changer ces paramètres, vous devez modifier le fichier ``app/config/parameters.yml``.

Nous avons déjà créé un utilisateur : le login et le mot de passe sont ``wallabag``.

.. caution:: Avec cette archive, wallabag ne vérifie pas si les extensions obligatoires sont présentes sur votre serveur pour bien fonctionner (ces vérifications sont faites durant le ``composer install`` quand vous avez un serveur dédié, voir ci-dessus).

Exécutez cette commande pour télécharger et décompresser l'archive :

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Maintenant, lisez la documentation ci-dessous pour crééer un virtual host. Accédez ensuite à votre installation de wallabag.
Si vous avez changé la configuration pour modifier le type de stockage (MySQL ou PostgreSQL), vous devrez vous créer un utilisateur via la commande ``php bin/console wallabag:install --env=prod``.

Installation avec Docker
------------------------

Nous vous proposons une image Docker pour installer wallabag facilement. Allez voir du côté de `Docker Hub <https://hub.docker.com/r/wallabag/wallabag/>`__ pour plus d'informations.

Commande pour démarrer le containeur
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

    docker pull wallabag/wallabag

Virtual hosts
-------------

Configuration avec Apache
~~~~~~~~~~~~~~~~~~~~~~~~~

En imaginant que vous vouliez installer wallabag dans le dossier ``/var/www/wallabag`` et que vous utilisiez PHP comme un module Apache, voici un vhost pour wallabag :

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

Configuration avec Nginx
~~~~~~~~~~~~~~~~~~~~~~~~

En imaginant que vous vouliez installer wallabag dans le dossier ``/var/www/wallabag``, voici un fichier de configuration Nginx pour wallabag :

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

.. tip::

    Si vous voulez importer un fichier important dans wallabag, vous devez ajouter cette ligne dans votre configuration nginx ``client_max_body_size XM; # allows file uploads up to X megabytes``.

Configuration avec lighttpd
~~~~~~~~~~~~~~~~~~~~~~~~~~~

En imaginant que vous vouliez installer wallabag dans le dossier ``/var/www/wallabag``, voici un fichier de configuration pour wallabag (éditez votre fichier ``lighttpd.conf`` collez-y cette configuration) :

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

Droits d'accès aux dossiers du projet
-------------------------------------

Environnement de test
~~~~~~~~~~~~~~~~~~~~~

Quand nous souhaitons juste tester wallabag, nous lançons simplement la commande  ``php bin/console server:run --env=prod`` pour démarrer l'instance wallabag et tout se passe correctement car l'utilisateur qui a démarré le projet a accès naturellement au repertoire courant, tout va bien.

Environnement de production
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Dès lors que nous utilisons Apache ou Nginx pour accéder à notre instance wallabag, et non plus la commande ``php bin/console server:run --env=prod`` pour la démarrer, il faut prendre garde à octroyer les bons droits aux bons dossiers afin de préserver la sécurité de l'ensemble des fichiers fournis par le projet.

Aussi, le dossier, connu sous le nom de ``DocumentRoot`` (pour apache) ou ``root`` (pour Nginx), doit être impérativement accessible par l'utilisateur de Apache ou Nginx. Le nom de cet utilisateur est généralement ``www-data``, ``apache`` ou ``nobody`` (selon les systèmes linux utilisés).

Donc le dossier ``/var/www/wallabag/web`` doit être accessible par ce dernier. Mais cela ne suffira pas si nous nous contentons de ce dossier, et nous pourrions avoir, au mieux une page blanche en accédant à la page d'accueil du projet, au pire une erreur 500.

Cela est dû au fait qu'il faut aussi octroyer les mêmes droits d'accès au dossier ``/var/www/wallabag/var`` que ceux octroyés au dossier ``/var/www/wallabag/web``. Ainsi, on règle le problème par la commande suivante :

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/var

Il en est de même pour les dossiers suivants :

* /var/www/wallabag/bin/
* /var/www/wallabag/app/config/
* /var/www/wallabag/vendor/

en tapant

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/bin
   chown -R www-data:www-data /var/www/wallabag/app/config
   chown -R www-data:www-data /var/www/wallabag/vendor

sinon lors de la mise à jour vous finirez par rencontrer les erreurs suivantes :


.. code-block:: bash

    Unable to write to the "bin" directory.
    file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
    file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied
