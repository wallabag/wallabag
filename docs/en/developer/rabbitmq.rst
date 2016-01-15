Install RabbitMQ for asynchronous tasks
=======================================

In order to launch asynchronous tasks (useful for huge imports for example), we use RabbitMQ.

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

Edit your ``parameters.yml`` file to edit RabbitMQ configuration.

Launch RabbitMQ consumer
------------------------

Put this command in a cron job:

.. code:: bash

  bin/console rabbitmq:consumer entries -w