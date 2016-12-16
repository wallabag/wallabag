Tâches asynchrones
==================

Pour lancer des tâches asynchrones (utile pour des imports importants par exemple), nous pouvons utiliser RabbitMQ ou Redis.

Installer RabbitMQ pour des tâches asynchrones
----------------------------------------------

Pour lancer des tâches asynchrones (utile pour des imports importants par exemple), nous pouvons utiliser RabbitMQ.

Pré-requis
^^^^^^^^^^

Vous devez installer RabbitMQ sur votre serveur.

Installation
^^^^^^^^^^^^

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Configuration et démarrage
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

Arrêter RabbitMQ
^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmqctl stop

Configurer RabbitMQ dans wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Modifiez votre fichier ``app/config/parameters.yml`` pour éditer la configuration RabbitMQ. Celle par défaut devrait convenir :

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest
    rabbitmq_prefetch_count: 10 # lire http://www.rabbitmq.com/consumer-prefetch.html

Activer RabbitMQ dans wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Dans les paramètres internes, section **Import**, activez RabbitMQ (avec la valeur 1).

Démarrer les clients RabbitMQ
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

En fonction du service dont vous souhaitez importer vos données, vous devez activer un (ou plusieurs si vous souhaitez en supporter plusieurs) cron job :

.. code:: bash

  # for Pocket import
  bin/console rabbitmq:consumer -e=prod import_pocket -w

  # for Readability import
  bin/console rabbitmq:consumer -e=prod import_readability -w

  # for Instapaper import
  bin/console rabbitmq:consumer -e=prod import_instapaper -w

  # for wallabag v1 import
  bin/console rabbitmq:consumer -e=prod import_wallabag_v1 -w

  # for wallabag v2 import
  bin/console rabbitmq:consumer -e=prod import_wallabag_v2 -w

  # for Firefox import
  bin/console rabbitmq:consumer -e=prod import_firefox -w

  # for Chrome import
  bin/console rabbitmq:consumer -e=prod import_chrome -w

Installer Redis pour des tâches asynchrones
-------------------------------------------

Pour lancer des tâches asynchrones (utile pour des imports importants par exemple), nous pouvons utiliser Redis.

Pré-requis
^^^^^^^^^^

Vous devez installer Redis sur votre serveur.

Installation
^^^^^^^^^^^^

.. code:: bash

  apt-get install redis-server

Démarrage
^^^^^^^^^

Le serveur devrait déjà être démarré après l'installation. Si ce n'est pas le cas, vous pouvez le démarrer ainsi :

.. code:: bash

  redis-server

Configurer Redis dans wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Modifiez votre fichier ``app/config/parameters.yml`` pour éditer la configuration Redis. Celle par défaut devrait convenir :

.. code:: yaml

    redis_host: localhost
    redis_port: 6379

Activer Redis dans wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Dans les paramètres internes, section **Import**, activez Redis (avec la valeur 1).

Démarrer les clients Redis
^^^^^^^^^^^^^^^^^^^^^^^^^^

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
  bin/console wallabag:import:redis-worker -e=prod chrome -vv >> /path/to/wallabag/var/logs/redis-chrome.log

Si vous souhaitez démarrer l'import pour quelques messages uniquement, vous pouvez spécifier cette valeur en paramètre (ici 12) et le client va s'arrêter après le 12ème message :

.. code:: bash

  bin/console wallabag:import:redis-worker -e=prod pocket -vv --maxIterations=12
