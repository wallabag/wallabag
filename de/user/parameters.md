Was bedeuten die Parameter?
===========================

Standardeinstellungen der parameters.yml
----------------------------------------

Dies ist die letzte standardisierte Version der
app/config/parameters.yml-Datei. Stelle sicher, dass sich deine mit
dieser ähnelt. Wenn du nicht weißt, welchen Wert du setzen sollst,
belasse es bei dem Standardwert.

``` {.sourceCode .yml}
parameters:
    database_driver: pdo_sqlite
    database_host: 127.0.0.1
    database_port: null
    database_name: symfony
    database_user: root
    database_password: null
    database_path: '%kernel.root_dir%/../data/db/wallabag.sqlite'
    database_table_prefix: wallabag_
    database_socket: null
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
```

Bedeutung von jedem Parameter
-----------------------------
