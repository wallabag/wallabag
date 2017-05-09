

Installa wallabag
=================

Requisiti
---------
wallabag é compatibile con PHP >= 5.5, incluso PHP 7.

.. nota::

  Per installare facilmente wallabag vi forniamo un Makefile, dunque avrete bisogno dello strumento make.

wallabag utilizza un gran numero di librerie PHP per funzionare. Queste librerie vanno installate tramite uno strumento chiamato Composer. Dovete installarlo se non lo avete giá fatto e assicuratevi di usare la versione 1.2 ( se giá avete Composer, esegui il comando composer selfupdate).

Installa Composer:

::
    curl -s http://getcomposer.org/installer | php

`Qui <https://getcomposer.org/doc/00-intro.md>`__ puoi trovare istruzioni specifiche.

Per far funzionare wallabag avrete anche bisogno delle seguenti estensioni. Alcune di queste dovrebbero essere giá attive nella vostra versione di PHP, per cui potrebbe non essere necessario installare tutti i pacchetti corrispondenti.

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

wallabag usa PDO per connettersi, per cui avrete bisogno di uno dei seguenti:

-pdo_mysql
-pdo_sqlite
-pdo_pgsql

E il corrispondente database server.

Installazione
-------------

Su un web server dedicato (raccomandato)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Per installare wallabag stesso dovete eseguire i seguenti comandi:

::

    git clone https://github.com/wallabag/wallabag.git
    cd wallabag && make install

Per attivare il server incorporato di PHP e verificare che l’installazione sia andata a buon fine potete eseguire:

::

    make run

E accedere a wallabag all’indirizzo http://ipdeltuoserver:8000

.. consiglio::

   Per definire i parametri con variabili d’ambiente é necessario impostare queste ultime con il prefisso ``SYMFONY_``. Per esempio, ``SYMFONY__DATABASE_DRIVER``. Puoi guardare la `documentazione di Symfony <http://symfony.com/doc/current/cookbook/configuration/external_parameters.html>`__ per maggiori informazioni.

A proposito di hosting condiviso
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Offriamo un pacchetto con tutte le dipendenze incluse. La configurazione di default usa SQLite per il database. Se volete cambiare queste impostazioni, modificate app/config/parameters.yml.

Abbiamo giá creato un utente: il login e la password sono wallabag.

.. attenzione:

  Con questo pacchetto, wallabag non controlla le estensioni obbligatorie usate nell’applicazione (questi controlli sono fatti durante ``composer install`` quando hai un server web dedicato, vedi sopra).

Eseguite questo comando per scaricare ed estrarre il pacchetto piú aggiornato:

.. code-block:: bash

   wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Troverete il `hash md5 del pacchetto piú aggiornato sul nostro sito <https://static.wallabag.org/releases/>`_.

Ora leggete la seguente documentazione per creare il vostro host virtuale poi accedete al vostro wallabag. Se avete cambiato la configurazione del database per usare MySQL o PostrgreSQL, dovrete creare un utente con il comando php bin/console wallabag:install --env=prod .

Installazione con Docker
~~~~~~~~~~~~~~~~~~~~~~~~

Offriamo un’immagine Docker per installare wallabag facilmente. Guarda la nostra repository su `Docker Hub <https://hub.docker.com/r/wallabag/wallabag/>`__  per maggiori informazioni.

Comando per avviare il container

.. code-block:: bash

   docker pull wallabag/wallabag

Installazione su Cloudron
~~~~~~~~~~~~~~~~~~~~~~~~~

Cloudron fornisce un modo facile di installare webapps sul vostro server mirato ad automatizzare i compiti del sysadmin ed a mantenere le app aggiornate.
wallabag é pacchettizzata come app Cloudron ed é disponibile all'installazione direttamente dallo store.

`Installa wallabag sul tuo Cloudron <https://cloudron.io/store/org.wallabag.cloudronapp.html>`__

Host virtuali
-------------

Configurazione su Apache
~~~~~~~~~~~~~~~~~~~~~~~~

Non dimenticate di attivare la mod *rewrite* di Apache

.. code-block:: bash

    a2enmod rewrite && systemctl reload apache2

Assumendo che voi abbiate installato wallabag nella cartella ``/var/www/wallabag`` e che vogliate usare PHP come un modulo Apache, ecco un vhost per l’applicazione:

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

Dopo aver riavviato o ricaricato Apache dovreste essere in grado di accedere a wallabag tramite l’indirizzo http://domain.tld.

Configurazione su Nginx
~~~~~~~~~~~~~~~~~~~~~~~

Assumendo che abbiate installato wallabag nella cartella ``/var/www/wallabag``, ecco una ricetta per l’applicazione:

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


