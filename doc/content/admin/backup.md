---
title: Backup
weight: 4
---

If you make a mistake with your wallabag and lose data,
or you need to move your wallabag to another server, you'll want to
backup your data. This article describes what you need to backup.

## Basic settings

wallabag stores deploy-time settings through environment variables. Back up the
place where you define them, for example `.env.local`, `docker/php/env`, or
your web server / PHP-FPM configuration. If you upgraded an older installation
and still keep `app/config/parameters.yml`, back up that file too until you
finish migrating away from it.

## Database

As wallabag supports different kinds of databases, the way to perform the
backup depends on the database you use, so you need to refer to the
vendor documentation.

Here's some examples:

-   MySQL: <http://dev.mysql.com/doc/refman/5.7/en/backup-methods.html>
-   PostgreSQL:
    <https://www.postgresql.org/docs/current/static/backup.html>

### SQLite

To backup the SQLite database, you just need to copy the directory
data/db from the wallabag application directory.

## Pictures

The pictures retrieved by wallabag are stored under `web/assets/images`.
