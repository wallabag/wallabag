# How to contribute

## Test it locally

### Using Docker

- Clone the repository
- Ensure your Docker daemon is running
- Launch `docker-compose up`

You'll then have:
- a web server (nginx)
- a PHP daemon (using FPM)
- a Redis database (to handle imports)
- a SQLite database to store articles

You can now access your wallabag instance using that url: `http://127.0.0.1:8000`

If you want to test using an other database than SQLite, uncomment the `postgres` or `mariadb` code from the `docker-compose.yml` file at the root of the repo. Also uncomment related line in the `php` section so the database will be linked to your PHP instance.

### Using your own PHP server

- Ensure you are running PHP > 7.1.
- Clone the repository
- Launch `composer install`
- If you got some errors, fix them (they might be related to some missing PHP extension from your machine)
- Then `php bin/console wallabag:install`
- If you got some errors, fix them (they might be related to some missing PHP extension from your machine)
- Run `php bin/console server:run`

You can now access your wallabag instance using that url: `http://127.0.0.1:8000`

## You found a bug
Please [open a new issue](https://github.com/wallabag/wallabag/issues/new).

To fix the bug quickly, we need some infos: please answer to the questions in the issue form.

If you have the skills, look for errors into PHP, server and application logs (see `var/logs`).

Note : If you have large portions of text, use [Github's Gist service](https://gist.github.com/) or other pastebin-like.

## You want to fix a bug or to add a feature
Please fork wallabag and work with **the master branch**.
