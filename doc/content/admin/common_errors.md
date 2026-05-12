---
title: Common errors
weight: 2
---

Here is a list of common errors that we have seen in GitHub's issues.

## Migration script assumes quote table names are enabled for MySQL

If during migration you experience problems with MySQL with an error like this:

> SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '"wallabag_entry" ADD uuid LONGTEXT DEFAULT NULL' at line 1

This means you should enable the `ANSI_QUOTES` of `SQL_MODE`.

You can do that in your `app/config/config.yml` file:

```yaml
# Doctrine Configuration
doctrine:
    dbal:
        # ...
        options:
            # PDO::MYSQL_ATTR_INIT_COMMAND
            1002: "SET SQL_MODE=ANSI_QUOTES"
```

[Related issue](https://github.com/wallabag/wallabag/issues/3036)

## "Incorrect string value" with MySQL

If when adding an article you got a MySQL error like:

> SQLSTATE[HY000]: General error: 1366 Incorrect string value: '\xF0\x9F\x98\x89</...'

It means the collation of the database is wrong. MySQL's utf8 doesn't support emoji, utf8mb4 does.

You can update your MySQL server configuration with:

```
[mysqld]
character-set-client-handshake = FALSE
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

Also, [checks your database parameter `database_charset`]({{< relref "parameters.md#database-parameters" >}}).

[Related issue](https://github.com/wallabag/wallabag/issues/2976)
