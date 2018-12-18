# Erreur durant la récupération des articles

## Pourquoi la récupération des articles échoue ?

Il peut y avoir plusieurs raisons :

- problème de connexion internet ;
- problème au niveau du serveur hébergeant l'article ;
- wallabag ne peut pas récupérer le contenu à cause de la structure du site web.

Si la récupération de l'article échoue, il peut être utile d'essayer une nouvelle fois de récupérer le contenu en cliquant dans la barre latérale sur le bouton **Recharger le contenu**. (Note : ce bouton est également très utile pour recharger un article en prenant en compte les modifications locales sur les _site configs_, voir ci-dessous.)

![Réessayer de récupérer le contenu](../../img/user/refetch.png)

## Vérifier les logs de production pour les messages d'erreurs

Par défaut, si le contenu d'un site ne peut pas être correctement analysé à cause d'une erreur dans la requête (une page inexistante, un temps de réponse trop long etc.), un message d'erreur sera affiché dans le fichier `WALLABAG_DIR/var/logs/prod.log`.

Si vous voyez une ligne qui commence par `graby.ERROR` et qui correspond à votre période de test, c'est que la requête a échoué à cause d'une erreur. La nature de l'erreur peut déjà donner quelques indications sur son origine :

 - une erreur `404` indiquera que wallabag n'a pas trouvé d'article à l'URL indiquée ;
 - une erreur `403` pourra indiquer que l'URL renvoie vers une page à l'accès interdit (ou que le site a mis en place des mesures pour empêcher la récupération de son contenu) ;
 - une erreur `500` pourra indiquer un problème sur le serveur distant ou de votre connexion internet ;
 - une erreur `504` ou `408` pourra indiquer que le serveur met trop longtemps à répondre etc.

