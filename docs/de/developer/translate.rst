Übersetze wallabag
==================

wallabag Webapplikation
-----------------------

Übersetzungsdateien
~~~~~~~~~~~~~~~~~~~

.. note::

    Da wallabag hauptsächlich von einem französischem Team entwickelt wird, betrachte
    die französische Übersetzung als die aktuellste und kopiere sie, um deine eigene Übersetzung zu starten.

Du kannst die Übersetzungsdateien hier finden: https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations.

Du musst die ``messages.CODE.yml`` und ``validators.CODE.yml`` erstellen, wobei CODE
der ISO 639-1 Code deiner Sprache ist (`siehe Wikipedia <https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes>`__).

Andere Dateien zum Übersetzen:

- https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations.
- https://github.com/wallabag/wallabag/tree/master/src/Wallabag/UserBundle/Resources/translations.

Du musst die ``THE_TRANSLATION_FILE.CODE.yml`` Dateien erstellen.

Konfigurationsdatei
~~~~~~~~~~~~~~~~~~~

Du musst die `app/config/config.yml <https://github.com/wallabag/wallabag/blob/master/app/config/config.yml>`__ bearbeiten,
um deine Sprache auf der Konfigurationsseite in wallabag anzuzeigen (um Nutzern zu erlauben zu dieser neuen Übersetzung zu wechseln).

Unter dem Abschnitt ``wallabag_core.languages`` musst du eine neue Zeile mit deiner Übersetzung hinzufügen. Zum Beispiel:

::

    wallabag_core:
        ...
        languages:
            en: 'English'
            fr: 'Français'


Für die erste Spalte (``en``, ``fr``, etc.) musst du den ISO 639-1 Code deiner Sprache hinzufügen (siehe oben).

Für die zweite Spalte trägst du den Namen deiner Sprache ein. Nur den.

wallabag Dokumentation
----------------------

.. note::

    Im Gegensatz zur Webapplikation ist die Hauptsprache für die Dokumentation Englisch.

Documentationsdateien sind hier gespeichert: https://github.com/wallabag/wallabag/tree/master/docs

Du musst die Ordnerstruktur des Ordners ``en`` beachten, wenn du deine eigene Übersetzung startest.
