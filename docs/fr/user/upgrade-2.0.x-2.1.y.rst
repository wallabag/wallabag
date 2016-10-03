Mettre à jour de la 2.0.x à la 2.1.y
====================================

.. warning::
Avant cette migration, si vous aviez configuré l'import depuis Pocket en ajoutant votre consumer key dans les paramètres internes, pensez à effectuer une sauvegarde de celle-ci : vous devrez l'ajouter dans la configuration de wallabag après la mise à jour.

Mise à jour sur un serveur dédié
--------------------------------

La dernière version de wallabag est publiée à cette adresse : https://www.wallabag.org/pages/download-wallabag.html. Pour mettre à jour votre installation de wallabag, exécutez les commandes suivantes dans votre répertoire d'installation (remplacez ``2.1.1`` par le numéro de la dernière version) :

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Mise à jour sur un hébergement mutualisé
----------------------------------------

Effectuez une sauvegarde du fichier ``app/config/parameters.yml``.

Téléchargez la dernière version de wallabag :

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(hash md5 de l'archive 2.1.0 : ``6c33520e29cc754b687f9cee0398dede``)

Décompressez l'archive dans votre répertoire d'installation et remplacez le fichier ``app/config/parameters.yml`` avec le votre.

Nous avons ajouté de nouveaux paramètres dans cette nouvelle version. Vous devez donc éditer le fichier ``app/config/parameters.yml`` en ajoutant ces lignes (et en remplaçant par votre configuration) :

.. code-block:: bash

    # RabbitMQ processing
    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

    # Redis processing
    redis_host: localhost
    redis_port: 6379

Si vous utilisez SQLite, vous devez également conserver le contenu du répertoire ``data/``.

Videz le répertoire ``var/cache``.

Vous allez devoir également exécuter des requêtes SQL pour mettre à jour votre base de données. Nous partons du principe que le préfixe de vos tables est ``wallabag_`` et que le serveur SQL est un serveur MySQL :

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';

