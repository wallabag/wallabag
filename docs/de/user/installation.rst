Installation von wallabag
=========================

Voraussetzungen
---------------

wallabag ist kompatibel mit **PHP >= 5.6**, inkl. PHP 7.

.. note::

    To install wallabag easily, we create a ``Makefile``, so you need to have the ``make`` tool.

wallabag nutzt eine große Anzahl an Bibliotheken, um zu funktionieren. Diese Bibliotheken müssen mit einem Tool namens Composer installiert werden. Du musst es installieren sofern du es bisher noch nicht gemacht hast.

Composer installieren:

::

    curl -s https://getcomposer.org/installer | php

Du kannst eine spezifische Anleitung `hier <https://getcomposer.org/doc/00-intro.md>`__ finden.

Du benötigst die folgenden Extensions damit wallabag funktioniert. Einige von diesen sind vielleicht schon in deiner Version von PHP aktiviert, somit musst du eventuell
nicht alle folgenden Pakete installieren.

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

wallabag nutzt PDO, um sich mit der Datenbank zu verbinden, darum benötigst du eines der folgenden Komponenten:

- pdo_mysql
- pdo_sqlite
- pdo_pgsql

und dessen dazugehörigen Datenbankserver.

Installation
------------

Auf einem dedizierten Webserver (empfohlener Weg)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Um wallabag selbst zu installieren, musst du die folgenden Kommandos ausführen:

::

    git clone https://github.com/wallabag/wallabag.git
    cd wallabag && make install

Um PHPs eingebauten Server zu starten und zu testen, ob alles korrekt installiert wurde, kannst du folgendes Kommando ausführen:

::

    make run

und wallabag unter http://deineserverip:8000 erreichen.

.. tip::

    Um Parameter mit Umgebungsvariable zu definieren, musst du die Variable mit dem ``SYMFONY__`` Präfix setzen. Zum Beispiel ``SYMFONY__DATABASE_DRIVER``. Du kannst einen Blick die `Symfony Dokumentation <http://symfony.com/doc/current/cookbook/configuration/external_parameters.html>`__ werfen.

Auf einem geteilten Webhosting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Wir stellen ein Paket inkl. aller Abhängigkeiten bereit.
Die Standardkonfiguration nutzt SQLite für die Datenbank. Wenn du diese Einstellung ändern willst, ändere bitte ``app/config/parameters.yml``.

Wir haben bereits einen Nutzer erstellt: Login und Passwort sind ``wallabag``.

.. caution:: Mit diesem Paket überprüft wallabag nicht die von der Applikation gebrauchten Exentions (diese Tests werden während ``composer install`` durchgeführt wenn du einen dedizierten Webserver hast, siehe oben).

Führe dieses Kommando aus, um das neueste Paket herunterzuladen und zu entpacken:

.. code-block:: bash

    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Du findest die `md5 Hashsumme des neuesten Pakets auf unserer Website <https://static.wallabag.org/releases/>`_.

Jetzt lies die Dokumentation, um einen Virtualhost zu erstellen, dann greife auf dein wallabag zu.
Wenn du die Datenbankkonfiguration eingestellt hast, MySQL oder PostgreSQL zu nutzen, musst du einen Nutzer über das folgende Kommando erstellen ``php bin/console wallabag:install --env=prod``.

Installation mit Docker
~~~~~~~~~~~~~~~~~~~~~~~

Wir stellen ein Docker Image zu Verfügung, um wallabag einfach zu installieren. Schaue in unser Repository in unserem `Docker Hub <https://hub.docker.com/r/wallabag/wallabag/>`__, um mehr Informationen zu erhalten.

Kommando, um den Container zu starten
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

    docker pull wallabag/wallabag

Cloudron Installation
~~~~~~~~~~~~~~~~~~~~~~~~

Cloudron bietet einfache Webapp Installation auf deinem Server, mit Fokus auf System Administrator Automatisierung und Updates.
Ein Wallabag Paket ist direkt zur Installation durch den Cloudron Store verfügbar.

`Installiere wallabag auf deinem Cloudron <https://cloudron.io/store/org.wallabag.cloudronapp.html>`__

Virtualhosts
------------

Konfiguration von Apache
~~~~~~~~~~~~~~~~~~~~~~~~

Vergiss nicht, die *rewrite* mod von Apache zu aktivieren.

.. code-block:: bash

    a2enmod rewrite && systemctl reload apache2

Angenommen du willst wallabag in das Verzeichnis ``/var/www/wallabag`` installieren und du willst PHP als Apache Modul nutzen, dann ist hier ein vhost für wallabag:

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

Nach dem du Apache neugeladen oder neugestartet hast, solltest du nun wallabag unter http://domain.tld erreichen.

Konfiguration von Nginx
~~~~~~~~~~~~~~~~~~~~~~~

Angenommen du willst wallabag in das Verzeichnis ``/var/www/wallabag`` installieren, dann ist hier ein Rezept für wallabag:

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

Nach dem Neuladen oder Neustarten von nginx solltest du nun wallabag unter http://domain.tld erreichen.

