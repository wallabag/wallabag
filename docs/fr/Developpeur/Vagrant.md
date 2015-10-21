---
language: Français
currentMenu: vagrant
subTitle: Vagrant
---

# Vagrant

Pour vous permettre de mettre en place rapidement la configuration requise pour wallabag, nous vous proposons un fichier Vagrantfile.

[Définition Wikipedia](http://fr.wikipedia.org/wiki/Vagrant)

    Vagrant est un logiciel libre et open-source pour la création et la configuration des environnements de développement virtuel. Il peut être considéré comme un wrapper autour du logiciel de virtualisation comme VirtualBox.

## Utiliser Vagrant pour wallabag

Voici la procédure pour exécuter wallabag au sein d'un conteneur Vagrant :

    wget -O wallabag-dev.zip https://github.com/wallabag/wallabag/archive/dev.zip
    unzip wallabag-dev.zip
    cd wallabag-dev
    vagrant up

Accédez maintenant à `http://localhost:8003` et à vous de jouer !

## Qu'a installé le Vagrantfile ?

Le script installe un serveur LAMP, à savoir :

* Ubuntu 14.04
* Un serveur web Apache2
* PHP5
* SQLite ou MySQL ou PostgreSQL pour PHP
* XDebug pour PHP
