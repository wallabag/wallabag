# Errori nell'ottenere gli articoli

## Perché l'ottenimento di un articolo fallisce?

Ci possono essere varie ragioni:

-   problema del network
-   wallabag non può ottenere il contenuto a causa della struttura del
    sito web

Potete provare a risolvere il problema da soli ( in modo che noi
possiamo concentrarci nel migliorare wallabag internamente invece di
scrivere siteconfig :) ).

Potete provare a vedere se funziona qui:
[<http://f43.me/feed/test>](http://f43.me/feed/test) (usa quasi lo
stesso sistema per ottenere contenuto).

Se funziona lì e non su wallabag, significa che c'è qualcosa all'interno
di wallabag che causa il malfunzionamento del parser (difficile da
aggiustare: si prega di riportare il problema).

Se non funziona, provate a estrarre un siteconfig usando:
[<http://siteconfig.fivefilters.org/>](http://siteconfig.fivefilters.org/)
(selezionate quale parte del contenuto é effettivamente contenuto). Potete
[leggere prima questa documentazione](http://help.fivefilters.org/customer/en/portal/articles/223153-site-patterns).

Potete testarlo sul sito **f43.m3**: cliccate su **Want to try a custom
siteconfig?** e inserite il file generato da siteconfig.fivefilters.org.

Ripetete finché non avrete qualcosa di buono.

Potete poi inviare una pull request a
[<https://github.com/fivefilters/ftr-site-config>](https://github.com/fivefilters/ftr-site-config)
il quale è il repository ufficiale per i file siteconfig.

## Come posso provare a riottenere questo articolo?

Se wallabag ha fallito a ottenere l'articolo, potete cliccare sul
bottone di ricaricamento (il terzo bottone nella figura sottostante).

![Riottienere contenuto](../../img/user/refetch.png)

## Abilitare il registro per aiutarci a identificare il problema

Se non riuscite proprio ad ottenere il contenuto dopo aver provato i due passi precedenti, potete abilitare il registro, il che ci aiuterà a capire perché l'ottenimento fallisce.

- modificate `app/config/config_prod.yml`
- rimpiazzate [nella riga 18](https://github.com/wallabag/wallabag/blob/master/app/config/config_prod.yml#L18) `error` con `debug`
- `rm -rf var/cache/*`
- svuotate il file `var/logs/prod.log`
- ricaricate il vostro wallabag e riottenete il contenuto
- incollate il file `var/logs/prod.log` in un nuovo "issue" su GitHub
