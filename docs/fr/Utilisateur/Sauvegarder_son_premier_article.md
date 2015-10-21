---
language: Français
currentMenu: save_article
subTitle: Sauvegarder son premier article
---

# Sauvegarder son premier article

Une fois connecté sur wallabag, vous avez plusieurs moyens pour sauvegarder un article.

## Depuis l'application web

Voyons d'abord comment faire depuis l'application web. Dans le menu, vous avez un lien **Sauvegarder un lien**. En cliquant dessus, un formulaire s'affiche : vous n'avez qu'à saisir l'adresse internet de l'article concerné.

Validez et le contenu de l'article est enregistré.

Par défaut, seul le texte est sauvegardé. Si vous souhaitez également conserver une copie des images sur votre serveur, il faut activer le paramètre `DOWNLOAD_PICTURES`. Lisez le chapitre [Les options cachées](../Administrateur/Options_cachees.md) pour en savoir plus.

## Depuis le bookmarklet

[Définition Wikipedia](http://fr.wikipedia.org/wiki/Bookmarklet)

    Un bookmarklet est un petit programme JavaScript pouvant être stocké :
    * soit en tant qu'URL dans un signet (marque-page ou lien favori) avec la plupart des navigateurs Web ;
    * soit en tant qu'hyperlien dans une page web.

Depuis le menu de wallabag, cliquez sur **configuration**. Dans la première partie de cette page, nous avons listé les différents moyens de sauvegarder un article. Vous trouverez ainsi le bookmarklet (c'est le lien `bag it !`) à glisser / déposer dans la barre de favoris de votre navigateur.

Dorénavant, lorsque vous souhaitez sauvegarder un article sur lequel vous êtes en train de surfer, vous n'avez qu'à cliquer sur ce bookmarklet et l'article sera automatiquement enregistré.

## Depuis son smartphone
### Avant toute chose

Pour pouvoir utiliser une application smartphone, vous devez activer les flux RSS depuis la partie **configuration** de wallabag. Certaines informations seront ainsi affichées, comme votre **token** (jeton de sécurité). Lisez le chapitre [Flux RSS](Flux_RSS.md) pour en savoir plus.

### Android
#### Installation et configuration

Vous pouvez télécharger l'application Android depuis le [Google Play Store](https://play.google.com/store/apps/details?id=fr.gaulupeau.apps.InThePoche) et depuis [F-droid](https://f-droid.org/app/fr.gaulupeau.apps.InThePoche). C'est exactement la même application sur ces deux plateformes de téléchargement.

Une fois installée, démarrez l'application, rendez-vous dans la partie **settings** et renseignez les champs **URL** (Adresse complète de votre installation de wallabag ou de votre compte Framabag), **User ID** (très souvent, il vous faudra mettre 1 comme valeur). Si vous avez créé plusieurs comptes depuis wallabag, il faudra saisir l'identifiant du compte que vous souhaitez connecter à votre application) et **Token** (recopiez bien tous les caractères du token, disponible dans la **configuration** de wallabag).

#### Sauvegarde d'un article

Maintenant que tout est bien configuré, dès que vous naviguerez avec le navigateur de votre smartphone, vous pourrez à tout moment partager un article dans wallabag depuis le menu **Partager** : vous trouverez une entrée **Bag it!** qui ajoutera l'article dans wallabag.

#### Lecture

Lorsque vous ouvrez l'application, cliquez sur **Synchronize** : vos articles dernièrement sauvegardés seront ainsi téléchargés sur votre smartphone.

Vous n'avez maintenant plus besoin de connexion internet : cliquez sur le bouton **List articles** pour commencer votre lecture.

En bas de chaque article, un bouton **Mark as read** vous permet d'archiver l'article.

Aujourd'hui, la synchronisation ne s'effectue que dans un sens (de wallabag vers l'application), ce qui empêche de marquer comme lu un article sur wallabag depuis votre smartphone.

### iOS
#### Installation et configuration
TODO

#### Utilisation
TODO

### Windows Phone
#### Installation et configuration

Vous pouvez télécharger l'application Windows Phone depuis le [Windows Store](http://www.windowsphone.com/fr-fr/store/app/wallabag/ff890514-348c-4d0b-9b43-153fff3f7450) ou directement dans le Store de votre smartphone.

Une fois installée, l'application affichera une notification au premier lancement, demandant la configuration du serveur wallabag. Rendez-vous dans la partie **Configuration** de l'application en appuyant sur les 3 petits points du menu en bas de l'écran, puis renseignez les champs **URL** (Adresse complète de votre installation de wallabag ou de votre compte Framabag), **User ID** (très souvent, il vous faudra mettre 1 comme valeur).  
Si vous avez créé plusieurs comptes depuis wallabag, il faudra saisir l'identifiant du compte que vous souhaitez connecter à votre application) et **Token** (recopiez bien tous les caractères du token, disponible dans la **configuration** de wallabag).  
Enfin, sauvegardez les paramètres entrés.

## Depuis son navigateur
### Extension Firefox classique

Téléchargez l'extension Firefox [sur le site addons.mozilla.org](https://addons.mozilla.org/firefox/addon/wallabag/) et installez-la comme toute autre extension Firefox.

Dans les préférences de l'extension, renseignez l'URL complète de votre installation de wallabag ou de votre compte Framabag.

Personnalisez la barre d'outils de Firefox pour ajouter wallabag (icône `w`). Lorsque vous vous trouvez sur un article que vous souhaitez sauvegarder, cliquez sur cette icône : une nouvelle fenêtre s'ouvrira pour ajouter l'article et elle se refermera automatiquement.

### Extension Mozilla Services (Social API)

*Disponible uniquement à compter de wallabag 1.9.1*

Avec les versions 29 et supérieures de Firefox, votre navigateur possède une interface intégrée permettant le partage direct vers de multiples réseaux sociaux. Dans l'interface de Firefox, elle est symbolisée par un icône en forme d'avion en papier que vous pourrez utiliser pour partager une page, ce qui signifie ici enregistrer un article dans wallabag.
Vous pouvez ajouter ce service depuis la page de configuration de wallabag en cliquant sur Extension Mozilla Services (Social API). Vous devez aussi accepter l'utilisation des Services Firefox.

### Chrome

Téléchargez l'extension Chrome [sur le site dédié](https://chrome.google.com/webstore/detail/wallabag/bepdcjnnkglfjehplaogpoonpffbdcdj) et installez-la comme toute autre extension Chrome.

Dans les options de l'extension, renseignez l'URL complète de votre installation de wallabag ou de votre compte Framabag.

Lors de l'installation de l'extension, une nouvelle icône est apparue dans la barre d'outils de Chrome, une icône `w`. Lorsque vous vous trouvez sur un article que vous souhaitez sauvegarder, cliquez sur cette icône : une popup s'ouvrira et vous confirmera que l'article a bien été sauvegardé.

### Opera

Les dernières versions d'Opera (15+) permettent d'installer des extensions compatibles avec Chrome.

Il faut tout d'abord installer l'extension [Download Chrome Extensions](https://addons.opera.com/en/extensions/details/download-chrome-extension-9/) pour installer des extensions à partir du Chrome Web Store. Ensuite, on peut se rendre [sur le site de Google](https://chrome.google.com/webstore/detail/wallabag/bepdcjnnkglfjehplaogpoonpffbdcdj) et récupérer l'extension Chrome en cliquant sur *Add to Opera*. Vous obtiendrez un message qui vous invitera à confirmer l'action car l'extension ne provient pas d'une source approuvée. Le comportement sera ensuite le même que pour Chrome (ci-dessus).
