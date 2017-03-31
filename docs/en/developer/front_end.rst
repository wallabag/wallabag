Tips for front-end developers
=============================

Starting from version 2.3, wallabag uses webpack to bundle its assets.

Dev mode
--------

If the server runs in dev mode, you need to run ``yarn run build:dev`` to generate the outputted javascript files for each theme. These are named ``%theme%.dev.js`` and are ignored by git. You need to relaunch ``yarn run build:dev`` for each change made to one of the assets files (js, css, pictures, fonts,...).

Live reload
-----------

Webpack brings support for live reload, which means you don't need to regenerate the assets file for each change neither reload the page manually. Changes are applied automatically in the web page. Just set the ``use_webpack_dev_server`` setting to ``true`` in ``app/config/config.yml`` and run ``yarn run watch`` and you're good to go.

.. note::

    Don't forget to put back ``use_webpack_dev_server`` to ``false`` when not using the live reload feature.

Production builds
-----------------

When you want to commit your changes, build them in production environment by using ``yarn run build:prod``. This will build all the assets needed for wallabag. To test that it properly works, you'll need to have a server in production mode, for instance with ``bin/console server:run -e=prod``.

.. note::

    Don't forget to generate production builds before committing !


Code style
----------

Code style is checked by two tools : stylelint for (S)CSS and eslint for JS. ESlint config is based on the Airbnb base preset.
