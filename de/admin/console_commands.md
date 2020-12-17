Kommandozeilenbefehle
=====================

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

wallabag hat ein paar Kommandos, um einige Aufgaben zu managen. Du kannst
alle Kommandos auflisten, in dem du `bin/console` in dem wallabag Ordner
ausführst.

Jedes Kommando hat eine Hilfefunktion, die durch
`bin/console help %kommando%` einsehbar ist.

> Wenn du in einer produktiven Umgebung bist, denke daran, `--env=prod` zu
jedem Kommando hinzuzufügen.

Wichtige Kommandos
------------------

Von Symfony:

 - `assets:install`: Ist hilfreich, wenn die Assets fehlen.
 - `cache:clear`: Sollte nach jedem Update ausgeführt werden (inkl. `make update`).
 - `doctrine:migrations:status`: Gibt den Status deiner Datenbankmigration aus.
 - `fos:user:activate`: Aktiviert einen Benutzer manuell.
 - `fos:user:change-password`: Ändert ein Passwort für einen Benutzer.
 - `fos:user:create`: Erstellt einen Benutzer.
 - `fos:user:deactivate`: Deaktiviert einen Benutzer (nicht löschen).
 - `fos:user:demote`: Entfernt eine Rolle von einem Benutzer, typischerweise Adminrechte.
 - `fos:user:promote`: Fügt einem Benutzer einen Rolle hinzu, typischerweise Adminrechte.
 - `rabbitmq:*`: Ist hilfreich, wenn du RabbitMQ benutzt.

Speziell für wallabag:

 - `wallabag:clean-duplicates`: Entfernt alle doppelten Einträge von einem oder allen Benutzern.
 - `wallabag:export`: Exportiert alle Einträge eines Benutzers. Du kannst den Ausgabepfad für die Datei wählen.
 - `wallabag:import`: Importiert Einträge in verschiedenen Formaten für einen Benutzeraccount.
 - `wallabag:import:redis-worker`: Ist hilfreich, wenn du Redis benutzt.
 - `wallabag:install`: (Neu)installierung von wallabag
 - `wallabag:tag:all`: Taggt alle Einträge für einen Benutzer entsprechend seiner/ihrer Tagging-Regeln.
 - `wallabag:user:show`: Zeigt die Details eines Benutzers.

wallabag:clean-duplicates
-------------------------

Dieser Befehl hilft dir deine Artikelliste aufzuräumen falls du Duplikate hast.

Benutzung:

```
wallabag:clean-duplicates [<benutzername>]
```

Arguments:

 - benutzername: aufzuräumender Benutzeraccount


wallabag:export
---------------

Dieser Befehl hilft dir alle Einträge eines Benutzers zu exportieren.

Benutzung:

```
wallabag:export <benutzername> [<dateipfad>]
```

Argumente:

 - benutzername: Benutzer, von dem die Einträge exportiert werden sollen
 - dateipfad: Pfad für die zu exportierende Datei


wallabag:import
---------------

Dieser Befehl hilft dir, die Einträge von einem JSON-Export zu importieren.

Benutzung:

```
wallabag:import [--] <benutzer> <dateipfad>
```

Argumente:

 - benutzer: Benutzer, für den die Einträge sind
 - dateipfad: Pfad zur JSON-Datei

Optionen:

 - `--importer=IMPORTER`: Der zu benutzende Importer: v1, v2, instapaper, pinboard, readability, firefox oder chrome [Standard: "v1"]
 - `--markAsRead=MARKASREAD`: Markiert alle Einträge als gelesen [Standard: false]
 - `--useUserId`: Die Benutzer-ID anstatt des Benutzernamens, um den Account zu identifizieren
 - `--disableContentUpdate`: Deaktiviere das erneute Laden aktualisierter Inhalte von einer URL


wallabag:import:redis-worker
----------------------------

Dieser Befehl hilft dir, den Redis-Prozess zu starten.

Benutzung:

```
wallabag:import:redis-worker [--] <serviceName>
```

Argumente:

 - serviceName: der zu benutzende Service: wallabag_v1, wallabag_v2, pocket, readability, pinboard, firefox, chrome oder instapaper

Optionen:

 - `--maxIterations[=MAXITERATIONS]`: Anzahl der Iterationen bevor gestoppt wird [Standard: false]


wallabag:install
----------------

Dieser Befehl hilft dir, wallabag zu installieren oder neu zu installieren.

Benutzung:

```
wallabag:install
```


wallabag:tag:all
----------------

Dieser Befehl hilft dir, alle Einträg eines Nutzers mit dessen Tagging-Regeln zu markieren.

Benutzung:

```
wallabag:tag:all <benutzer>
```

Argumente:
 - benutzer: Benutzer, dessen Einträge mit Tags versehen werden sollen.


wallabag:user:show
------------------

Dieser Befehl zeigt dir die Details eines Benutzers.

Benutzung:

```
wallabag:user:show <benutzer>
```

Argumente:
 - benutzer: Benutzer, dessen Details angezeigt werden sollen.
