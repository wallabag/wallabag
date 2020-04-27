# Rechte, um das Projektverzeichnis zu betreten

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

## Testumgebung

Wenn wir nur wallabag testen wollen, führen wir nur das Kommando
`php bin/console server:run --env=prod` aus, um unsere wallabag Instanz
zu starten und alles wird geschmeidig laufen, weil der Nutzer, der das
Projekt gestartet hat, den aktuellen Ordner ohne Probleme betreten kann.

## Produktionsumgebung

Sobald wir Apache oder Nginx nutzen, um unsere wallabag Instanz zu
erreichen, und nicht das Kommando
`php bin/console server:run --env=prod` nutzen, sollten wir dafür
sorgen, die Rechte vernünftig zu vergeben, um die Ordner des Projektes
zu schützen.

Um dies zu machen, muss der Ordner, bekannt als `DocumentRoot` (bei
Apache) oder `root` (bei Nginx), von dem Apache-/Nginx-Nutzer zugänglich
sein. Sein Name ist meist `www-data`, `apache` oder `nobody` (abhängig
vom genutzten Linuxsystem).

Der Ordner `/var/www/wallabag/web` musst dem letztgenannten zugänglich
sein. Aber dies könnte nicht genug sein, wenn wir nur auf diesen Ordner
achten, weil wir eine leere Seite sehen könnten oder einen Fehler 500,
wenn wir die Homepage des Projekt öffnen.

Dies kommt daher, dass wir die gleichen Rechte dem Ordner
`/var/www/wallabag/var` geben müssen, so wie wir es für den Ordner
`/var/www/wallabag/web` gemacht haben. Somit beheben wir das Problem mit
dem folgenden Kommando:

```bash
chown -R www-data:www-data /var/www/wallabag/var
```

Es muss analog für die folgenden Ordner ausgeführt werden

-   /var/www/wallabag/bin/
-   /var/www/wallabag/app/config/
-   /var/www/wallabag/vendor/
-   /var/www/wallabag/data/

durch Eingabe der Kommandos

```bash
chown -R www-data:www-data /var/www/wallabag/bin
chown -R www-data:www-data /var/www/wallabag/app/config
chown -R www-data:www-data /var/www/wallabag/vendor
chown -R www-data:www-data /var/www/wallabag/data/
```

ansonsten wirst du früher oder später folgenden Fehlermeldung sehen:

```
Unable to write to the "bin" directory.
file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied
```

### Zusätzliche Regeln für SELinux

Wenn SELinux in deinem System aktiviert ist, wirst du zusätzliche
Kontexte konfigurieren müssen damit wallabag ordentlich funktioniert. Um
zu testen, ob SELinux aktiviert ist, führe einfach folgendes aus:

`getenforce`

Dies wird `Enforcing` ausgeben, wenn SELinux aktiviert ist. Einen neuen
Kontext zu erstellen, erfordert die folgende Syntax:

`semanage fcontext -a -t <context type> <full path>`

Zum Beispiel:

`semanage fcontext -a -t httpd_sys_content_t "/var/www/wallabag(/.*)?"`

Dies wird rekursiv den httpd\_sys\_content\_t Kontext auf das wallabag
Verzeichnis und alle darunterliegenden Dateien und Ordner anwenden. Die
folgenden Regeln werden gebraucht:

| Vollständiger Pfad  | Kontext |
| ------------- | ------------- |
| /var/www/wallabag(/.\*)?  | `httpd_sys_content_t`  |
| /var/www/wallabag/data(/.\*)?  | `httpd_sys_rw_content_t`  |
| /var/www/wallabag/var/logs(/.\*)?  | `httpd_log_t`  |
| /var/www/wallabag/var/cache(/.\*)?  | `httpd_cache_t`  |


Nach dem diese Kontexte erstellt wurden, tippe das folgende, um deine
Regeln anzuwenden:

`restorecon -R -v /var/www/wallabag`

Du kannst deine Kontexte in einem Verzeichnis überprüfen, indem du
`ls -lZ` tippst und alle deine aktuellen Regeln mit
`semanage fcontext -l -C` überprüfst.

Wenn du das vorkonfigurierte latest-v2-package installierst, dann ist
eine weitere Regel während der Installation nötig:

`semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/wallabag/var"`

Nachdem du erfolgreich dein wallabag erreichst und die Installation
fertiggestellt hast, kann dieser Kontext entfernt werden:

```bash
semanage fcontext -d -t httpd_sys_rw_content_t "/var/www/wallabag/var"
retorecon -R -v /var/www/wallabag/var
```
