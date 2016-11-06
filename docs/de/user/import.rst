Migration von einem Drittanbieter
=================================

Von Pocket
-----------

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

Klicke auf den ``Importieren`` Link im menü, auf ``Inhalte importieren`` in dem Pocketabschnitt und
dann auf ``Verbinde mit Pocket und importieren Daten``.

Du musst wallabag erlauben, mit deinem Pocketaccount zu interagieren.
Deine Daten werden importiert. Datenimport kann ein sehr anspruchsvoller Prozess für deinen Server
sein (wir müssen daran arbeiten, um diesen Import zu verbessern).

From Readability
----------------

Export your Readability data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the tools (`https://www.readability.com/tools/ <https://www.readability.com/tools/>`_) page, click on "Export your data" in the "Data Export" section. You will received an email to download a json (which does not end with .json in fact).

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Readability section
and then select your json file and upload it.

Your data will be imported. Data import can be a demanding process for your server.

From Pinboard
-------------

Export your Pinboard data
~~~~~~~~~~~~~~~~~~~~~~~~~

On the backup (`https://pinboard.in/settings/backup <https://pinboard.in/settings/backup>`_) page, click on "JSON" in the "Bookmarks" section. A JSON file will be downloaded (like ``pinboard_export``).

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Pinboard section
and then select your json file and upload it.

Your data will be imported. Data import can be a demanding process for your server.

From Instapaper
---------------

Export your Instapaper data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the settings (`https://www.instapaper.com/user <https://www.instapaper.com/user>`_) page, click on "Download .CSV file" in the "Export" section. A CSV file will be downloaded (like ``instapaper-export.csv``).

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Instapaper section
and then select your CSV file and upload it.

Your data will be imported. Data import can be a demanding process for your server.


Von einer HTML oder JSON Datei
------------------------------

*Funktion noch nicht implementiert in wallabag v2.*
