# Requirements

wallabag is compatible with **PHP >= 5.6**, including PHP 7.1.

> **[info] Information**
>
> To install wallabag easily, we provide a `Makefile`, so you need to have the `make` tool.

wallabag uses a large number of PHP libraries in order to function.
These libraries must be installed with a tool called Composer. You need
to install it if you have not already done so and be sure to use the 1.2
version (if you already have Composer, run a `composer selfupdate`).

Install Composer:

    curl -s https://getcomposer.org/installer | php

You can find specific instructions
[here](https://getcomposer.org/doc/00-intro.md).

You'll also need the following extensions for wallabag to work. Some of
these may already activated in your version of PHP, so you may not have
to install all corresponding packages.

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

wallabag uses PDO to connect to the database, so you'll need one of the
following:

-   pdo_mysql
-   pdo_pgsql
-   pdo_sqlite

and its corresponding database server.
