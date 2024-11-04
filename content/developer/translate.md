---
title: Translate
weight: 7
---

## wallabag web application

### Translation files

Since wallabag is primarily developed by a French team, the French
translation is the most up-to-date. Use it as a reference to create
your own translation.

Translation files are available here:
https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations.

You need to create `messages.CODE.yml` and `validators.CODE.yml`, where
CODE is the ISO 639-1 code of your language ([see
Wikipedia](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)).

Other files to translate:

- https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations
- https://github.com/wallabag/wallabag/tree/master/src/Wallabag/UserBundle/Resources/translations

You need to create `THE_TRANSLATION_FILE.CODE.yml` files.

### Configuration file

Edit the [app/config/wallabag.yml](https://github.com/wallabag/wallabag/blob/master/app/config/wallabag.yml)
file to display your language on the Configuration page of wallabag, allowing users to switch to the new translation.

In the `wallabag_core.languages` section, add a new line containing your
translation, as shown in this example:

```yaml
wallabag_core:
    ...
    languages:
        en: 'English'
        fr: 'Français'
```

For the first column (`en`, `fr`, etc.), add the ISO 639-1 code for your language.

For the second column, enter the name of your language in its native form.

## wallabag documentation

Unlike the web application, English is the primary language for documentation.

Documentation files are located at:
<https://github.com/wallabag/doc>

When creating your translation, maintain the same folder structure as the `en` directory.
