Domande frequenti
=================

Durante l'installazione ho riscontrato l'errore ``Error Output: sh: 1: @post-cmd: not found``
---------------------------------------------------------------------------------------------

Sembra che ci sia un problema con la vostra installazione di ``composer``. Provate a disinstallarlo e reinstallarlo.

`Leggete la documentazione su composer per sapere come installarlo
<https://getcomposer.org/doc/00-intro.md>`__.

Non riesco a convalidare il modulo di registrazione
---------------------------------------------------

Assicuratevi che tutti i campi siano riempiti correttamente:

* indirizzo email valido
* stessa password nei due campi

Non riesco a ricevere la mia mail di attivazione
------------------------------------------------

Siete sicuri che il vostro indirizzo email sia corretto? avete controllato la cartella di spam?

Se ancora non vedete la mail di attivazione, assicuratevi di aver installato e configurato a dovere un mail transfer agent. Assicuratevi di includere una regola del firewall per SMTP. Per esempio, se usate firewalld:

::

    firewall-cmd --permanent --add-service=smtp
    firewall-cmd --reload

Infine, se avete SELinux abilitato, impostate la seguente regola:

``setsebool -P httpd_can_sendmail 1``

Quando clicco il link di attivazione, mi appare questo messaggio: ``L'utente con token di conferma "DtrOPfbQeVkWf6N" non esiste``.
----------------------------------------------------------------------------------------------------------------------------------

Avete giá attivato il vostro account oppure l'URL dell'email di attivazione é sbagliato.

Ho dimenticato la mia password
------------------------------

Potete ripristinare la password cliccando il link``Hai dimenticato la password?``, nella pagina di login. Quindi, riempite il modulo con la vostra email o il vostro username e riceverete un'email per ripristinare la vostra password.

Ho riscontrato l'errore ``failed to load external entity`` cercando di installare wallabag
------------------------------------------------------------------------------------------

Come descritto `qui <https://github.com/wallabag/wallabag/issues/2529>`_, modificate il vostro file ``web/app.php`` ed aggiungete questa linea: ``libxml_disable_entity_loader(false);`` sulla linea 5. 

Questo é un bug di Doctrine / PHP, non possiamo farci nulla.


