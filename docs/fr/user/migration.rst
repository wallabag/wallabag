Migrer wallabag
===============

Depuis wallabag 1.x
-------------------

Si vous utilisiez wallabag v1.x, vous devez exporter vos données avant de migrer à wallabag v2.x, à cause du changement complet de l'application et de sa base de données. Sur votre ancienne instance de wallabag v1, vous pouvez exporter vos données en allant sur la page de configuration de l'application.

.. image:: ../../img/user/export_v1.png
   :alt: Export depuis wallabag v1
   :align: center

.. note::
    Si vous avez plusieurs comptes sur la même instance de wallabag, chaque utilisateur doit exporter ses données depuis wallabag v1 et les importer dans la v2.

.. note::
    S'il vous arrive des problèmes durant l'export ou l'import, n'hésitez pas à `demander de l'aide <https://www.wallabag.org/pages/support.html>`__.

Une fois que vous avez récupéré le fichier json contenant vos données, vous pouvez installer wallabag v2 si c'est nécessaire en suivant `la procédure standard <http://doc.wallabag.org/fr/v2/user/installation.html>`__.

Une fois que vous avez créé un compte utilisateur sur votre nouvelle instance de wallabag v2, rendez-vous dans la section `Import`. Vous devez choisir l'import depuis wallabag v1 puis sélectionner votre fichier json récupéré précédemment.

.. image:: ../../img/user/import_wallabagv1.png
   :alt: Import depuis wallabag v1
   :align: center

Depuis wallabag 2.x
-------------------

Depuis l'instance sur laquelle vous étiez, rendez-vous dans la section `Tous les articles`, puis exportez ces articles au format json.

.. image:: ../../img/user/export_v2.png
   :alt: Export depuis wallabag v2
   :align: center

Depuis votre nouvelle instance de wallabag, créez votre compte utilisateur puis cliquez sur le lien dans le menu pour accéder à l'import. Choisissez l'import depuis wallabag v2 puis sélectionnez votre fichier json pour l'uploader.

.. note::
    S'il vous arrive des problèmes durant l'export ou l'import, n'hésitez pas à `demander de l'aide <https://www.wallabag.org/pages/support.html>`__.

Import via la ligne de commande (CLI)
-------------------------------------

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
