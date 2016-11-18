Asynchrone Aufgaben
===================

Um große asynchrone Aufgaben zu starten (etwa für große Importe), können wir RabbitMQ oder Redis nutzen.

Installation von RabbitMQ für asynchrone Aufgaben
-------------------------------------------------

Voraussetzungen
^^^^^^^^^^^^^^^

Du musst RabbitMQ auf deinem Server installiert haben.

Installation
^^^^^^^^^^^^

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Konfiguration und Start
^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

RabbitMQ stoppen
^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmqctl stop

RabbitMQ für wallabag konfigurieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Bearbeite deine ``app/config/parameters.yml``-Datei, um die RabbitMQ-Parameter zu ändern. Die Standardwerte sollten in Ordnung sein:

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

RabbitMQ in wallabag aktivieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In den internen Einstellungen, aktiviere RabbitMQ im Import-Abschnitt mit dem Wert 1.

Starte den RabbitMQ-Consumer
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Abhängig davon, über welchen Service du importieren möchtest, musst du den entsprechenden (oder mehrere) Cronjob aktivieren:

.. code:: bash

  # für den Pocket-Import
  bin/console rabbitmq:consumer -e=prod import_pocket -w

  # für den Readability-Import
  bin/console rabbitmq:consumer -e=prod import_readability -w

  # für den Instapaper-Import
  bin/console rabbitmq:consumer -e=prod import_instapaper -w

  # für den wallabag v1-Import
  bin/console rabbitmq:consumer -e=prod import_wallabag_v1 -w

  # für den wallabag v2-Import
  bin/console rabbitmq:consumer -e=prod import_wallabag_v2 -w

  # für den Firefox-Import
  bin/console rabbitmq:consumer -e=prod import_firefox -w

  # für den Chrome-Import
  bin/console rabbitmq:consumer -e=prod import_chrome -w

Redis für asynchrone Aufgaben installieren
------------------------------------------

Um große asynchrone Aufgaben zu starten (etwa für große Importe), können wir auch Redis nutzen.

Voraussetzungen
^^^^^^^^^^^^^^^

Du musst Redis auf deinem Server installiert haben.

Installation
^^^^^^^^^^^^

.. code:: bash

  apt-get install redis-server

Start
^^^^^

Der Server kann bereits nach der Installation laufen, falls nicht, kannst du ihn wie folgt starten:

.. code:: bash

  redis-server


Redis für wallabag konfigurieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Bearbeite deine ``app/config/parameters.yml``-Datei, um die Redis-Parameter zu ändern. Die Standardwerte sollten in Ordnung sein:

.. code:: yaml

    redis_host: localhost
    redis_port: 6379

Redis in wallabag aktivieren
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In den internen Einstellungen, aktiviere Redis im Import-Abschnitt mit dem Wert 1.

Starten des Redis-Consumer
^^^^^^^^^^^^^^^^^^^^^^^^^^

Abhängig davon, über welchen Service du importieren möchtest, musst du den entsprechenden (oder mehrere) Cronjob aktivieren:

.. code:: bash

  # für den Pocket-Import
  bin/console wallabag:import:redis-worker -e=prod pocket -vv >> /path/to/wallabag/var/logs/redis-pocket.log

  # für den Readability-Import
  bin/console wallabag:import:redis-worker -e=prod readability -vv >> /path/to/wallabag/var/logs/redis-readability.log

  # für den Instapaper-Import
  bin/console wallabag:import:redis-worker -e=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-instapaper.log

  # für den wallabag v1-Import
  bin/console wallabag:import:redis-worker -e=prod wallabag_v1 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v1.log

  # für den wallabag v2-Import
  bin/console wallabag:import:redis-worker -e=prod wallabag_v2 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v2.log

  # für den Firefox-Import
  bin/console wallabag:import:redis-worker -e=prod firefox -vv >> /path/to/wallabag/var/logs/redis-firefox.log

  # für den Chrome-Import
  bin/console wallabag:import:redis-worker -e=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-chrome.log

Wenn du den Import nur für einige Artikel nutzen willst, kannst du die Nummer festlegen (hier: 12) und der Consumer wird nach dem zwölften Artikel aufhören:

.. code:: bash

  bin/console wallabag:import:redis-worker -e=prod pocket -vv --maxIterations=12