Install Redis for asynchronous tasks
=======================================

In order to launch asynchronous tasks (useful for huge imports for example), we can use Redis.

Requirements
------------

You need to have Redis installed on your server.

Installation
~~~~~~~~~~~~

.. code:: bash

  apt-get install redis-server

Launch
~~~~~~

The server might be already running after installing, if not you can launch it using:

.. code:: bash

  redis-server


Configure Redis in wallabag
---------------------------

Edit your ``parameters.yml`` file to edit Redis configuration. The default one should be ok:

.. code:: yaml

    redis_host: localhost
    redis_port: 6379


Launch Redis consumer
------------------------

Depending on which service you want to import from you need to enable one (or many if you want to support many) cron job:

.. code:: bash

  # for Pocket import
  bin/console wallabag:import:redis-worker pocket -vv >> /path/to/wallabag/var/logs/redis-pocket.log

  # for Readbility import
  bin/console wallabag:import:redis-worker readability -vv >> /path/to/wallabag/var/logs/redis-readability.log

  # for wallabag v1 import
  bin/console wallabag:import:redis-worker wallabag_v1 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v1.log

  # for wallabag v2 import
  bin/console wallabag:import:redis-worker wallabag_v2 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v2.log

If you want to launch the import only for some messages and not all, you can specify this number (here 12) and the worker will stop right after the 12th message :

.. code:: bash

  bin/console wallabag:import:redis-worker pocket -vv --maxIterations=12
