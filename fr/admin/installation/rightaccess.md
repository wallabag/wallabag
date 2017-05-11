# Droits d'accès aux dossiers du projet

## Environnement de test

Quand nous souhaitons juste tester wallabag, nous lançons simplement la
commande `php bin/console server:run --env=prod` pour démarrer
l'instance wallabag et tout se passe correctement car l'utilisateur qui
a démarré le projet a accès naturellement au repertoire courant, tout va
bien.

## Environnement de production

Dès lors que nous utilisons Apache ou Nginx pour accéder à notre
instance wallabag, et non plus la commande
`php bin/console server:run --env=prod` pour la démarrer, il faut
prendre garde à octroyer les bons droits aux bons dossiers afin de
préserver la sécurité de l'ensemble des fichiers fournis par le projet.

Aussi, le dossier, connu sous le nom de `DocumentRoot` (pour apache) ou
`root` (pour Nginx), doit être impérativement accessible par
l'utilisateur de Apache ou Nginx. Le nom de cet utilisateur est
généralement `www-data`, `apache` ou `nobody` (selon les systèmes linux
utilisés).

Donc le dossier `/var/www/wallabag/web` doit être accessible par ce
dernier. Mais cela ne suffira pas si nous nous contentons de ce dossier,
et nous pourrions avoir, au mieux une page blanche en accédant à la page
d'accueil du projet, au pire une erreur 500.

Cela est dû au fait qu'il faut aussi octroyer les mêmes droits d'accès
au dossier `/var/www/wallabag/var` que ceux octroyés au dossier
`/var/www/wallabag/web`. Ainsi, on règle le problème par la commande
suivante :

```bash
chown -R www-data:www-data /var/www/wallabag/var
```

Il en est de même pour les dossiers suivants :

-   /var/www/wallabag/bin/
-   /var/www/wallabag/app/config/
-   /var/www/wallabag/vendor/

en tapant

```bash
chown -R www-data:www-data /var/www/wallabag/bin
chown -R www-data:www-data /var/www/wallabag/app/config
chown -R www-data:www-data /var/www/wallabag/vendor
```

sinon lors de la mise à jour vous finirez par rencontrer les erreurs
suivantes :

```bash
Unable to write to the "bin" directory.
file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied
```
