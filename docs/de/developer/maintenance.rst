Wartungsmodus
=============

Wenn du längere Aufgaben auf deiner wallabag Instanz ausführen willst, kannst du den Wartungsmodus aktivieren.
Keiner wird dann Zugang zu deiner Instanz haben.

Aktivieren des Wartungsmodus
----------------------------

Um den Wartungsmodus zu aktivieren, führe folgendes Kommando aus:

::

    bin/console lexik:maintenance:lock --no-interaction

Du kannst deine IP Adresse in ``app/config/config.yml`` setzen, wenn du Zugriff zu wallabag haben willst, auch wenn der Wartungsmodus aktiv ist. Zum Beispiel:

::

    lexik_maintenance:
        authorized:
            ips: ['127.0.0.1']


Deaktivieren des Wartungsmodus
------------------------

Um den Wartungsmodus zu deaktivieren, führe dieses Kommando aus:

::

    bin/console lexik:maintenance:unlock
