Migration von einem Drittanbieter
=================================

In wallabag 2.x kannst du Daten von folgenden Anbietern importieren:

- Pocket <#id1>`_
- Readability <#id2>`_
- Instapaper <#id4>`_
- wallabag 1.x <#id6>`_
- wallabag 2.x <#id7>`_

Wir haben zusätzlich `ein Skript für die Migration per Kommandozeile <#import-via-command-line-interface-cli>`_ geschrieben.

Da Importe eine Menge Zeit in Anspruch nehmen können, haben wir auch ein asynchrones Aufgabensystem entwickelt. `Du kannst die Dokumentation hier lesen <http://doc.wallabag.org/de/master/developer/asynchronous.html>`_ (für Experten).

Pocket
------

Erstelle eine neue Applikation in Pocket
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Um deine Daten von Pocket zu importieren, nutzen wir die Pocket API. Du musst
eine neue Applikation auf ihrer Entwicklerwebsite erstellen, um fortzufahren.

* Erstelle eine neue Applikation `auf der Entwicklerwebsite <https://getpocket.com/developer/apps/new>`_
* Fülle die erforderlichen Felder aus: Name, Beschreibung, Berechtigungen (nur **abrufen**), Plattform
  (**web**), akzeptiere die Nutzungsbedingungen und reiche deine neue Applikation ein

Pocket wird dir einen **Consumer Key** geben (z.B. `49961-985e4b92fe21fe4c78d682c1`).
Du musst den ``pocket_consumer_key`` in dem Abschnitt ``Import`` in dem ``Interne Einstellungen`` Menü
konfigurieren.

Jetzt ist alles in Ordnung, um von Pocket zu migrieren.

Importiere deine Daten in wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Klicke auf den ``Importieren`` Link im Menü, auf ``Inhalte importieren`` in dem Pocketabschnitt und
dann auf ``Verbinde mit Pocket und importieren Daten``.

Du musst wallabag erlauben, mit deinem Pocketaccount zu interagieren.
Deine Daten werden importiert. Datenimport kann ein sehr anspruchsvoller Prozess für deinen Server
sein (wir müssen daran arbeiten, um diesen Import zu verbessern).

Von Readability
----------------

Exportiere deine Readability Daten
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Auf der Seite Tools (`https://www.readability.com/tools/ <https://www.readability.com/tools/>`_), klicke auf "Exportiere deine Daten" in dem Abschnitt "Daten Export". Du wirst eine E-Mail empfangen, um eine JSON Datei herunterladen zu können (Datei endet aber nicht auf .json).

Importiere deine Daten in wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Klicke auf den ``Importieren`` Link im Menü, auf ``Importiere Inhalte`` in dem Readability Abschnitt und wähle dann deine JSON Datei aus und lade sie hoch.

Deine Daten werden importiert. Der Datenimport can ein beanspruchender Prozess für deinen Server sein.

Von Pinboard
-------------

Exportiere deine Pinboard Daten
~~~~~~~~~~~~~~~~~~~~~~~~~

Auf der Seite Backup (`https://pinboard.in/settings/backup <https://pinboard.in/settings/backup>`_), klicke auf "JSON" in dem Abschnitt "Lesezeichen". Eine JSON Datei wird heruntergeladen (z.B. ``pinboard_export``).

Importiere deine Daten in wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Klicke auf den ``Importieren`` Link im Menü, auf ``Importiere Inhalte`` in dem Pinboard Abschnitt und wähle dann deine JSON Datei aus und lade sie hoch.

Deine Daten werden importiert. Der Datenimport can ein beanspruchender Prozess für deinen Server sein.

Von Instapaper
---------------

Exportiere deine Instapaper Daten
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Auf der Seite Einstellungen (`https://www.instapaper.com/user <https://www.instapaper.com/user>`_), klicke auf "Download .CSV Datei" in dem Abschnitt "Export". Eine CSV Datei wird heruntergeladen (z.B. ``instapaper-export.csv``).

Importiere deine Daten in wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Klicke auf den ``Importieren`` Link im Menü, auf ``Importiere Inhalte`` in dem Instapaper Abschnitt und wähle dann deine JSON Datei aus und lade sie hoch.

Deine Daten werden importiert. Der Datenimport can ein beanspruchender Prozess für deinen Server sein.

wallabag 1.x
------------

Wenn du in der Vergangenheit wallabag 1.x genutzt hast, musst du deine Daten exportieren, bevor du auf wallabag 2.x umsteigst, da sich viel an der Anwendung und der Datenbank geändert hast. In deiner alten wallabag-installation kannst du deine Daten exportieren, indem du die Konfigurationsseite auf der alten wallabag-Instanz öffnest.

.. image:: ../../img/user/export_v1.png
   :alt: Export aus wallabag 1.x
   :align: center

.. note::
    Wenn du mehrere Accounts auf der gleichen wallabag-Instanz hast, muss jeder Nutzer seine Daten aus 1.x exportieren und in 2.x importieren.

.. note::
    Falls während des Exports oder des Imports Probleme auftreten sollten, scheue dich nicht, den `Support zu kontaktieren <http://gitter.im/wallabag/wallabag>`__.

Wenn du dann die JSON-Datei mit deinen Einträgen heruntergeladen hast, kannst du `wallabag 2 über die Standard-Prozedur installieren <http://doc.wallabag.org/en/master/user/installation.html>`__.

Nach dem Erstellen des Benutzeraccounts auf deiner neuen "wallabag 2.x"-Instanz, navigiere auf den Import-Bereich und wähle `Aus wallabag v1 importieren`. Wähle deine JSON-Datei und lade sie hoch.

.. image:: ../../img/user/import_wallabagv1.png
   :alt: Import aus wallabag v1
   :align: center

wallabag 2.x
------------

Gehe auf der alten wallabag-Instanz, die du vorher genutzt hast, auf `Alle Artikel` und exportiere diese dann als JSON.

.. image:: ../../img/user/export_v2.png
   :alt: Export aus wallabag v2
   :align: center

Nach dem Erstellen des Benutzeraccounts auf deiner neuen "wallabag 2.x"-Instanz, navigiere auf den Import-Bereich und wähle `Aus wallabag v2 importieren`. Wähle deine JSON-Datei und lade sie hoch.

.. note::
    Falls während des Exports oder des Imports Probleme auftreten sollten, scheue dich nicht, den `Support zu kontaktieren <http://gitter.im/wallabag/wallabag>`__.

Import über die Kommandozeile (CLI)
-----------------------------------

Falls du auf deinem Server Zugriff auf die Kommandozeile hast, kannst du den folgenden Befehl ausführen, um deine Daten aus wallabag v1 zu importieren:

::

    bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod

Bitte ersetze die Werte:

* ``1`` ist die Benutzer-ID in der Datenbank (die ID des ersten Benutzers ist immer 1)
* ``~/Downloads/wallabag-export-1-2016-04-05.json`` ist der Pfad zu deiner wallabag v1-Exportdatei

Wenn du alle Artikel als gelesen markieren möchtest, kannst du die ``--markAsRead``-Option hinzufügen.

Um eine wallabag 2.x-Datei zu importieren, musst du die Option ``--importer=v2`` hinzufügen.

Als Ergebnis wirst du so etwas erhalten:

::

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
