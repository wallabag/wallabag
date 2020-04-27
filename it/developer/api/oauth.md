# Creare un nuovo client API

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

Sul vostro account wallabag potete creare un nuovo client API presso
questo URL https://app.wallabag.it/developer/client/create.

Date solamente l'URL per il reindirizzamento della vostra applicazione e
create il vostro client. Se la vostra applicazione è desktop, inserite
l'URL che preferite.

Toverete informazioni come queste:

```
Client ID:

1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc

Client secret:

636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4
```

# Ottenere un token per l'accesso

Per ogni chiamata API avrete bisogno di un token. Creiamolo con questo
comando (rimpiazzate `client_id`, `client_secret`, `username` and
`password` con i loro valori):

```bash
http POST http://localhost:8000/oauth/v2/token \
    grant_type=password \
    client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc \
    client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4 \
    username=wallabag \
    password=wallabag
```

Otterrete questo risultato:

```http
HTTP/1.1 200 OK
Cache-Control: no-store, private
Connection: close
Content-Type: application/json
Date: Tue, 05 Apr 2016 08:44:33 GMT
Host: localhost:8000
Pragma: no-cache
X-Debug-Token: 19c8e0
X-Debug-Token-Link: /_profiler/19c8e0
X-Powered-By: PHP/7.0.4

{
    "access_token": "ZGJmNTA2MDdmYTdmNWFiZjcxOWY3MWYyYzkyZDdlNWIzOTU4NWY3NTU1MDFjOTdhMTk2MGI3YjY1ZmI2NzM5MA",
    "expires_in": 3600,
    "refresh_token": "OTNlZGE5OTJjNWQwYzc2NDI5ZGE5MDg3ZTNjNmNkYTY0ZWZhZDVhNDBkZTc1ZTNiMmQ0MjQ0OThlNTFjNTQyMQ",
    "scope": null,
    "token_type": "bearer"
}
```

Lavoreremo con il valore `access_token` nelle nostre prossime chiamate.

esempio di cURL:

```bash
curl -s "https://localhost:8000/oauth/v2/token?grant_type=password&client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc&client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4&username=wallabag&password=wallabag"
```
