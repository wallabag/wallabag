# Restricted access articles

wallabag has a system to get articles behind a paywall, by providing your credentials when the article is fetched.

In version 2.2, only the administrator could put his credentials in a config file, making the feature accessible to all users of the instance. With 2.3, all users have a panel to enter their own credentials.

## List of compatible websites

### French

| Name | Available from version |
| ------|-------- |
| Arrêt sur Images | 2.2 |
| Courrier International | 2.3 |
| Le Figaro | 2.3 |
| Le Monde | 2.3 |
| Le Monde Diplomatique | 2.3 |
| Mediapart | 2.2 |
| Next INpact | 2.2 |

## Enable paywall authentication

In internal settings, as a wallabag administrator, in the **Article** section, enable authentication for websites with paywall (with the value `1`).

![Enable paywall authentication](../../../img/user/paywall_auth.png)

## Manage your site credentials

Once enable, you'll see a new item in the left menu: **Site Credentials**.

Click on it to go to the management of your site credentials. You'll be able to add many login / password.

> **[info] Information**
>
> These information will only be accessible by **YOU** and no other users on the wallabag instance.

## Security

Login and password you'll set will be encrypted in the database which means the database administrator (and/or the admin of your wallabag instance) won't be able to read your credentials.

If you need more technical information, we are using [that scenario](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md#scenario-1-keep-data-secret-from-the-database-administrator) to protect your information.

## Paywall availability

If a website with a paywall isn't available you can try to build a config for it.

See the [developer section](../../developer/paywall.md) about that.
