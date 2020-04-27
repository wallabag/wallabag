# Eseguire wallabag in docker-compose

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

Per eseguire la vostra istanza di sviluppo di wallabag, dovreste
usare i file docker compose preconfigurati.

Requisiti
---------

Assicuratevi di avere [Docker](https://docs.docker.com/installation/ubuntulinux/) e [Docker
Compose](https://docs.docker.com/compose/install/) disponibili sul
vostro sistema e aggiornati.

Cambiare DBMS
-------------

Per default, wallabag inizierá con un database SQLite. Visto che
wallabag supporta Postgresql e MySQL, i container di docker sono
disponibili anche per questi.

In `docker-compose.yml`, per il DBMS scelto, togliete i commenti:

-   la definizione del container (blocco a livello root `postgres` o
    `mariadb`)
-   il link del container nel container`php`
-   il file env del container nel container `php`

Per far continuare ad eseguire i comandi Symfony sul vostro host (come
`wallabag:install`), dovreste anche:

-   caricare i file env appropriati sulla vostra riga di comando, in
    modo che possano esistere variabili come
    `SYMFONY__ENV__DATABASE_HOST`.
-   creare un `127.0.0.1 rdbms` sul vostro file di sistema `hosts`

Eseguire wallabag
-----------------

1.  Fate un fork o clonate il progetto
2.  Modificate `app/config/parameters.yml` per rimpiazzare le proprietá di `database_*` con quelle presenti nei commenti (con valori con prefisso `env.`)
3.  `composer install` per installare le dipendenze del progetto
4.  `php bin/console wallabag:install` per creare lo schema
5.  `docker-compose up` per eseguire i containers
6.  Infine, andate su <http://localhost:8080/> per trovare il vostro wallabag appena installato.

Durante i vari passi potreste incontrare problemi di permessi UNIX, percorsi sbagliati nella cache generata, etc... Operazioni come cambiare i file della cache o cambiare i proprietari dei file potrebbero essere richieste frequentemente, per cui non abbiate paura!
