# Conseils pour développeurs back-end

## Utiliser le serveur web interne de PHP

Le [serveur web interne de PHP](https://www.php.net/manual/fr/features.commandline.webserver.php) vous permet de développer sans avoir besoin d’installer un serveur web comme Nginx ou Apache.

Pour installer les dépendances PHP, construire la version `dev` du front-end, configurer wallabag et lancer le serveur interne, faites :

```
make dev
```

Notez que vous aurez besoin de [yarn](https://yarnpkg.com/fr/docs/install) pour construire la version `dev` du front-end.

## Linting

Pour rendre votre code conforme au style de code de wallabag, lancez :

```
php bin/php-cs-fixer fix --verbose
```
