# Migrer depuis ...

Dans wallabag 2.x, vous pouvez importer des données depuis :

-   [Pocket](Pocket.md)
-   [Instapaper](Instapaper.md)
-   [Readability](Readability.md)
-   [Pinboard](Pinboard.md)
-   [wallabag 1.x](wallabagv1.md)
-   [wallabag 2.x](wallabagv2.md)

Nous avons aussi développé [un script pour exécuter des migrations via
la ligne de commande](#import-via-la-ligne-de-commande-cli).

Puisque les imports peuvent gourmands en ressource, nous avons mis en
place un système de tâche asynchrone. [Vous trouverez la documentation ici](../../admin/asynchronous.md)
(niveau expert).

## Import via la ligne de commande (CLI)

Si vous avez accès à la ligne de commandes de votre serveur web, vous
pouvez exécuter cette commande pour import votre fichier wallabag v1 :

```bash
bin/console wallabag:import 1 ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod
```

Remplacez les valeurs :

-   `1` est l'identifiant de votre utilisateur en base (l'ID de votre
    premier utilisateur créé sur wallabag est 1)
-   `~/Downloads/wallabag-export-1-2016-04-05.json` est le chemin de
    votre export wallabag v1

Si vous voulez marquer tous ces articles comme lus, vous pouvez ajouter
l'option `--markAsRead`.

Pour importer un fichier wallabag v2, vous devez ajouter l'option
`--importer=v2`.

Vous obtiendrez :

    Start : 05-04-2016 11:36:07 ---
    403 imported
    0 already saved
    End : 05-04-2016 11:36:09 ---
