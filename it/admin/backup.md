Eseguire il backup di wallabag
==============================

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

Siccome a volte potreste commettere errori con il vostro wallabag e
perdere i vostri dati, oppure in caso dobbiate spostare il vostro
wallabag su un altro server, dovete fare un backup dei vostri dati.

Impostazioni base
-----------------

wallabag memorizza alcuni parametri base (come il server SMTP o il
backend del database) nel file app/config/parameters.yml.

Database
--------

Per il fatto che wallabag supporta vari tipi di database, il modo di
eseguire backup dipende dal database che state usando, quindi dovrete
fare riferimento alla documentazione del fornitore.

Ecco alcuni esempi:

-   MySQL: <http://dev.mysql.com/doc/refman/5.7/en/backup-methods.html>
-   PostgreSQL:
    <https://www.postgresql.org/docs/current/static/backup.html>

### SQLite

Per eseguire il backup di un database SQLite, dovete semplicemente
copiare la directory data/db dalla directory dell'applicazione wallabag.

Immagini
--------

Le immagini recuperate da wallabag sono memorizzate in web/assets/images
(la memoria delle immagini sará implementata in wallabag 2.2).
