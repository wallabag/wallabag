Asynchronous tasks
==================

In order to launch asynchronous tasks (useful for huge imports for example), we can use RabbitMQ or Redis.

Install RabbitMQ for asynchronous tasks
---------------------------------------

Requirements
^^^^^^^^^^^^

You need to have RabbitMQ installed on your server.

Installation
^^^^^^^^^^^^

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Configuration and launch
^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

Stop RabbitMQ
^^^^^^^^^^^^^

.. code:: bash

  rabbitmqctl stop


Configure RabbitMQ in wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Edit your ``app/config/parameters.yml`` file to edit RabbitMQ configuration. The default one should be ok:

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

Enable RabbitMQ in wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^

In internal settings, in the **Import** section, enable RabbitMQ (with the value 1).

Launch RabbitMQ consumer
^^^^^^^^^^^^^^^^^^^^^^^^

Depending on which service you want to import from you need to enable one (or many if you want to support many) cron job:

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

Install Redis for asynchronous tasks
------------------------------------

In order to launch asynchronous tasks (useful for huge imports for example), we can use Redis.

Requirements
^^^^^^^^^^^^

You need to have Redis installed on your server.

Installation
^^^^^^^^^^^^

.. code:: bash

  apt-get install redis-server

Launch
^^^^^^

The server might be already running after installing, if not you can launch it using:

.. code:: bash

  redis-server


Configure Redis in wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Edit your ``app/config/parameters.yml`` file to edit Redis configuration. The default one should be ok:

.. code:: yaml

    redis_host: localhost
    redis_port: 6379

Enable Redis in wallabag
^^^^^^^^^^^^^^^^^^^^^^^^

In internal settings, in the **Import** section, enable Redis (with the value 1).

Launch Redis consumer
^^^^^^^^^^^^^^^^^^^^^

Depending on which service you want to import from you need to enable one (or many if you want to support many) cron job:

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

If you want to launch the import only for some messages and not all, you can specify this number (here 12) and the worker will stop right after the 12th message :

.. code:: bash

  bin/console wallabag:import:redis-worker -e=prod pocket -vv --maxIterations=12
