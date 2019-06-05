# À quoi sert la configuration interne ?

La configuration interne n'est disponible que pour l'administrateur de l'instance.
Elle permet de gérer des paramètres plus sensibles, comme l'activation de certaines fonctionnalités.

## Analytics

### Activer Piwik

`1` pour afficher le code Javascript pour [Piwik](https://piwik.org/).

### URL de votre site dans Piwik (sans `http://` ou `https://`)

URL de votre instance Piwik.

### ID de votre site dans Piwik

Identifiant de votre site dans Piwik, disponible dans `Paramètres` -> `Sites web` -> `Gérer`.

Par exemple :

![ID de votre site dans Piwik](../../img/admin/id_piwik.png)

## Article

### Activer le partage vers Carrot

`1` pour activer le partage vers [Carrot.org](https://secure.carrot.org/), `0` pour désactiver.

### URL de Diaspora, si le service Diaspora est activé

URL de votre instance Diaspora\*.

### Activer l'authentification pour les articles derrière un paywall

`1` pour activer l'authentification pour les articles derrière un abonnement (ex: Mediapart, Next INpact, etc.).

### URL de Shaarli, si le service Shaarli est activé

URL de votre instance Shaarli.

### Activer le partage vers Diaspora

`1` pour activer le partage vers [Diaspora\*](https://diasporafoundation.org/), `0` pour désactiver.

### Activer le partage par email

`1` pour activer le partage par email, `0` pour désactiver.

### Autoriser une URL publique pour les articles

`1` pour permettre de partager publiquement des articles, `0` pour désactiver.

### Activer le partage vers Shaarli

`1` pour activer le partage vers [Shaarli](https://github.com/shaarli/Shaarli), `0` pour désactiver.

### Activer le partage vers Twitter

`1` pour activer le partage vers [Twitter](https://twitter.com/), `0` pour désactiver.

### Activer le partage vers Unmark.it

`1` pour activer le partage vers [Unmark.it](https://unmark.it/), `0` pour désactiver.

### Afficher un lien pour imprimer

`1` pour activer l'impression, `0` pour désactiver.

### URL de Unmark, si le service Unmark.it est activé

URL de votre instance Unmark.it.

## Export

### Activer l'export CSV

`1` pour activer l'export CSV, `0` pour le désactiver.

### Activer l'export ePub

`1` pour activer l'export ePub, `0` pour le désactiver.

### Activer l'export JSON

`1` pour activer l'export JSON, `0` pour le désactiver.

### Activer l'export .mobi

`1` pour activer l'export .mobi, `0` pour le désactiver.

### Activer l'export PDF

`1` pour activer l'export PDF, `0` pour le désactiver.

### Activer l'export TXT

`1` pour activer l'export TXT, `0` pour le désactiver.

### Activer l'export XML

`1` pour activer l'export XML, `0` pour le désactiver.

## Import

### Activer RabbitMQ

`1` pour activer RabbitMQ, `0` pour le désactiver (cf [Tâches asynchrones](../asynchronous.md)).

### Activer Redis

`1` pour activer Redis, `0` pour le désactiver (cf [Tâches asynchrones](../asynchronous.md)).

## Divers

### Activer le mode démo ?

`1` pour activer le mode démo, `0` pour le désactiver (il est impossible de modifier le compte utilisateur).

### Utilisateur de la démo

Nom d'utilisateur du compte utilisé pour la démo.

### Télécharger les images en local

`1` pour activer le téléchargement des images en local, `0`, pour désactiver.

Une fois cette fonctionnalité activée, les images des articles seront téléchargées dans le répertoire `/web/assets/images` de votre instance wallabag. De plus, le chemin des images dans les articles sera remplacé par l'image qui se trouve sur votre instance.

{% hint style="tip" %}
Si vous voulez que les GIFs restent animés, installer l'extension PHP `imagick`. C'est généralement disponible dans un paquet de votre distribution avec comme nom `php-imagick`.
{% endhint %}

### URL de support de wallabag

URL utilisée dans les emails envoyés par wallabag pour toute demande d'aide.
