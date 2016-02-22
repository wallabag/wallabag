# This file is a "template" of what your parameters.yml file should look like
parameters:
    database_driver: pdo_sqlite
    database_host: 127.0.0.1
    database_port: ~
    database_name: symfony
    database_user: root
    database_password: ~
    database_path: "%kernel.root_dir%/../data/db/wallabag.sqlite"
    database_table_prefix: wallabag_

    test_database_driver: pdo_pgsql
    test_database_host: localhost
    test_database_port:
    test_database_name: wallabag_test
    test_database_user: travis
    test_database_password: ~
    test_database_path: ~

    mailer_transport:  smtp
    mailer_host:       127.0.0.1
    mailer_user:       ~
    mailer_password:   ~

    locale:            en

    # A secret key that's used to generate certain security-related tokens
    secret:            ThisTokenIsNotSoSecretChangeIt

    # two factor stuff
    twofactor_auth: true
    twofactor_sender: no-reply@wallabag.org

    # fosuser stuff
    fosuser_confirmation: true

    from_email: no-reply@wallabag.org
