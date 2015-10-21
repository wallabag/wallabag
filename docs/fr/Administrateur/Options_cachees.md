---
language: Français
currentMenu: hidden
subTitle: Options cachées
---

# Options cachées

## Mise en garde

**Attention**, cette partie concerne les utilisateurs avancés. Nous allons modifier un fichier important de wallabag, `inc/poche/config.inc.php`, il est donc conseillé de faire une sauvegarde de celui-ci avant toute modification.  
**Toute erreur lors d'une modification d'un fichier de wallabag pourra entrainer des dysfonctionnements**.

Ce fichier est créé lorsque vous installez wallabag.  
Installez donc d'abord wallabag, faites une copie du fichier et ouvrez-le avec ~~Sublime Text~~ votre éditeur de texte préféré.

Dans ce fichier sont définis des paramètres qui ne sont, aujourd'hui, pas encore disponibles dans la page **Configuration** de wallabag.

## Modification des options avancées

Chaque paramètre est défini de cette façon :

    @define ('NOM_DU_PARAMETRE', 'Valeur du paramètre');

Pour chaque ligne, vous ne pouvez modifier que la partie `Valeur du paramètre`.

Listons maintenant les différents paramètres que vous pouvez changer :

* `HTTP_PORT` (par défaut, `80`) : correspond au port HTTP de votre serveur web. À changer si votre serveur web est derrière un proxy. Valeur attendue : un nombre.
* `SSL_PORT` (par défaut, `443`) : correspond au port SSL de votre serveur web. À changer si votre serveur web utilises SSLH. Valeur attendue : un nombre.
* `DEBUG_POCHE` (par défaut, `FALSE`) : si vous rencontrez des problèmes avec wallabag, nous vous demanderons peut-être d'activer le mode Debug. Valeurs attendues : `TRUE` ou `FALSE`.
* `DOWNLOAD_PICTURES` (par défaut, `FALSE`) : permet de télécharger sur votre serveur les images des articles. Ce paramètre est désactivé par défaut pour ne pas surcharger votre serveur web. Nous préférons vous laisser activer vous-même ce paramètre. Valeurs attendues : `TRUE` ou `FALSE`.
* `SHARE_TWITTER` (par défaut, `TRUE`) : permet d'activer le partage vers twitter. Valeurs attendues : `TRUE` ou `FALSE`.
* `SHARE_MAIL` (par défaut, `TRUE`) : permet d'activer le partage par email. Valeurs attendues : `TRUE` ou `FALSE`.
* `SHARE_SHAARLI` (par défaut, `FALSE`) : permet d'activer le partage vers votre installation de Shaarli (gestionnaire de favoris). Valeurs attendues : `TRUE` ou `FALSE`.
* `SHAARLI_URL` (par défaut, `'http://myshaarliurl.com'`) : définit l'URL de votre installation de Shaarli. Valeur attendue : une URL.
* `FLATTR` (par défaut, `TRUE`) : permet d'activer la possibilité de flattrer un article ([Flattr est une plateforme de micro-dons](http://fr.wikipedia.org/wiki/Flattr)). Si un article est flattrable, une icône s'affichera et vous permet d'envoyer un micro-don à l'auteur de l'article. Valeurs attendues : `TRUE` ou `FALSE`.
* `SHOW_PRINTLINK` (par défaut, `'1'`) : permet d'afficher le lien pour imprimer un article. Valeurs attendues : `'0'` pour désactiver ou `'1'` pour activer.
* `SHOW_READPERCENT` (par défaut, `'1'`) : permet d'afficher (sur les thèmes `default`, `dark`, `dmagenta`, `solarized`, `solarized-dark`) le pourcentage de lecture de l'article. Valeurs attendues : `'0'` pour désactiver ou `'1'` pour activer.
* `PAGINATION` (par défaut, `'12'`) : définit le nombre d'articles affichés sur une liste. Valeur attendue : un nombre.