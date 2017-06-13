# À quoi sert la configuration interne ?

La configuration interne n'est disponible que pour l'administrateur de l'instance.
Elle permet de gérer des paramètres plus sensibles, comme l'activation de certaines fonctionnalités.

## Analytics

### Enable Piwik

`1` pour afficher le code Javascript pour [Piwik](https://piwik.org/).

### Host of your website in Piwik (without `http://` ou `https://`)

URL de votre instance Piwik.

### ID of your website in Piwik

Identifiant de votre site dans Piwik, disponible dans `Paramètres` -> `Sites web` -> `Gérer`.

Par exemple :

![ID de votre site dans Piiwk](../../img/admin/id_piwik.png)

## Article

### Enable share to Carrot

`1` pour activer le partage vers [Carrot.org](https://secure.carrot.org/), `0` pour désactiver.

### Diaspora URL, if the service is enabled

URL de votre instance Diaspora*.

### Enable authentication for websites with paywall

`1` pour activer l'authentification pour les articles derrière un abonnement (ex: Mediapart, Next INpact, etc.).

### Shaarli URL, if the service is enabled

URL de votre instance Shaarli.

### Enable share to Diaspora

`1` pour activer le partage vers [Diaspora*](https://diasporafoundation.org/), `0` pour désactiver.

### Enable share by email

`1` pour activer le partage par email, `0` pour désactiver.

### Allow public url for entries

`1` pour permettre de partager publiquement des articles, `0` pour désactiver.

### Enable share to Shaarli

`1` pour activer le partage vers [Shaarli](https://github.com/shaarli/Shaarli), `0` pour désactiver.

### Enable share to Twitter

`1` pour activer le partage vers [Twitter](https://twitter.com/), `0` pour désactiver.

### Enable share to Unmark.it

`1` pour activer le partage vers [Unmark.it](https://unmark.it/), `0` pour désactiver.

### Display a link to print content

`1` pour activer l'impression, `0` pour désactiver.

### Unmark.it URL, if the service is enabled

URL de votre instance Unmark.it.

## Export

### Enable CSV export

`1` pour activer l'export CSV, `0` pour le désactiver.

### Enable ePub export

`1` pour activer l'export ePub, `0` pour le désactiver.

### Enable JSON export

`1` pour activer l'export JSON, `0` pour le désactiver.

### Enable .mobi export

`1` pour activer l'export .mobi, `0` pour le désactiver.

### Enable PDF export

`1` pour activer l'export PDF, `0` pour le désactiver.

### Enable TXT export

`1` pour activer l'export TXT, `0` pour le désactiver.

### Enable XML export

`1` pour activer l'export XML, `0` pour le désactiver.

## Import

### Enable RabbitMQ to import data asynchronously

`1` pour activer RabbitMQ, `0` pour le désactiver (cf [Tâches asynchrones](../asynchronous.md)).

### Enable Redis to import data asynchronously

`1` pour activer Redis, `0` pour le désactiver (cf [Tâches asynchrones](../asynchronous.md)).

## Misc

### Enable demo mode ? (only used for the wallabag public demo)

`1` pour activer le mode démo, `0` pour le désactiver (il est impossible de modifier le compte utilisateur).

### Demo user

Nom d'utilisateur du compte utilisé pour la démo.

### Download images locally

`1` pour activer le téléchargement des images en local, `0`, pour désactiver.

Une fois cette fonctionnalité activée, les images des articles seront téléchargées dans le répertoire `/web/assets/images` de votre instance wallabag. De plus, le chemin des images dans les articles sera remplacé par l'image qui se trouve sur votre instance.

### Support URL for wallabag

URL utilisée dans les emails envoyés par wallabag pour toute demande d'aide.

### URL of *your* wallabag instance

URL de votre instance de wallabag, utilisée pour le chemin des images, lorsque la fonctionnalité de téléchargement des images est active.
