# Installation

## Sur un serveur dédié (méthode conseillée)

Pour installer wallabag, vous devez exécuter ces commandes :

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

Si c'est votre première installation, vous pouvez répondre, sans crainte, "yes" lorsqu'on vous demandera de "reset" la base de donnée.

Maintenant, lisez la documentation ci-dessous pour crééer un virtual host.

{% hint style="info" %}
Pour définir des paramètres via des variables d'environnement, vous
pouvez les spécifier avec le préfixe `SYMFONY__`. Par exemple,
`SYMFONY__DATABASE_DRIVER`. Vous pouvez lire la [documentation
Symfony](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html)
pour en savoir plus.
{% endhint %}

{% hint style="tip" %}
Si vous voulez utiliser SQLite en tant que base de donnée, mettez `%kernel.root_dir%/../data/db/wallabag.sqlite` dans la valeur du paramètre `database_path` pendant l'installation.
{% endhint %}

{% hint style="info" %}
Si vous utilisez wallabag derrière Squid comme reverse proxy, assurez-vous de mettre à jour la configuration `squid.conf` pour inclure `login=PASS` au niveau de la ligne `cache_peer`. C'est nécessaire pour le fonctionnement de l'API.
{% endhint %}

## Sur un serveur mutualisé

Nous mettons à votre disposition une archive avec toutes les dépendances
à l'intérieur. La configuration par défaut utilise MySQL pour la base de données. Il est nécessaire de renseigner les informations de base de données dans le fichier `app/config/parameters.yml`. Attention : les mots de passes doivent être entourés de single quote (').

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

Le package statique nécessite que chaque commande comprenne le paramètre `--env=prod` car le package statique n'est utilisable que comme environnement de production (le mode développement n'est pas pris en charge et ne fonctionnera pas du tout).

Maintenant, lisez la documentation ci-dessous pour crééer un virtual
host. 

Accédez ensuite à votre installation de wallabag. 
Vous devrez vous créer un utilisateur via la commande
`php bin/console wallabag:install --env=prod`.

Si une erreur apparaît à cette étape, nettoyez le cache avec la commande `php bin/console cache:clear --env=prod` avant de relancer la commande précédente.

## Installation avec Docker

Nous vous proposons une image Docker pour installer wallabag facilement.
Allez voir du côté de [Docker
Hub](https://hub.docker.com/r/wallabag/wallabag/) pour plus
d'informations.

### Commande pour démarrer le containeur

```bash
docker pull wallabag/wallabag
```

## Installation sur Cloudron

Cloudron permet d'installer des applications web sur votre serveur
wallabag est proposé en tant qu'application Cloudron et est disponible
directement depuis le store.

[Installer wallabag sur
Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp.html)

## Installation sur YunoHost

YunoHost permet d'installer simplement des applications web sur votre serveur.
wallabag est proposé en tant qu'application YunoHost officielle et est disponible
directement depuis le dépôt officiel.

[![Installer wallabag sur
YunoHost](https://install-app.yunohost.org/install-with-yunohost.png)](https://install-app.yunohost.org/?app=wallabag2)

## Installation on Synology

SynoCommunity fournit un package pour installer wallabag sur votre NAS Synology.

[Installer wallabag sur Synology](https://synocommunity.com/package/wallabag)
