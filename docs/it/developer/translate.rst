Tradurre wallabag
=================

wallabag web app
----------------

File per la traduzione
~~~~~~~~~~~~~~~~~~~~~~

..  note::
  
     Visto che wallabag é principalmente sviluppato da un team francese, si prega di considerare che la traduzione francese é la più aggiornata, e si prega di copiarla e di creare la vostra propria traduzione.
     
Potete trovare qui i file per la traduzione:
https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations.

Dovrete creare ``messages.CODE.yml`` e ``validators.CODE.yml``, dove CODE é il codice ISO 639-1 della vostra lingua (`guardate wikipedia <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>`__).

Altri file da tradurre:

- https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations.
- https://github.com/wallabag/wallabag/tree/master/src/Wallabag/UserBundle/Resources/translations.

Dovete creare i file ``THE_TRANSLATION_FILE.CODE.yml``.

File di configurazione
~~~~~~~~~~~~~~~~~~~~~~

Dovete modificare `app/config/config.yml
<https://github.com/wallabag/wallabag/blob/master/app/config/config.yml>`__ per mostrare il vostro linguaggio nella pagina di configurazione di wallabag (per consentire agli utenti di passare a questa nuova traduzione).

Nella sezione ``wallabag_core.languages``, dovete aggiungere una nuova linea con la vostra traduzione. Per esempio:

::

    wallabag_core:
        ...
        languages:
            en: 'English'
            fr: 'Français'

Nella prima colonna (``en``, ``fr``, etc.), dovete aggiungere il codice ISO 639-1 della vostra lingua (vedete sopra).

Nella seconda colonna, aggiungete solamente il nome della vostra lingua.

documentazione di wallabag
--------------------------

.. note::
    Contrariamente alla web app, il linguaggio principale per la documentazione é l'inglese.
    
I file della documentazione sono memorizzati qui: https://github.com/wallabag/wallabag/tree/master/docs

Dovete rispettare la struttura della cartella ``en`` quando create la vostra traduzione.

