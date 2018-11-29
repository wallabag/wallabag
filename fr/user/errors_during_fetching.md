# Erreur durant la récupération des articles

## Pourquoi la récupération des articles échoue ?

Il peut y avoir plusieurs raisons :

-   problème de connexion internet ;
-   problème au niveau du serveur hébergeant l'article ;
-   wallabag ne peut pas récupérer le contenu à cause de la structure du site web.

Si la récupération de l'article échoue, il peut être utile d'essayer une nouvelle fois de récupérer le contenu en cliquant dans la barre latérale sur le bouton **Recharger le contenu**. (Note : ce bouton est également très utile pour recharger un article en prenant en compte les modifications locales sur les _site configs_, voir ci-dessous.)

![Réessayer de récupérer le contenu](../../img/user/refetch.png)


## Vérifier les logs de production pour les messages d'erreurs

Par défaut, si le contenu d'un site ne peut pas être correctement analysé à cause d'une erreur dans la requête (une page inexistante, un temps de réponse trop long etc.), un message d'erreur sera affiché dans le fichier `WALLABAG_DIR/var/logs/prod.log`.

Si vous voyez une ligne qui commence par `graby.ERROR` et qui correspond à votre période de test, c'est que la requête a échoué à cause d'une erreur. La nature de l'erreur peut déjà donner quelques indications sur son origine : une erreur `404` indiquera que wallabag n'a pas trouvé d'article à l'URL indiquée, une erreur `403` pourra indiquer que l'URL renvoie vers une page à l'accès interdit (ou que le site a mis en place des mesures pour empêcher la récupération de son contenu), une erreur `500` pourra indiquer un problème sur le serveur distant ou de votre connexion internet etc.

