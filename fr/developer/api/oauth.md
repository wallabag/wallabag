Créer un nouveau client d'API
-----------------------------

Depuis votre wallabag, vous pouvez créer un nouveau client d'API à cette
URL https://app.wallabag.it/developer/client/create.

Vous devez renseigner l'URL de redirection de votre application et créer
votre client. Si votre application est une application desktop,
renseignez l'URL que vous souhaitez.

Vous obtiendrez les informations suivantes :

```
Client ID:

1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc

Client secret:

636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4
```

Créer un jeton
--------------

Pour chaque appel d'API, vous aurez besoin d'un jeton. Créons-le avec la
commande suivante (remplacez `client_id`, `client_secret`, `username`
and `password` par leur valeur):

```bash
http POST http://localhost:8000/oauth/v2/token \
    grant_type=password \
    client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc \
    client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4 \
    username=wallabag \
    password=wallabag
```

Vous obtiendrez :

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

Nous allons utiliser la valeur de `access_token` dans nos prochains
appels.

Exemple cURL :

```bash
curl -s "https://localhost:8000/oauth/v2/token?grant_type=password&client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc&client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4&username=wallabag&password=wallabag"
```
