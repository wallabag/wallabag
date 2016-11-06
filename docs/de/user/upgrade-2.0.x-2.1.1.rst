Wallabag von 2.0.x auf 2.1.1 updaten
====================================

.. warning::
Wenn du den Import von Pocket durch das Hinzufügen des Consumer Key in den internen Einstellungen konfiguriert hast, fertige bitte ein Backup deines Keys an, bevor du auf das neue Release migrierst: Du wirst den Key nach dem Update in der Konfiguration erneut eintragen müssen.

Update auf einem dedizierten Webserver
--------------------------------------

Das neueste Release ist auf https://www.wallabag.org/pages/download-wallabag.html veröffentlicht. Um deine wallabag-Installation auf die neueste Version zu aktualisieren, führe die folgenden Kommandos in deinem wallabag-Ordner aus (ersetze ``2.1.1`` mit der neuesten Releasenummer):

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Update auf einem Shared Webhosting
----------------------------------

Sichere deine ``app/config/parameters.yml`` Datei.

Lade das neueste Release von wallabag herunter:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(2.1.1 md5 Hashsumme: ``9584a3b60a2b2a4de87f536548caac93``)

Entpacke das Archiv in deinen wallabag-Ordner und ersetze ``app/config/parameters.yml`` mit deiner Datei.

Bitte beachte, dass wir in dieser Version neue Parameter hinzugefügt haben. Du musst die Datei ``app/config/parameters.yml`` bearbeiten und die folgenden Zeilen hinzufügen (ersetze die Werte mit deiner Konfiguration):

.. code-block:: yml

    parameters:
        database_driver: pdo_sqlite
        database_host: 127.0.0.1
        database_port: null
        database_name: symfony
        database_user: root
        database_password: null
        database_path: '%kernel.root_dir%/../data/db/wallabag.sqlite'
        database_table_prefix: wallabag_
        database_socket: null
        mailer_transport: smtp
        mailer_host: 127.0.0.1
        mailer_user: null
        mailer_password: null
        locale: en
        secret: ovmpmAWXRCabNlMgzlzFXDYmCFfzGv
        twofactor_auth: true
        twofactor_sender: no-reply@wallabag.org
        fosuser_registration: true
        fosuser_confirmation: true
        from_email: no-reply@wallabag.org
        rss_limit: 50
        rabbitmq_host: localhost
        rabbitmq_port: 5672
        rabbitmq_user: guest
        rabbitmq_password: guest
        redis_scheme: tcp
        redis_host: localhost
        redis_port: 6379
        redis_path: null

Du kannst `hier eine Dokumentation über die Parameter finden <http://doc.wallabag.org/en/master/user/parameters.html>`_.

Wenn du SQLite nutzt, musst auch das ``data/`` Verzeichnis in die neue Installation kopieren.

Leere den ``var/cache`` Ordner.

Du musst einige SQL-Abfragen ausführen, um die Datenbank zu aktualisieren. Wir nehmen in diesem Fall an, dass das Tabellenpräfix ``wallabag_`` ist und eine MySQL-Datenbank genutzt wird:

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';
