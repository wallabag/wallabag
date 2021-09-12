# Upgrade your wallabag installation

You will find here different ways to upgrade your wallabag:

-   [from 2.3.x to 2.4.x](#upgrading-from-23x-to-24x)
-   [from 2.3.x to 2.3.y](#upgrading-from-23x-to-23y)
-   [from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x)
-   [from 2.x.y to 2.3.x](#upgrading-from-2xy-to-23x)
-   [from 1.x to 2.x](#from-wallabag-1x)

{% hint style="info" %}
But **first**, ensure you have `composer` installed on your server (or at least the `composer.phar` binary in the root directory of wallabag). If not, [please install it](https://getcomposer.org/download/).
{% endhint %}

## Upgrading from 2.3.x to 2.4.x

Same steps as for [upgrading from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x).

### For shared hosting

PHP version compatibility have changed, check the [release notes](https://github.com/wallabag/wallabag/releases/tag/2.4.0) for details.

[Apply the general steps for upgrades on the shared hosting](#upgrade-on-a-shared-hosting) with the following modifications :

Use these [data queries for the database upgrade to 2.4](./query-upgrade-23-24.md).

Four parameters were created and MUST be added to your `app/config/parameters.yml` file : `mailer_port`, `mailer_encryption`, `mailer_auth_mode` and `sentry_dsn` 

Do not forget to run `bin/console cache:clear --env=prod` in the wallabag directory afterwards


## Upgrading from 2.3.x to 2.3.y

```bash
make update
```

That's all.

If you got an error with `Not a git repository`, it means you didn't install wallabag using git but rather using an archive. [Follow steps for the shared hosting](#upgrade-on-a-shared-hosting) then.

## Upgrading from 2.2.x to 2.3.x

### Upgrade on a dedicated web server

```bash
make update
```

### Upgrade on a shared hosting

1. Backup your `app/config/parameters.yml` file.
1. Download the last release of wallabag:

    ```bash
    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
    ```

    You will find the [md5 hash of the latest package on our website](https://wallabag.org/en#download).

1. Extract the archive in your wallabag folder and replace `app/config/parameters.yml` with yours.
1. Please check that your `app/config/parameters.yml` contains all the parameters as they are **all mandatory**. You can find [here a documentation about parameters](./parameters.md).
1. I you have modified the `app/config/parameters.yml` file, run `bin/console cache:clear --env=prod` afterwards in the wallabag directory. A warning will appear if a parameter was forgotten.
1. If you use SQLite, you must also copy your `data/` folder inside the new installation.
1. Empty `var/cache` folder.
1. You must run some SQL queries to upgrade your database. We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.
1. You may encounter issues with indexes names: if so, please change queries with the correct index name.
1. [You can find all the queries here](./query-upgrade-22-23.html).

## Upgrading from 2.x.y to 2.3.x

If your wallabag instance is < 2.2.0, there is no automatic script. You need to:

-   export your data
-   install wallabag 2.3.x ([read the installation documentation](./installation/))
-   import data in this fresh installation ([read the import documentation](../user/import/) )

## From wallabag 1.x

There is no automatic script to update from wallabag 1.x to wallabag 2.x. You need to:

-   export your data
-   install wallabag 2.x ([read the installation documentation](./installation/))
-   import data in this fresh installation ([read the import documentation](../user/import/) )
