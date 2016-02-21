Install wallabag
================

Requirements
------------

Installation
------------

Install Composer:

::

    curl -s http://getcomposer.org/installer | php

Next, on your web server, run this command:

::

    SYMFONY_ENV=prod composer create-project wallabag/wallabag wallabag "2.0.*@alpha" --no-dev
    php bin/console wallabag:install --env=prod

Now you can access to http://yourwebsite/wallabag.
