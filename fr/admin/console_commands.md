# Actions en ligne de commande

wallabag a un certain nombre de commandes CLI pour effectuer des tâches.
Vous pouvez lister toutes les commandes en exécutant `bin/console` dans le
dossier d'installation de wallabag.

Chaque commande a une aide correspondante accessible via
`bin/console help %command%`.

<div class="admonition note">

Si vous êtes dans un environnement de production, souvenez-vous
d'ajouter `-e prod` à chaque commande.

</div>

Commandes notables
------------------

De Symfony:

-   `assets:install`: Peut-être utile si les *assets* sont manquants.
-   `cache:clear`: doit être exécuté après chaque mise à jour (appelé dans make update).
-   `doctrine:migrations:status`: Montre le statut de vos migrations de vos bases de données.
-   `fos:user:activate`: Activer manuellement un utilisateur.
-   `fos:user:change-password`: Changer le mot de passe pour un utilisateur.
-   `fos:user:create`: Créer un utilisateur.
-   `fos:user:deactivate`: Désactiver un utilisateur (non supprimé).
-   `fos:user:demote`: Supprimer un rôle d'un utilisateur, typiquement les droits d'administration.
-   `fos:user:promote`: Ajoute un rôle à un utilisateur, typiquement les droits d'administration.
-   `rabbitmq:*`: Peut-être utile si vous utilisez RabbitMQ.

Spécifique à wallabag:

- `wallabag:clean-duplicates`: Supprime tous les articles dupliqués pour un utilisateur ou bien tous.
- `wallabag:export`: Exporte tous les articles pour un utilisateur. Vous pouvez choisir le chemin du fichier exporté.
- `wallabag:import`: Importe les articles en différents formats dans un compte utilisateur.
- `wallabag:import:redis-worker`: Utile si vous utilisez Redis.
- `wallabag:install`: (ré)Installer wallabag
- `wallabag:tag:all`: Tagger tous les articles pour un utilisateur ou une utilisatrice en utilisant ses règles de tags automatiques.
- `wallabag:user:show`: Affiche les détails d'un utilisateur.

wallabag:clean-duplicates
-------------------------

Cette commande vous aide à faire le ménage dans les articles de l'utilisateur donné en supprimant les doublons.

Utilisation:

```
wallabag:clean-duplicates [<username>]
```

Arguments:

 - username: Utilisateur sur lequel travailler.


wallabag:export
---------------

Cette commande vous aide à exporter toutes les entrées pour un utilisateur.

Utilisation:

```
wallabag:export <username> [<filepath>]
```

Arguments:

 - username: Utilisateur sur lequel travailler.
 - filepath: Chemin vers le fichier exporté.


wallabag:import
---------------

Cette commande vous aide à importer des articles depuis un fichier JSON.

Utilisation:

```
wallabag:import [--] <username> <filepath>
```

Arguments:

 - username: Utilisateur sur lequel travailler.
 - filepath: Chemin vers le fichier JSON à importer.

Options:

 - `--importer=IMPORTER`: L'import à utiliser: v1, v2, instapaper, pinboard, readability, firefox ou chrome [par défaut: "v1"]
 - `--markAsRead=MARKASREAD`: Marquer toutes les entrées à lues [par défaut: false]
 - `--useUserId`: Utiliser un ID utilisateur plutôt que son nom d'utilisateur
 - `--disableContentUpdate`: Permet de désactiver la récupération des contenus depuis une URL


wallabag:import:redis-worker
----------------------------

Cette commande vous aide à lancer un worker Redis.

Utilisation:

```
wallabag:import:redis-worker [--] <serviceName>
```

Arguments:

 - serviceName: Service à utiliser par le worker: wallabag_v1, wallabag_v2, pocket, readability, pinboard, firefox, chrome ou instapaper

Options:

 - `--maxIterations[=MAXITERATIONS]`: Le nombre d'itérations que fera le worker avant de s'arrêter [par défaut: false]


wallabag:install
----------------

Cette commande vous aide à installer ou re-installer wallabag.

Utilisation:

```
wallabag:install
```


wallabag:tag:all
----------------

Cette commande vous aide à tagguer toutes les entrées en utilisant les règles automatique de taggages.

Utilisation:

```
wallabag:tag:all <username>
```

Arguments:
 - username: Utilisateur sur lequel travailler.


wallabag:user:show
------------------

Cette commande vous permet d'afficher les détails d'un utilisateur.

Utilisation:

```
wallabag:user:show <username>
```

Arguments:
 - username: Utilisateur à afficher.
