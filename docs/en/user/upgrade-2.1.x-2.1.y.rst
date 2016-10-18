Upgrading from 2.1.x to 2.1.y
=============================

Upgrade on a dedicated web server
---------------------------------

The last release is published on https://www.wallabag.org/pages/download-wallabag.html. In order to upgrade your wallabag installation and get the last version, run the following commands in you wallabag folder (replace ``2.1.2`` by the last release number):

::

    rm -rf var/cache/*
    git fetch origin
    git fetch --tags
    git checkout 2.1.2 --force
    SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
    php bin/console cache:clear --env=prod

Upgrade on a shared hosting
---------------------------

Backup your ``app/config/parameters.yml`` file.

Download the last release of wallabag:

.. code-block:: bash

    wget http://wllbg.org/latest-v2-package && tar xvf latest-v2-package

You will find the `md5 hash of the latest package on our website <https://www.wallabag.org/pages/download-wallabag.html>`_.

Extract the archive in your wallabag folder and replace ``app/config/parameters.yml`` with yours.

If you use SQLite, you must also copy your ``data/`` folder inside the new installation.

Empty ``var/cache`` folder.
