Configurazione
==============

Ora che siete acceduti, é ora di configurare il vostro account come volete.

cliccate sul menu ``Configurazione``. Avrete cinque tab: ``Impostazioni``, ``RSS``, ``Informazioni utente``, ``Password`` e ``Regole di tagging``.

Impostazioni
------------

Tema
~~~~

wallabag é personalizzabile. Potete scegliere il vostro tema preferito qui. Il tema di default é ``Material``, é il tema usato nelle immagini della documentazione.

Oggetti per pagina
~~~~~~~~~~~~~~~~~~

Potete cambiare il numero di articoli mostrati su ogni pagina.

Velocitá di lettura
~~~~~~~~~~~~~~~~~~~

wallabag calcola un tempo di lettura per ogni articolo. Potete definire qui, grazie a questa lista, se siete dei lettori lenti of veloci. wallabag ricalcolerá il tempo di lettura per ogni articolo.

Lingua
~~~~~~

Potete cambiare la lingua dell'interfaccia di wallabag.

RSS
---

wallabag offre feed RSS per ogni stato dell'articolo: non letto, preferito e archiviato.

Per prima cosa dovete creare un token personale: cliccate su ``Crea il tuo token``. É possibile cambiare il proprio token cliccando su ``Rigenera il tuo token``.

Ora avrete tre link, uno per ogni stato: aggiungeteli al vostro lettore RSS preferito.

Potete anche definire quanti articoli volete nel vostro feed RSS (valore di default: 50)-

Informazioni dell'utente
------------------------

Potete cambiare il vostro nome, il vostro indirizzo email e abilitare l'``Autenticazione a due fattori``.

Autenticazione a due fattori (2FA)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    L'autenticazione a due fattori (conosciuta anche come 2FA) é una tecnologia brevettata nel 1984 che offre l'identificazione degli    utenti tramite una combinazione di due componenti differenti.

    https://it.wikipedia.org/wiki/Autenticazione_a_due_fattori

**Attenzione**: abilitare la 2FA dall'interfaccia di configurazione è possibile solamente se ciò è stato abilitato precedentemente in `app/config/parameters.yml` impostando la proprietà *twofactor_auth* su `true` (non dimenticate di svuotare `/var/cache` dopo la modifica).

Se abilitate la 2FA, ogni volta che vogliate accedere a wallabag, riceverete un codice via email. Dovrete inserire il codice nel seguente modulo.

.. image:: ../../img/user/2FA_form.png
    :alt: Two factor authentication
    :align: center

Se non volete ricevere il codice ogni volta che vogliate accedere, potete spuntare la casella ``I'm on a trusted computer``: wallabag vi ricorderá per 15 giorni.

Password
--------

Qui potete cambiare la password (minimo 8 caratteri)

Regole di tagging
-----------------

Se volete assegnare un tag ai nuovi articoli, questa parte della configurazione fa per voi.

Cosa significa « regole di tagging » ?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sono regole usate da wallabag per etichettare i nuovi articoli. Ogni volta che un nuovo articolo viene aggiunto, verranno usate tutte le regole di tagging per aggiungere le etichette che avete configurato, risparmiandovi quindi il lavoro di classificare manualmente i vostri articoli.

Come le uso?
~~~~~~~~~~~~

Immaginiamo che vogliate taggare un contenuto come *« lettura corta »* quando il tempo di lettura è inferiore ai 3 minuti. In questo caso, dovreste mettere « readingTime <= 3 » nel campo **Regola**
e *« lettura corta »* nel campo **Tag**. Molte etichette possono essere aggiunte simultaneamente separandole con una virgola: *« lettura corta, da leggere »*. 
Si possono scrivere regole complesse usando gli operatori predefiniti:
se *« readingTime >= 5 AND domainName = "github.com" »* allora etichetta come *« lettura lunga, github »*.

Quali variabili ed operatori posso usare per scrivere le regole?

I seguenti operatori e variabili possono essere usati per creare regole di tagging (attenzione, per alcuni valori, dovete aggiungere le virgolette, per esempio ``language = "en"``):

===========  ==============================================  ========= ===========
Variabile    Significato                                     Operatore Significato
-----------  ----------------------------------------------  --------- -----------
title        Titolo dell'articolo                            <=        Minore di…
url          URL dell'articolo                               <         Strettamente minore di…
isArchived   Se l'articolo é archiviato o no                 =>        Maggiore di…
isStarred    Se l'articolo é preferito o no                  >         Strettamente maggiore di…
content      Il contenuto dell'articolo                      =         Uguale a…
language     La lingua dell'aritcolo                         !=        Diverso da…
mimetype     The entry's mime-type                           OR        Una regola o l'altra
readingTime  Il tempo di lettura dell'articolo stimato       AND       Una regola e l'altra
domainName   Il nome del dominio dell'articolo               matches   Vede se un soggetto corrisponde alla ricerca (indipendentemente dal maiuscolo o minuscolo). Esempio: titolo corrisponde a "football"
===========  ==============================================  ========  ==========



