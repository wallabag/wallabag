Articoli dietro ad un paywall
=============================

wallabag puó acquisire articoli da siti web che usano un sistema paywall

Abilitate l'autenticazione paywall
----------------------------------

Su impostazioni interne, nella sezione **Articolo**, abilitate l'autenticazione per siti con paywall (con il valore 1).

Configurate le credenziali in wallabag
--------------------------------------

Modificate il vostro file ``app/config/parameters.yml`` per modificare le credenziali per ogni sito con paywall. Ecco un esempio di alcuni siti francesi:

.. code:: yaml

    sites_credentials:
        mediapart.fr: {username: "myMediapartLogin", password: "mypassword"}
        arretsurimages.net: {username: "myASILogin", password: "mypassword"}

.. note::

    These credentials will be shared between each user of your wallabag instance.

Fate il parsing dei file di configurazione
------------------------------------------

Leggete `questa parte della documentazione *link mancante*` per capire i file di configurazione.

Ogni file di configurazione del parsing deve essere migliorato aggiungendo ``requires_login``, ``login_uri``, ``login_username_field``, ``login_password_field`` e ``not_logged_in_xpath``.

Fate attenzione, il modulo di login deve essere nel contenuto della pagina quando wallabag lo carica. É impossibile per wallab essere autenticato su un sito dove il modulo di login é caricato dopo la pagina (da ajax per esempio).

``login_uri`` é l'URL di azione del modulo (l'attributo ``action`` del modulo).
``login_username_field`` é l'attributo ``name`` nel campo di login.
``login_password_field`` é l'attributo ``name`` nel campo password.

Per esempio:

.. code::

    title://div[@id="titrage-contenu"]/h1[@class="title"]
    body: //div[@class="contenu-html"]/div[@class="page-pane"]

    requires_login: yes

    login_uri: http://www.arretsurimages.net/forum/login.php
    login_username_field: username
    login_password_field: password

    not_logged_in_xpath: //body[@class="not-logged-in"]
