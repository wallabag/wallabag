Wallabag updaten
================

.. warning::
Wenn du den Import von Pocket durch das Hinzufügen des Consumer Key in den internen Einstellungen konfiguriert hast, fertige bitte ein Backup deines Keys an, bevor du auf das neue Release migrierst: Du wirst den Key nach dem Update in der Konfiguration erneut eintragen müssen.

Update auf einem dedizierten Webserver
--------------------------------------

Das neueste Release ist auf https://www.wallabag.org/pages/download-wallabag.html veröffentlicht. Um deine wallabag-Installation auf die neueste Version zu aktualisieren, führe die folgenden Kommandos in deinem wallabag-Ordner aus (ersetze ``2.1.0`` mit der neuesten Releasenummer):

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

(md5 hash: ``6c33520e29cc754b687f9cee0398dede``)

Entpacke das Archiv in deinen wallabag-Ordner und ersetze ``app/config/parameters.yml`` mit deiner Datei.

Bitte beachte, dass wir in dieser Version neue Parameter hinzugefügt haben. Du musst die Datei ``app/config/parameters.yml`` bearbeiten und die folgenden Zeilen hinzufügen (ersetze die Werte mit deiner Konfiguration):

.. code-block:: bash

    # RabbitMQ processing
    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

    # Redis processing
    redis_host: localhost
    redis_port: 6379

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
