Was bedeuten die Parameter?
===========================
.. csv-table:: Datenbankparameter
   :header: "Name", "Standardwert", "Beschreibung"

   "database_driver", "pdo_sqlite", "Sollte pdo_sqlite oder pdo_mysql oder pdo_pgsql sein"
   "database_host", "127.0.0.1", "Hostadresse deiner Datenbank (normalerweise localhost oder 127.0.0.1)"
   "database_port", "~", "Port deiner Datenbank (Du kannst ``~`` stehen lassen, um den Standardport zu nutzen)"
   "database_name", "symfony", "Benenne deine Datenbank"
   "database_user", "root", "Benutzer, der Schreibrecht in der Datenbank hat"
   "database_password", "~", "Passwort des Benutzers"
   "database_path", "``""%kernel.root_dir%/../data/db/wallabag.sqlite""``", "nur für SQLite, definiere, wo die Datenbankdatei abgelegt werden soll. Lass den Parameter leer für andere Datenbanktypen."
   "database_table_prefix", "wallabag_", "alle wallabag Tabellen erhalten diesen Präfix im Namen. Du kannst einen ``_`` dafür im Präfix nutzen, um das zu verdeutlichen."
   "database_socket", "null", "Wenn deine Datenbank einen Socket statt TCP nutzt, schreibe hier den Pfad zum Socket hin (andere Verbindungsparameter werden dann ignoriert."

.. csv-table:: Konfiguration, um mit wallabag E-Mails senden zu können
   :header: "Name", "Standardwert", "Beschreibung"

   "mailer_transport", "smtp",  "Die exakte Transportmethode, um E-Mails zuzustellen. Gültige Werte sind: smtp, gmail, mail, sendmail, null (was das Mailen deaktivert)"
   "mailer_host", "127.0.0.1",  "Der Host, zu dem sich verbunden wird, wenn SMTP als Transport genutzt wird."
   "mailer_user", "~",  "Der Benutzername, wenn SMTP als Transport genutzt wird."
   "mailer_password", "~",  "Das Passwort, wenn SMTP als Transport genutzt wird."

.. csv-table:: Andere wallabag Optionen
   :header: "Name", "Standardwert", "Beschreibung"

   "locale", "en", "Standardsprache deiner wallabag Instanz (wie z.B. en, fr, es, etc.)"
   "secret", "ovmpmAWXRCabNlMgzlzFXDYmCFfzGv", "Dieser String sollte einzigartig für deine Applikation sein und er wird genutzt, um sicherheitsrelevanten Operationen mehr Entropie hinzuzufügen."
   "twofactor_auth", "true", "true, um Zwei-Faktor-Authentifizierung zu aktivieren"
   "twofactor_sender", "no-reply@wallabag.org", "E-Mail-Adresse des Senders der Mails mit dem Code für die Zwei-Faktor-Authentifizierung"
   "fosuser_registration", "true", "true, um die Registrierung für jedermann zu aktivieren"
   "fosuser_confirmation", "true", "true, um eine Bestätigungsmail für jede Registrierung zu senden"
   "from_email", "no-reply@wallabag.org", "E-Mail-Adresse, die im Absenderfeld jeder Mail genutzt wird"
   "rss_limit", "50", "Artikellimit für RSS Feeds"

.. csv-table:: RabbitMQ Konfiguration
   :header: "Name", "Standardwert", "Beschreibung"

   "rabbitmq_host", "localhost", "Host deines RabbitMQ"
   "rabbitmq_port", "5672", "Port deines RabbitMQ"
   "rabbitmq_user", "guest", "Benutzer, der die Queue lesen kann"
   "rabbitmq_password", "guest", "Passwort dieses Benutzers"

.. csv-table:: Redis Konfiguration
   :header: "Name", "Standardwert", "Beschreibung"

   "redis_scheme", "tcp", "Bestimmt das Protokoll, dass genutzt wird, um mit Redis zu kommunizieren. Gültige Werte sind: tcp, unix, http"
   "redis_host", "localhost", "IP oder Hostname des Zielservers (ignoriert bei Unix Schema)"
   "redis_port", "6379", "TCP/IP Port des Zielservers (ignoriert bei Unix Schema)"
   "redis_path", "null", "Pfad zur Unix Domain Socket Datei, wenn Redis Unix Domain Sockets nutzt"
