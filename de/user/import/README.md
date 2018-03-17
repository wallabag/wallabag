Migration von einem Drittanbieter
=================================

In wallabag 2.x kannst du Daten von folgenden Anbietern importieren:

-   [Pocket](Pocket.md)
-   [Instapaper](Instapaper.md)
-   [Readability](Readability.md)
-   [Pinboard](Pinboard.md)
-   [wallabag 1.x](wallabagv1.md)
-   [wallabag 2.x](wallabagv2.md)

Wir haben zusätzlich [ein Skript für die Migration per
Kommandozeile](#import-via-command-line-interface-cli) geschrieben.

Da Importe eine Menge Zeit in Anspruch nehmen können, haben wir auch ein
asynchrones Aufgabensystem entwickelt. [Du kannst die Dokumentation hier lesen](../../admin/asynchronous.md)
(für Experten).

Import über die Kommandozeile (CLI)
-----------------------------------

Falls du auf deinem Server Zugriff auf die Kommandozeile hast, kannst du
den folgenden Befehl ausführen, um deine Daten aus wallabag v1 zu
importieren:

```bash
bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod
```

Bitte ersetze die Werte:

-   `1` ist die Benutzer-ID in der Datenbank (die ID des ersten
    Benutzers ist immer 1)
-   `~/Downloads/wallabag-export-1-2016-04-05.json` ist der Pfad zu
    deiner wallabag v1-Exportdatei

Wenn du alle Artikel als gelesen markieren möchtest, kannst du die
`--markAsRead`-Option hinzufügen.

Um eine wallabag 2.x-Datei zu importieren, musst du die Option
`--importer=v2` hinzufügen.

Als Ergebnis wirst du so etwas erhalten:

```
Start : 05-04-2016 11:36:07 ---
403 imported
0 already saved
End : 05-04-2016 11:36:09 ---
```
