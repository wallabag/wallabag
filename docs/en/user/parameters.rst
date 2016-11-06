What is the meaning of the parameters?
======================================

Default `parameters.yml` file
-----------------------------

Here is the last version of the default `app/config/parameters.yml` file. Be sure that yours respects this one.
If you don't know which parameter you need to set, please leave the default one.

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

.. csv-table:: Database parameters
   :header: "name", "default", "description"

   "database_driver", "pdo_sqlite", "Should be pdo_sqlite or pdo_mysql or pdo_pgsql"
   "database_host", "127.0.0.1", "host of your database (usually localhost or 127.0.0.1)"
   "database_port", "~", "port of your database (you can leave ``~`` to use the default one)"
   "database_name", "symfony", "name of your database"
   "database_user", "root", "user that can write to this database"
   "database_password", "~", "password of that user"
   "database_path", "``""%kernel.root_dir%/../data/db/wallabag.sqlite""``", "only for SQLite, define where to put the database file. Leave it empty for other database"
   "database_table_prefix", "wallabag_", "all wallabag's tables will be prefixed with that string. You can include a ``_`` for clarity"
   "database_socket", "null", "If your database is using a socket instead of tcp, put the path of the socket (other connection parameters will then be ignored)"

.. csv-table:: Configuration to send emails from wallabag
   :header: "name", "default", "description"

   "mailer_transport", "smtp",  "The exact transport method to use to deliver emails. Valid values are: smtp, gmail, mail, sendmail, null (which will disable the mailer)"
   "mailer_host", "127.0.0.1",  "The host to connect to when using smtp as the transport."
   "mailer_user", "~",  "The username when using smtp as the transport."
   "mailer_password", "~",  "The password when using smtp as the transport."

.. csv-table:: Other wallabag's option
   :header: "name", "default", "description"

   "locale", "en", "Default language of your wallabag instance (like en, fr, es, etc.)"
   "secret", "ovmpmAWXRCabNlMgzlzFXDYmCFfzGv", "This is a string that should be unique to your application and it's commonly used to add more entropy to security related operations."
   "twofactor_auth", "true", "true to enable Two factor authentication"
   "twofactor_sender", "no-reply@wallabag.org", "email of the email sender to receive the two factor code"
   "fosuser_registration", "true", "true to enable public registration"
   "fosuser_confirmation", "true", "true to send a confirmation by email for each registration"
   "from_email", "no-reply@wallabag.org", "email address used in From: field in each email"
   "rss_limit", "50", "limit for RSS feeds"

.. csv-table:: RabbitMQ configuration
   :header: "name", "default", "description"

   "rabbitmq_host", "localhost", "Host of your RabbitMQ"
   "rabbitmq_port", "5672", "Port of your RabbitMQ"
   "rabbitmq_user", "guest", "User that can read queues"
   "rabbitmq_password", "guest", "Password of that user"

.. csv-table:: Redis configuration
   :header: "name", "default", "description"

   "redis_scheme", "tcp", "Specifies the protocol used to communicate with an instance of Redis. Valid values are: tcp, unix, http"
   "redis_host", "localhost", "IP or hostname of the target server (ignored for unix scheme)"
   "redis_port", "6379", "TCP/IP port of the target server (ignored for unix scheme)"
   "redis_path", "null", "Path of the UNIX domain socket file used when connecting to Redis using UNIX domain sockets"
