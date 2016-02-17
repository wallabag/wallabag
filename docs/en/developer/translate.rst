Translate wallabag
==================

Translation files
-----------------

You can find translation files here: https://github.com/wallabag/wallabag/tree/v2/src/Wallabag/CoreBundle/Resources/translations.

You have to create ``messages.CODE.yml`` and ``validators.CODE.yml``, where CODE is the the ISO 639-1 code of your language (`see wikipedia <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>`__).

There is still one file to translate: https://github.com/wallabag/wallabag/tree/v2/app/Resources/CraueConfigBundle/translations.

You have to create ``CraueConfigBundle.CODE.yml``.

Configuration file
------------------

You have to edit `app/config/config.yml
<https://github.com/wallabag/wallabag/blob/v2/app/config/config.yml>`__ to display your language on Configuration page of wallabag (to allow users to switch to this new translation).

Under the ``wallabag_core.languages`` section, you have to add a new line for with your translation. For example

::

    wallabag_core:
        ...
        languages:
            en: 'English'
            fr: 'Français'


For the first column (``en``, ``fr``, etc.), you have to add the ISO 639-1 code of your language (see above).

For the second column, it's the name of your language. Just that.
