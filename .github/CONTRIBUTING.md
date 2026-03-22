# How to contribute

## Test it locally

### Using Docker

- Clone the repository
- Ensure your Docker daemon is running
- Copy `docker/php/env.example` to `docker/php/env` and customize
- Launch `make dev-docker-up`
- Launch `make dev-setup`

The Docker `php` service serves wallabag on `http://127.0.0.1:8000`. Use `make dev-docker-down` when you want to tear the stack down and reset Docker volumes.

Run `make dev-watch` in another terminal while working on frontend assets so Encore rebuilds them automatically.

You'll then have:
- a PHP daemon with standalone web server
- a Redis database (to handle imports)
- a SQLite database to store articles

You can now access your wallabag instance using that url: `http://127.0.0.1:8000`

If you want to test using an other database than SQLite, uncomment the `postgres` or `mariadb` code from the `compose.yaml` file at the root of the repo. Also uncomment related line in the `php` section so the database will be linked to your PHP instance.

### Using Podman

- Clone the repository
- Copy `docker/php/env.example` to `docker/php/env` and customize
- Bootstrap and build:

```sh
# Build container image
(cd docker/php && podman build . -t wallabag-dev)

# Bootstrap PHP dependencies
podman run --replace --name wallabag_app --user 0:0 --env-file docker/php/env --volume "$(pwd):/var/www/html" wallabag-dev composer install

# Bootstrap your installation
podman run --replace --name wallabag_app --user 0:0 --env-file docker/php/env --volume "$(pwd):/var/www/html" wallabag-dev bin/console wallabag:install

# Bootstrap frontend dependencies
podman run --replace --name wallabag_app --user 0:0 --env-file docker/php/env --volume "$(pwd):/var/www/html" wallabag-dev yarn install

# Build frontend assets
podman run --replace --name wallabag_app --user 0:0 --env-file docker/php/env --volume "$(pwd):/var/www/html" wallabag-dev yarn build:dev
```

- Start the stack:

```sh
# Start Redis container
podman run --replace --detach --name wallabag_redis -p 6379:6379 redis:6-alpine

# Start Wallabag container
podman run --replace --name wallabag_app -p 8000:8000 --user 0:0 --env-file docker/php/env --volume "$(pwd):/var/www/html" wallabag-dev
```

### Using your own PHP server

- Ensure you are running PHP >= 8.2.
- Clone the repository
- Run `make dev` to bootstrap wallabag and start the built-in server in `dev`
- If you got some errors, fix them (they might be related to some missing PHP extension from your machine)
- If you only need to start the built-in server later, run `make run`
- Run `make dev-watch` in another terminal if you are changing frontend assets

You can now access your wallabag instance using that url: `http://127.0.0.1:8000`

### Database configuration

The project ships a `.env` file (committed) with `DATABASE_URL` defaulting to SQLite. For local development with a different database, create a `.env.local` file at the root of the repository (it is gitignored) and set `DATABASE_URL` there:

```dotenv
# MariaDB / MySQL
DATABASE_URL=mysql://root:root@127.0.0.1:3306/wallabag?charset=utf8mb4
# MariaDB / MySQL (Docker Compose)
DATABASE_URL=mysql://root:wallaroot@mariadb:3306/wallabag?charset=utf8mb4
# PostgreSQL
DATABASE_URL=postgresql://wallabag:wallapass@127.0.0.1:5432/wallabag?charset=utf8
# PostgreSQL (Docker Compose)
DATABASE_URL=postgresql://wallabag:wallapass@postgres:5432/wallabag?charset=utf8
```

For tests, the committed `.env.test` defaults to SQLite. To run the test suite against MySQL or PostgreSQL, create a `.env.test.local` file (gitignored) at the root of the repository:

```dotenv
# MariaDB / MySQL
DATABASE_URL=mysql://root:root@127.0.0.1:3306/wallabag_test?charset=utf8mb4
# MariaDB / MySQL (Docker Compose)
DATABASE_URL=mysql://root:wallaroot@mariadb:3306/wallabag_test?charset=utf8mb4
# PostgreSQL
DATABASE_URL=postgresql://wallabag:wallapass@127.0.0.1:5432/wallabag_test?charset=utf8
# PostgreSQL (Docker Compose)
DATABASE_URL=postgresql://wallabag:wallapass@postgres:5432/wallabag_test?charset=utf8
```

Make sure to use a different database name from the one in `.env.local` to avoid conflicts between your development and test environments.

## You found a bug
Please [open a new issue](https://github.com/wallabag/wallabag/issues/new).

To fix the bug quickly, we need some infos: please answer to the questions in the issue form.

If you have the skills, look for errors into PHP, server and application logs (see `var/logs`).

Note : If you have large portions of text, use [Github's Gist service](https://gist.github.com/) or other pastebin-like.

## You want to fix a bug or to add a feature
Please fork wallabag and work with **the master branch**.

## Run Tests and PHP formatter

All pull requests need to pass the tests and the code needs match the style guide.

The repository uses a GNU make `Makefile`. If your system ships a non-GNU `make`, use `gmake` for the commands below.

To run the tests locally run `make test`. You can also narrow a run to specific files by appending them as extra goals, for example `make test-unit tests/unit/Helper/RedirectTest.php`.

To run the PHP formatter run `make fix-cs`.

To run the PHPStan static analysis run `make phpstan`.

To run the JS linter run `make lint-js`.

To run the SCSS linter run `make lint-scss`.

To rebuild frontend assets automatically while developing run `make dev-watch`.
