Sauvegarde de wallabag
======================

Parce que des fois vous faites des erreurs avec votre installation de wallabag et vous perdez des données ou parce que vous souhaitez migrer votre installation sur un autre serveur, vous souhaitez faire une sauvegarde de vos données.
Cette documentation décrit ce que vous devez sauvegarder.

Configuration
-------------
wallabag stocke quelques paramètres (comme la configuration SMTP ou les infos de bases de données) dans le fichier `app/config/parameters.yml`.

Base de données
---------------
Comme wallabag supporte différentes sortes de bases de données, la manière de sauvegarder dépend du type de base de données que vous utilisez, donc vous devez vous en référez à la documentation correspondante.

Quelques exemples :

- MySQL: http://dev.mysql.com/doc/refman/5.7/en/backup-methods.html
- PostgreSQL: https://www.postgresql.org/docs/current/static/backup.html

SQLite
~~~~~~
Pour sauvegarder une base SQLite, vous devez juste copier le répertoire `data/db` de votre installation wallabag.

Images
------
Les images sauvegardées par wallabag sont stockées dans `web/assets/images` (le stockage des images sera implémenté dans wallabag 2.2).