Merci d'indiquer tout le passage correspondant à l'erreur dans le ticket que vous ouvrirez sur [GitHub](https://github.com/wallabag/wallabag/issues).

## Le contenu n'est pas celui attendu ou il est incomplet

Wallabag utilise une conjonction de deux systèmes pour récupérer le contenu d'un article : l'utilisation de fichiers de configuration spécifiques à chaque domaine (souvent appelés _site config_, que vous pouvez trouver dans `vendor/j0k3r/graby-site-config/`) et _php-readability_, qui analyse automatiquement le contenu d'une page web pour déterminer ce qui a la plus de chance d'être le contenu recherché.

Ces systèmes ne sont pas infaillibles et il faudra parfois mâcher le travail à wallabag ! Afin de faciliter le travail des développeurs, il faut d'abord vérifier l'origine du problème en activant les logs détaillés de wallabag, puis éventuellement créer (ou mettre à jour) le fichier de configuration du site hébergeant l'article voulu.

### Vérifier sur **f43.me** si le problème est également présent

Une première vérification à faire est de tester l'URL sur ce site : [<http://f43.me/feed/test>](http://f43.me/feed/test). Celui-ci utilise globalement la même manière de fonctionner que wallabag pour récupérer les articles. Si ça fonctionne sur **f43.me** et pas sur wallabag, c'est qu'il y a un souci avec wallabag qui casse le parser (difficile à résoudre : merci d'ouvrir un [nouveau ticket à ce sujet sur GitHub](https://github.com/wallabag/wallabag/issues/new)).

Si vous hébergez votre propre instance de wallabag, vous pouvez nous joindre des logs détaillés qui nous serons très utile pour déterminer plus justement l'origine du problème (voir ci-après).

### Activer les logs (auto-hébergement)

Si l'étude des logs « basiques » n'a pas permis d'identifier une erreur criante et que vous n'avez pas le contenu de l'article, l'erreur est peut-être ailleurs. Dans ce cas, vous devrez activer les logs sur votre instance wallabag pour nous aider à trouver ce qui ne va pas.

- éditez le fichier `app/config/config_prod.yml` ;
- à [la ligne 18](https://github.com/wallabag/wallabag/blob/master/app/config/config_prod.yml#L18), remplacez `error` par `debug` ;
- videz le cache avec la commande `rm -rf var/cache/*` ;
- videz le contenu du fichier de log avec `cat /dev/null > var/logs/prod.log` ;
- rechargez votre instance wallabag et rechargez le contenu qui pose souci ;
- si vous ne réussissez pas à déterminer avec les logs l'origine du problème, copiez/collez le contenu du fichier `var/logs/prod.log` dans un nouveau [ticket d'incident GitHub](https://github.com/wallabag/wallabag/issues/new).

### Création ou mise à jour d'un fichier de configuration (_site config_)

Le plus souvent, les erreurs de récupération de contenu ne sont pas dues à une erreur du serveur distant mais se résument à des fichiers de configuration absents ou dépassés (suite par exemple à une refonte du site hébergeant le contenu). On pourra ainsi avoir le titre de l'article non renseigné, pas de corps d'article, des éléments surnuméraires ou manquants etc.

Vous pouvez essayer de résoudre ce problème vous-même en créant ou modifiant un fichier de configuration (comme ça, nous restons concentrés pour améliorer wallabag au lieu d'écrire ces fichiers de configuration :) ) ! De (très) nombreux exemples sont disponibles sur le dépôt [fivefilters/ftr-site-config](https://github.com/fivefilters/ftr-site-config) qui est le projet principal pour stocker les fichiers de configuration.

#### _Site config_ type

Pour un article hébergé à l'adresse `https://www.unsitedinformation.com/xxx/mon-article.html`, wallabag cherchera le fichier de configuration `unsitedinformation.com.txt` dans le dossier `vendor/j0k3r/graby-site-config`. Un fichier de configuration classique pourra se présenter ainsi :
```
# Titre de l'article
title: [XPath]

# Corps de l'article
body: [XPath]

# Élément(s) à supprimer du rendu final
strip: [XPath]

# Une URL de test, par exemple l'article sur lequel vous vous basez pour écrire ce fichier
test_url: https://www.unsitedinformation.com/xxx/mon-article.html
```

Les `[XPath]` correspondent aux adresses des éléments d'intérêt dans le code HTML de la page web ; les indiquer dans le fichier de configuration permet à wallabag d'aller chercher directement le contenu voulu sans devoir déterminer qui est quoi.

Vous pouvez déterminer ces chemins `XPath` à l'aide de [ce site web](http://siteconfig.fivefilters.org/) : chargez la page de votre article en indiquant son URL, puis sélectionnez la ou les parties du contenu qui correspondent à ce que vous souhaitez garder, le `XPath` est indiqué en bas de page. Vous pouvez également regarder directement le code de la page (`Ctrl`+`U` et/ou `F12` sur la plupart des navigateurs modernes) et déterminer les `XPath` à l'aide des règles décrites dans la partie suivante.

Il est possible d'indiquer d'autres éléments dans un fichier de configuration (date, auteur(s), suppressions généralisées etc.), nous vous conseillons la lecture de [cette documentation](https://help.fivefilters.org/full-text-rss/site-patterns.html#pattern-format) pour connaître l'étendue des possibles.

Enfin, il est possible de tester au fur et à mesure votre fichier de configuration, jusqu'à obtenir le résultat souhaité :

*   en enregistrant votre fichier de configuration dans `vendor/j0k3r/graby-site-config`, puis en rechargeant le contenu de l'article comme expliqué plus haut ;
*   sur [*f43.me*](https://f43.me/feed/test), en cliquant sur _Want to try a custom siteconfig?_ et en copiant le contenu de votre configuration avant de cliquer sur _Test_. Notez que vous aurez alors des informations supplémentaires après récupération en cliquant sur l'onglet _Debug_.

Une fois que votre fichier de configuration vous convient, vous pouvez créer une _pull request_ sur le dépôt rassemblant tous les fichiers de configuration : [fivefilters/ftr-site-config](https://github.com/fivefilters/ftr-site-config). (Note : même si vous ne maîtrisez pas _git_, il est possible de [créer ou de modifier des fichiers sur ce dépôt directement depuis votre navigateur web](https://help.github.com/articles/editing-files-in-another-user-s-repository/)). En attendant que les ajouts sur ce dépôt soient acceptés puis répercutés dans wallabag, vous pouvez conserver ce fichier de configuration dans le répertoire `vendor/j0k3r/graby-site-config` de votre wallabag (ces modifications sont cependant supprimées quand vous mettez à jour wallabag).

#### Quelques bases de XPath 1.0

