# Voraussetzungen

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

wallabag ist kompatibel mit **PHP &gt;= 7.2**.

{% hint style="info" %}
To install wallabag easily, we create a `Makefile`, so you need to have the `make` tool.
{% endhint %}

wallabag nutzt eine große Anzahl an Bibliotheken, um zu funktionieren.
Diese Bibliotheken müssen mit einem Tool namens Composer installiert
werden. Du musst es installieren sofern du es bisher noch nicht gemacht
hast.

Composer installieren:

```bash
curl -s https://getcomposer.org/installer | php
```

Du kannst eine spezifische Anleitung
[hier](https://getcomposer.org/doc/00-intro.md) finden.

Du benötigst die folgenden Extensions damit wallabag funktioniert.
Einige von diesen sind vielleicht schon in deiner Version von PHP
aktiviert, somit musst du eventuell nicht alle folgenden Pakete
installieren.

-   php-session
-   php-ctype
-   php-dom
-   php-hash
-   php-simplexml
-   php-json
-   php-gd
-   php-mbstring
-   php-xml
-   php-tidy
-   php-iconv
-   php-curl
-   php-gettext
-   php-tokenizer
-   php-bcmath
-   php-intl

wallabag nutzt PDO, um sich mit der Datenbank zu verbinden, darum
benötigst du eines der folgenden Komponenten:

-   pdo_mysql
-   pdo_sqlite
-   pdo_pgsql

und dessen dazugehörigen Datenbankserver.
