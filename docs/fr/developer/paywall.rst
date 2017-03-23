Articles derrière un paywall
============================

wallabag peut récupérer le contenu des articles des sites qui utilisent un système de paiement.

Activer l'authentification pour les paywall
-------------------------------------------

Dans les paramètres internes, en tant qu'administrateur de wallabag, section **Article**, activez l'authentification pour les articles derrière un paywall (avec la valeur 1).

Configurer les accès dans wallabag
----------------------------------

Éditez le fichier ``app/config/parameters.yml`` pour modifier les accès aux sites avec paywall. Par exemple, sous Ubuntu :

``sudo -u www-data nano /var/www/html/wallabag/app/config/parameters.yml``

Voici un exemple pour certains sites (attention, ne pas utiliser la touche "tab", seulement des espaces) :

.. code:: yaml

    sites_credentials:
        mediapart.fr: {username: "myMediapartLogin", password: "mypassword"}
        arretsurimages.net: {username: "myASILogin", password: "mypassword"}

.. note::

    Ces accès seront partagés entre chaque utilisateur de votre instance wallabag.

Fichiers de configuration pour parser les articles
--------------------------------------------------

.. note::

    Lisez `cette documentation <http://doc.wallabag.org/fr/master/user/errors_during_fetching.html>`_ pour en savoir plus sur ces fichiers de configuration, qui se trouvent dans le répertoire ``vendor/j0k3r/graby-site-config/``. Pour la majorité des sites, ce fichier est déjà configuré : les instructions qui suivent concernent seulement les sites non encore configurés.

Chaque fichier de configuration doit être enrichi en ajoutant ``requires_login``, ``login_uri``,
``login_username_field``, ``login_password_field`` et ``not_logged_in_xpath``.

Attention, le formulaire de connexion doit se trouver dans le contenu de la page lors du chargement de celle-ci.
Il sera impossible pour wallabag de se connecter à un site dont le formulaire de connexion est chargé après coup (en ajax par exemple).

``login_uri`` correspond à l'URL à laquelle le formulaire est soumis (attribut ``action`` du formulaire).
``login_username_field`` correspond à l'attribut ``name`` du champ de l'identifiant.
``login_password_field`` correspond à l'attribut ``name`` du champ du mot de passe.

Par exemple :

.. code::

    title://div[@id="titrage-contenu"]/h1[@class="title"]
    body: //div[@class="contenu-html"]/div[@class="page-pane"]

    requires_login: yes

    login_uri: http://www.arretsurimages.net/forum/login.php
    login_username_field: username
    login_password_field: password

    not_logged_in_xpath: //body[@class="not-logged-in"]

Dernière étape : nettoyer le cache
----------------------------------

Il est nécessaire de nettoyer le cache de wallabag avec la commande suivante (ici sous Ubuntu) : ``sudo -u www-data php /var/www/html/wallabag/bin/console cache:clear -e=prod``
