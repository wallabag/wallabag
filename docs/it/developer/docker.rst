Eseguite wallabag in docker-compose
===================================

Per eseguire la vostra propria istanza di sviluppo di wallabag, dovreste usare i file docker compose preconfigurati.

Requisiti
---------

Assicuratevi di avere `Docker
<https://docs.docker.com/installation/ubuntulinux/>` e  `Docker
Compose <https://docs.docker.com/compose/install/>`__  disponibili sul vostro sistema e aggiornati.

Cambiate DBMS
-------------

Per default, wallabag inizierá con un database SQLite.
Visto che wallabag supporta Postgresql e MySQL, i container di docker sono disponibili anche per questi.

In ``docker-compose.yml``, per il DBMS scelto, togliete i commenti:

- la definizione del container (blocco a livello root ``postgres`` o ``mariadb``)
- il link del container nel container``php``
- il file env del container nel container ``php``

Per far continuare ad eseguire i comandi Symfony sul vostro host (come ``wallabag:install``), dovreste anche:

- caricare i file env appropriati sulla vostra riga di comando, in modo che possano esistere variabili come ``WALLABAG_DATABASE_HOST``.
- creare un ``127.0.0.1 rdbms`` sul vostro file di sistema ``hosts``

Eseguite wallabag
-----------------

#. Fate un fork o clonate il progetto
#. ``composer install`` per installare le dipendenze del progetto
#. ``php bin/console wallabag:install`` per creare lo schema
#. ``docker-compose up`` per eseguire i containers
#. Infine, andate su http://localhost:8080/ per trovare il vostro wallabag appena installato.

Durante i vari passi potreste incontrare problemi di permessi UNIX, percorsi sbagliati nella cache generata, etc...
Operazioni come cambiare i file della cache o cambiare i proprietari dei file potrebbero essere richiesto frequentemente, per cui non abbiate paura!