.. tip::

    Wenn du eine große Datei in wallabag importieren willst, solltest du diese Zeile zu deiner nginx Konfiguration hinzufügen ``client_max_body_size XM; # allows file uploads up to X megabytes``.

Konfiguration von lighttpd
~~~~~~~~~~~~~~~~~~~~~~~~~~

Angenommen du willst wallabag in das Verzeichnis ``/var/www/wallabag`` installieren, dann ist hier ein Rezept für wallabag (bearbeite deine ``lighttpd.conf`` und füge die Konfiguration dort ein):

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

Rechte, um das Projektverzeichnis zu betreten
---------------------------------------------

Testumgebung
~~~~~~~~~~~~

Wenn wir nur wallabag testen wollen, führen wir nur das Kommando ``php bin/console server:run --env=prod`` aus, um unsere wallabag Instanz zu starten und alles wird geschmeidig laufen, weil der Nutzer, der das Projekt gestartet hat, den aktuellen Ordner ohne Probleme betreten kann.

Produktionsumgebung
~~~~~~~~~~~~~~~~~~~

Sobald wir Apache oder Nginx nutzen, um unsere wallabag Instanz zu erreichen, und nicht das Kommando ``php bin/console server:run --env=prod`` nutzen, sollten wir dafür sorgen, die Rechte vernünftig zu vergeben, um die Ordner des Projektes zu schützen.

Um dies zu machen, muss der Ordner, bekannt als ``DocumentRoot`` (bei Apache) oder ``root`` (bei Nginx), von dem Apache-/Nginx-Nutzer zugänglich sein. Sein Name ist meist ``www-data``, ``apache`` oder ``nobody`` (abhängig vom genutzten Linuxsystem).

Der Ordner ``/var/www/wallabag/web`` musst dem letztgenannten zugänglich sein. Aber dies könnte nicht genug sein, wenn wir nur auf diesen Ordner achten, weil wir eine leere Seite sehen könnten oder einen Fehler 500, wenn wir die Homepage des Projekt öffnen.

Dies kommt daher, dass wir die gleichen Rechte dem Ordner ``/var/www/wallabag/var`` geben müssen, so wie wir es für den Ordner ``/var/www/wallabag/web`` gemacht haben. Somit beheben wir das Problem mit dem folgenden Kommando:

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/var

Es muss analog für die folgenden Ordner ausgeführt werden

* /var/www/wallabag/bin/
* /var/www/wallabag/app/config/
* /var/www/wallabag/vendor/
* /var/www/wallabag/data/

durch Eingabe der Kommandos

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/bin
   chown -R www-data:www-data /var/www/wallabag/app/config
   chown -R www-data:www-data /var/www/wallabag/vendor
   chown -R www-data:www-data /var/www/wallabag/data/

ansonsten wirst du früher oder später folgenden Fehlermeldung sehen:

.. code-block:: bash

    Unable to write to the "bin" directory.
    file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
    file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied

Zusätzliche Regeln für SELinux
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Wenn SELinux in deinem System aktiviert ist, wirst du zusätzliche Kontexte konfigurieren müssen damit wallabag ordentlich funktioniert. Um zu testen, ob SELinux aktiviert ist, führe einfach folgendes aus:

``getenforce``

Dies wird ``Enforcing`` ausgeben, wenn SELinux aktiviert ist. Einen neuen Kontext zu erstellen, erfordert die folgende Syntax:

``semanage fcontext -a -t <context type> <full path>``

Zum Beispiel:

``semanage fcontext -a -t httpd_sys_content_t "/var/www/wallabag(/.*)?"``

Dies wird rekursiv den httpd_sys_content_t Kontext auf das wallabag Verzeichnis und alle darunterliegenden Dateien und Ordner anwenden. Die folgenden Regeln werden gebraucht:

+-----------------------------------+----------------------------+
| Vollständiger Pfad                | Kontext                    |
+===================================+============================+
| /var/www/wallabag(/.*)?           | ``httpd_sys_content_t``    |
+-----------------------------------+----------------------------+
| /var/www/wallabag/data(/.*)?      | ``httpd_sys_rw_content_t`` |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/logs(/.*)?  | ``httpd_log_t``            |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/cache(/.*)? | ``httpd_cache_t``          |
+-----------------------------------+----------------------------+

Nach dem diese Kontexte erstellt wurden, tippe das folgende, um deine Regeln anzuwenden:

``restorecon -R -v /var/www/wallabag``

Du kannst deine Kontexte in einem Verzeichnis überprüfen, indem du ``ls -lZ`` tippst und alle deine aktuellen Regeln mit ``semanage fcontext -l -C`` überprüfst.

Wenn du das vorkonfigurierte latest-v2-package installierst, dann ist eine weitere Regel während der Installation nötig:

``semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/wallabag/var"``

Nachdem du erfolgreich dein wallabag erreichst und die Installation fertiggestellt hast, kann dieser Kontext entfernt werden:

::

    semanage fcontext -d -t httpd_sys_rw_content_t "/var/www/wallabag/var"
    retorecon -R -v /var/www/wallabag/var
