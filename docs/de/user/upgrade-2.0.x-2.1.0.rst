Wallabag updaten
================

.. warning::
Before this migration, if you configured the Pocket import by adding your consumer key in Internal settings, please do a backup of it: you'll have to add it into the Config page after the upgrade.

Update auf einem dedizierten Webserver
--------------------------------------

Das neueste Release ist auf https://www.wallabag.org/pages/download-wallabag.html veröffentlicht. Um deine wallabag Installation auf die neueste Version upzudaten, führe die folgenden Kommandos in deinem wallabag Ordner aus (ersetze ``2.1.0`` mit der neuesten Releasenummer):

::

    git fetch origin
    git fetch --tags
    git checkout 2.1.0
    ASSETS=build ./install.sh
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Update auf einem Shared Webhosting
----------------------------------

Sichere deine ``app/config/parameters.yml`` Datei.

Lade das neueste Release von wallabag herunter:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(md5 hash: ``4f84c725d1d6e3345eae0a406115e5ff``)

Entpacke das Archiv in deinen wallabag Ordner und ersetze ``app/config/parameters.yml`` mit deiner Datei.

Wenn du SQLite nutzt, musst auch das ``data/`` Verzeichnis in die neue Installation kopieren.

Leere den ``var/cache`` Ordner.

Sie müssen einige SQL-Abfragen ausführen, um Ihre Datenbank zu aktualisieren. We assume that the table prefix is ``wallabag_`` and the database server is a MySQL one:

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';
