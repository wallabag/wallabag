Migrare da...
=============

In wallabag 2.x, potete importare dati da:

- `Pocket <#id1>`_
- `Readability <#id2>`_
- `Instapaper <#id4>`_
- `wallabag 1.x <#id6>`_
- `wallabag 2.x <#id7>`_

Abbiamo anche sviluppato `uno script per eseguire migrazioni tramite la linea di comando <#import-via-command-line-interface-cli>`_.

Poiché le importazioni possono richiedere molto tempo, abbiamo sviluppato un sistema di compiti asincroni. *inserisci qui link una volta tradotto articolo su asynchronous*

Pocket
------

Creare una nuova applicazione su Pocket
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Per importare dati da Pocket usiamo l'API di Pocket. Dovete creare una nuova applicazione sul loro sito per sviluppatori per continuare.

* Create una nuova applicazione `sul sito  per sviluppatori <https://getpocket.com/developer/apps/new>`_
* Riempite i campi richiesti: nome dell'applicazione, descrizione dell'applicazione, permessi (solo **retrieve**), piattaforma (**web**), accettate i termini di servizio ed inviate la vostra nuova applicazione

Pocket vi dará una **Consumer Key** (per esempio, `49961-985e4b92fe21fe4c78d682c1`). Dovete configurare la ``pocket_consumer_key`` dal menu ``Config``.

Ora é tutto pronto per migrare da Pocket.

Importate i vostri dati su wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliccate sul link `Importa` nel menu, su `Importa contenuti` nella sezione Pocket e poi su ``Connetti a Pocket ed importa i dati``

Dovete autorizzare wallabag a interagire con il vostro account Pocket.
I vostri dati saranno importati. L'importazione di dati puó essere un processo esigente per il vostro server.

Instapaper
----------

Esportate i vostri dati di Instapaper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sulla pagina delle impostazioni (`https://www.instapaper.com/user <https://www.instapaper.com/user>`_), cliccate su "Download .CSV file" nella sezione "Export". Verrá scaricato un file CSV (like ``instapaper-export.csv``).

Importate i vostri dati in wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliccate sul link `Importa` sul menu, poi su `Importa contenuti` nella sezione di Instapaper, quindi selezionate il vostro file CSV e caricatelo.

I vostri dati saranno importati. L'importazione di dati puó essere un'operazione esigente per il server.

wallabag 1.x
------------

Se state usando wallabag 1.x, dovete esportare i dati prima di migrare a wallabag 2.x, poiché l'applicazione ed il suo database sono cambiati molto. Potete esportare i vostri dati dalla vostra vecchia installazione di wallabag sulla pagina Config di questa.

.. image:: ../../img/user/export_v1.png
   :alt: Exporting from wallabag v1
   :align: center

.. nota::
    Se avete account multipli nella stessa istanza di wallabag, ogni utente dovrá esportare da v1 ed importare su v2.

.. nota::
    Se riscontrate problemi durante l'importazione o l'esportazione, non esitate a `chiedere supporto <http://gitter.im/wallabag/wallabag>`__.

Quando avrete ottenuto il file json contenente i vostri articoli, potrete installare wallabag v2 seguendo, se necessario, `la procedura standard *link mancante*`.

Dopo aver creato un account utente sulla vostra nuova istanza di wallabag v2, dovete andare alla sezione `Importa` e selezionare `Importa da wallabag v1`. Selezionate il vostro file json e caricatelo.

.. image:: ../../img/user/import_wallabagv1.png
   :alt: Import from wallabag v1
   :align: center

wallabag 2.x
------------

Dalla istanza di wallabag precedente sulla quale eravate prima, andate su `Tutti gli articoli`, poi esportate questi articoli come json.

.. image:: ../../img/user/export_v2.png
   :alt: Export depuis wallabag v2
   :align: center

Dalla vostra nuova istanza di wallabag, create un account utente e cliccate sul link nel menu per procedere all'importazione. Scegliete di importare da wallabag v2 e selezionate il vostro file json per caricarlo.

.. nota::
    Se riscontrate problemi durante l'importazione o l'esportazione, non esitate a `chiedere supporto <http://gitter.im/wallabag/wallabag>`__.

Importate dall'interfaccia a riga di comando (CLI)
--------------------------------------------------

Se avete un accesso CLI al vostro server web, potete eseguire questo comando per importare ció che avete esportato da wallabag v1:

::

    bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Rimpiazzate i valori:

* ``1`` é l'identificatore utente nel database (l'ID del primo utente creato su wallabag é 1)
* ``~/Downloads/wallabag-export-1-2016-04-05.json`` é il percorso del file esportato da wallabag v1

Se volete segnare tutti questi articoli come giá letti, potete aggiungere l'opzione ``--markAsRead``.
Per importare un file di wallabag v2, dovete aggiungere l'opzione ``--importer=v2``.

Come risultato avrete questo messaggio:

::

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
