---
title: Asynchronous
weight: 6
---

To launch asynchronous tasks (useful for large imports), we can use RabbitMQ or Redis.

Automatic feed subscriptions use the same import settings. With neither transport enabled, wallabag saves articles synchronously. When both are enabled, RabbitMQ takes priority. Manual article saves are always synchronous.

If an enabled transport is unavailable, the refresh reports a failure. Check the worker and retry the refresh. Publishing and recording history are separate operations, so a retry after a failed database write can send a message again. Workers use the usual article deduplication.

### Schedule feed refreshes

Run `php bin/console wallabag:fetch-rss --env=prod` from the wallabag directory. The command reports individual feed errors and continues when the database remains available. For example, to refresh subscriptions every hour with cron:

```cron
0 * * * * cd /path/to/wallabag && php bin/console wallabag:fetch-rss --env=prod >> var/logs/feed-refresh.log 2>&1
```

## Install RabbitMQ for asynchronous tasks

### Requirements

You need to have RabbitMQ installed on your server.

### Installation

```
wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
apt-key add rabbitmq-signing-key-public.asc
apt-get update
apt-get install rabbitmq-server
```

### Configuration and launch

```
rabbitmq-plugins enable rabbitmq_management # (useful to have a web interface, available at http://localhost:15672/ (guest/guest)
rabbitmq-server -detached
```

### Stop RabbitMQ

```
rabbitmqctl stop
```

### Configure RabbitMQ in wallabag

Set `RABBITMQ_URL` and `WALLABAG_RABBITMQ_PREFETCH_COUNT` in your environment
configuration (`.env.local`, `docker/php/env`, or your production environment).
The default values are:

```dotenv
RABBITMQ_URL=amqp://guest:guest@127.0.0.1:5672
WALLABAG_RABBITMQ_PREFETCH_COUNT=10
```

### Enable RabbitMQ in wallabag

In internal settings, in the **Import** section, enable RabbitMQ (with
the value 1).

### Launch RabbitMQ consumer

Depending on which service you want to import from you need to enable
one (or many if you want to support many) cron job:

```
# for Pocket CSV import
bin/console rabbitmq:consumer --env=prod import_pocket_csv -w

# for Readability import
bin/console rabbitmq:consumer --env=prod import_readability -w

# for Instapaper import
bin/console rabbitmq:consumer --env=prod import_instapaper -w

# for wallabag v1 import
bin/console rabbitmq:consumer --env=prod import_wallabag_v1 -w

# for wallabag v2 import
bin/console rabbitmq:consumer --env=prod import_wallabag_v2 -w

# for Firefox import
bin/console rabbitmq:consumer --env=prod import_firefox -w

# for Chrome import
bin/console rabbitmq:consumer --env=prod import_chrome -w

# for URL list import
bin/console rabbitmq:consumer --env=prod import_url_list -w

# for automatic feed imports
bin/console rabbitmq:consumer --env=prod import_feeds -w
```

Install Redis for asynchronous tasks
------------------------------------

In order to launch asynchronous tasks (useful for huge imports for
example), we can use Redis.

### Requirements

You need to have Redis installed on your server.

### Installation

```
apt-get install redis-server
```

### Launch

The server might be already running after installing, if not you can
launch it using:

```
redis-server
```

### Configure Redis in wallabag

Set `REDIS_URL` in your environment configuration (`.env.local`,
`docker/php/env`, or your production environment). The default value is:

```dotenv
REDIS_URL=redis://127.0.0.1:6379
```

### Enable Redis in wallabag

In internal settings, in the **Import** section, enable Redis (with the
value 1).

### Launch Redis consumer

Depending on which service you want to import from you need to enable
one (or many if you want to support many) cron job:

```
# for Pocket CSV import
bin/console wallabag:import:redis-worker --env=prod pocket_csv -vv >> /path/to/wallabag/var/logs/redis-pocket.log

# for Readability import
bin/console wallabag:import:redis-worker --env=prod readability -vv >> /path/to/wallabag/var/logs/redis-readability.log

# for Instapaper import
bin/console wallabag:import:redis-worker --env=prod instapaper -vv >> /path/to/wallabag/var/logs/redis-instapaper.log

# for wallabag v1 import
bin/console wallabag:import:redis-worker --env=prod wallabag_v1 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v1.log

# for wallabag v2 import
bin/console wallabag:import:redis-worker --env=prod wallabag_v2 -vv >> /path/to/wallabag/var/logs/redis-wallabag_v2.log

# for Firefox import
bin/console wallabag:import:redis-worker --env=prod firefox -vv >> /path/to/wallabag/var/logs/redis-firefox.log

# for Chrome import
bin/console wallabag:import:redis-worker --env=prod chrome -vv >> /path/to/wallabag/var/logs/redis-chrome.log

# for URL list import
bin/console wallabag:import:redis-worker --env=prod url_list -vv >> /path/to/wallabag/var/logs/redis-url_list.log

# for automatic feed imports
bin/console wallabag:import:redis-worker --env=prod feeds -vv
```

If you want to launch the import only for some messages and not all, you
can specify this number (here 12) and the worker will stop right after
the 12th message :

```
bin/console wallabag:import:redis-worker --env=prod pocket_csv -vv --maxIterations=12
```