Merci d'indiquer tout le passage correspondant à l'erreur dans le ticket que vous ouvrirez sur [GitHub](https://github.com/wallabag/wallabag/issues).

## Le contenu n'est pas celui attendu ou il est incomplet

Wallabag utilise une conjonction de deux systèmes pour récupérer le contenu d'un article :

- l'utilisation de fichiers de configuration spécifiques à chaque domaine (souvent appelés _site config_, que vous pouvez trouver dans `vendor/j0k3r/graby-site-config/`) ;
- [php-readability](https://github.com/j0k3r/php-readability), qui analyse automatiquement le contenu d'une page web pour déterminer ce qui a la plus de chance d'être le contenu recherché.

Ces systèmes ne sont pas infaillibles et il faudra parfois mâcher le travail à wallabag ! Afin de faciliter le travail des développeurs, il faut d'abord vérifier l'origine du problème en activant les logs détaillés de wallabag, puis éventuellement créer (ou mettre à jour) le fichier de configuration du site hébergeant l'article voulu.

### Vérifier sur **f43.me** si le problème est également présent

Une première vérification à faire est de tester l'URL sur ce site : [<http://f43.me/feed/test>](http://f43.me/feed/test). Celui-ci utilise globalement la même manière de fonctionner que wallabag pour récupérer les articles. Si ça fonctionne sur **f43.me** et pas sur wallabag, c'est qu'il y a un souci avec wallabag qui casse le parser (difficile à résoudre : merci d'ouvrir un [nouveau ticket à ce sujet sur GitHub](https://github.com/wallabag/wallabag/issues/new)).

Si vous hébergez votre propre instance de wallabag, vous pouvez nous joindre des logs détaillés qui nous serons très utile pour déterminer plus justement l'origine du problème (voir ci-après).

### Activer les logs (auto-hébergement)

Si l'étude des logs « basiques » n'a pas permis d'identifier une erreur criante et que vous n'avez pas le contenu de l'article, l'erreur est peut-être ailleurs. Dans ce cas, vous devrez activer les logs sur votre instance wallabag pour nous aider à trouver ce qui ne va pas.

- dans le fichier `app/config/config_prod.yml`, modifiez la section `monolog` comme suit :
```yaml
monolog:
    handlers:
        main:
            type: fingers_crossed
            action_level: error
            handler: nested
            channels: ['!graby']
        nested:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
        graby:
            type: stream
            path: "%kernel.logs_dir%/graby.log"
            level: debug
            channels: ['graby']
        console:
            type: console
```
- videz le cache avec la commande `rm -rf var/cache/*` ;
- rechargez votre instance wallabag et rechargez le contenu qui pose souci.

Si vous ne réussissez pas à déterminer avec les logs l'origine du problème, copiez/collez le contenu du fichier `var/logs/graby.log` dans un nouveau [ticket d'incident GitHub](https://github.com/wallabag/wallabag/issues/new).

### Création ou mise à jour d'un fichier de configuration (_site config_)

Le plus souvent, les erreurs de récupération de contenu ne sont pas dues à une erreur du serveur distant mais se résument à des fichiers de configuration absents ou dépassés (par exemple, suite à une refonte du site hébergeant le contenu). On pourra ainsi avoir le titre de l'article non renseigné, pas de corps d'article, des éléments surnuméraires ou manquants etc.

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

Vous pouvez déterminer ces chemins _XPath_ à l'aide de [ce site web](http://siteconfig.fivefilters.org/) : chargez la page de votre article en indiquant son URL, puis sélectionnez la ou les parties du contenu qui correspondent à ce que vous souhaitez garder, le _XPath_ est indiqué en bas de page. Vous pouvez également regarder directement le code de la page (`Ctrl`+`U` et/ou `F12` sur la plupart des navigateurs modernes) et déterminer les _XPath_ à l'aide des règles décrites dans la partie suivante.

Il est possible d'indiquer d'autres éléments dans un fichier de configuration (date, auteur(s), suppressions généralisées etc.), nous vous conseillons la lecture de [cette documentation](https://help.fivefilters.org/full-text-rss/site-patterns.html#pattern-format) pour connaître l'étendue des possibles.

Enfin, il est possible de tester au fur et à mesure votre fichier de configuration, jusqu'à obtenir le résultat souhaité :

* en enregistrant votre fichier de configuration dans `vendor/j0k3r/graby-site-config`, puis en rechargeant le contenu de l'article comme expliqué plus haut ;
* sur [*f43.me*](https://f43.me/feed/test), en cliquant sur _Want to try a custom siteconfig?_ et en copiant le contenu de votre configuration avant de cliquer sur _Test_. Notez que vous aurez alors des informations supplémentaires après récupération en cliquant sur l'onglet _Debug_.

Une fois que votre fichier de configuration vous convient, vous pouvez créer une _pull request_ sur le dépôt rassemblant tous les fichiers de configuration : [fivefilters/ftr-site-config](https://github.com/fivefilters/ftr-site-config). (Note : même si vous ne maîtrisez pas _git_, il est possible de [créer ou de modifier des fichiers sur ce dépôt directement depuis votre navigateur web](https://help.github.com/articles/editing-files-in-another-user-s-repository/)). En attendant que les ajouts sur ce dépôt soient acceptés puis répercutés dans wallabag, vous pouvez conserver ce fichier de configuration dans le répertoire `vendor/j0k3r/graby-site-config` de votre wallabag (ces modifications sont cependant supprimées quand vous mettez à jour wallabag).

#### Quelques bases de XPath 1.0

_XPath_ (pour _XML Path Language_) est une norme permettant de décrire précisément le chemin d'accès à un élément dans un document _XML_, et _a fortiori_ dans une page web. Ces chemins sont principalement déterminés par les relations parent/enfant(s) des balises HTML (`<div></div>`, `<a></a>`, `<p></p>`, `<section></section>` etc.) et leur(s) attribut(s) (`class`, `id`, `src`, `href` etc.). La norme est illisible, mais [cette section](https://www.w3.org/TR/1999/REC-xpath-19991116/#path-abbrev) donne un bon résumé de ce qui est possible.

Quelques exemples étant cependant plus parlants, nous nous baserons sur le (faux) document suivant :
```html
<html>
    <head>
        <!-- metadonnées de la page web -->
    </head>
    <body>
        <div class="header">
            <header>
                <h1>Le titre de mon site</h1>
            </header>
        </div>
        <div itemprop="articleBody">
            <article>
                <h1>Le titre de mon article</h1>
                <p class="author">par Jean Dupont</p>
                <section>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

                    <p><strong>Lire aussi : </strong><a href="http://...">Lien vers un autre article</a></p>

                    <div class="ads spam">Une publicité.</div>
                </section>
                <section>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                </section>
            </article>
        </div>
        <div id="footer">
            <footer>
                <!-- plein de publicités -->
            </footer>
        </div>
    </body>
</html>
```

##### Sélection du titre

Le titre du document est contenu dans le _header_ de plus haut niveau, c'est-à-dire `<h1>[...]</h1>`. Son _XPath_ complet est `/html/body/div[2]/article/h1` : partant de la racine `/`, il faut aller dans la balise `html`, puis `body`, puis la seconde `div` rencontrée, puis dans `article` pour enfin arriver au `h1`. (Notez que mon `header` contient également un `h1` correspondant au titre du site.) Cependant, ce chemin complet est beaucoup trop complexe dans le cas de pages avec beaucoup d'éléments imbriqués ; il existe donc un certain nombre de raccourcis utiles.

Le premier est `//` qui permet de sauter un nombre indéfini de balises avant (ou entre) les éléments indiqués :
* `//h1` sélectionnerait à la fois le titre du site et de l'article ;
* `//div//h1` sélectionnerait également le titre du site et de l'article (les balises `header` et `article` étant ici ignorées) ;
* `//article/h1` permet de sélectionner le(s) `h1` directement contenus dans `article`, donc le titre de l'article !

Enfin, il est recommandé lorsque cela est possible de s'appuyer sur les attributs des balises. Il paraît en effet logique de différencier les `h1` par le fait que l'un est contenu dans la `div` possédant `class="header"` et l'autre dans la `div` possédant `itemprop="ArticleBody`. Pour cela, on ajoutera au nom de la balise `[@class="header"]` ou `[@itemprop="articleBody"]` :
* `//div[@class="header"]//h1` sélectionnerait le titre du site ;
* `//div[@itemprop="articleBody"]//h1` sélectionnerait le titre de l'article.

_Note : dans ce cas très simple, comme les attributs sont différents, on pourrait mettre uniquement `//div[@itemprop]//h1` sans préciser la valeur de l'attribut. Cela est cependant moins restrictif et a donc plus de chances de poser des problèmes dans un document complexe._

Le fichier de configuration de ce site pourra au final indiquer `title: //div[@itemprop="articleBody"]//h1` ou `title: //article/h1`.

##### Sélection du corps et suppression des éléments superflus

La sélection du corps de l'article est ici assez triviale, tout étant contenu dans la balise `article` et ses enfants. On pourra ainsi indiquer dans notre fichier de configuration `body: //article` ou `body: //div[@itemprop="articleBody"]`.

Cependant, l'article récupéré contiendra alors les publicités contenues dans `<div class="ads spam">[...]</div>`, ainsi que des liens que l'on peut vouloir supprimer (_Lire aussi : [...]_). Heureusement, les fichiers de configuration permettent d'utiliser une instruction `strip: [XPath]` pour enlever les éléments superflus !

_XPath_ ne permet pas d'identifier une valeur parmi d'autres dans un attribut : les chemins `//div[@class="ads"]` ou `//div[@class="spam"]` n'indiqueront pas celui du bloc contenant ici une publicité !
La fonction `contains()` permet de chercher une chaine de caractère dans une autre (ici dans la valeur de l'attribut `class`) : `//div[contains(@class, "ads")]` ou `//div[contains(@class, "spam")]` réussiront à sélectionner le contenu souhaité.

**Cette solution avec `contains()` n'est toutefois à utiliser que dans des cas très simples.** En effet, ces chemins pourront également sélectionner des `div` dont la classe est `pads`, `mads`, `adsaaaaaaaa` etc. Pour sélectionner précisément une balise dont un attribut est composé d'une liste, il faudra utiliser l'instruction barbare suivante :

**`//div[contains(concat(' ', normalize-space(@class), ' '), ' ads ')]`**

Au final, le fichier de configuration pourra contenir indifféremment :
```
strip: //div[contains(concat(' ', normalize-space(@class), ' '), ' ads ')]
strip: //div[contains(concat(' ', normalize-space(@class), ' '), ' spam ')]

# Mais cela fonctionne aussi (très restrictif) !
strip: //div[@class="ads spam"]
```

Enfin, nous souhaitons supprimer les liens connexes de l'article afin de ne pas perturber la lecture dans wallabag, et il faudra donc sélectionner le paragraphe suivant :
```html
<p><strong>Lire aussi : </strong><a href="...">Lien vers un autre site</a></p>
```
Ici, il n'est pas possible de sélectionner le paragraphe grâce à un attribut et on ne peut décemment pas supprimer toutes les balises `strong` ou `a` du document. _XPath_ permet toutefois de préciser le ou les enfant(s) que doit avoir une balise pour être sélectionnée, avec la notation `//balise[enfant]`.

_Note : ne pas confondre la notation `//balise[@attribut]` (p. ex. `//div[@class="..."]`) et `//balise[enfant]` (p.ex. `//p[strong]`). De même, ne pas confondre `//p/strong` qui sélectionne la balise `strong` et son contenu avec `//p[strong]` qui sélectionne le(s) paragraphe(s) contenant au moins une balise `strong`._

Comme de multiples types de paragraphes peuvent contenir un lien `a` ou une balise `strong`, on pourra restreindre le chemin en utilisant l'opérateur `and` : `//p[strong and a]` permettra ainsi de sélectionner seulement un paragraphe ayant les deux éléments. Pour cibler encore plus précisément ce paragraphe, on pourra aussi examiner le contenu d'une balise avec `contains()`, pour finalement obtenir dans notre fichier de configuration :

`strip: //p[contains(strong, 'Lire') and a]`

#### Liens utiles

Si vous souhaitez plus d'informations sur _XPath_, la norme [contient un résumé assez clair de ce qui est faisable](https://www.w3.org/TR/1999/REC-xpath-19991116/#path-abbrev). Le site **devhints.io** a également [une anti-sèche très complète sur _XPath_](https://devhints.io/xpath).

Si vous voulez tester de manière dynamique si un _XPath_ fonctionne pour sélectionner un ou plusieurs éléments d'une page, vous pouvez également utiliser [ce bac à sable](http://www.whitebeam.org/library/guide/TechNotes/xpathtestbed.rhtm) qui évalue des chemins sur un code HTML/XML que vous pouvez _uploader_.
