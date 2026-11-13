---
title: Upgrade
weight: 10
---

You will find here different ways to upgrade your wallabag:

-   [from 2.6.x to 2.7.x](#upgrading-from-26x-to-27x)
-   [from 2.5.x to 2.6.x](#upgrading-from-25x-to-26x)
-   [from 2.3.x to 2.4.x](#upgrading-from-23x-to-24x)
-   [from 2.3.x to 2.3.y](#upgrading-from-23x-to-23y)
-   [from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x)
-   [from 2.x.y to 2.3.x](#upgrading-from-2xy-to-23x)
-   [from 1.x to 2.x](#from-wallabag-1x)

{{< callout type="info" >}}
But **first**, ensure you have `composer` installed on your server (or at least the `composer.phar` binary in the root directory of wallabag). If not, [please install it](https://getcomposer.org/download/).
{{< /callout >}}

{{< callout type="info" >}}
The historical upgrade notes below keep the configuration names that were used
at the time of each release. Current wallabag releases are configured with
environment variables instead. See [Parameters]({{< relref "../parameters.md" >}})
for the current variable list and the legacy-to-env mapping.
{{< /callout >}}

## Upgrading from 2.6.x to 2.7.x

{{< callout type="warning" >}}
Back up your database, your `app/config/parameters.yml` file and your `data/` folder before upgrading.
{{< /callout >}}

### What changed

-   **PHP 8.2 or later is required**. PHP 7.4, 8.0 and 8.1 are no longer supported.
-   **wallabag is now configured with environment variables**. Your `app/config/parameters.yml` file is still read, so you can keep it for now, but it is deprecated and will be removed in wallabag 3.0. See [Parameters]({{< relref "../parameters.md" >}}) for the list of variables and how each legacy key maps to them. The `rss_limit` key is no longer used.
-   **The front controller is now `web/index.php`**. `web/app.php` still works but is deprecated. If you switch your web server configuration to `web/index.php`, set `APP_ENV=prod` (in your web server environment or in `.env.local`), otherwise wallabag starts in the `dev` environment.
-   **Built assets moved from `web/wallassets/` to `web/build/`**. Update your web server configuration if it references `wallassets`.
-   Pages and API endpoints you are not allowed to access now return a 404 error instead of a 403 error.
-   Public registration is disabled by default (`WALLABAG_REGISTRATION_ENABLED=0`). If you keep `parameters.yml`, your `fosuser_registration` value is still used.
-   A compromised-password check can be enabled on user forms with `WALLABAG_COMPROMISED_PASSWORD_CHECK_ENABLED`. It is disabled by default.
-   The MOBI export, the Pocket API import and the demo mode have been removed. You can still import from Pocket with the CSV export.
-   On MySQL and MariaDB, the database migrations convert the remaining `utf8mb3` tables to `utf8mb4`. This can take a while on large databases.
-   If you configure wallabag only with environment variables (without `parameters.yml`), `APP_SECRET` is required: it has no default value.
-   Import files are now stored in `var/import`, outside of the `web` folder. If you upgrade from 2.6.15 or an older version, delete the content of the `web/uploads/import` folder: it may contain files left by previous imports.

### Upgrade on a dedicated web server

```bash
make update
```

`make update` checks out the latest release tag of the repository (pre-releases such as alpha or beta versions are skipped), installs the dependencies and runs the database migrations.

### Upgrade on a shared hosting

1. Back up your database. If you use SQLite, it is in the `data/` folder copied below.
1. Download and extract the 2.7.x package in a new folder.
1. Copy from your current installation:
    - `app/config/parameters.yml` (or set the equivalent environment variables, see [Parameters]({{< relref "../parameters.md" >}})),
    - the `data/` folder: it contains the key used to encrypt your site credentials, and your database if you use SQLite,
    - the `web/assets/images/` folder if you enabled images download.
1. Run the database migrations, then clear the cache:

    ```bash
    php bin/console doctrine:migrations:migrate --env=prod --no-interaction
    php bin/console cache:clear --env=prod
    ```

## Upgrading from 2.5.x to 2.6.x

Same steps as for [upgrading from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x).

⚠️ **There are two points to focus on for that update**:

1. We added new fields in the database, don't forget to run migration (by running `make update`) otherwise your wallabag won't work.
2. We've updated the mailer config which needs to be replicated otherwise the image might not work.

   We removed these fields from `app/config/parameters.yml`:
   - `mailer_transport`
   - `mailer_user`
   - `mailer_password`
   - `mailer_host`
   - `mailer_port`
   - `mailer_encryption`
   - `mailer_auth_mode`

   And we added `mailer_dsn` as a replacement. Here is [an example of DSN](https://symfony.com/doc/4.4/mailer.html#using-built-in-transports): `smtp://user:pass@smtp.example.com:port`

[You can find all the queries here]({{< relref "query-upgrade-25-26.md" >}}).

## Upgrading from 2.3.x to 2.4.x

Same steps as for [upgrading from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x).

### For shared hosting

PHP version compatibility has changed. Check the [release notes](https://github.com/wallabag/wallabag/releases/tag/2.4.0) for details.

[Apply the general steps for upgrades on shared hosting](#upgrade-on-a-shared-hosting) with the following modifications:

Use these [data queries for the database upgrade to 2.4]({{< relref "query-upgrade-23-24.md" >}}).

Four parameters were created and MUST be added to your `app/config/parameters.yml` file: `mailer_port`, `mailer_encryption`, `mailer_auth_mode` and `sentry_dsn`.

Do not forget to run `bin/console cache:clear --env=prod` in the wallabag directory afterwards.

## Upgrading from 2.3.x to 2.3.y

```bash
make update
```

That's all.

If you get an error with `Not a git repository`, it means you installed wallabag using an archive rather than git. In this case, [follow the steps for shared hosting](#upgrade-on-a-shared-hosting).

## Upgrading from 2.2.x to 2.3.x

### Upgrade on a dedicated web server

```bash
make update
```

### Upgrade on a shared hosting

1. Backup your `app/config/parameters.yml` file.
1. Download the latest release of wallabag:

    ```bash
    wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
    ```

    You will find the [md5 hash of the latest package on our website](https://wallabag.org/en#download).

1. Extract the archive in your wallabag folder and replace `app/config/parameters.yml` with yours.
1. Please check that your `app/config/parameters.yml` contains all the parameters as they are **all mandatory**. You can find documentation about parameters [here]({{< relref "parameters.md" >}}).
1. If you have modified the `app/config/parameters.yml` file, run `bin/console cache:clear --env=prod` afterwards in the wallabag directory. A warning will appear if any parameter is missing.
1. If you use SQLite, you must also copy your `data/` folder into the new installation.
1. Empty the `var/cache` folder.
1. You must run SQL queries to upgrade your database. We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.
1. You may encounter issues with indexes names: if so, please change queries with the correct index name.
1. You can find all the required queries in the [query upgrade documentation]({{< relref "query-upgrade-22-23.md" >}}).

## Upgrading from 2.x.y to 2.3.x

If your wallabag instance is < 2.2.0, there is no automatic script. You need to:

-   export your data
-   install wallabag 2.3.x ([read the installation documentation]({{< relref "../installation/" >}}))
-   import data in this fresh installation ([read the import documentation]({{< relref "../../user/import/" >}}) )

## From wallabag 1.x

There is no automatic script to update from wallabag 1.x to wallabag 2.x. You need to:

-   export your data
-   install wallabag 2.x ([read the installation documentation]({{< relref "../installation/" >}}))
-   import data in this fresh installation ([read the import documentation]({{< relref "../../user/import/" >}}) )
