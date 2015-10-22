---
language: Français
currentMenu: install
subTitle: Télécharger et installer wallabag
---

# Télécharger et installer wallabag

## Je ne souhaite pas installer wallabag

Puisque vous ne voulez pas ou ne pouvez pas installer wallabag, nous vous proposons de créer un compte gratuit sur [Framabag](https://framabag.org/), lequel utilise notre logiciel. [Lisez la documentation complète](../Utilisateur/Framabag.md).

## Je souhaite installer wallabag

### Je souhaite télécharger wallabag manuellement

[Télécharger la dernière version de wallabag](http://wllbg.org/latest) et décompresser-là :

    wget http://wllbg.org/latest
    unzip latest
    mv wallabag-version-number wallabag

Copiez les fichiers sur votre serveur web. Dans le cas d'Ubuntu/Debian, il s'agit de /var/www/html/ :

    sudo mv wallabag /var/www/html/

Puis sautez le paragraphe suivant.

### Je souhaite télécharger wallabag via composer

Vous devez installer composer : 

    curl -s http://getcomposer.org/installer | php

Ensuite, sur votre serveur web, exécutez cette commande : 

    composer create-project wallabag/wallabag . dev-master

Tout est téléchargé dans le répertoire courant.

#### Pré-requis pour votre serveur web

Wallabag nécessite qu'un certain nombre de composants soient installées sur votre serveur web.
Pour être sûr que votre serveur possède tous les pré-requis, ouvrez dans votre navigateur la page `http://monserveur.com/wallabag/install/index.php`.

Les composants sont :   
 
* [PHP 5.3.3 ou plus](http://php.net/manual/fr/install.php) **avec support [PDO](http://php.net/manual/en/book.pdo.php)**
* [XML pour PHP](http://php.net/fr/xml)
* [PCRE](http://php.net/fr/pcre)
* [ZLib](http://php.net/en/zlib) (son absence affectera le traitement des pages compressées)
* [mbstring](http://php.net/en/mbstring) et/ou [iconv](http://php.net/en/iconv) (sinon, certaines pages ne pourront pas être lues - même en anglais)
* L'extension [DOM/XML](http://php.net/manual/en/book.dom.php)
* [Filtrage des données](http://php.net/manual/fr/book.filter.php)
* [GD](http://php.net/manual/en/book.image.php) (son absence empèchera la sauvegarde des images)
* [Tidy pour PHP](http://php.net/fr/tidy) (son absence peut poser problème avec certaines pages)
* [cURL](http://php.net/fr/curl) avec `Parallel URL fetching` (optionel)
* [Parse ini file](http://uk.php.net/manual/en/function.parse-ini-file.php) 
* [allow_url_fopen](http://www.php.net/manual/fr/filesystem.configuration.php#ini.allow-url-fopen) (optionel si cURL présent)
* [gettext](http://php.net/manual/fr/book.gettext.php) (nécessaire pour le support multilingues)

Installez les composants manquants avant de poursuivre. Par exemple pour installer Tidy sur Ubuntu/Debian :

    sudo apt-get install php5-tidy
    sudo service apache2 reload
    
Note : si voux utilisez IIS comme serveur web, vous devez interdire l'*Authentification Anonyme* et [permettre L'*Authentification de base*](https://technet.microsoft.com/fr-fr/library/cc772009%28v=ws.10%29.aspx) pour autoriser la connexion.

#### Twig installation

Pour pouvoir fonctionner, wallabag a besoin de `Twig`, une bibliothèque de modèles.
Si vous ne pouvez pas installer `composer` (dans le cas d'hébergement mutualisé par exemple), nous vous proposons un fichier
incluant `Twig`. Ce fichier peut être télécharger depuis la page `http://monserveur.com/wallabag/install/index.php` (section INSTALLATION TWIG) ou directement ici [http://wllbg.org/vendor](http://wllbg.org/vendor). Décompressez-le dans votre répertoire wallabag.

Alternativement, vous pouvez installer `Twig` en lançant `composer` depuis votre dossier wallabag (toujours dans le cas d'Ubuntu/Debian : <code>/var/www/html/wallabag/</code>) en exécutant les commandes :

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

### Création de la base de données

Wallabag peut s'installer sur différents types de bases de données :

* [SQLite](http://php.net/manual/fr/book.sqlite.php). Le plus simple de tous. Rien de particulier à configurer.
* [MySQL](http://php.net/manual/fr/book.mysql.php). Un système de base de données bien connu, qui est dans la plupart des cas plus efficace que SQLite.
* [PostgreSQL](http://php.net/manual/fr/book.pgsql.php). Certaines personnes l'ont trouvé mieux que MySQL.

Nous vous conseillons d'utiliser MySQL, plus performante. Il est alors nécessaire de créer une nouvelle base (par exemple `wallabag`), un nouvel utilisateur (par exemple  `wallabag`) et un mot de passe (ici `VotreMotdePasse`). Vous pouvez pour cela utiliser `phpMyAdmin`, ou exécuter les commandes suivantes :

    mysql -p -u root
    mysql> CREATE DATABASE wallabag;
    mysql> GRANT ALL PRIVILEGES ON `wallabag`.* TO 'wallabag'@'localhost' IDENTIFIED BY 'VotreMotdePasse';
    mysql> exit
    
*Note :* Si vous utilisez MySQL ou Postgresql, vous devrez **remplir tous les champs**, sinon l'installation ne fonctionera pas et un message d'erreur vous dira ce qui ne va pas. Vous devez créer manuellement la base de données qui sera utilisée par wallabag avec un outil comme PHPMyAdmin ou en ligne de commande.

### Permissions

Le serveur web doit avoir accès en écriture aux répertoires `assets`, `cache` et `db`. Sans cela, un message vous indiquera que l'installation est impossible :

    sudo chown -R www-data:www-data /var/www/html/wallabag

### Installation de wallabag. Enfin.

Accédez à wallabag depuis votre navigateur : `http://votreserveur.com/wallabag`. Si votre serveur est bien configuré, vous arrivez sur l'écran d'installation.

Renseignez le type de votre base de données (`sqlite`, `mysql` ou `postgresql`) et les informations de votre base de données. Dans le cas de la base MySQL créée plus haut, la configuration standard sera :

    Database engine:    MySQL
    Server:             localhost
    Database: 	        wallabag
    Username:	        wallabag
    Password:	        VotreMotdePasse

Créez enfin votre premier utilisateur et son mot de passe (différents de l'utilisateur de la base de données).

wallabag est maintenant installé.

### Connexion

Depuis votre navigateur, vous arrivez sur l'écran d'identification : saisissez votre identifiant et votre mot de passe et vous voici connecté.
