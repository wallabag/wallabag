Installiere RabbitMQ für asynchrone Aufgaben
============================================

Um asynchrone Aufgaben zu starten (nützlich z.B. für große Imports), können wir RabbitMQ nutzen.

Voraussetzungen
---------------

Du musst RabbitMQ auf deinem Server installiert haben.

Installation
~~~~~~~~~~~~

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Konfiguration und Starten
~~~~~~~~~~~~~~~~~~~~~~~~~

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

RabbitMQ stoppen
~~~~~~~~~~~~~~~

.. code:: bash

  rabbitmqctl stop


Konfigure RabbitMQ in wallabag
------------------------------

Bearbeite die Datei ``parameters.yml``, um die RabbitMQ Konfiguration einzurichten. Die Standardkonfiguration sollte ok sein:

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest


Starte den RabbitMQ Consumer
----------------------------

Abhängig von welchem Service du importieren möchtest, solltest du einen Cron Job aktivieren (oder mehrere, wenn du viele unterstützen willst):

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

