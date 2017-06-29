Erreur durant la récupération des articles
==========================================

Pourquoi la récupération des articles échoue ?
----------------------------------------------

Il peut y avoir plusieurs raisons :

-   problème de connexion internet
-   wallabag ne peut pas récupérer le contenu à cause de la structure du
    site web

Comment puis-je aider pour réparer ça ?
---------------------------------------

Vous pouvez essayer de résoudre ce problème vous même (comme ça, nous
restons concentrés pour améliorer wallabag au lieu d'écrire ces fichiers
de configuration :) ).

Vous pouvez essayer de voir si ça fonctionne ici :
[<http://f43.me/feed/test>](http://f43.me/feed/test) (ce site utilise
principalement la même manière de fonctionner que wallabag pour
récupérer les articles).

Si ça fonctionne ici et pas sur wallabag, c'est qu'il y a un souci avec
wallabag qui casse le parser (difficile à résoudre : merci d'ouvrir un
nouveau ticket à ce sujet).

Si ça ne fonctionne pas, vous pouvez essayer de créer un fichier de
configuration en utilisant :
[<http://siteconfig.fivefilters.org/>](http://siteconfig.fivefilters.org/)
(sélectionnez les parties du contenu qui correspondent à ce que vous
souhaitez garder). Vous pouvez [lire cette documentation
avant](http://help.fivefilters.org/customer/en/portal/articles/223153-site-patterns).

Vous pouvez tester ce fichier sur le site **f43.me** : cliquez sur
**Want to try a custom siteconfig?** et insérez le fichier généré depuis
siteconfig.fivefilters.org.

Répétez cette opération jusqu'à avoir quelque chose qui vous convienne.

Ensuite, vous pouvez créer une pull request ici
[<https://github.com/fivefilters/ftr-site-config>](https://github.com/fivefilters/ftr-site-config),
qui est le projet principal pour stocker les fichiers de configuration.
Si vous ne voulez pas attendre que votre PR soit mergée, vous pouvez mettre vos fichiers de config dans le répertoire `vendor/j0k3r/graby-site-config` de votre wallabag. Mais ces modifications seront supprimées quand vous mettez à jour wallabag.



Comment puis-je réessayer de récupérer le contenu ?
---------------------------------------------------

Si wallabag échoue en récupérant l'article, vous pouvez cliquer sur le
bouton suivant (le troisième sur l'image ci-dessous).

![Réessayer de récupérer le contenu](../../img/user/refetch.png)
