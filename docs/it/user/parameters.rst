Qual'é il significato dei parametri?
====================================

File `parameters.yml` di default
--------------------------------

Ecco l'ultima versione del file `app/config/parameters.yml` di default. Assicuratevi che la vostra rispetti questa.
Se non sapete quale parametro dovete impostare, si prega di lasciare quello di default.

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

Significato di ogni parametro
-----------------------------

.. csv-table:: Parametri del database
   :header: "nome", "default", "descrizione"

   "database_driver", "pdo_sqlite", "Dovrebbe essere pdo_sqlite o pdo_mysql o pdo_pgsql"
   "database_host", "127.0.0.1", "Host del vostro database (generalmente localhost o 127.0.0.1)"
   "database_port", "~", "Porta del vostro database (potete lasciare ``~`` per usare quella di default)"
   "database_name", "symfony", "Nome del vostro database"
   "database_user", "root", "L'utente che puó scrivere su questo database"
   "database_password", "~", "Password di quell'utente"
   "database_path", "``""%kernel.root_dir%/../data/db/wallabag.sqlite""``", "Solo per SQLite, definite dove mettere il file del database. Lasciatelo vuoto se usate un altro database"
   "database_table_prefix", "wallabag_", "Tutte le tavole di wallabag avranno quella stringa come prefisso. Potete includere un ``_`` per maggior chiarezza"
   "database_socket", "null", "Se il vostro database usa un socket al posto di tcp, inserite il percorso del socket (altri parametri di connessione saranno ignorati)"

.. csv-table:: Configurazione per inviare email da wallabag
   :header: "nome", "default", "descrizione"

   "mailer_transport", "smtp",  "Il metodo di trasporto esatto usato per consegnare email. Valori validi sono: smtp, gmail, mail, sendmail, null (ció disattiva il mailer)"
   "mailer_host", "127.0.0.1",  "L'host al quale connettersi quando si usa smtp come metodo di trasporto."
   "mailer_user", "~",  "Lo username quando si usa smtp come metodo di trasporto."
   "mailer_password", "~",  "La password quando si usa smtp come metodo di trasporto."

.. csv-table:: Altre opzioni di wallabag
   :header: "nome", "default", "descrizione"

   "locale", "en", "Lingua di default della vostra istanza di wallabag (come en, fr, es, etc.)"
   "secret", "ovmpmAWXRCabNlMgzlzFXDYmCFfzGv", "Questa é una stringa che dovrebbe essere unica per la vostra applicazione ed é usata comunemente per aggiungere piú entropia alle operazioni di sicurezza."
   "twofactor_auth", "true", "true per abilitare l'autenticazione a due fattori"
   "twofactor_sender", "no-reply@wallabag.org", "Email del mittente per ricevere il codice a due fattori"
   "fosuser_registration", "true", "true per abilitare la registrazione pubblica"
   "fosuser_confirmation", "true", "true per inviare una mail di conferma per ogni registrazione"
   "from_email", "no-reply@wallabag.org", "Indirizzo email usato nel campo Da: in ogni email"
   "rss_limit", "50", "Limite per i feed RSS"

.. csv-table:: Configurazione di RabbitMQ
   :header: "nome", "default", "descrizione"

   "rabbitmq_host", "localhost", "Host del vostro RabbitMQ"
   "rabbitmq_port", "5672", "Porta del vostro RabbitMQ"
   "rabbitmq_user", "guest", "Utente che puó leggere le code"
   "rabbitmq_password", "guest", "Password di quell'utente"

.. csv-table:: Configurazione di Redis
   :header: "nome", "default", "descrizione"

   "redis_scheme", "tcp", "Specifica il protocollo da usare per comunicare con una istanza di Redis. Valori validi sono: tcp, unix, http"
   "redis_host", "localhost", "IP o hostname del server bersaglio (ignorato per lo schema unix)"
   "redis_port", "6379", "Porta TCP/IP del server bersaglio (ignorato per lo schema unix)"
   "redis_path", "null", "Percorso del file domain socket di UNIX usato quando ci si connette a Redis usando domain socket di UNIX"
