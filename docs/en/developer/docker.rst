Run Wallabag in docker-compose
==============================

In order to run your own development instance of wallabag, you may
want to use the pre-configured docker compose files.

Requirements
------------

Make sure to have `Docker
<https://docs.docker.com/installation/ubuntulinux/>`__ and `Docker
Compose <https://docs.docker.com/compose/install/>`__ availables on
your system and up to date.

Switch DBMS
-----------

By default, Wallabag will start with a sqlite database.
Since Wallabag provide support for Postgresql and MySQL, docker
containers are also available for these ones.

In ``docker-compose.yml``, for the chosen DBMS uncomment :

- the container definition (``postgres`` or ``mariadb`` root level
  block)
- the container link in the ``php`` container
- the container env file in the ``php`` container

In order to keep running Symfony commands on your host (such as
``wallabag:install``), you also should :

- source the proper env files on your command line, so variables
  like ``SYMFONY__ENV__DATABASE_HOST`` will exist.
- create a ``127.0.0.1 rdbms`` on your system ``hosts`` file

Run Wallabag
------------

#. Fork and clone the project
#. Edit ``app/config/parameters.yml`` to replace ``database_*``
   properties with commented ones (with values prefixed by ``env.``)
#. ``composer install`` the project dependencies
#. ``php app/console wallabag:install`` to create the schema
#. ``docker-compose up`` to run the containers
#. Finally, browse to http://localhost:8080/ to find your freshly
   installed wallabag.

At various step, you'll probably run into UNIX permission problems,
bad paths in generated cache, etc…
Operations like removing cache files or changing files owners might
be frequently required, so don't be afraid !
