Migration von v1 oder v2
========================

Von wallabag 1.x
-----------------

Wenn du bisher wallabag v1.x genutzt hast, musst du deine Daten exportieren bevor du zu wallabag v2.x migrierst, weil die Applikation und ihre Datenbank sich stark geändert haben. In deiner alten wallabag Installation kannst du deine Daten auf der Konfigurationsseite exportieren.

.. image:: ../../img/user/export_v1.png
   :alt: Export von wallabag v1
   :align: center

.. note::
    Wenn du mehrere Accounts auf der gleichen Instanz von wallabag hast, muss jeder Nutzer von v1 exportieren und in v2 seine Daten importieren.

.. note::
    Wenn du Probleme während des Exports oder Imports hast, scheue dich nicht davor `nach Hilfe zu fragen <https://www.wallabag.org/pages/support.html>`__.

Wenn du eine JSON Datei mit deinen Artikeln erhalten hast, kannst du wallabag v2 installieren falls benötigt durch Befolgen `der Standardprozedur <http://doc.wallabag.org/en/master/user/installation.html>`__.

Nachdem du einen Nutzerkonto auf deiner neuen wallabag v2 Instanz eingerichtet hast, kannst du zu dem Abschnitt `Import` springen und `Import von wallabag v1` auswählen. Wähle deine JSON Datei aus und lade sie hoch.

.. image:: ../../img/user/import_wallabagv1.png
   :alt: Import von wallabag v1
   :align: center

Import via command-line interface (CLI)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Wenn du CLI Zugang zu deinem Webserver hast, kannst du dieses Kommando ausführen, um deine Aritkel vom wallabag v1 Export zu importieren:

::

    bin/console wallabag:import-v1 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Bitte ersetze folgende Werte:

* ``1`` ist die Nutzer ID in der Databank (Die ID von dem ersten erstellten Nutzer in wallabag ist 1)
* ``~/Downloads/wallabag-export-1-2016-04-05.json`` ist der Pfad zu deinem wallabag v1 Export

Du wirst eine solche Ausgabe erhalten:

::

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---

Von wallabag 2.x
----------------

In der vorherigen wallabag Instanz, gehe zu `Alle Artikel` und exportiere diese Artikel als JSON.

.. image:: ../../img/user/export_v2.png
   :alt: Export von wallabag v2
   :align: center

In deiner neuen wallabag Instanz erstellst du ein Nutzerkonto und klickst auf den Link im Menü, um den Import fortzusetzen. Wähle Import von wallabag v2 aus und lade deine JSON Datei hoch.

.. note::
    Wenn du Probleme während des Exports oder Imports hast, scheue dich nicht davor `nach Hilfe zu fragen <https://www.wallabag.org/pages/support.html>`__.
