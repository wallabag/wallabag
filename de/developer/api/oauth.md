Einen neuen API Client erstellen
--------------------------------

In deinem wallabag Account, kannst du einen neuen API Client unter
dieser URL <http://localhost:8000/developer/client/create> erstellen.

Gib dazu nur die Umleitungs-URL deiner Appliaktion an und erstelle
deinen Client. Wenn deine Applikation eine Desktopapplikation ist, trage
die URL, die dir am besten passt, ein.

Du bekommst Informationen wie diese:

```
Client ID:

1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc

Client secret:

636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4
```

Einen Aktualisierungstoken erhalten
-----------------------------------

Für jeden API Aufruf brauchst du einen Token. Lass uns einen erstellen
mit diesem Kommando (ersetze `client_id`, `client_secret`, `username`
und `password` mit ihren Werten):

```bash
http POST http://localhost:8000/oauth/v2/token \
    grant_type=password \
    client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc \
    client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4 \
    username=wallabag \
    password=wallabag
```

Du bekommst folgendes zurück:

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

Wir werden mit dem `access_token` Wert in unseren nächsten Aufrufen
arbeiten.

cURL Beispiel:

```bash
curl -s "https://localhost:8000/oauth/v2/token?grant_type=password&client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc&client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4&username=wallabag&password=wallabag"
```

Existierende Einträge erhalten
------------------------------

Dokumentation für diese Methode:
<http://localhost:8000/api/doc#get--api-entries>.{\_format}

Da wir auf einer neuen wallabag Installation arbeiten, bekommen wir
keine Ergebnisse mit diesem Kommando:

```bash
http GET http://localhost:8000/api/entries.json \
"Authorization:Bearer ZGJmNTA2MDdmYTdmNWFiZjcxOWY3MWYyYzkyZDdlNWIzOTU4NWY3NTU1MDFjOTdhMTk2MGI3YjY1ZmI2NzM5MA"
```

gibt zurück:

```http
HTTP/1.1 200 OK
0: application/json
Cache-Control: no-cache
Connection: close
Content-Type: application/json
Date: Tue, 05 Apr 2016 08:51:32 GMT
Host: localhost:8000
Set-Cookie: PHPSESSID=nrogm748md610ovhu6j70c3q63; path=/; HttpOnly
X-Debug-Token: 4fbbc4
X-Debug-Token-Link: /_profiler/4fbbc4
X-Powered-By: PHP/7.0.4

{
    "_embedded": {
        "items": []
    },
    "_links": {
        "first": {
            "href": "http://localhost:8000/api/entries?page=1&perPage=30"
        },
        "last": {
            "href": "http://localhost:8000/api/entries?page=1&perPage=30"
        },
        "self": {
            "href": "http://localhost:8000/api/entries?page=1&perPage=30"
        }
    },
    "limit": 30,
    "page": 1,
    "pages": 1,
    "total": 0
}
```

Das Array `items` ist leer.

cURL Beispiel:

```bash
curl --get "https://localhost:8000/api/entries.html?access_token=ZGJmNTA2MDdmYTdmNWFiZjcxOWY3MWYyYzkyZDdlNWIzOTU4NWY3NTU1MDFjOTdhMTk2MGI3YjY1ZmI2NzM5MA"
```
