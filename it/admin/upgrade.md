# Aggiornate la vostra installazione di wallabag

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

Troverete qui i differenti modi per aggiornare il vostro wallabag:

-   [da 2.0.x a 2.1.1](#aggiornamento-da-2-1-x-a-2-2-x)
-   [da 2.0.x a 2.1.1](#aggiornamento-da-2-0-x-a-2-1-1)
-   [da 1.x a 2.x](#da-wallabag-1-x)

## Aggiornamento da 2.1.x a 2.2.x

### Aggiornamento su un server web dedicato

**Da 2.1.x:**

```bash
make update
php bin/console doctrine:migrations:migrate --no-interaction -e=prod
```

**Da 2.2.0:**

```bash
make update
```

#### Spiegazioni a proposito delle migrazioni del database

Durante l'aggiornamento eseguiamo le migrazioni del database.

Tutte le migrazioni del database sono memorizzate in `app/DoctrineMigrations`. Potete eseguire ogni migrazione individualmente:
`bin/console doctrine:migrations:execute 20161001072726 --env=prod`.

Potete anche annullare ogni migrazione individualmente:
`bin/console doctrine:migrations:execute 20161001072726 --down --env=prod`.

Eccon la lista delle migrazioni per le versioni da 2.1.x a 2.2.0:

-   `20161001072726`: aggiunte foreign keys per il reset degli account
-   `20161022134138`: database convertito alla codifica `utf8mb4` (solo per MySQL)
-   `20161024212538`: aggiunta la colonna `user_id` su `oauth2_clients` per evitare che gli utenti eliminino i client API per altri utenti
-   `20161031132655`: aggiunta l'impostazione interna per abilitare/disabilitare il download delle immagini
-   `20161104073720`: aggiunto l'indice `created_at` sulla tabella `entry`
-   `20161106113822`: aggiunto il campo `action_mark_as_read` nella tabella `config`
-   `20161117071626`: aggiunta l'impostazione interna per condividere articoli con unmark.it
-   `20161118134328`: aggiunto il campo `http_status` nella tabella `entry`
-   `20161122144743`: aggiunta l'impostazione interna per abilitare/disabilitare l'ottenimento di articoli con paywall
-   `20161122203647`: eliminati i campi `expired` e `credentials_expired` nella tabella `user`
-   `20161128084725`: aggiunto il campo `list_mode` nella tabella `config`
-   `20161128131503`: eliminati i campi `locked`, `credentials_expire_at` e
    `expires_at` nella tabella `user`
-   `20161214094402`: rinominato `uuid` come `uid` nella tabella `entry`
-   `20161214094403`: aggiunto indice `uid` sulla tabella `entry`
-   `20170127093841`: aggiunti indici `is_starred` e `is_archived` nella tabella
    `entry` table

### Aggiornamento su un hosting condiviso

Fate un backup del vostro file `app/config/parameters.yml`.

Scaricate l'ultima versione di wallabag:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

Troverete il [hash md5 dell' ultimo pacchetto sul nostro sito](https://static.wallabag.org/releases/).

Estraete l'archivio nella vostra cartella di wallabag e rimpiazzate il file `app/config/parameters.yml` con il vostro.

Si prega di controllare che il vostro file `app/config/parameters.yml` contenga tutti i parametri richiesti. Qui potete trovare [la documentazione a proposito dei parametri](./parameters.md).

Se usate SQLite, dovete anche copiare la vostra cartella `data/` dentro la nuova installazione.

Svuotate la cartella `var/cache`.

Dovete eseguire delle query SQL per aggiornare il vostro database. Presumiamo che il prefisso della tabella sia `wallabag_`. Non dimenticate di fare un backup del vostro database prima della migrazione.

Potreste incontrare problemi con i nomi degli indici: se ciò dovesse accadere, cambiate le query con il nome dell'indice corretto.

[Qui potete trovare tutte le query](query-upgrade-21-22.md).

## Aggiornamento da 2.0.x a 2.1.1

Prima di questa migrazione, se avete configurato l'importazione da Pocket aggiungendo la vostra consumer key nelle Impostazioni intere , si prega farne un backup: dovrete aggiungerla nella pagina Config dopo l'aggiornamento.

### Aggiornamento su un web server dedicato

```bash
rm -rf var/cache/*
git fetch origin
git fetch --tags
git checkout 2.1.1 --force
SYMFONY\_ENV=prod composer install --no-dev -o --prefer-dist
php bin/console doctrine:migrations:migrate --env=prod
php bin/console cache:clear --env=prod
```

### Aggiornamento su un hosting condiviso

Fate un backup del file `app/config/parameters.yml`. 

Scaricate la versione 2.1.1 di wallabag:

```bash
wget http://framabag.org/wallabag-release-2.1.1.tar.gz && tar xvf wallabag-release-2.1.1.tar.gz
```

(hash md5 del pacchetto 2.1.1: `9584a3b60a2b2a4de87f536548caac93`)

Estraete l'archivio nella vostra cartella di wallabag e sostituite
`app/config/parameters.yml` con il vostro.

Si prega di controllare che il vostro `app/config/parameters.yml` contenga tutti i
parametri richiesti. Potete trovare qui la [documentazione sui parametri]
(./parameters.md).

Se usate SQLite, dovete anche copiare la vostra cartella `data/` dentro
la nuova installazione.

Svuotate la cartella `var/cache`.

Dovete eseguire delle query di SQL per aggiornare il vostro database.
Presumiamo che il prefisso della tabella sia `wallabag_` e che il
database sia MySQL:

```sql
ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL;
INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('share_public', '1', 'entry');
ALTER TABLE `wallabag_oauth2_clients` ADD name longtext COLLATE 'utf8_unicode_ci' DEFAULT NULL;
INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_redis', '0', 'import');
INSERT INTO `wallabag_craue_config_setting` (`name`, `value`, `section`) VALUES ('import_with_rabbitmq', '0', 'import');
ALTER TABLE `wallabag_config` ADD `pocket_consumer_key` VARCHAR(255) DEFAULT NULL;
DELETE FROM `wallabag_craue_config_setting` WHERE `name` = 'pocket_consumer_key';
```

Da wallabag 1.x
---------------

Non esiste uno script automatico per aggiornare da wallabag 1.x a
wallabag 2.x. Dovete:

-   esportare i vostri dati
-   installare wallabag 2.x ([leggete la documentazione a proposito dell'installazione](./installation/))
-   importate i dati in questa nuova installazione ([leggete la documentazione a proposito dell'importazione](../user/import/))
