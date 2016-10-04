Upgrading from 2.0.x to 2.1.y
=============================

.. warning::
Before this migration, if you configured the Pocket import by adding your consumer key in Internal settings, please do a backup of it: you'll have to add it into the Config page after the upgrade.

Upgrade on a dedicated web server
---------------------------------

The last release is published on https://www.wallabag.org/pages/download-wallabag.html. In order to upgrade your wallabag installation and get the last version, run the following commands in you wallabag folder (replace ``2.1.1`` by the last release number):

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Upgrade on a shared hosting
---------------------------

Backup your ``app/config/parameters.yml`` file.

Download the last release of wallabag:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(md5 hash of the 2.1.1 package: ``9584a3b60a2b2a4de87f536548caac93``)

Extract the archive in your wallabag folder and replace ``app/config/parameters.yml`` with yours.

Please note that we added new parameters in this version. You have to edit ``app/config/parameters.yml`` by adding these lines (replace with your configuration) :

.. code-block:: bash

    # RabbitMQ processing
    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

    # Redis processing
    redis_host: localhost
    redis_port: 6379

If you use SQLite, you must also copy your ``data/`` folder inside the new installation.

Empty ``var/cache`` folder.

You must run some SQL queries to upgrade your database. We assume that the table prefix is ``wallabag_`` and the database server is a MySQL one:

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';
