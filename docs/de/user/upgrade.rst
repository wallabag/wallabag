wallabag-Installation aktualisieren
===================================

Du wirst hier mehrere Wege finden, um deine wallabag zu aktualisieren:

- `von 2.0.x zu 2.1.1 <#upgrade-von-2-0-x-zu-2-1-1>`_
- `von 2.1.x zu 2.1.y <#upgrade-von-2-1-x-zu-2-1-y>`_
- `von 1.x zu 2.x <#upgrade-von-1-x>`_

Upgrade von 2.0.x zu 2.1.1
---------------------------

.. warning::

    Mache eine Sicherung deines Pocket-Consumer-Key, falls hinzugefügt, da dieser nach dem Upgrade erneut hinzugefügt werden muss.

Upgrade auf einem dedizierten Webserver
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Upgrade auf einem Shared Hosting
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Sichere deine ``app/config/parameters.yml``-Datei.

Lade das 2.1.1-Release von wallabag herunter:

.. code-block:: bash

    wget http://framabag.org/wallabag-release-2.1.1.tar.gz && tar xvf wallabag-release-2.1.1.tar.gz

(md5 hash of the 2.1.1 package: ``9584a3b60a2b2a4de87f536548caac93``)

Extrahiere das Archiv in deinen wallabag-Ordner und ersetze die ``app/config/parameters.yml`` mit deiner.

Bitte überprüfe, dass deine ``app/config/parameters.yml`` alle notwendigen Parameter enthält. Eine Dokumentation darüber `findest du hier <http://doc.wallabag.org/de/master/user/parameters.html>`_.

Falls du SQLite nutzt, musst du außerdem deinen ``data/``-Ordner in die neue Installation kopieren.

Leere den ``var/cache``-Ordner.

Du musst einige SQL-Abfragen durchführen, um deine Datenbank zu aktualisieren. Wir gehen in diesem Fall davon aus, dass das Tabellenpräfix ``wallabag_`` ist und eine MySQL-Datenbank verwendet wird:

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';

Upgrade von 2.1.x zu 2.1.y
-----------------------------

Upgrade auf einem dedizierten Webserver
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Um deine wallabag-Installation auf die letzte Version zu aktualisieren, führe den folgenden Befehl in deinem wallabag-Ordner aus:

::

    make update

Upgrade auf einem Shared Hosting
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Sichere deine ``app/config/parameters.yml``-Datei.

Lade das letzte Release von wallabag herunter:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Du findest den `aktuellen MD5-Hash auf unserer Webseite <https://www.wallabag.org/pages/download-wallabag.html>`_.

Extrahiere das Archiv in deinen wallabag-Ordner und ersetze die ``app/config/parameters.yml`` mit deiner.

Bitte überprüfe, dass deine ``app/config/parameters.yml`` alle notwendigen Parameter enthält. Eine Dokumentation darüber `findest du hier <http://doc.wallabag.org/de/master/user/parameters.html>`_.

Falls du SQLite nutzt, musst du außerdem deinen ``data/``-Ordner in die neue Installation kopieren.

Leere den ``var/cache``-Ordner.

Upgrade von 1.x
---------------

Es gibt kein automatisiertes Skript, um wallabag 1.x auf wallabag 2.x zu aktualisieren. Du musst:

- deine Daten exportieren
- wallabag 2.x installieren (Dokumentation <http://doc.wallabag.org/en/master/user/installation.html>`_ )
- die Daten in die neue Installation importieren (`Dokumentation <http://doc.wallabag.org/en/master/user/import.html>`_ )