Aggiornate la vostra installazione di wallabag
==============================================

Troverete qui i differenti modi per aggiornare il vostro wallabag:

- `da 2.0.x a 2.1.1 <#upgrade-from-2-0-x-to-2-1-1>`_
- `da 2.1.x a 2.1.y <#upgrading-from-2-1-x-to-2-1-y>`_
- `da 1.x a 2.x <#from-wallabag-1-x>`_

Aggiornate da 2.0.x a 2.1.1
---------------------------

.. attenzione::

    prima di questa migrazione, se avete configurato l'importazione di Pocket aggiungendo la vostra consumer key nelle Impostazioni interne, si prega di farne un backup: dovrete aggiungere questa nella pagina di configurazione dopo l'aggiornamento.

Aggiornamento su un web server dedicato
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
:

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.1 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console doctrine:migrations:migrate --env=prod
    php bin/console cache:clear --env=prod

Aggiornamento su un hosting condiviso
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Fate un backup del file ``app/config/parameters.yml``.
Scaricate la versione 2.1.1 di wallabag:

.. code-block:: bash

    wget http://framabag.org/wallabag-release-2.1.1.tar.gz && tar xvf wallabag-release-2.1.1.tar.gz

(hash md5 del pacchetto 2.1.1: ``9584a3b60a2b2a4de87f536548caac93``)

Estraete l'archivio nella vostra cartella di wallabag e sostituite ``app/config/parameters.yml`` con il vostro.

Controllate che il vostro ``app/config/parameters.yml`` contenga tutti i parametri richiesti. Potete trovare qui la documentazione sui parametri *link mancante*.

Se usate SQLite, dovete anche copiare la vostra cartella ``data/`` dentro la nuova installazione.

Svuotate la cartella ``var/cache``.

Dovete eseguire delle query di SQL per aggiornare il vostro database. Assumiamo che il prefisso della tabella sia ``wallabag_`` e che il database sia MySQL:

.. code-block:: sql

    ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
    ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
    INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
    ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
    DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';

Aggiornamento da 2.1.x a 2.1.y
------------------------------

Aggiornamento su un web server dedicato
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Per aggiornare la vostra installazione di wallabag ed ottenere l'ultima versione, eseguite il seguente comando nella vostra cartella wallabag:

::

    make update

Aggiornamento su un hosting condiviso
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Fate un backup del file ``app/config/parameters.yml``.

Scaricate l'ultima versione di wallabag:

. code-block:: bash

    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package

Troverete il `hash md5 dell'ultima versione del pacchetto sul nostro sito <https://www.wallabag.org/pages/download-wallabag.html>`_.

Estraete l'archivio nella vostra cartella di wallabag e rimpiazzate ``app/config/parameters.yml`` con il vostro.

Controllate che il vostro ``app/config/parameters.yml`` contenga tutti i parametri richiesti.

Potete trovare qui la documentazione sui parametri *link mancante*.

Se usate SQLite, dovete anche copiare la vostra cartella ``data/`` dentro la nuova installazione.

Svuotate la cartella ``var/cache``.

Da wallabag 1.x
---------------

Non esiste uno script automatico per aggiornare da wallabag 1.x a wallabag 2.x. Dovete:

- esportare i vostri dati
- installare wallabag 2.x (leggete la documentazione a proposito dell'installazione *link mancante*) 
- importate i dati in questa nuova installazione (leggete la documentazione a proposito dell'importazione)
