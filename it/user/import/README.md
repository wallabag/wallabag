Migrare da...
=============

In wallabag 2.x, potete importare dati da:

-   [Pocket](Pocket.md)
-   [Instapaper](Instapaper.md)
-   [wallabag 1.x](wallabagv1.md)
-   [wallabag 2.x](wallabagv2.md)
-   Readability
-   Pinboard

Abbiamo anche sviluppato [uno script per eseguire migrazioni tramite la
linea di comando](#import-via-command-line-interface-cli).

Poiché le importazioni possono richiedere molto tempo, abbiamo
sviluppato un sistema di compiti asincroni. *inserisci qui link una
volta tradotto articolo su asynchronous*

Importate dall'interfaccia a riga di comando (CLI)
--------------------------------------------------

Se avete un accesso CLI al vostro server web, potete eseguire questo
comando per importare ció che avete esportato da wallabag v1:

    bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Rimpiazzate i valori:

-   `1` é l'identificatore utente nel database (l'ID del primo utente
    creato su wallabag é 1)
-   `~/Downloads/wallabag-export-1-2016-04-05.json` é il percorso del
    file esportato da wallabag v1

Se volete segnare tutti questi articoli come giá letti, potete
aggiungere l'opzione `--markAsRead`. Per importare un file di wallabag
v2, dovete aggiungere l'opzione `--importer=v2`.

Come risultato avrete questo messaggio:

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
