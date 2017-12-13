# Upgrade your wallabag installation

You will find here different ways to upgrade your wallabag:

-   [from 2.2.x to 2.3.x](#upgrading-from-22x-to-23x)
-   [from 2.x.y to 2.3.x](#upgrading-from-2xy-to-23x)
-   [from 1.x to 2.x](#from-wallabag-1x)

## Upgrading from 2.2.x to 2.3.x

### Upgrade on a dedicated web server

If your current installation is < 2.2.x, please upgrade your instance to 2.2 before.

**From 2.2.x:**

```bash
make update
```

### Upgrade on a shared hosting

Backup your `app/config/parameters.yml` file.

Download the last release of wallabag:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

You will find the [md5 hash of the latest package on our
website](https://wallabag.org/en#download).

Extract the archive in your wallabag folder and replace
`app/config/parameters.yml` with yours.

Please check that your `app/config/parameters.yml` contains all the
required parameters. You can find [here a documentation about
parameters](./parameters.md).

If you use SQLite, you must also copy your `data/` folder inside the new
installation.

Empty `var/cache` folder.

You must run some SQL queries to upgrade your database. We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.

You may encounter issues with indexes names: if so, please change queries with the correct index name.

[You can find all the queries here](query-upgrade-22-23.md).

### Explanations about database migrations

During the update, we execute database migrations.

All the database migrations are stored in `app/DoctrineMigrations`. You
can execute each migration individually:
`bin/console doctrine:migrations:execute 20161001072726 --env=prod`.

You can also cancel each migration individually:
`bin/console doctrine:migrations:execute 20161001072726 --down --env=prod`.

Here is the migrations list for 2.2.x to 2.3.x release:

-   `20170327194233`: added the internal setting to share articles to
    Scuttle
-   `20170405182620`: added `published_at` and `published_by` fields on
    `entry` table
-   `20170407200919`: removed useless `is_public` field on `entry` table
-   `20170420134133`: removed useless `download_pictures` value in
    `craue_config_setting` table
-   `20170501115751`: added `site_credential` table to store username / password
    for websites with authentication
-   `20170510082609`: changed length for username, username_canonical, email and
    email_canonical fields in `user` table
-   `20170511115400`: added `headers` field on `entry` table
-   `20170511211659`: increased length of `quote` column on `annotation` table
-   `20170602075214`: added the internal setting to create user via the API
-   `20170606155640`: removed useless `wallabag_url` value in
    `craue_config_setting` table
-   `20170719231144`: changed tags to lowercase
-   `20170824113337`: added `starred_at` field on `entry` table
-   `20171008195606`: changed `reading_time` field to prevent null value
-   `20171105202000`: added `origin_url` field on `entry` table
-   `20171120163128`: added the internal setting to enable the storage of
    article headers
-   `20171125164500`: added the internal setting to enable the usage of the origin URL in shaarli sharing

## Upgrading from 2.x.y to 2.3.x

If your wallabag instance is < 2.2.0, there is no automatic script. You need to:

-   export your data
-   install wallabag 2.3.x ([read the installation documentation](./installation/))
-   import data in this fresh installation ([read the import documentation](../user/import/) )

## From wallabag 1.x

There is no automatic script to update from wallabag 1.x to wallabag
2.x. You need to:

-   export your data
-   install wallabag 2.x ([read the installation documentation](./installation/))
-   import data in this fresh installation ([read the import documentation](../user/import/) )
