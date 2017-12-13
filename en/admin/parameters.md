# What is the meaning of the parameters?

## Default parameters.yml file

Here is the last version of the default app/config/parameters.yml file.
Be sure that yours respects this one. If you don't know which value you
need to set, please leave the default one.

> **[danger] Information**
>
> To apply changes to `parameters.yml`, you have to clear your cache by deleting everything in `var/cache` with this command: `bin/console cache:clear -e=prod`.

```yaml
parameters:
    database_driver: pdo_mysql
    database_host: 127.0.0.1
    database_port: null
    database_name: wallabag
    database_user: root
    database_password: null
    database_path: null
    database_table_prefix: wallabag_
    database_socket: null
    database_charset: utf8mb4
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    locale: en
    secret: ovmpmAWXRCabNlMgzlzFXDYmCFfzGv
    twofactor_auth: true
    twofactor_sender: no-reply@wallabag.org
    fosuser_registration: true
    fosuser_confirmation: true
    from_email: no-reply@wallabag.org
    rss_limit: 50
    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest
    redis_scheme: tcp
    redis_host: localhost
    redis_port: 6379
    redis_path: null
    redis_password: null
    domain_name: https://your-wallabag-url-instance.com
```

## Meaning of each parameter

### Database parameters

| Name  | Description | Default |
| ------|---------|------------ |
| database_driver | Should be pdo_sqlite or pdo_mysql or pdo_pgsql  | pdo_sqlite |
| database_host  | host of your database (usually localhost or 127.0.0.1) | 127.0.0.1 |
| database_port  | port of your database (you can leave ``~`` to use the default one) | ~ |
| database_name | name of your database | symfony |
| database_user | user that can write to this database | root |
| database_password | password of that user| ~ |
| database_path | only for SQLite, define where to put the database file. Put it to null for any other database | `%kernel.root_dir%/ ../data/db/wallabag.sqlite` |
| database_table_prefix | all wallabag's tables will be prefixed with that string. You can include a ``_`` for clarity | wallabag_ |
| database_socket | If your database is using a socket instead of tcp, put the path of the socket (other connection parameters will then be ignored) | null |
| database_charset | For PostgreSQL & SQLite you should use utf8, for MySQL use utf8mb4 to handle emoji and other special characters | utf8mb4 |

## Mailer parameters

| Name | Description | Default |
| -----|-------------|-------- |
| mailer_transport | The exact transport method to use to deliver emails. Valid values are: smtp, gmail, mail, sendmail, null (which will disable the mailer) | smtp |
| mailer_host | The host to connect to when using smtp as the transport.| 127.0.0.1 |
| mailer_user | The username when using smtp as the transport. | ~ |
| mailer_password | The password when using smtp as the transport. | ~ |

## Other wallabag options

| Name | Description | Default |
| -----|-------------|-------- |
| locale | Default language of your wallabag instance (like en, fr, es, etc.) | en |
| secret | This is a string that should be unique to your application and it's commonly used to add more entropy to security related operations. | ovmpmAWXRCabNlMgzlzFXDYmCFfzGv |
| twofactor_auth | true to enable the possibility of Two factor authentication | true |
| twofactor_sender | email of the email sender to receive the two factor code | no-reply@wallabag.org |
| fosuser_registration | true to enable public registration | true |
| fosuser_confirmation | true to send a confirmation by email for each registration | true |
| from_email | email address used in From: field in each email | no-reply@wallabag.org |
| rss_limit | item limit for RSS feeds | 50 |
| domain_name (**new in 2.3.0**) | Full URL of your wallabag instance (without the trailing slash) | https://your-wallabag-url-instance.com |

## RabbitMQ options

| Name | Description | Default |
| -----|-------------|-------- |
| rabbitmq_host | Host of your RabbitMQ | localhost |
| rabbitmq_port | Port of your RabbitMQ instance | 5672 |
| rabbitmq_user | User that can read queues | guest |
| rabbitmq_password | Password of that user | guest |

## Redis options

| Name | Description | Default |
| -----|-------------|-------- |
| redis_scheme | Specifies the protocol used to communicate with an instance of Redis. Valid values are: tcp, unix, http | tcp |
| redis_host | IP or hostname of the target server (ignored for unix scheme) | localhost |
| redis_port | TCP/IP port of the target server (ignored for unix scheme) | 6379 |
| redis_path | Path of the UNIX domain socket file used when connecting to Redis using UNIX domain sockets | null
| redis_password | Password defined in the Redis server configuration (parameter `requirepass` in `redis.conf`) | null
