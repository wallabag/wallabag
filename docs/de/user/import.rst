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

Von Instapaper
--------------

*Funktion noch nicht implementiert in wallabag v2.*

Von Readability
---------------

*Funktion noch nicht implementiert in wallabag v2.*

Von einer HTML oder JSON Datei
------------------------------

*Funktion noch nicht implementiert in wallabag v2.*
