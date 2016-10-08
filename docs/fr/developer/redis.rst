Installer Redis pour des tâches asynchrones
===========================================

Pour lancer des tâches asynchrones (utile pour des imports importants par exemple), nous pouvons utiliser Redis.

Pré-requis
----------

Vous devez installer Redis sur votre serveur.

Installation
~~~~~~~~~~~~

.. code:: bash

  apt-get install redis-server

Démarrage
~~~~~~~~~

Le serveur devrait déjà être démarré après l'installation. Si ce n'est pas le cas, vous pouvez le démarrer ainsi :

.. code:: bash

  redis-server


Configurer Redis dans wallabag
-------------------------------

Modifiez votre fichier ``parameters.yml`` pour éditer la configuration Redis. Celle par défaut devrait convenir :

.. code:: yaml

    redis_host: localhost
    redis_port: 6379


Démarrer les clients Redis
--------------------------

En fonction du service dont vous souhaitez importer vos données, vous devez activer un (ou plusieurs si vous souhaitez en supporter plusieurs) cron job :

.. code:: bash

  # for Pocket import
  bin/console wallabag:import:redis-worker -e=prod pocket -vv >> /path/to/wallabag/var/logs/redis-pocket.log

  # for Readability import
  bin/console wallabag:import:redis-worker -e=prod readability -vv >> /path/to/wallabag/var/logs/redis-readability.log

  # for Instapaper import
  bin/console wallabag:import:redis-worker -e=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-instapaper.log

  # for wallabag v1 import
  bin/console wallabag:import:redis-worker -e=prod wallabag_v1 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v1.log

  # for wallabag v2 import
  bin/console wallabag:import:redis-worker -e=prod wallabag_v2 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v2.log

  # for Firefox import
  bin/console wallabag:import:redis-worker -e=prod firefox -vv >> /path/to/wallabag/var/logs/redis-firefox.log

  # for Chrome import
  bin/console wallabag:import:redis-worker -e=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-chrome.log

Si vous souhaitez démarrer l'import pour quelques messages uniquement, vous pouvez spécifier cette valeur en paramètre (ici 12) et le client va s'arrêter après le 12ème message :

.. code:: bash

  bin/console wallabag:import:redis-worker -e=prod pocket -vv --maxIterations=12
