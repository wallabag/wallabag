Exécuter wallabag avec docker-compose
=====================================

Pour faire tourner votre propre instance de développement de wallabag, vous pouvez
utiliser les fichiers docker pré-configurés.

Pré-requis
----------

Soyez sur d'avoir `Docker
<https://docs.docker.com/installation/ubuntulinux/>`__ et `Docker
Compose <https://docs.docker.com/compose/install/>`__ installés et à jour sur votre
système.

Changer de SGBD
---------------

Par défaut, wallabag fonctionne avec une base de données SQLite.
Depuis que wallabag supporte Postgresql et MySQL, les conteneurs Docker sont
aussi disponibles pour ces SGBD.

Dans ``docker-compose.yml``, en fonction de votre SGBD, décommentez :

- la définition du conteneur (le block racine ``postgres`` ou ``mariadb``)
- le conteneur ``links`` dans le conteneur ``php``
- le conteneur ``env_file`` dans le conteneur ``php``

Pour que les commandes Symfony (par exemple ``wallabag:install``) continuent de
fonctionner sur votre système, vous devez aussi :

- charger le bon fichier d'environnement dans votre ligne de commandes (``source``),
pour que les variables comme ``WALLABAG_DATABASE_HOST`` existent.
- ajouter une ligne ``127.0.0.1 rdbms`` dans votre fichier ``hosts``

Exécuter wallabag
-----------------

#. Forker et cloner le projet
#. ``composer install`` pour installer les dépendances
#. ``php bin/console wallabag:install`` pour créer le schéma de la BDD
#. ``docker-compose up`` pour démarrer les conteneurs
#. Enfin, se rendre sur http://localhost:8080/ pour accéder à une installation
    tout propre de wallabag.

Il est possible de rencontrer des problèmes de droits UNIX, de mauvais chemins
dans les fichiers de cache, etc…
Les opérations comme vider le cache ou restaurer les permissions des fichiers
peuvent être fréquemment nécessaires, n'ayez crainte !
