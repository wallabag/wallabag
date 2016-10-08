Install RabbitMQ for asynchronous tasks
=======================================

In order to launch asynchronous tasks (useful for huge imports for example), we can use RabbitMQ.

Requirements
------------

You need to have RabbitMQ installed on your server.

Installation
~~~~~~~~~~~~

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Configuration and launch
~~~~~~~~~~~~~~~~~~~~~~~~

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

Stop RabbitMQ
~~~~~~~~~~~~~

.. code:: bash

  rabbitmqctl stop


Configure RabbitMQ in wallabag
------------------------------

Edit your ``parameters.yml`` file to edit RabbitMQ configuration. The default one should be ok:

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest


Launch RabbitMQ consumer
------------------------

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
