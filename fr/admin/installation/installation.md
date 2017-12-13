# Installation

### Sur un serveur dédié (méthode conseillée)

Pour installer wallabag, vous devez exécuter ces commandes :

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

Pour démarrer le serveur interne à php et vérifier que tout s'est
installé correctement, vous pouvez exécuter :

```bash
make run
```

Et accéder wallabag à l'adresse <http://lipdevotreserveur:8000>

Pour définir des paramètres via des variables d'environnement, vous
pouvez les spécifier avec le préfixe `SYMFONY__`. Par exemple,
`SYMFONY__DATABASE_DRIVER`. Vous pouvez lire la [documentation
Symfony](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html)
pour en savoir plus.

### Sur un serveur mutualisé

Nous mettons à votre disposition une archive avec toutes les dépendances
à l'intérieur. La configuration par défaut utilise SQLite pour la base
de données. Si vous souhaitez changer ces paramètres, vous devez
modifier le fichier `app/config/parameters.yml`.

Nous avons déjà créé un utilisateur : le login et le mot de passe sont
`wallabag`.

Avec cette archive, wallabag ne vérifie pas si les extensions
obligatoires sont présentes sur votre serveur pour bien fonctionner (ces
vérifications sont faites durant le `composer install` quand vous avez
un serveur dédié, voir ci-dessus).

Exécutez cette commande pour télécharger et décompresser l'archive :

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

Vous trouverez [le hash md5 du dernier package sur notre
site](https://static.wallabag.org/releases/).

Maintenant, lisez la documentation ci-dessous pour crééer un virtual
host. Accédez ensuite à votre installation de wallabag. Si vous avez
changé la configuration pour modifier le type de stockage (MySQL ou
PostgreSQL), vous devrez vous créer un utilisateur via la commande
`php bin/console wallabag:install --env=prod`.

### Installation avec Docker

Nous vous proposons une image Docker pour installer wallabag facilement.
Allez voir du côté de [Docker
Hub](https://hub.docker.com/r/wallabag/wallabag/) pour plus
d'informations.

#### Commande pour démarrer le containeur

```bash
docker pull wallabag/wallabag
```

### Installation sur Cloudron

Cloudron permet d'installer des applications web sur votre serveur
wallabag est proposé en tant qu'application Cloudron et est disponible
directement depuis le store.

[Installer wallabag sur
Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp.html)

### Installation sur YunoHost

YunoHost permet d'installer simplement des applications web sur votre serveur.
wallabag est proposé en tant qu'application YunoHost officielle et est disponible
directement depuis le dépôt officiel.

[![Installer wallabag sur 
YunoHost](https://install-app.yunohost.org/install-with-yunohost.png)](https://install-app.yunohost.org/?app=wallabag2)