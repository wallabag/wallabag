# Was bedeuten die Parameter?

## Standardeinstellungen der parameters.yml

Dies ist die letzte standardisierte Version der
app/config/parameters.yml-Datei. Stelle sicher, dass sich deine mit
dieser ähnelt. Wenn du nicht weißt, welchen Wert du setzen sollst,
belasse es bei dem Standardwert.

{% hint style='danger' %}
To apply changes to `parameters.yml`, you have to clear your cache by deleting everything in `var/cache` with this command: `bin/console cache:clear -e=prod`.
{% endhint %}

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

## Bedeutung von jedem Parameter

### Datenbankparameter

| Name  | Beschreibung | Standardwert |
| ------|---------|------------ |
| database_driver | Sollte pdo_sqlite oder pdo_mysql oder pdo_pgsql sein  | pdo_sqlite |
| database_host  | Hostadresse deiner Datenbank (normalerweise localhost oder 127.0.0.1) | 127.0.0.1 |
| database_port  | Port deiner Datenbank (Du kannst ~ stehen lassen, um den Standardport zu nutzen) | ~ |
| database_name | Benenne deine Datenbank | symfony |
| database_user | Benutzer, der Schreibrecht in der Datenbank hat | root |
| database_password | Passwort des Benutzers | ~ |
| database_path | nur für SQLite, definiere, wo die Datenbankdatei abgelegt werden soll. Lass den Parameter leer für andere Datenbanktypen. | `%kernel.root_dir%/ ../data/db/wallabag.sqlite` |
| database_table_prefix | alle wallabag Tabellen erhalten diesen Präfix im Namen. Du kannst einen _ dafür im Präfix nutzen, um das zu verdeutlichen. | wallabag_ |
| database_socket | Wenn deine Datenbank einen Socket statt TCP nutzt, schreibe hier den Pfad zum Socket hin (andere Verbindungsparameter werden dann ignoriert. | null |
| database_charset | For PostgreSQL & SQLite you should use utf8, for MySQL use utf8mb4 to handle emoji and other special characters | utf8mb4 |

## E-Mails Konfiguration

| Name  | Beschreibung | Standardwert |
| -----|-------------|-------- |
| mailer_transport | Die exakte Transportmethode, um E-Mails zuzustellen. Gültige Werte sind: smtp, gmail, mail, sendmail, null (was das Mailen deaktivert) | smtp |
| mailer_host | Der Host, zu dem sich verbunden wird, wenn SMTP als Transport genutzt wird. | 127.0.0.1 |
| mailer_user | Der Benutzername, wenn SMTP als Transport genutzt wird. | ~ |
| mailer_password | Das Passwort, wenn SMTP als Transport genutzt wird. | ~ |

## Andere wallabag Optionen

| Name  | Beschreibung | Standardwert |
| ------|-------------|-------------- |
| locale | Standardsprache deiner wallabag Instanz (wie z.B. en, fr, es, etc.) | en |
| secret | Dieser String sollte einzigartig für deine Applikation sein und er wird genutzt, um sicherheitsrelevanten Operationen mehr Entropie hinzuzufügen. | ovmpmAWXRCabNlMgzlzFXDYmCFfzGv |
| twofactor_auth | true, um Zwei-Faktor-Authentifizierung zu aktivieren | true |
| twofactor_sender | E-Mail-Adresse des Senders der Mails mit dem Code für die Zwei-Faktor-Authentifizierung | no-reply@wallabag.org |
| fosuser_registration | true, um die Registrierung für jedermann zu aktivieren | true |
| fosuser_confirmation | true, um eine Bestätigungsmail für jede Registrierung zu senden | true |
| from_email | E-Mail-Adresse, die im Absenderfeld jeder Mail genutzt wird | no-reply@wallabag.org |
| rss_limit | Artikellimit für RSS Feeds | 50 |
| domain_name | Komplette URL deiner wallabag-Instanz (ohne Slash am Ende) | https://deine-wallabag-instanz.de |

## RabbitMQ Konfiguration

| Name  | Beschreibung | Standardwert |
| -----|-------------|-------- |
| rabbitmq_host | Host deines RabbitMQ | localhost |
| rabbitmq_port | Port deines RabbitMQ | 5672 |
| rabbitmq_user | Benutzer, der die Queue lesen kann | guest |
| rabbitmq_password | Passwort dieses Benutzers | guest |

## Redis Konfiguration

| Name  | Beschreibung | Standardwert |
| -----|-------------|-------- |
| redis_scheme | Bestimmt das Protokoll, dass genutzt wird, um mit Redis zu kommunizieren. Gültige Werte sind: tcp, unix, http | tcp |
| redis_host | IP oder Hostname des Zielservers (ignoriert bei Unix Schema) | localhost |
| redis_port | TCP/IP Port des Zielservers (ignoriert bei Unix Schema) | 6379 |
| redis_path | Pfad zur Unix Domain Socket Datei, wenn Redis Unix Domain Sockets nutzt | null
| redis_password | Kennwort, welches in der Redis-Server-Konfiguration definiert ist (Parameter requirepass in redis.conf) | null
