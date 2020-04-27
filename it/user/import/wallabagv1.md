# wallabag 1.x

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

Se state usando wallabag 1.x, dovete esportare i dati prima di migrare a
wallabag 2.x, poiché l'applicazione ed il suo database sono cambiati
molto. Potete esportare i vostri dati dalla vostra vecchia installazione
di wallabag sulla pagina Configurazione di questa.

![Esportando da wallabag v1](../../../img/user/export_v1.png)

Se avete più account sulla stessa istanza di wallabag, ogni utente deve esportare i propri dati da v1 ed importarli in v2.

Se riscontrate dei problemi durante l'esportazione o l'importazione, non esitate a [chiedere aiuto](https://gitter.im/wallabag/wallabag).

Quando avrete ottenuto il file json contenente i vostri articoli,
potrete installare wallabag v2 seguendo, se necessario, [la procedura standard](../../admin/installation/).
Dopo aver creato un account utente sulla vostra nuova istanza di wallabag v2, dovete andare alla sezione Importa e selezionare
Importa da wallabag v1. Selezionate il vostro file JSON e caricatelo.

![Importando da wallabag v1](../../img/user/import_wallabagv1.png)
