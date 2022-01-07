# Installation

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

## Auf einem dedizierten Webserver (empfohlener Weg)

Um wallabag selbst zu installieren, musst du die folgenden Kommandos
ausführen:

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

Um PHPs eingebauten Server zu starten und zu testen, ob alles korrekt
installiert wurde, kannst du folgendes Kommando ausführen:

```bash
make run
```

und wallabag unter <http://deineserverip:8000> erreichen.

Um Parameter mit Umgebungsvariable zu definieren, musst du die Variable
mit dem `SYMFONY__` Präfix setzen. Zum Beispiel
`SYMFONY__DATABASE_DRIVER`. Du kannst einen Blick die [Symfony
Dokumentation](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html)
werfen.

### Auf einem geteilten Webhosting

Wir stellen ein Paket inkl. aller Abhängigkeiten bereit. Die
Standardkonfiguration nutzt MySQL für die Datenbank. Wenn du diese
Einstellung ändern willst, ändere bitte `app/config/parameters.yml`.
Achtung: die Passwörter müssen in einfache Anführungszeichen (') eingeschlossen werden.

Wir haben bereits einen Nutzer erstellt: Login und Passwort sind
`wallabag`.

Mit diesem Paket überprüft wallabag nicht die von der Applikation
gebrauchten Exentions (diese Tests werden während `composer install`
durchgeführt wenn du einen dedizierten Webserver hast, siehe oben).

Führe dieses Kommando aus, um das neueste Paket herunterzuladen und zu
entpacken:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

Du findest die [md5 Hashsumme des neuesten Pakets auf unserer
Website](https://static.wallabag.org/releases/).

Das statische Paket erfordert, dass jeder Befehl mit `--env=prod` angehängt wird, da das statische Paket nur als prod-Umgebung verwendet werden kann (Entwicklungsumgebung wird nicht unterstützt und funktioniert überhaupt nicht).

Jetzt lies die Dokumentation, um einen Virtualhost zu erstellen, dann
greife auf dein wallabag zu. Wenn du die Datenbankkonfiguration
eingestellt hast, MySQL oder PostgreSQL zu nutzen, musst du einen Nutzer
über das folgende Kommando erstellen
`php bin/console wallabag:install --env=prod`.

### Installation mit Docker

Wir stellen ein Docker Image zu Verfügung, um wallabag einfach zu
installieren. Schaue in unser Repository in unserem [Docker
Hub](https://hub.docker.com/r/wallabag/wallabag/), um mehr Informationen
zu erhalten.

#### Kommando, um den Container zu starten

```bash
docker pull wallabag/wallabag
```

### Cloudron Installation

Cloudron bietet einfache Webapp Installation auf deinem Server, mit
Fokus auf System Administrator Automatisierung und Updates. Ein Wallabag
Paket ist direkt zur Installation durch den Cloudron Store verfügbar.

[Installiere wallabag auf deinem
Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp.html)
