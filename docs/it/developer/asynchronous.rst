Compiti Asincroni
=================

Per avviare compiti asincroni (utile ad esempio per grandi importazioni), Possiamo usare RabbitMQ o Redis.

Installare RabbitMQ per compiti asincroni
-----------------------------------------

Requisiti
^^^^^^^^^

Dovete avere RabbitMQ installato sul vostro server.

Installazione
^^^^^^^^^^^^^

.. code:: bash

  wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
  apt-key add rabbitmq-signing-key-public.asc
  apt-get update
  apt-get install rabbitmq-server

Configurazione ed avvio
^^^^^^^^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
  rabbitmq-server -detached

Fermare RabbitMQ
^^^^^^^^^^^^^^^^

.. code:: bash

  rabbitmqctl stop


Configurare RabbitMQ in wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Modificate il vostro file ``app/config/parameters.yml`` per modificare la configurazione di RabbitMQ. Quella di default dovrebbe andare bene:

.. code:: yaml

    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest
    rabbitmq_prefetch_count: 10 # read http://www.rabbitmq.com/consumer-prefetch.html

Abilitare RabbitMQ su wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Su Strumenti, nella sezione **Importa**, abilitate RabbitMQ (con il valore 1).

Avviare RabbitMQ consumer
^^^^^^^^^^^^^^^^^^^^^^^^^

Dipendendo da quale servizio vogliate importare, dovrete abilitare uno (o più se volete supportare molti) o più cronjob:

.. code:: bash

  # per importare da Pocket
  bin/console rabbitmq:consumer -e=prod import_pocket -w

  # per importare da Readability
  bin/console rabbitmq:consumer -e=prod import_readability -w

  # per importare da Instapaper
  bin/console rabbitmq:consumer -e=prod import_instapaper -w

  # per importare da wallabag v1
  bin/console rabbitmq:consumer -e=prod import_wallabag_v1 -w

  # per importare da wallabag v2
  bin/console rabbitmq:consumer -e=prod import_wallabag_v2 -w

  # per importare da Firefox
  bin/console rabbitmq:consumer -e=prod import_firefox -w

  # per importare da Chrome
  bin/console rabbitmq:consumer -e=prod import_chrome -w

Installare Redis per compiti asincroni
--------------------------------------

Per avviare compiti asincroni (utile ad esempio per grandi importazioni), Possiamo usare Redis.

Requisiti
^^^^^^^^^

Dovete avere Redis installato sul vostro server.

Installazione
^^^^^^^^^^^^^

.. code:: bash

  apt-get install redis-server


Avvio
^^^^^

Il server dovrebbe già essere attivo dopo l'installazione, altrimenti potete avviarlo usando:

.. code:: bash

  redis-server


Configurare Redis su wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Modificate il vostro file ``app/config/parameters.yml`` per modificare la configurazione di Redis. Quella di default dovrebbe andare bene:

.. code:: yaml

    redis_host: localhost
    redis_port: 6379

Abilitare Redis su wallabag
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Su Strumenti, nella sezione **Importa**, abilitate Redis (con il valore 1).

Avviare Redis consumer
^^^^^^^^^^^^^^^^^^^^^^

Dipendendo da quale servizio vogliate importare, dovrete abilitare uno (o più se volete supportare molti) o più cronjob:

.. code:: bash

  # per importare da Pocket
  bin/console wallabag:import:redis-worker -e=prod pocket -vv >> /path/to/wallabag/var/logs/redis-pocket.log

  # per importare da Readability
  bin/console wallabag:import:redis-worker -e=prod readability -vv >> /path/to/wallabag/var/logs/redis-readability.log

  # per importare da Instapaper
  bin/console wallabag:import:redis-worker -e=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-instapaper.log

  # per importare da wallabag v1
  bin/console wallabag:import:redis-worker -e=prod wallabag_v1 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v1.log

  # per importare da wallabag v2
  bin/console wallabag:import:redis-worker -e=prod wallabag_v2 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v2.log

  # per importare da Firefox
  bin/console wallabag:import:redis-worker -e=prod firefox -vv >> /path/to/wallabag/var/logs/redis-firefox.log

  # per importare da Chrome
  bin/console wallabag:import:redis-worker -e=prod chrome -vv >> /path/to/wallabag/var/logs/redis-chrome.log

Se volete avviare l'importazione solamente per alcuni messaggi e non tutti, potete specificare questo numero (qui 12) e il programma si fermerà dopo il dodicesimo messaggio:

.. code:: bash

  bin/console wallabag:import:redis-worker -e=prod pocket -vv --maxIterations=12

