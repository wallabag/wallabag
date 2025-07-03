---
title: Installation
weight: 2
---

## On a dedicated web server (recommended way)

To install wallabag itself, you must run the following commands:

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

If it's your first installation, you can safely answer "yes" when asking to reset the database.

Now, read the next step to create your virtual host, then
access your wallabag.

{{< callout type="info" >}}
To define parameters with environment variables, you have to set these variables with `SYMFONY__` prefix, for example, `SYMFONY__DATABASE_DRIVER`.
You can have a look at [Symfony documentation](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html).
{{< /callout >}}

{{< callout type="info" >}}
If you want to use SQLite to store your data, please put `%kernel.root_dir%/../data/db/wallabag.sqlite` for the `database_path` parameter during installation.
{{< /callout >}}

{{< callout type="info" >}}
If you're installing wallabag behind Squid as a reverse proxy, make sure to update your `squid.conf` configuration to include `login=PASS` in the `cache_peer` line. This is necessary for API calls to work properly.
{{< /callout >}}

## On shared hosting

We provide a package with all dependencies inside. The default
configuration uses MySQL for the database. To add the setting for your database, please edit `app/config/parameters.yml`. Beware that passwords must be surrounded by single quotes (').

We have already created a user: the login and password are `wallabag`.

With this package, wallabag doesn't check for mandatory extensions used
in the application (these checks are made during `composer install`
when you have a dedicated web server, see above).

Execute this command to download and extract the latest package:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

You will find the [md5 hash of the latest package on our website](https://wallabag.org/en#download).

The static package requires each command to be appended by `--env=prod` as the static package is only usable as a prod environment (dev environment is not supported and won't work at all).

Now, read the next step to create your virtual host.

You must create your first user by using the command `php bin/console wallabag:install --env=prod`
If an error occurs at this step due to bad settings, you must clear the cache with `php bin/console cache:clear --env=prod` before you try again the previous command.

Then you can access your wallabag.

## Usage of wallabag.it

[wallabag.it](https://wallabag.it) is a paid service to use wallabag without installing it on a web server.

This service always ships the latest release of wallabag. [You can create your account here](https://app.wallabag.it/). Try it for free: you'll get a 14-day free trial with no limitation (no credit card information required).

## Installation with Docker or Docker compose

### Command to launch container

This example starts wallabag at `http://localhost:8080` using SQLite backend and persists its data to Docker named volumes:

```bash
docker run \
  -v wallabag-data:/var/www/wallabag/data \
  -v wallabag-images:/var/www/wallabag/web/assets/images \
  -p 8080:80 -e "SYMFONY__ENV__DOMAIN_NAME=http://localhost:8080" \
  wallabag/wallabag
```

The default username and password are `wallabag:wallabag`. For more information, see [wallabag on Docker Hub](https://hub.docker.com/r/wallabag/wallabag/).

## Installation on Cloudron

Cloudron provides an easy way to install webapps on your server with a
focus on sysadmin automation and keeping apps updated. wallabag is
packaged as a Cloudron app and available to install directly from the
store.

[Install wallabag on your Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp2.html)

## Installation on YunoHost

YunoHost provides an easy way to install webapps on your server with a
focus on sysadmin automation and keeping apps updated. wallabag is
packaged as an official YunoHost app and is available to install directly from the
official repository.

[![Install wallabag with YunoHost](https://install-app.yunohost.org/install-with-yunohost.png)](https://install-app.yunohost.org/?app=wallabag2)

## Installation on alwaysdata

alwaysdata's Marketplace allows to easily install wallabag (and many other
applications) on a Public or Private Cloud.

[Install wallabag on alwaysdata](https://www.alwaysdata.com/en/marketplace/wallabag/)

## Installation on Synology

The SynoCommunity provides a package to install wallabag on your Synology NAS.

[Install wallabag with Synology](https://synocommunity.com/package/wallabag)
