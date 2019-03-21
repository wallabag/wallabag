# Configurare l'accesso al paywall

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

{% hint style="working" %}
Questa è la parte tecnica a proposito del paywall. Se state cercando la sezione dedicata all'utente, si prega di leggere [questa pagina](../user/articles/restricted.md).
{% endhint %}

Leggete [questa parte della documentazione](../user/errors_during_fetching.md)
per capire i file di configurazione, i quali si trovano sotto `vendor/j0k3r/graby-site-config/`. Per la maggior parte dei siti, questo file è già configurato: le istruzioni seguenti sono valide solo per i siti web che non sono ancora configurati.

Ogni file di configurazione del parsing deve essere migliorato
aggiungendo `requires_login`, `login_uri`, `login_username_field`,
`login_password_field` e `not_logged_in_xpath`.

Fate attenzione, il modulo di login deve essere nel contenuto della
pagina quando wallabag lo carica. È impossibile per wallabag essere
autenticato su un sito dove il modulo di login è caricato dopo la pagina
(da ajax per esempio).

`login_uri` è l'URL di azione del modulo (l'attributo `action` del
modulo). `login_username_field` è l'attributo `name` nel campo di login.
`login_password_field` è l'attributo `name` nel campo password.

Per esempio:

```
title://div[@id="titrage-contenu"]/h1[@class="title"]
body: //div[@class="contenu-html"]/div[@class="page-pane"]

requires_login: yes

login_uri: http://www.arretsurimages.net/forum/login.php
login_username_field: username
login_password_field: password

not_logged_in_xpath: //body[@class="not-logged-in"]
```
