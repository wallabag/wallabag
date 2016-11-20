Mettre à jour votre installation de wallabag
============================================

Vous trouverez ici différentes manières de mettre à jour wallabag :

- `de la 2.0.x à la 2.1.1 <#mettre-a-jour-de-la-2-0-x-a-la-2-1-1>`_
- `de la 2.1.x à la 2.1.y <#mettre-a-jour-de-la-2-1-x-a-la-2-1-y>`_
- `de la 1.x à la 2.x <#depuis-wallabag-1-x>`_

Mettre à jour de la 2.0.x à la 2.1.1
------------------------------------

.. warning::
Avant cette migration, si vous aviez configuré l'import depuis Pocket en ajoutant votre consumer key dans les paramètres internes, pensez à effectuer une sauvegarde de celle-ci : vous devrez l'ajouter dans la configuration de wallabag après la mise à jour.

Mise à jour sur un serveur dédié
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Mise à jour sur un hébergement mutualisé
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Effectuez une sauvegarde du fichier ``app/config/parameters.yml``.

Téléchargez la version 2.1.1 de wallabag :

.. code-block:: bash

    wget https://framabag.org/wallabag-release-2.1.1.tar.gz && tar xvf wallabag-release-2.1.1.tar.gz

(hash md5 de l'archive 2.1.1 : ``9584a3b60a2b2a4de87f536548caac93``)

Décompressez l'archive dans votre répertoire d'installation et remplacez le fichier ``app/config/parameters.yml`` avec le votre.

Merci de vérifier que votre fichier ``app/config/parameters.yml`` contient tous les paramètres requis. Vous trouverez `ici une documentation détaillée concernant les paramètres <http://doc.wallabag.org/fr/master/user/parameters.html>`_.

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

Mettre à jour de la 2.1.x à la 2.1.y
------------------------------------

Mise à jour sur un serveur dédié
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Pour mettre à jour votre installation de wallabag et récupérer la dernière version, exécutez la commande suivante dans votre répertoire d'installation : 

::

    make update

Mise à jour sur un hébergement mutualisé
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Effectuez une sauvegarde du fichier ``app/config/parameters.yml``.

Téléchargez la dernière version de wallabag :

.. code-block:: bash

    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Vous trouverez `le hash md5 du dernier package sur notre site <https://www.wallabag.org/pages/download-wallabag.html>`_.

Décompressez l'archive dans votre répertoire d'installation et remplacez le fichier ``app/config/parameters.yml`` avec le votre.

Merci de vérifier que votre fichier ``app/config/parameters.yml`` contient tous les paramètres requis. Vous trouverez `ici une documentation détaillée concernant les paramètres <http://doc.wallabag.org/fr/master/user/parameters.html>`_.

Si vous utilisez SQLite, vous devez également conserver le contenu du répertoire ``data/``.

Videz le répertoire ``var/cache``.

Depuis wallabag 1.x
-------------------

Il n'y a pas de script automatique pour mettre à jour wallabag 1.x en wallabag 2.x. Vous devez :

- exportez vos données
- installer wallabag 2.x (`lisez la documentation d'installation <http://doc.wallabag.org/fr/master/user/installation.html>`_ )
- importer vos données dans votre installation toute propre (`lisez la documentation d'import <http://doc.wallabag.org/fr/master/user/import.html>`_ )
