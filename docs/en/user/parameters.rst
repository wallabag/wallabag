What is the meaning of the parameters?
======================================

.. code-block:: yml

    # Database parameters
    database_driver: pdo_sqlite or pdo_mysql or pdo_pgsql
    database_host: 127.0.0.1
    database_port: ~
    database_name: symfony
    database_user: root
    database_password: ~
    database_path: "%kernel.root_dir%/../data/db/wallabag.sqlite" or empty (when using mysql or postgresql)
    database_table_prefix: wallabag_
    database_socket: null

    # Configuration to send emails from wallabag
    mailer_transport:  smtp
    mailer_host:       127.0.0.1
    mailer_user:       ~
    mailer_password:   ~

    locale:            en # Default language of your wallabag instance

    secret:            ovmpmAWXRCabNlMgzlzFXDYmCFfzGv # A random string used for security

    twofactor_auth: true # true to enable Two factor authentication
    twofactor_sender: no-reply@wallabag.org

    fosuser_registration: true # true to enable public registration
    fosuser_confirmation: true # true to send a confirmation by email for each registration

    from_email: no-reply@wallabag.org # email address used in From: field in each email

    rss_limit: 50 # limit for RSS feeds

    # RabbitMQ configuration
    rabbitmq_host: localhost
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest

    # Redis configuration
    redis_scheme: tcp
    redis_host: localhost
    redis_port: 6379
    redis_path: null

