---
language: Français
currentMenu: site_config
subTitle: Écrire un fichier de configuration
---

# Écrire un fichier de configuration

wallabag peut utiliser des fichiers de configuration spécifiques à un site pour lire les articles de ce site. Ces fichiers sont stockés dans le répertoire [`inc/3rdparty/site_config/standard`](https://github.com/wallabag/wallabag/tree/master/inc/3rdparty/site_config/standard).

Le format utilisé pour ces fichiers est [XPath](http://www.w3.org/TR/xpath20/). Inspirez-vous des exemples dans le répertoire pour en créer de nouveaux.

## Génération automatique de fichiers de configuration

@FiveFilters a créé un [outil très utile](http://siteconfig.fivefilters.org/) pour créer des fichiers de configuration. Vous devez taper l'adresse d'un article qui vous intéresse. puis vous sélectionnez le contenu que vous souhaitez.

![siteconfig](https://lut.im/RNaO7gGe/l9vRnO1b)

Vous devez confirmer cette zone en essayant avec d'autres articles.
Quand vous avez trouvé la bonne zone, cliquez simplement sur *Download Full-Text RSS site config* (Téléchargez la configuration du site Full-Text RSS) pour télécharger le fichier à inclure dans le répertoire.

## Génération manuelle de fichiers de configuration

Si l'outil de FiveFilters ne marche pas tel qu´attendu, regardez la source d'un article (Ctrl+U sur Firefox ou Chromium). Cherchez votre contenu parmi le code source et repérez l'attribut `class` ou `id` de la zone que vous souhaitez.

Une fois que vous avez obtenu l'attribut `id` ou `class`, vous pouvez écrire par exemple l'une ou l'autre de ces lignes :

```
body: //div[@class='myclass']
body: //div[@id='myid']
```

Ensuite, testez votre fichier de configuration avec d'autres articles du même site. Si vous avez trouvé le bon contenu mais que vous voulez enlever des parties inutiles, ajoutez la ligne suivante (avec l'attribut `class` correspondant à la partie inutile) :

```
strip: //div[@class='hidden']
```

Vous pouvez regarder d'autres options pour les fichiers de configuration de sites [sur l'aide du site de  FiveFilters](http://help.fivefilters.org/customer/portal/articles/223153-site-patterns).
