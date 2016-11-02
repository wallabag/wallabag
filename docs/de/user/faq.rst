Häufig gestellte Fragen
==========================

Während der Installation sehe ich den Fehler ``Error Output: sh: 1: @post-cmd: not found``
------------------------------------------------------------------------------------------

Es scheint, dass du ein Problem bei deiner ``composer`` Installation hast. Versuche es zu deinstallieren und neu zu installieren.

`Lies die Dokumentation über composer, um zu erfahren wie es installiert wird
<https://getcomposer.org/doc/00-intro.md>`__.

Ich kann das Registrierungsformular nicht validieren
----------------------------------------------------

Stelle sicher, dass alle Felder ausgefüllt sind:

* valide E-Mail-Adresse
* das gleiche Passwort in zwei Feldern

Ich erhalte meine Aktivierungsmail nicht
----------------------------------------

Bist du sicher, dass deine eingegebene E-Mail-Adresse korrekt war? Hast du deinen Spamordner überprüft?

Wenn du dann immer noch nicht deine Aktivierungsmail siehst, stelle bitte sicher, dass du einen MTA
korrekt installiert und eingerichtet hast. Prüfe, dass deine Firewallregel existiert, z.B. für firewalld:

::
    firewall-cmd --permanent --add-service=smtp
    firewall-cmd --reload

Schließlich, falls du SELinux aktiviert hast, setze folgende Regel:

``setsebool -P httpd_can_sendmail 1``

Wenn ich den Aktivierungslink klicke, bekomme ich die Nachricht ``Der Nutzer mit dem Bestätigungstoken "DtrOPfbQeVkWf6N" existiert nicht``
------------------------------------------------------------------------------------------------------------------------------------------

Du hast deinen Account schon aktiviert oder die URL der Aktivierungsmail ist falsch.

Ich habe mein Passwort vergessen
--------------------------------

Du kannst dein Passwort zurücksetzen, indem du auf den Link ``Kennwort vergessen?`` auf der Loginseite klickst. Fülle dann das Formular mit deiner E-Mail-Adresse oder deinem Nutzernamen aus
und du wirst eine E-Mail zum Passwort zurücksetzen erhalten.

I've got the ``failed to load external entity`` error when I try to install wallabag
------------------------------------------------------------------------------------

As described `here <https://github.com/wallabag/wallabag/issues/2529>`_, please edit your ``web/app.php`` file and add this line: ``libxml_disable_entity_loader(false);`` on line 5.
