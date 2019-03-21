Testsuite
=========

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

Per assicurare la qualità di sviluppo di wallabag, abbiamo scritto i
test con [PHPUnit](https://phpunit.de). 

Se contribuite al progetto
(traducendo l'applicazione, risolvendo i bug o aggiungendo nuove
funzioni), si prega di scrivere i propri test.

Per avviare la testsuite di wallabag dovete installare
[ant](http://ant.apache.org). Poi, eseguite il comando `make test`, il quale dapprima popolerà il database di test con dei dispositivi e poi eseguirà i test.
