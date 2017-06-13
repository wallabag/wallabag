# What are the internal settings for?

The internal settings page is only available for the instance administrator. It allows to handle more sensible settings, such as activating some features.

## Analytics

### Enable Piwik

`1` to include the Javascript code for [Piwik](https://piwik.org/).

### Host of your website in Piwik (without `http://` ou `https://`)

URL for your Piwik server.

### ID of your website in Piwik

ID of your website inside Piwik, available in `Paramètres` -> `Sites web` -> `Gérer`.

For instance:

![ID de votre site dans Piiwk](../../img/admin/id_piwik.png)

## Article

### Enable share to Carrot

`1` to show the share button for [Carrot.org](https://secure.carrot.org/), `0` to deactivate.

### Diaspora URL, if the service is enabled

URL of your Diaspora\* instance.

### Enable authentication for websites with paywall

`1` to activate authentification for articles with a paywall (ex: Mediapart, Next INpact, etc.).

### Shaarli URL, if the service is enabled

URL Shaarli instance.

### Enable share to Diaspora

`1` to show the share button for [Diaspora\*](https://diasporafoundation.org/), `0` to deactivate.

### Enable share by email

`1` to show the share button for email, `0` to deactivate.

### Allow public url for entries

`1` to allow to publicly share articles, `0` to deactivate.

### Enable share to Shaarli

`1` to show the share button for [Shaarli](https://github.com/shaarli/Shaarli), `0` to deactivate.

### Enable share to Twitter

`1` to show the share button for [Twitter](https://twitter.com/), `0` to deactivate.

### Enable share to Unmark.it

`1` pour activer le partage vers [Unmark.it](https://unmark.it/), `0` to deactivate.

### Display a link to print content

`1` to show the print button, `0` to deactivate.

### Unmark.it URL, if the service is enabled

URL of your Unmark.it instance.

## Export

### Enable CSV export

`1` to activate CSV export, `0` to deactivate.

### Enable ePub export

`1` to activate ePub export, `0` to deactivate.

### Enable JSON export

`1` to activate JSON export, `0` to deactivate.

### Enable .mobi export

`1` to activate .mobi export, `0` to deactivate.

### Enable PDF export

`1` to activate PDF export, `0` to deactivate.

### Enable TXT export

`1` to activate TXT export, `0` to deactivate.

### Enable XML export

`1` to activate XML export, `0` to deactivate.

## Import

### Enable RabbitMQ to import data asynchronously

`1` to activate RabbitMQ, `0` to deactivate (see [Asynchronous tasks](../asynchronous.md)).

### Enable Redis to import data asynchronously

`1` to activate Redis, `0` to deactivate (see [Asynchronous tasks](../asynchronous.md)).

## Misc

### Enable demo mode ? (only used for the wallabag public demo)

`1` to activate demo mode, `0` to deactivate (it's not possible to modify the user account).

### Demo user

Username of the account used for demo.

### Download images locally

`1` to activate local pictures downloading, `0`, to deactivate.

Once this feature activated, the articles pictures will be downloaded in the `/web/assets/images` folder of your wallabag instance. The path of pictures in articles will also be updated to the path of pictures which are on your instance.

### Support URL for wallabag

URL used in emails sent by wallabag for any help request.
