# Migrare da...

In wallabag 2.x, potete importare dati da:

-   [Pocket](Pocket.md)
-   [Instapaper](Instapaper.md)
-   [Readability](Readability.md)
-   [Pinboard](Pinboard.md)
-   [wallabag 1.x](wallabagv1.md)
-   [wallabag 2.x](wallabagv2.md)

Abbiamo anche sviluppato [uno script per eseguire migrazioni tramite la
linea di comando](#import-via-command-line-interface-cli).

Poiché le importazioni possono richiedere molto tempo, abbiamo
sviluppato un sistema di compiti asincroni. [Qui potete leggere la documentazione](../../admin/asynchronous.md)
(per esperti).

# Importare dall'interfaccia a riga di comando (CLI)

Se avete un accesso CLI al vostro server web, potete eseguire questo
comando per importare ció che avete esportato da wallabag v1:

    bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Si prega di rimpiazzare i valori:

-   `username` è il nome utente
-   `~/Downloads/wallabag-export-1-2016-04-05.json` è il percorso del
    file esportato da wallabag v1

Se volete segnare tutti questi articoli come già letti, potete
aggiungere l'opzione `--markAsRead`. 

Per importare un file di wallabag
v2, dovete aggiungere l'opzione `--importer=v2`.

Come risultato avrete questo messaggio:

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
