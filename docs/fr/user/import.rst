Migrer depuis ...
=================

Dans wallabag 2.x, vous pouvez importer des données depuis :

- `Pocket <#id1>`_
- `Readability <#id2>`_
- `Instapaper <#id4>`_
- `wallabag 1.x <#id6>`_
- `wallabag 2.x <#id7>`_

Nous avons aussi développé `un script pour exécuter des migrations via la ligne de commande <#import-via-la-ligne-de-commande-cli>`_.

Puisque les imports peuvent gourmands en ressource, nous avons mis en place un système de tâche asynchrone. `Vous trouverez la documentation ici <http://doc.wallabag.org/fr/master/developer/asynchronous.html>`_ (niveau expert).

Pocket
------

Créer une nouvelle application dans Pocket
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Pour importer vos données depuis Pocket, nous utilisons l'API de Pocket.
Vous devez créer une nouvelle application sur leur site dédié aux développeurs pour continuer.

* Créez une nouvelle application `sur leur site Développeurs <https://getpocket.com/developer/apps/new>`_
* Remplissez les champs requis : nom de l'application, description de l'application,
  permissions (seulement **retrieve**), la plateforme (**web**), acceptez les
  termes d'utilisation du service et soumettez votre application

Pocket vous fournira une **Consumer Key** (par exemple, `49961-985e4b92fe21fe4c78d682c1`).
Vous devez configurer la ``pocket_consumer_key`` dans le menu ``Configuration``.

Maintenant, tout est bien configuré pour migrer depuis Pocket.

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Pocket puis sur ``Se connecter à Pocket et importer les données``.

Vous devez autoriser wallabag à se connecter à votre compte Pocket.
Vos données vont être importées. L'import de données est une action qui peut être couteuse
pour votre serveur.

Readability
-----------

Exportez vos données de Readability
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sur la page des outils (`https://www.readability.com/tools/ <https://www.readability.com/tools/>`_), cliquez sur "Export your data" dans la section "Data Export". Vous allez recevoir un email avec un lien pour télécharger le json.

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Readability et ensuite sélectionnez votre fichier json pour l'uploader.

Vos données vont être importées. L'import de données est une action qui peut être couteuse pour votre serveur.

Depuis Pinboard
---------------

Exportez vos données de Pinboard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sur la page « Backup » (`https://pinboard.in/settings/backup <https://pinboard.in/settings/backup>`_), cliquez sur « JSON » dans la section « Bookmarks ». Un fichier json (sans extension) sera téléchargé (``pinboard_export``).

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Pinboard et ensuite sélectionnez votre fichier json pour l'uploader.

Vos données vont être importées. L'import de données est une action qui peut être couteuse pour votre serveur.

Depuis Instapaper
-----------------

Exportez vos données de Instapaper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sur la page des paramètres (`https://www.instapaper.com/user <https://www.instapaper.com/user>`_), cliquez sur "Download .CSV file" dans la section "Export". Un fichier CSV se téléchargera (``instapaper-export.csv``).

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Instapaper et ensuite sélectionnez votre fichier CSV pour l'uploader.

Vos données vont être importées. L'import de données est une action qui peut être couteuse pour votre serveur.

wallabag 1.x
------------

Si vous utilisiez wallabag v1.x, vous devez exporter vos données avant de migrer à wallabag v2.x, à cause du changement complet de l'application et de sa base de données. Sur votre ancienne instance de wallabag v1, vous pouvez exporter vos données en allant sur la page de configuration de l'application.

.. image:: ../../img/user/export_v1.png
   :alt: Export depuis wallabag v1
   :align: center

.. note::
    Si vous avez plusieurs comptes sur la même instance de wallabag, chaque utilisateur doit exporter ses données depuis wallabag v1 et les importer dans la v2.

.. note::
    S'il vous arrive des problèmes durant l'export ou l'import, n'hésitez pas à `demander de l'aide <http://gitter.im/wallabag/wallabag>`_.

Une fois que vous avez récupéré le fichier json contenant vos données, vous pouvez installer wallabag v2 si c'est nécessaire en suivant `la procédure standard <http://doc.wallabag.org/fr/master/user/installation.html>`_.

Une fois que vous avez créé un compte utilisateur sur votre nouvelle instance de wallabag v2, rendez-vous dans la section `Import`. Vous devez choisir l'import depuis wallabag v1 puis sélectionner votre fichier json récupéré précédemment.

.. image:: ../../img/user/import_wallabagv1.png
   :alt: Import depuis wallabag v1
   :align: center

wallabag 2.x
------------

Depuis l'instance sur laquelle vous étiez, rendez-vous dans la section `Tous les articles`, puis exportez ces articles au format json.

.. image:: ../../img/user/export_v2.png
   :alt: Export depuis wallabag v2
   :align: center

Depuis votre nouvelle instance de wallabag, créez votre compte utilisateur puis cliquez sur le lien dans le menu pour accéder à l'import. Choisissez l'import depuis wallabag v2 puis sélectionnez votre fichier json pour l'uploader.

.. note::
    S'il vous arrive des problèmes durant l'export ou l'import, n'hésitez pas à `demander de l'aide <http://gitter.im/wallabag/wallabag>`_.

Import via la ligne de commande (CLI)
-------------------------------------<http://doc.wallabag.org/en/master/user/parameters.html

Si vous avez accès à la ligne de commandes de votre serveur web, vous pouvez exécuter cette commande pour import votre fichier wallabag v1 :

::

    bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Remplacez les valeurs :

* ``1`` est l'identifiant de votre utilisateur en base (l'ID de votre premier utilisateur créé sur wallabag est 1)
* ``~/Downloads/wallabag-export-1-2016-04-05.json`` est le chemin de votre export wallabag v1

Si vous voulez marquer tous ces articles comme lus, vous pouvez ajouter l'option ``--markAsRead``.

Pour importer un fichier wallabag v2, vous devez ajouter l'option ``--importer=v2``.

Vous obtiendrez :

::

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
