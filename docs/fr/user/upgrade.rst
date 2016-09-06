Mettre à jour wallabag
======================

Mise à jour sur un serveur dédié
--------------------------------

La dernière version de wallabag est publiée à cette adresse : https://www.wallabag.org/pages/download-wallabag.html. Pour mettre à jour votre installation de wallabag, exécutez les commandes suivantes dans votre répertoire d'installation (remplacez ``2.0.7`` par le numéro de la dernière version) :

::

    git fetch origin
    git fetch --tags
    git checkout 2.0.7
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console cache:clear --env=prod

Mise à jour sur un hébergement mutualisé
----------------------------------------

Effectuez une sauvegarde du fichier ``app/config/parameters.yml``.

Téléchargez la dernière version de wallabag :

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(hash md5 de l'archive : ``18aadd1003a08eb11f5341b9755029f8``)

Décompressez l'archive dans votre répertoire d'installation et remplacez le fichier ``app/config/parameters.yml`` avec le votre.

Si vous utilisez SQLite, vous devez également conserver le contenu du répertoire ``data/``.

Videz le répertoire ``var/cache``.
