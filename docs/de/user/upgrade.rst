Wallabag updaten
================

Update auf einem dedizierten Webserver
--------------------------------------

Das neueste Release ist auf https://www.wallabag.org/pages/download-wallabag.html veröffentlicht. Um deine wallabag Installation auf die neueste Version upzudaten, führe die folgenden Kommandos in deinem wallabag Ordner aus (ersetze ``2.0.3`` mit der neuesten Releasenummer):

::

    git fetch origin
    git fetch --tags
    git checkout 2.0.3
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console cache:clear --env=prod

Update auf einem Shared Webhosting
----------------------------------

Sichere deine ``app/config/parameters.yml`` Datei.

Lade das neueste Release von wallabag herunter:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Entpacke das Archiv in deinen wallabag Ordner und ersetze ``app/config/parameters.yml`` mit deiner Datei.

Wenn du SQLite nutzt, musst auch das ``data/`` Verzeichnis in die neue Installation kopieren.

Leere den ``var/cache`` Ordner.
