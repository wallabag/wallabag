wallabag sichern
================
Da es manchmal vorkommen kann, dass dir ein Fehler mit deiner wallabag unterläuft und du Daten verlierst oder deine wallabag auf einen anderen Server verschieben willst, ist eine Sicherung der Daten sicher ratsam.
Dieser Artikel beschreibt, was du für die Sicherung benötigst.

Grundlegende Einstellungen
--------------------------
wallabag speichert grundlegende Parameter (etwa der SMTP-Server oder das Datenbank-Backend) in der Datei `app/config/parameters.yml`.

Datenbank
---------
Da wallabag verschiedene Datenbank-Typen unterstützt, hängt der Weg der Sicherung von dem verwendeten Typ ab. Daher verweisen wir an dieser Stelle auf die entsprechenden Dokumentationen:

Hier sind einige Beispiele:

- MySQL: http://dev.mysql.com/doc/refman/5.7/en/backup-methods.html
- PostgreSQL: https://www.postgresql.org/docs/current/static/backup.html

SQLite
~~~~~~
Um die SQLite-Datenbank zu sichern, ist es lediglich notwendig, das Verzeichnis `data/db` aus dem wallabag-Installations-Ordner zu kopieren.

Bilder
------
Die Bilder, die von wallabag empfangen worden, sind unter `web/assets/images` gespeichert (der Bilder-Speicher wird in wallabag 2.2 implementiert).
