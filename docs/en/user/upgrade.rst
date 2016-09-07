Upgrade wallabag
================

Upgrade on a dedicated web server
---------------------------------

The last release is published on https://www.wallabag.org/pages/download-wallabag.html. In order to upgrade your wallabag installation and get the last version, run the following commands in you wallabag folder (replace ``2.0.8`` by the last release number):

::

    git fetch origin
    git fetch --tags
    git checkout 2.0.8
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console cache:clear --env=prod

Upgrade on a shared hosting
---------------------------

Backup your ``app/config/parameters.yml`` file.

Download the last release of wallabag:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

(md5 hash of the package: ``4f84c725d1d6e3345eae0a406115e5ff``)

Extract the archive in your wallabag folder and replace ``app/config/parameters.yml`` with yours.

If you use SQLite, you must also copy your ``data/`` folder inside the new installation.

Empty ``var/cache`` folder.
