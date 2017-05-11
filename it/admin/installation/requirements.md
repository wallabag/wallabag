Requisiti
---------

wallabag é compatibile con PHP &gt;= 5.5, incluso PHP 7.

wallabag utilizza un gran numero di librerie PHP per funzionare. Queste
librerie vanno installate tramite uno strumento chiamato Composer.
Dovete installarlo se non lo avete giá fatto e assicuratevi di usare la
versione 1.2 ( se giá avete Composer, esegui il comando composer
selfupdate).

Installa Composer:

```bash
curl -s <http://getcomposer.org/installer> | php
```

[Qui](https://getcomposer.org/doc/00-intro.md) puoi trovare istruzioni
specifiche.

Per far funzionare wallabag avrete anche bisogno delle seguenti
estensioni. Alcune di queste dovrebbero essere giá attive nella vostra
versione di PHP, per cui potrebbe non essere necessario installare tutti
i pacchetti corrispondenti.

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

wallabag usa PDO per connettersi, per cui avrete bisogno di uno dei
seguenti:

* pdo_mysql
* pdo_sqlite
* pdo_pgsql

E il corrispondente database server.
