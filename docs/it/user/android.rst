Applicazione Android
====================

Scopo di questo documento
-------------------------

Questo documento spiega come configurare la vostra applicazione Android in modo che funzioni con la vostra istanza di wallabag. Non vi é differenza in questa procedura sia per wallabag v1 che per v2.

Passi per configurare la vostra app
-----------------------------------

Quando aprirete l'app per la prima volta, vedrete una schermata di benvenuto dove vi si consiglia per prima cosa di configurare l'app per la vostra istanza di wallabag.

.. image:: ../../img/user/android_welcome_screen.en.png
    :alt: Welcome screen
    :align: center

Confermate semplicemente quel messaggio e verrete reindirizzati alla schermata delle impostazioni.

.. image:: ../../img/user/android_configuration_screen.en.png
    :alt: Settings screen
    :align: center

Inserite i vostri dati di wallabag. Dovrete inserire il vostro indirizzo di wallabag. **É importante che questo URL non finisca con una barra**. Inserite anche le vostre credenziali nei campi user name e password.

.. image:: ../../img/user/android_configuration_filled_in.en.png
    :alt: Filled in settings
    :align: center

Dopo aver completato l'inserimento dei dati, premete il bottone Connection test e aspettate che il test finisca.

.. image:: ../../img/user/android_configuration_connection_test.en.png
    :alt: Connection test with your wallabag data
    :align: center

Il test di connessione dovrebbe finire con successo. In caso contrario, dovrete prima risolvere questo problema fino a che possiate procedere.

.. image:: ../../img/user/android_configuration_connection_test_success.en.png
    :alt: Connection test successful
    :align: center

Dopo che il test sará avvenuto con successo, potrete premere il bottone per ottenere le credenziali del vostro feed. L'app cercherá di connettersi alla vostra istanza di wallabag e ottenere l'id utente e il token corrispondente per i feed.

.. image:: ../../img/user/android_configuration_get_feed_credentials.en.png
    :alt: Getting the feed credentials
    :align: center

Quando il processo di ottenimento delle credenziali del vostro feed sará concluso con successo, vedrete un messaggio toast, il quale avviserá che l'id utente ed il token sono stati inseriti nel modulo.

.. image:: ../../img/user/android_configuration_feed_credentials_automatically_filled_in.en.png
    :alt: Getting feed credentials successful
    :align: center

Ora dovrete scorrere fino alla fine del menu delle impostazioni. Ovviamente potrete cambiare le impostazioni in base alle vostre preferenze.
Terminate la configurazione della vostra app premendo il bottone per il salvataggio.

.. image:: ../../img/user/android_configuration_scroll_bottom.en.png
    :alt: Bottom of the settings screen
    :align: center

Dopo aver premuto il bottone apparirá la seguente schermata. L'app proporrá di iniziare il processo di sincronizzazione per aggiornare i vostri feed ed articoli. É raccomandato accettare quest'azione e premere Sí.

.. image:: ../../img/user/android_configuration_saved_feed_update.en.png
    :alt: Settings saved the first time
    :align: center

Alla fine, dopo che la sincronizzazione sará avvenuta con successo, apparirá la lista degli articoli non letti.

.. image:: ../../img/user/android_unread_feed_synced.en.png
    :alt: Filled article list cause feeds successfully synchronized
    :align: center

Limiti conosciuti
-----------------

Autenticazione a due fattori (2FA)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Attualmente l'applicazione Android non supporta l'autenticazione a due fattori. Dovreste disabilitare questa opzione in modo da far funzionare l'applicazione.

Quantitá limitata di articoli con wallabag v2
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Nella vostra istanza web di wallabag potete configurare quanti oggetti sono parte del feed RSS. Questa opzione non esisteva in wallabag v1, dove tutti gli articoli erano parte del feed. Quindi se imposterete il numero di articoli da visualizzare maggiore del numero di oggetti che sono contenuti nel vostro feed RSS, vedrete solamente il numero di oggetti nel vostro feed RSS.

Crittografia SSL/TLS
~~~~~~~~~~~~~~~~~~~~

Se potete raggiungere la vostra istanza web di wallabag via HTTPS, dovreste usare quest'ultimo, in particolar modo se il vostro URL HTTP vi reindirizza a quello HTTPS. Attualmente l'app non puó gestire propriamente il reindirizzamento.

Riferimenti
-----------

- `Codice sorgente dell'applicazione Android <https://github.com/wallabag/android-app>`_
- `Applicazione Android su F-Droid <https://f-droid.org/repository/browse/?fdfilter=wallabag&fdid=fr.gaulupeau.apps.InThePoche>`_
- `Applicazione Android su Google Play <https://play.google.com/store/apps/details?id=fr.gaulupeau.apps.InThePoche>`_




 






