---
title: Requirements
weight: 1
---

wallabag is compatible with **PHP >= 7.4**, including **PHP 8.4** since wallabag 2.6.15.

### PHP 8.4 and legacy dependencies

The 2.6 release series retains legacy dependencies. PHP 8.4 may emit deprecation
notices, and `composer check-platform-reqs` may report outdated PHP upper bounds
in these packages even though the application runs with them.

Install the dependencies using `composer install` and the supplied `composer.lock`.
Keep the Composer PHP platform override at 7.4.29 to preserve compatibility with
older supported PHP versions. A general dependency update or
`--ignore-platform-reqs` is not required for installation.

In production, set `display_errors = Off` and `log_errors = On` in the PHP
configuration used by your web server. This keeps notices out of HTTP responses
without hiding errors from the logs. CLI and web server PHP configurations may
differ.

{{< callout type="info" >}}
To install wallabag easily, we provide a `Makefile`, so you need to have the `make` tool.
{{< /callout >}}

## Composer

wallabag uses a large number of PHP libraries.
These libraries must be installed with a tool called **Composer**.

Check that the installed version is at least 1.8.0:

    composer --version

If not, try to upgrade it using

    composer selfupdate

If that command isn't recognized, please [re-install it](https://getcomposer.org/doc/00-intro.md).

## PHP Extensions

You'll also need the following extensions. Some of these may already be activated, so you may not have to install all corresponding packages.

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
