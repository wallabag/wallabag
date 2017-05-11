# Pré-requis

wallabag est compatible avec **PHP &gt;= 5.6**, PHP 7 inclus.

Pour installer wallabag facilement, nous avons créé un `Makefile`, vous
avez donc besoin d'avoir installé l'outil `make`.

wallabag utilise un grand nombre de bibliothèques PHP pour fonctionner.
Ces bibliothèques doivent être installées à l'aide d'un outil nommé
Composer. Vous devez l'installer si ce n'est déjà fait et vous assurer
que vous utilisez bien la version 1.2 (si vous avez déjà Composer, faite
un `composer selfupdate`).

Installation de Composer :

```bash
curl -s https://getcomposer.org/installer | php
```

Vous pouvez trouver des instructions spécifiques [ici (en
anglais)](https://getcomposer.org/doc/00-intro.md).

Vous aurez besoin des extensions suivantes pour que wallabag fonctionne.
Il est possible que certaines de ces extensions soient déjà activées
dans votre version de PHP, donc vous n'avez pas forcément besoin
d'installer tous les paquets correspondants.

-   php-session
-   php-ctype
-   php-dom
-   php-hash
-   php-simplexml
-   php-json
-   php-gd
-   php-mbstring
-   php-xml
-   php-tidy
-   php-iconv
-   php-curl
-   php-gettext
-   php-tokenizer
-   php-bcmath

wallabag utilise PDO afin de se connecter à une base de données, donc
vous aurez besoin d'une extension et d'un système de bases de données
parmi :

-   pdo_mysql
-   pdo_sqlite
-   pdo_pgsql
