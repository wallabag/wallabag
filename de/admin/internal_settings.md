# Wozu sind die internen Einstellungen?

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

Die Seite interne Einstellungen ist nur für den Administrator der Instanz verfügbar. Sie erlaubt die Verwaltungen von weiteren Einstellungen, wie z.B. die Aktivierung einiger Featuers.

## Analytiken

### Piwik aktivieren

`1`, um den Javascript Code für [Piwik](https://piwik.org/) einzufügen.

### Host deiner Website in Piwik (ohne `http://` ou `https://`)

URL für deinen Piwik Server.

### ID deiner Website in Piwik

ID deiner Website in Piwik, verfügbar in `Einstellungen` -> `Websites` -> `Verwalten`.

Zum Beispiel:

![ID deiner Website in Piwik](../../img/admin/id_piwik.png)

## Artikel

### Diaspora URL, wenn der Service aktiviert ist

URL deiner Diaspora\* Instanz.

### Aktivieren Authentifizierung für Websites mit Bezahlschranke

`1`, um die Authentifizierung für Websites mit einer Bezahlschranke zu aktiveren (z.B.: Mediapart, Next INpact, etc.).

### Shaarli URL, wenn der Service aktiviert ist

URL Shaarli Instanz.

### Aktiviere das Teilen via Diaspora

`1`, um den Teilenbutton für [Diaspora\*](https://diasporafoundation.org/) anzuzeigen, `0` zum Deaktivieren.

### Aktiviere das Teilen via E-Mail

`1`, um den Teilenbutton für E-mail anzuzeigen, `0` zum Deaktivieren.

### Erlaube öffentliches Teilen von URLs für Artikel

`1`, um das öffentliche Teilen zu erlauben, `0` zum Deaktivieren.

### Aktiviere das Teilen via Shaarli

`1`, um den Teilenbutton für [Shaarli](https://github.com/shaarli/Shaarli) anzuzeigen, `0` zum Deaktivieren.

### Aktiviere das Teilen via Twitter

`1`, um den Teilenbutton für [Twitter](https://twitter.com/) anzuzeigen, `0` zum Deaktivieren.

### Aktiviere das Teilen via Unmark.it

`1`, um den Teilenbutton für [Unmark.it](https://unmark.it/) anzuzeigen, `0` zum Deaktivieren.

### Zeige Link zum Drucken des Artikels

`1`, um den Druckenbutton anzuzeigen, `0` zum Deaktivieren.

### Unmark.it URL, wenn der Service aktiviert ist

URL deiner Unmark.it Instanz.

## Export

### Aktiviere CSV Export

`1`, um den CSV Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere ePub Export

`1`, um den ePub Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere JSON Export

`1`, um den JSON Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere mobi Export

`1`, um den mobi Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere PDF Export

`1`, um den PDF Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere TXT Export

`1`, um den TXT Export zu aktivieren, `0` zum Deaktivieren.

### Aktiviere XML Export

`1`, um den XML Export zu aktivieren, `0` zum Deaktivieren.

## Import

### Aktiviere RabbitMQ, um Daten asynchron zu importieren

`1`, um RabbitMQ zu aktiveren, `0` zum Deaktivieren (siehe [Asynchrone Aufgaben](../asynchronous.md)).

### Aktiviere Redis, um Daten asynchron zu importieren

`1`, um Redis zu aktiveren, `0` zum Deaktivieren (siehe [Asynchrone Aufgaben](../asynchronous.md)).

## Verschiedenes

### Demomodus aktivieren? (nur in der öffentlichen wallabag Demo genutzt)

`1`, um den Demomodus zu aktiveren, `0` zum Deaktivieren (es ist nicht möglich, das Benutzerkonto zu ändern)

### Demobenutzer

Benutzername des Kontos, das für die Demo genutzt wirde.

### Lokaler Bilderdownload

`1`, um den Download der Bilder zu aktivieren, `0`, zum Deaktivieren.

Sobald dieses Feature aktiviert ist, werden die Artikel Bilder in den Ordner `/web/assets/images` deiner wallabag Instanz heruntergeladen. Der Pfad der Bilder innerhalb der Artikel wird auch aktualisiert zu dem Pfad, wo die Bilder auf deiner Instanz liegen.

### Support URL für wallabag

URL, die in von wallabag gesendeten E-Mails für jegliche Hilfegesuche genutzt wird.
