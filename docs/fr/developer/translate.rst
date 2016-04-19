Traduire wallabag
=================

L'application web
-----------------

Fichiers de traductions
~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    Comme wallabag est principalement dévelopée par une équipe française, c'est
    cette traduction qui est considérée comme la plus récente. Merci de vous baser
    sur celle-ci pour créer votre traduction.

Les principaux fichiers de traduction se trouvent ici : https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations.

Vous devez créer le fichier ``messages.CODE.yml``,
où CODE est le code ISO 639-1 de votre langue (`cf wikipedia <https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1>`__).

Autres fichiers à traduire :

- https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations.
- https://github.com/wallabag/wallabag/tree/master/app/Resources/FOSUserBundle/translations.

Vous devez créer les fichiers ``LE_FICHIER_DE_TRADUCTION.CODE.yml``.

Fichier de configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Vous devez éditer `app/config/config.yml
<https://github.com/wallabag/wallabag/blob/master/app/config/config.yml>`__ pour
afficher votre langue dans la page Configuration de wallabag (pour permettre aux
utilisateurs de choisir cette nouvelle traduction).

Dans la section ``wallabag_core.languages``, vous devez ajouter une nouvelle ligne
avec votre traduction. Par exemple :

::

    wallabag_core:
        ...
        languages:
            en: 'English'
            fr: 'Français'


Pour la première colonne (``en``, ``fr``, etc.), vous devez ajouter le code ISO 639-1
de votre langue (voir ci-dessus).

Pour la seconde colonne, c'est juste le nom de votre langue.

Documentation de wallabag
-------------------------

.. note::

    Contrairement à l'application, la langue principale de la documentation est l'anglais

Les fichiers de documentation se trouvent ici : https://github.com/wallabag/wallabag/tree/master/docs

Vous devez respecter la structure du dossier ``en`` quand vous crééz votre traduction.
