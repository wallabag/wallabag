# À quoi servent les paramètres ?

## Fichier parameters.yml par défaut

Voici la dernière version par défaut du fichier
app/config/parameters.yml. Soyez sur que le votre respecte celui-ci. Si
vous ne savez pas quelle valeur vous devez mettre, laissez celle par
défaut.

> **[danger] Information**
>
> Pour appliquer les changements dans `parameters.yml`, vous devez vider le cache en supprimant tout ce qui se trouve dans `var/cache` avec cette commande : `bin/console cache:clear -e=prod`.

```yml
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

## Signification de chaque paramètre

### Paramètres de base de données

| Nom  | Description | Valeur par défaut |
| ------|---------|------------ |
| database_driver | Doit être pdo_sqlite ou pdo_mysql ou pdo_pgsql | pdo_sqlite |
| database_host  | Hôte de votre base de données (généralement localhost ou 127.0.0.1) | 127.0.0.1 |
| database_port  | Port de votre base de données (vous pouvez laisser ~ pour utiliser celui par défaut) | ~ |
| database_name | Nom de votre base de données | symfony |
| database_user | Utilisateur de votre base de données | root |
| database_password | Mot de passe de cet utilisateur | ~ |
| database_path | Uniquement pour SQLite. Chemin du fichier de base de données. Laissez vide pour les autres bases de données. | `%kernel.root_dir%/ ../data/db/wallabag.sqlite` |
| database_table_prefix | Toutes les tables de wallabag seront préfixées par cette chaine. Vous pouvez ajouter un _ pour plus de clarté | wallabag_ |
| database_socket | Si votre base de données utilise un socket plutôt que tcp, spécifiez le chemin du socket (les autres paramètres de connexion seront alors ignorés) | null |
| database_charset | Pour PostgreSQL & SQLite vous devriez utiliser utf8, pour MySQL utilisez utf8mb4 pour prendre en charge les emoji et autres caractères spéciaux | utf8mb4 |

## Paramètres email

| Nom  | Description | Valeur par défaut |
| -----|-------------|-------- |
| mailer_transport | Méthode de transport exacte utilisée pour envoyer des emails. Les valeurs correctes sont : smtp, gmail, mail, sendmail, null (ce qui désactivera l'envoi des emails) | smtp |
| mailer_host | Hôte sur lequel se connecter quand on utilise smtp comme transport. | 127.0.0.1 |
| mailer_user | Utilisateur smtp. | ~ |
| mailer_password | Mot de passe de cet utilisateur. | ~ |

## Autres paramètres de wallabag

| Nom  | Description | Valeur par défaut |
| -----|-------------|-------- |
| locale | Langue par défaut de votre instance wallabag (comme en, fr, es, etc.) | en |
| secret | C'est une chaine qui doit être unique à votre application et qui est couramment utilisée pour ajouter plus d'entropie aux opérations relatives à la sécurité. | ovmpmAWXRCabNlMgzlzFXDYmCFfzGv |
| twofactor_auth | true pour activer l'authentification à deux facteurs | true |
| twofactor_sender | Email de l'expéditeur du code de l'authentification à deux facteurs | no-reply@wallabag.org |
| fosuser_registration | true pour activer l'inscription publique | true |
| fosuser_confirmation | true pour envoyer un email de confirmation pour chaque création de compte | true |
| from_email | Email de l'expéditeur pour chaque email envoyé | no-reply@wallabag.org |
| rss_limit | Limite pour les flux RSS | 50 |
| domain_name | URL complète de votre instance wallabag (sans le / de fin) | https://your-wallabag-url-instance.com |

## Options de RabbitMQ

| Nom  | Description | Valeur par défaut |
| -----|-------------|-------- |
| rabbitmq_host | Hôte de votre instance RabbitMQ | localhost |
| rabbitmq_port | Port de votre instance RabbitMQ | 5672 |
| rabbitmq_user | Utilisateur de votre instance RabbitMQ | guest |
| rabbitmq_password | Mot de passe de cet utilisateur | guest |

## Options de Redis

| Nom  | Description | Valeur par défaut |
| -----|-------------|-------- |
| redis_scheme | Définit le protocole utilisé pour commuiquer avec l'instance Redis. Les valeurs correctes sont : tcp, unix, http | tcp |
| redis_host | IP ou hôte du serveur cible (ignoré pour un schéma unix) | localhost |
| redis_port | Port TCP/IP du serveur cible (ignoré pour un schéma unix) | 6379 |
| redis_path | Chemin du fichier de socket du domaine UNIX utilisé quand on se connecte à Redis en utilisant les sockets du domaine UNIX | null
| redis_password | Mot de passe défini dans la configuration serveur de Redis (paramètre requirepass dans redis.conf) | null
