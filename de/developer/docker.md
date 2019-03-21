Lasse wallabag in docker-compose laufen
=======================================

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

Um deine eigene Entwicklungsinstanz von wallabag laufen zu lassen,
möchtest du vielleicht die vorkonfigurierten docker compose Dateien
nutzen.

Voraussetzungen
---------------

Stelle sicher
[Docker](https://docs.docker.com/installation/ubuntulinux/) und [Docker
Compose](https://docs.docker.com/compose/install/) auf deinem System
verfügbar und aktuell zu haben.

Wechsel des DBMS
----------------

Standardmäßig startet wallabag mit einer SQLite Datenbank. Da wallabag
Unterstützung für Postgresql und MySQL bietet, gibt es auch docker
Container für diese.

In der `docker-compose.yml` kommentierst du für das gewählte DBMS aus:

-   die Container Definition (`postgres` oder `mariadb` root Level
    Block)
-   den Container Link in dem `php` Container
-   die Container Umgebungsdatei in dem `php` Container

Um mit Symfony Kommandos auf deinem Host auszuführen (wie z.B.
`wallabag:install`), sollst du außerdem:

-   die richtige Umgebungsdatei auf deiner Kommandozeile einlesen,
    sodass Variablen wie `SYMFONY__ENV__DATABASE_HOST` existieren
-   eine Zeile `127.0.0.1 rdbms` in deiner `hosts` Datei auf dem System
    erstellen

wallabag laufen lassen
----------------------

1.  Forke und klone das Projekt
2.  Bearbeite `app/config/parameters.yml` um `database_*` Eigenschaften
    mit den kommentierten zu ersetzen (mit Werten mit `env.` Präfix)
3.  `composer install` die Projektabhängigkeiten
4.  `php bin/console wallabag:install`, um das Schema zu erstellen
5.  `docker-compose up` um die Container laufen zu lassen
6.  Schließlich öffne <http://localhost:8080/>, um dein frisch
    installiertes wallabag zu finden.

In den verschiedenen Schritten wirst du vielleicht in verschiendene
Probleme laufen wie UNIX Berechtigungsprobleme, falschen Pfaden im
generierten Cache, etc.… Operationen wie das Löschen der Cachedateien
oder das Ändern der Dateibesitzer können öfter gebraucht werden, darum
habe keine Angst sie anzupassen.
