---
title: Requirements
weight: 1
---

wallabag is compatible with **PHP >= 7.4**.

{{< callout type="info" >}}
To install wallabag easily, we provide a `Makefile`, so you need to have the `make` tool.
{{< /callout >}}

## Composer

wallabag uses a large number of PHP libraries.
These libraries must be installed with a tool called **Composer**.

Check that the installed version is at least the 1.8.0:

    composer --version

If not, try to upgrade it using

    composer selfupdate

If that command isn't recognized, please, [re-install it](https://getcomposer.org/doc/00-intro.md).

## PHP Extensions

You'll also need the following extensions. Some of these may already activated, so you may not have to install all corresponding packages.

-   php-session
-   php-ctype
-   php-dom
-   php-hash
-   php-simplexml
-   php-json
-   php-gd
-   php-mbstring
-   php-xml
-   php-tidy
-   php-iconv
-   php-curl
-   php-gettext
-   php-tokenizer
-   php-bcmath
-   php-intl
-   php-fpm

wallabag uses PDO to connect to the database, so you'll need one of the following:

-   pdo_mysql
-   pdo_pgsql
-   pdo_sqlite

and its corresponding database server.
