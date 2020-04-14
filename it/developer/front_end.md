# Consigli per sviluppatori front-end

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

Iniziando dalla versione 2.3, wallabag usa webpack per raggruppare le sue risorse.

## Modalità sviluppatore

Se il server funziona in modalità sviluppatore, dovete eseguire `yarn run build:dev` per generare i file javascript in uscita per ogni tema. Questi sono chiamati
`%theme%.dev.js` e sono ignorati da git. Dovete riavviare
`yarn run build:dev` per ogni cambiamento fatto ad uno del file delle risorse
(js, css, pictures, fonts,...).

## Ricaricamento in tempo reale

Webpack aggiunge il supporto per il ricaricamento in tempo reale, ciò significa che non dovete rigenerare il file delle risorse per ogni cambiamento e nemmeno ricaricare la pagina manualmente. I cambiamenti sono applicati automaticamente nella pagina web. Impostate semplicemente
l'impostazione `use_webpack_dev_server` come `true` in
`app/config/config.yml` ed eseguite `yarn run watch` ed il gioco è fatto.

Non dimenticate di cambiare `use_webpack_dev_server` in `false` quando la funzione di ricaricamento in tempo reale non è utilizzata.


## Build di produzione

Quando vorrete fare un commit con i vostri cambiamenti, costruiteli in un ambiente di produzione usando `yarn run build:prod`. Questo costruirà tutte le risorse necessarie per wallabag. Per assicurarsi che ciò funzioni a dovere, dovrete avere il vostro server in modalità produzione, per esempio con
`bin/console server:run -e=prod`.

Non dimenticate di generare le build di produzione prima di fare un commit!

# Stile del codice

Lo stile del codice è controllato da due strumenti: stylelint per (S)CSS e eslint per
JS. La configurazione di eslint è basata sul preset base di Airbnb.
