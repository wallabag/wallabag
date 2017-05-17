Kommandozeilenbefehle
=====================

wallabag hat ein paar Kommandos, um einige Aufgaben zu managen. Du kannst
alle Kommandos auflisten, in dem du `bin/console` in dem wallabag Ordner
ausführst.

Jedes Kommando hat eine Hilfefunktion, die durch
`bin/console help %kommando%` einsehbar ist.

> Wenn du in einer produktiven Umgebung bist, denke daran, `-e prod` zu
jedem Kommando hinzuzufügen.

Wichtige Kommandos
------------------

-   `assets:install`: Ist hilfreich wenn Assets fehlen.
-   `cache:clear`: Sollte nach jedem Update ausgeführt werden (ist in
    `make update` bereits integriert).
-   `doctrine:migrations:status`: Gibt den Status deiner Datenbankmigration
    zurück
-   `fos:user:activate`: Einen Nutzer manuell aktivieren.
-   `fos:user:change-password`: Ein Passwort eines Nutzers ändern.
-   `fos:user:create`: Einen Nutzer erstellen.
-   `fos:user:deactivate`: Einen Nutzer deaktivieren (nicht löschen).
-   `fos:user:demote`: Entfernt eine Rolle von einem Nutzer, typischerweise
    Adminrechte.
-   `fos:user:promote`: Fügt eine Rolle einem Nutzer hinzu, typischerweise
    Adminrechte.
-   `rabbitmq:*`: Ist hilfreich, wenn du RabbitMQ nutzt.
-   `wallabag:clean-duplicates`: Entfernt alle doppelten Einträge für 
    einen oder alle Nutzer.
-   `wallabag:export`: Exportiert alle Einträge eines Nutzers. Du kannst
    den Ausgabepfad für die Datei angeben.
-   `wallabag:import`: Importiert Einträge in verschiedene Format für ein
    Nutzerkonto.
-   `wallabag:import:redis-worker`: Hilfreich, wenn du Redis nutzt.
-   `wallabag:install`: wallabag (neu) installieren.
-   `wallabag:<tag:all>`: Tagge alle Einträge eines Nutzers entsprechend
    seiner Tagging-Regeln.
