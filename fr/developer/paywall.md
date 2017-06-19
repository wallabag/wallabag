# Configurer l'accès à un paywall

> **[warning] Important**
>
> C'est la partie technique à propos du paywall. Si vous cherchez la partie utilisateur, allez plutôt [voir cette page](../user/articles/restricted.md).


Lisez [cette documentation](../user/errors_during_fetching.md)
pour en savoir plus sur ces fichiers de configuration, qui se trouvent
dans le répertoire `vendor/j0k3r/graby-site-config/`. Pour la majorité
des sites, ce fichier est déjà configuré : les instructions qui suivent
concernent seulement les sites non encore configurés.

Chaque fichier de configuration doit être enrichi en ajoutant
`requires_login`, `login_uri`, `login_username_field`,
`login_password_field` et `not_logged_in_xpath`.

Attention, le formulaire de connexion doit se trouver dans le contenu de
la page lors du chargement de celle-ci. Il sera impossible pour wallabag
de se connecter à un site dont le formulaire de connexion est chargé
après coup (en ajax par exemple).

`login_uri` correspond à l'URL à laquelle le formulaire est soumis
(attribut `action` du formulaire). `login_username_field` correspond à
l'attribut `name` du champ de l'identifiant. `login_password_field`
correspond à l'attribut `name` du champ du mot de passe.

Par exemple :

```
title://div[@id="titrage-contenu"]/h1[@class="title"]
body: //div[@class="contenu-html"]/div[@class="page-pane"]

requires_login: yes

login_uri: http://www.arretsurimages.net/forum/login.php
login_username_field: username
login_password_field: password

not_logged_in_xpath: //body[@class="not-logged-in"]
```
