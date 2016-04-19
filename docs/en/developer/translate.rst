Translate wallabag
==================

wallabag web application
------------------------

Translation files
~~~~~~~~~~~~~~~~~

.. note::

    As wallabag is mainly developed by a French team, please consider that french
    translation is the most updated one and please copy it to create your own translation.

You can find translation files here: https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations.

You have to create ``messages.CODE.yml``, where CODE
is the ISO 639-1 code of your language (`see wikipedia <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>`__).

Other files to translate:

- https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations.
- https://github.com/wallabag/wallabag/tree/master/app/Resources/FOSUserBundle/translations.

You have to create ``THE_TRANSLATION_FILE.CODE.yml`` files.

Configuration file
~~~~~~~~~~~~~~~~~~

You have to edit `app/config/config.yml
<https://github.com/wallabag/wallabag/blob/master/app/config/config.yml>`__ to display
your language on Configuration page of wallabag (to allow users to switch to this new translation).

Under the ``wallabag_core.languages`` section, you have to add a new line with
your translation. For example:

::

    wallabag_core:
        ...
        languages:
            en: 'English'
            fr: 'Français'


For the first column (``en``, ``fr``, etc.), you have to add the ISO 639-1 code
of your language (see above).

For the second column, it's the name of your language. Just that.

wallabag documentation
----------------------

.. note::

    Contrary to the web application, the main language for documentation is english.

Documentation files are stored here: https://github.com/wallabag/wallabag/tree/master/docs

You need to respect the ``en`` folder structure when you create your own translation.
