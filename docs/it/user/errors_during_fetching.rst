
Errori durante l'ottenimento degli articoli
===========================================

Perché l'ottenimento di un articolo fallisce?
---------------------------------------------

Ci possono essere varie ragioni:

- problema del network
- wallabag non può ottenere il contenuto a causa della struttura del sito web

Potete provare a risolvere il problema da soli ( in modo che noi possiamo concentrarci nel migliorare wallabag internamente invece di scrivere siteconfig
:) ).

Potete provare a vedere se funziona qui: `http://f43.me/feed/test <http://f43.me/feed/test>`_ (usa quasi lo stesso sistema per ottenere contenuto).

Se funziona lì e non su wallabag, significa che c'è qualcosa all'interno di wallabag che causa il malfunzionamento del parser (difficile da aggiustare: si prega di riportare il problema).

Se non funziona, provate a estrarre un site config usando: `http://siteconfig.fivefilters.org/ <http://siteconfig.fivefilters.org/>`_ (seleziona quale parte del contenuto é effettivamente contenuto). Potete `leggere prima questa documentazione <http://help.fivefilters.org/customer/en/portal/articles/223153-site-patterns>`_.

Potete testarlo sul sito **f43.m3**: cliccate su **Want to try a custom siteconfig?** e inseritvi il file generato in and put the generated file from siteconfig.fivefilters.org.

Ripetete finché non avrete qualcosa di buono.

Potete poi inviare una pull request a `https://github.com/fivefilters/ftr-site-config <https://github.com/fivefilters/ftr-site-config>`_ il quale é il repository ufficiale per i file siteconfig.

Come posso provare a riottenere questo articolo?
------------------------------------------------

Se wallabag ha fallito a ottenere l'articolo, potete cliccare sul bottone di ricaricamento (il terzo bottone nella figura sottostante).

.. image:: ../../img/user/refetch.png
   :alt: Refetch content
   :align: center


