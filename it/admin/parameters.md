Qual'é il significato dei parametri?
====================================

File parameters.yml di default
------------------------------

Ecco l'ultima versione del file app/config/parameters.yml di default.
Assicuratevi che la vostra rispetti questa. Se non sapete quale
parametro dovete impostare, si prega di lasciare quello di default.

```yaml
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
    database_charset: utf8
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
```

Significato di ogni parametro
-----------------------------

### Parametri del database

| Nome  | Descrizione | Default |
| ------|---------|------------ |
| database_driver | Dovrebbe essere pdo_sqlite o pdo_mysql o pdo_pgsql  | pdo_sqlite |
| database_host  | Host del vostro database (generalmente localhost o 127.0.0.1) | 127.0.0.1 |
| database_port  | Porta del vostro database (potete lasciare ~ per usare quella di default) | ~ |
| database_name | Nome del vostro database | symfony |
| database_user | L'utente che puó scrivere su questo database | root |
| database_password | Password di quell'utente | ~ |
| database_path | Solo per SQLite, definite dove mettere il file del database. Lasciatelo vuoto se usate un altro database | `%kernel.root_dir%/ ../data/db/wallabag.sqlite` |
| database_table_prefix | Tutte le tavole di wallabag avranno quella stringa come prefisso. Potete includere un _ per maggior chiarezza | wallabag_ |
| database_socket | Se il vostro database usa un socket al posto di tcp, inserite il percorso del socket (altri parametri di connessione saranno ignorati) | null |
| database_charset | For PostgreSQL & SQLite you should use utf8, for MySQL use utf8mb4 to handle emoji and other special characters | utf8mb4 |

## Configurazione per inviare email

| Nome  | Descrizione | Default |
| -----|-------------|-------- |
| mailer_transport | The exact transport method to use to deliver emails. Valid values are: smtp, gmail, mail, sendmail, null (which will disable the mailer) | smtp |
| mailer_host | The host to connect to when using smtp as the transport.| 127.0.0.1 |
| mailer_user | The username when using smtp as the transport. | ~ |
| mailer_password | The password when using smtp as the transport. | ~ |

## Altre opzioni di wallabag

| Nome  | Descrizione | Default |
| -----|-------------|-------- |
| locale | Lingua di default della vostra istanza di wallabag (come en, fr, es, etc.) | en |
| secret | Questa é una stringa che dovrebbe essere unica per la vostra applicazione ed é usata comunemente per aggiungere piú entropia alle operazioni di sicurezza. | ovmpmAWXRCabNlMgzlzFXDYmCFfzGv |
| twofactor_auth | true per abilitare l'autenticazione a due fattori | true |
| twofactor_sender | Email del mittente per ricevere il codice a due fattori | no-reply@wallabag.org |
| fosuser_registration | true per abilitare la registrazione pubblica | true |
| fosuser_confirmation | true per inviare una mail di conferma per ogni registrazione | true |
| from_email | Indirizzo email usato nel campo Da: in ogni email | no-reply@wallabag.org |
| rss_limit | Limite per i feed RSS | 50 |

## Configurazione di RabbitMQ

| Nome  | Descrizione | Default |
| -----|-------------|-------- |
| rabbitmq_host | Host del vostro RabbitMQ | localhost |
| rabbitmq_port | Porta del vostro RabbitMQ | 5672 |
| rabbitmq_user | Utente che puó leggere le code | guest |
| rabbitmq_password | Password di quell'utente | guest |

## Configurazione di Redis

| Nome  | Descrizione | Default |
| -----|-------------|-------- |
| redis_scheme | Specifica il protocollo da usare per comunicare con una istanza di Redis. Valori validi sono: tcp, unix, http | tcp |
| redis_host | IP o hostname del server bersaglio (ignorato per lo schema unix) | localhost |
| redis_port | Porta TCP/IP del server bersaglio (ignorato per lo schema unix) | 6379 |
| redis_path | Percorso del file domain socket di UNIX usato quando ci si connette a Redis usando domain socket di UNIX | null
| redis_password | Password defined in the Redis server configuration (parameter `requirepass` in `redis.conf`) | null
