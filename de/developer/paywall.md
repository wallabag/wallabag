Artikel hinter einer Paywall
============================

wallabag kann auch Artikel von Webseiten auslesen, welche eine Paywall (dt. Bezahlschranke) verwenden.

Paywall-Authentifizierung aktivieren
------------------------------------

Setze in den internen Einstellungen im Bereich "Artikel" die Authentifizierung
für Webseiten mit einer Paywall (mit dem Wert 1).

Anmeldedaten in wallabag konfigurieren
---------------------------------

Bearbeite deine `app/config/parameters.yml`-Datei, um die Daten
für jede Webseite mit einer Paywall zu hinterlegen. Hier ist ein Beispiel für einige französische Webseiten:

``` {.sourceCode .yaml}
sites_credentials:
    mediapart.fr: {username: "myMediapartLogin", password: "mypassword"}
    arretsurimages.net: {username: "myASILogin", password: "mypassword"}
```

<div class="admonition note">

Diese Daten werden mit jedem Nutzer der wallabag-Instanz geteilt.

</div>

Konfigurationsdateien parsen
-----------------------------

<div class="admonition note">

Lese [diesen Teil der Dokumentation](../user/errors_during_fetching.md),
um die Konfiguration zu verstehen.

</div>

Jede Parsing-Konfigurationsdatei muss mit den Feldern
`requires_login`, `login_uri`, `login_username_field`,
`login_password_field` und `not_logged_in_xpath` erweitert werden.

Sei vorsichtig, das Login-Formular muss auf der Inhaltsseite sein,
wenn wallabag diese lädt. Es ist unmöglich, auf einer Webseite angemeldet
zu werden, bei welcher das Login-Formular erst im Nachhinein (etwa durch
AJAX) geladen wird.

`login_uri` ist die Aktions-URL des Formulars (`action`-Attribut).
`login_username_field` ist das `name`-Attribut von dem Login-Feld.
`login_password_field` ist das `name`-Attribut von dem Password-Feld.

Beispiel:

``` {.sourceCode .}
title://div[@id="titrage-contenu"]/h1[@class="title"]
body: //div[@class="contenu-html"]/div[@class="page-pane"]

requires_login: yes

login_uri: http://www.arretsurimages.net/forum/login.php
login_username_field: username
login_password_field: password

not_logged_in_xpath: //body[@class="not-logged-in"]
```
