---
title: oAuth
weight: 1
---

## Creating a new API client

In your wallabag account, you can create a new API client by visiting:
https://app.wallabag.it/developer/client/create.

Provide the redirect URL of your application and create your client.
If your application is a desktop one, use any URL that suits your needs.

You get information like this:

```
Client ID:

1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc

Client secret:

636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4
```

## Obtaining a access token

For each API call, you'll need an access token. Create one using the following 
command, replacing `client_id`, `client_secret`, `username`, and `password` with your actual values:

```bash
http POST http://localhost:8000/oauth/v2/token \
    grant_type=password \
    client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc \
    client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4 \
    username=wallabag \
    password=wallabag
```

The response will look like this:

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

We will use the `access_token` value in our subsequent API calls.

cURL example:

```bash
curl -s "https://localhost:8000/oauth/v2/token?grant_type=password&client_id=1_3o53gl30vhgk0c8ks4cocww08o84448osgo40wgw4gwkoo8skc&client_secret=636ocbqo978ckw0gsw4gcwwocg8044sco0w8w84cws48ggogs4&username=wallabag&password=wallabag"
```
