À quoi servent les paramètres ?
===============================

Fichier `parameters.yml` par défaut
-----------------------------------

Voici la dernière version par défaut du fichier `app/config/parameters.yml`. Soyez sur que le votre respecte celui-ci.
Si vous ne savez pas quelle valeur vous devez mettre, laissez celle par défaut.

.. code-block:: yml

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

Meaning of each parameter
-------------------------

.. csv-table:: Paramètres de base de données
   :header: "name", "default", "description"

   "database_driver", "pdo_sqlite", "Doit être pdo_sqlite ou pdo_mysql ou pdo_pgsql"
   "database_host", "127.0.0.1", "Hôte de votre base de données (généralement localhost ou 127.0.0.1)"
   "database_port", "~", "Port de votre base de données (vous pouvez laisser ``~`` pour utiliser celui par défaut)"
   "database_name", "symfony", "Nom de votre base de données"
   "database_user", "root", "Utilisateur de votre base de données"
   "database_password", "~", "Mot de passe de cet utilisateur"
   "database_path", "``""%kernel.root_dir%/../data/db/wallabag.sqlite""``", "Uniquement pour SQLite. Chemin du fichier de base de données. Laissez vide pour les autres bases de données."
   "database_table_prefix", "wallabag_", "Toutes les tables de wallabag seront préfixées par cette chaine. Vous pouvez ajouter un ``_`` pour plus de clarté"
   "database_socket", "null", "Si votre base de données utilise un socket plutôt que tcp, spécifiez le chemin du socket (les autres paramètres de connexion seront alors ignorés)"

.. csv-table:: Configuration pour envoyer des emails depuis wallabag
   :header: "name", "default", "description"

   "mailer_transport", "smtp",  "Méthode de transport exacte utilisée pour envoyer des emails. Les valeurs correctes sont : smtp, gmail, mail, sendmail, null (ce qui désactivera l'envoi des emails)"
   "mailer_host", "127.0.0.1",  "Hôte sur lequel se connecter quand on utilise smtp comme transport."
   "mailer_user", "~",  "Utilisateur smtp."
   "mailer_password", "~",  "Mot de passe de cet utilisateur."

.. csv-table:: Autres options de wallabag
   :header: "name", "default", "description"

   "locale", "en", "Langue par défaut de votre instance wallabag (comme en, fr, es, etc.)"
   "secret", "ovmpmAWXRCabNlMgzlzFXDYmCFfzGv", "C'est une chaine qui doit être unique à votre application et qui est couramment utilisée pour ajouter plus d'entropie aux opérations relatives à la sécurité."
   "twofactor_auth", "true", "true pour activer l'authentification à deux facteurs"
   "twofactor_sender", "no-reply@wallabag.org", "Email de l'expéditeur du code de l'authentification à deux facteurs"
   "fosuser_registration", "true", "true pour activer l'inscription publique"
   "fosuser_confirmation", "true", "true pour envoyer un email de confirmation pour chaque création de compte"
   "from_email", "no-reply@wallabag.org", "Email de l'expéditeur pour chaque email envoyé"
   "rss_limit", "50", "Limite pour les flux RSS"

.. csv-table:: Configuration RabbitMQ
   :header: "name", "default", "description"

   "rabbitmq_host", "localhost", "Hôte de votre instance RabbitMQ"
   "rabbitmq_port", "5672", "Port de votre instance RabbitMQ"
   "rabbitmq_user", "guest", "Utilisateur de votre instance RabbitMQ"
   "rabbitmq_password", "guest", "Mot de passe de cet utilisateur"

.. csv-table:: Configuration Redis
   :header: "name", "default", "description"

   "redis_scheme", "tcp", "Définit le protocole utilisé pour commuiquer avec l'instance Redis. Les valeurs correctes sont : tcp, unix, http"
   "redis_host", "localhost", "IP ou hôte du serveur cible (ignoré pour un schéma unix)"
   "redis_port", "6379", "Port TCP/IP du serveur cible (ignoré pour un schéma unix)"
   "redis_path", "null", "Chemin du fichier de socket du domaine UNIX utilisé quand on se connecte à Redis en utilisant les sockets du domaine UNIX"
