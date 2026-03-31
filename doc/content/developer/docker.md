---
title: Docker
weight: 2
---

Run wallabag in docker-compose
==============================

This document describes how to use Docker for wallabag development
purposes. For running wallabag in production, please use the
[official docker-compose configuration
provided](https://github.com/wallabag/docker).

To run your own development instance of wallabag, you can use the
pre-configured docker compose files provided in the wallabag repository.

Requirements
------------

Make sure to have
[Docker](https://docs.docker.com/installation/ubuntulinux/) and [Docker
Compose](https://docs.docker.com/compose/install/) available on your
system and up to date.

Switch DBMS
-----------

By default, wallabag starts with a SQLite database. However, wallabag
also supports PostgreSQL and MySQL. Docker containers are available for both.

For the chosen DBMS, update `compose.yaml` and the repo-level env overrides:

-   uncomment the container definition (`postgres` or `mariadb`)
-   uncomment the matching `depends_on` line in the `php` service
-   adjust `docker/mariadb/env` or `docker/postgres/env` if you need
    different credentials or database names for the database container
-   set `DATABASE_URL` in `.env.local` for development
-   set `DATABASE_URL` in `.env.test.local` if you want tests to use the
    same engine with a separate test database

If you keep running Symfony commands on your host instead of inside the `php`
container, use host-reachable values in `.env.local` and `.env.test.local`
instead of the Docker Compose hostnames. When the Docker stack is running, the
`Makefile` already runs Symfony commands in the `php` container by default.

Run wallabag
------------

1.  Fork and clone the project
2.  Copy `docker/php/env.example` to `docker/php/env`
3.  Adjust any Docker-specific overrides you need
4.  If you are not using SQLite, uncomment the matching database service in
    `compose.yaml`, uncomment the matching `depends_on` line in the `php`
    service, and set `DATABASE_URL` in `.env.local`
5.  Set `DATABASE_URL` in `.env.test.local` as well if you want tests to use
    the same database engine with a separate test database
6.  `make dev-docker-up` to start the containers
7.  `make dev-setup` to install dependencies, prepare the database, and build assets
8.  Finally, browse to <http://localhost:8000/> to find your freshly
    installed wallabag.

At various step, you'll probably run into UNIX permission problems, bad
paths in generated cache, etc… Operations like removing cache files or
changing files owners might be frequently required, so don't be afraid!
