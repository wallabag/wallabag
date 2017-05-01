Actions en ligne de commande
============================

wallabag a un certain nombre de commandes CLI pour effectuer des tâches. Vous pouvez lister toutes les commandes en exécutant `bin/console` dans le dossier d'installation de wallabag.

Chaque commande a une aide correspondante accessible via `bin/console help %command%`.

.. note::

    Si vous êtes dans un environnement de production, souvenez-vous d'ajouter `-e prod` à chaque commande.

Commandes notables
------------------

* `assets:install`: Peut-être utile si les *assets* sont manquants.
* `cache:clear`: doit être exécuté après chaque mise à jour (appelé dans `make update`).
* `doctrine:migrations:status`: Montre le statut de vos migrations de vos bases de données.
* `fos:user:activate`: Activer manuellement un utilisateur.
* `fos:user:change-password`: Changer le mot de passe pour un utilisateur.
* `fos:user:create`: Créer un utilisateur.
* `fos:user:deactivate`: Désactiver un utilisateur (non supprimé).
* `fos:user:demote`: Supprimer un rôle d'un utilisateur, typiquement les droits d'administration.
* `fos:user:promote`: Ajoute un rôle à un utilisateur, typiquement les droits d'administration.
* `rabbitmq:*`: Peut-être utile si vous utilisez RabbitMQ.
* `wallabag:clean-duplicates`: Supprime tous les articles dupliqués pour un utilisateur ou bien tous.
* `wallabag:export`: Exporte tous les articles pour un utilisateur. Vous pouvez choisir le chemin du fichier exporté.
* `wallabag:import`: Importe les articles en différents formats dans un compte utilisateur.
* `wallabag:import:redis-worker`: Utile si vous utilisez Redis.
* `wallabag:install`: (ré)Installer wallabag
* `wallabag:tag:all`: Tagger tous les articles pour un utilisateur ou une utilisatrice en utilisant ses règles de tags automatiques.
