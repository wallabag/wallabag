Installer wallabag
==================

Pré-requis
----------

Installation
------------

Installez Composer:

::

    curl -s http://getcomposer.org/installer | php

Ensuite, sur votre serveur web, exécutez cette commande :

::

    SYMFONY_ENV=prod composer create-project wallabag/wallabag wallabag "2.0.*@alpha" --no-dev
    php bin/console wallabag:install --env=prod

VOus pouvez maintenant accéder à wallabag ici http://votresiteweb/wallabag.