Dopo aver riavviato o ricaricato Nginx dovreste essere in grado di accedere a wallabag tramite l’indirizzo http://domain.tld.

Configurazione su lighttpd
~~~~~~~~~~~~~~~~~~~~~~~~~~

Assumendo che abbiate installato wallabag nella cartella /var/www/wallabag, ecco una ricetta per l’applicazione (modificate il vostro file lighttpd.conf e incollatevi questa configurazione):

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


Diritti di accesso alle cartelle del progetto
---------------------------------------------

Ambiente di test
~~~~~~~~~~~~~~~~

Quando vorremo solamente testare wallabag, eseguiremo il comando ``make run`` per avviare la nostra istanza di wallabag e tutto funzionerá correttamente poiché l’utente che ha iniziato il progetto puó accedere alla cartella corrente senza problemi.

Ambiente di produzione
~~~~~~~~~~~~~~~~~~~~~~

Non appena useremo Apache o Nginx per accedere alla nostra istanza di wallabag, e non avviandola con il comando ``make run``, dovremo aver cura di concedere i giusti diritti sulle giuste cartelle per far rimanere sicure tutte le cartelle del progetto.

Per fare ció, il nome della cartella, conosciuta come ``DocumentRoot`` (per Apache) o ``root`` (per Nginx), deve essere assolutamente accessibile all’utente Apache/Nginx. Il suo nome è generalmente ``www-data``, ``apache`` o ``nobody`` (dipendendo dal sistema Linux utilizzato).

Quindi la cartella ``/var/www/wallabag/web`` deve essere accessibile da quest’ultimo. Questo tuttavia potrebbe non essere sufficiente se solo ci importa di questa cartella poiché potremmo incontrare una pagina bianca o un errore 500 quando cerchiamo di accedere alla homepage del progetto.

Questo é dato dal fatto che dovremo concedere gli stessi diritti di accesso di ``/var/www/wallabag/web``  alla cartella ``/var/www/wallabag/var`` . Risolveremo quindi il problema con il seguente comando:

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/var


Deve essere tutto uguale per le seguenti cartelle:

* /var/www/wallabag/bin/
* /var/www/wallabag/app/config/
* /var/www/wallabag/vendor/
* /var/www/wallabag/data/

inserendo

.. code-block:: bash

   chown -R www-data:www-data /var/www/wallabag/bin
   chown -R www-data:www-data /var/www/wallabag/app/config
   chown -R www-data:www-data /var/www/wallabag/vendor
   chown -R www-data:www-data /var/www/wallabag/data/

Altrimenti prima o poi incontreremo questi messaggi di errore:

.. code-block:: bash

    Unable to write to the "bin" directory.
    file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
    file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied

Regole aggiuntive per SELinux
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

se SELinux é abilitato sul vostro sistema, dovrete configurare contesti aggiuntivi in modo che wallabag funzioni correttamente. Per controllare se SELinux é abilitato, semplicemente inserisci ció che segue:

``getenforce``

Questo mostrerá ``Enforcing`` se SELinux é abilitato. Creare un nuovo contesto coinvolge la seguente sintassi:

``semanage fcontext -a -t <context type> <full path>``

Per esempio:

``semanage fcontext -a -t httpd_sys_content_t "/var/www/wallabag(/.*)?"``

Questo applicherá ricorsivamente il constesto httpd_sys_content_t alla cartella wallabag e a tutti i file e cartelle sottostanti. Sono necessarie le seguenti regole:

+-----------------------------------+----------------------------+
| Percorso completo                 | Contesto                   |
+===================================+============================+
| /var/www/wallabag(/.*)?           | ``httpd_sys_content_t``    |
+-----------------------------------+----------------------------+
| /var/www/wallabag/data(/.*)?      | ``httpd_sys_rw_content_t`` |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/logs(/.*)?  | ``httpd_log_t``            |
+-----------------------------------+----------------------------+
| /var/www/wallabag/var/cache(/.*)? | ``httpd_cache_t``          |
+-----------------------------------+----------------------------+

Dopo aver creato questi contesti, inserite ció che segue per applicare le vostre regole:

``restorecon -R -v /var/www/wallabag``

Potrete controllare i contesti in una cartella scrivendo ``ls -lZ`` e potrete vedere tutte le regole correnti con ``semanage fcontext -l -C``.

Se state installando il pacchetto latest-v2-package, é necessaria un'ulteriore regola durante la configurazione iniziale:

``semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/wallabag/var"``

Dopo che siate acceduti con successo al vostro wallabag e abbiate completato la configurazione iniziale, questo contesto puó essere rimosso:

::

    semanage fcontext -d -t httpd_sys_rw_content_t "/var/www/wallabag/var"
    retorecon -R -v /var/www/wallabag/var
