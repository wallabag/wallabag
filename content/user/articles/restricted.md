---
title: Restricted access
weight: 5
---

wallabag has a system to get articles behind a paywall, by providing your credentials when the article is fetched.

In version 2.2, only the administrator could put his credentials in a config file, making the feature accessible to all users of the instance. With 2.3, all users have a panel to enter their own credentials.

## List of compatible websites

| Name | Available from version |
| ------|-------- |
| Alternatives Economiques | 2.3 |
| Arrêt sur Images | 2.2 |
| Canard PC | 2.3 |
| Courrier International | 2.3 |
| GameKult | 2.3 |
| Le Figaro | 2.3 |
| Le Monde | 2.3 |
| Le Monde Diplomatique | 2.3 |
| Le Point | 2.3 |
| LWN.net | 2.3 |
| Mediapart | 2.2 |
| Next INpact | 2.2 |
| Reflets.info | 2.3 |
| The Economist | 2.3 |


## Enable paywall authentication

In internal settings, as a wallabag administrator, in the **Article** section, enable authentication for websites with paywall (with the value `1`).

![Enable paywall authentication](../../../img/user/paywall_auth.png)

## Manage your site credentials

Once enable, you'll see a new item in the top-right menu: **Site Credentials**.

Click on it to go to the management of your site credentials. You'll be able to add many login / password.

{{< callout type="info" >}}
These information will only be accessible by **YOU** and no other users on the wallabag instance.
{{< /callout >}}

## Security

Login and password you'll set will be encrypted in the database which means the database administrator won't be able to read your credentials. _However_, the server administrator might have access (if it retrieves the encryption key and then manually decode your credentials).

If you need more technical information, we are using [that scenario](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md#scenario-1-keep-data-secret-from-the-database-administrator) to protect your information.

## Paywall availability

If a website with a paywall isn't available you can try to build a config for it.

See the [developer section]({{< relref "../../developer/paywall.md" >}}) about that.
