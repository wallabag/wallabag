# Installation

## On a dedicated web server (recommended way)

To install wallabag itself, you must run the following commands:

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

To start PHP's build-in server and test if everything did install
correctly, you can do:

```bash
make run
```

And access wallabag at <http://yourserverip:8000>

To define parameters with environment variables, you have to set these
variables with `SYMFONY__` prefix. For example,
`SYMFONY__DATABASE_DRIVER`. You can have a look at [Symfony
documentation](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html).

## On shared hosting

We provide a package with all dependencies inside. The default
configuration uses SQLite for the database. If you want to change these
settings, please edit `app/config/parameters.yml`.

We already created a user: login and password are `wallabag`.

With this package, wallabag doesn't check for mandatory extensions used
in the application (theses checks are made during `composer install`
when you have a dedicated web server, see above).

Execute this command to download and extract the latest package:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

You will find the [md5 hash of the latest package on our
website](https://static.wallabag.org/releases/).

Now, read the following documentation to create your virtual host, then
access your wallabag. If you changed the database configuration to use
MySQL or PostgreSQL, you need to create a user via this command
`php bin/console wallabag:install --env=prod`.

## Installation with Docker

We provide you a Docker image to install wallabag easily. Have a look at
our repository on [Docker
Hub](https://hub.docker.com/r/wallabag/wallabag/) for more information.

### Command to launch container

```bash
docker pull wallabag/wallabag
```

## Installation on Cloudron

Cloudron provides an easy way to install webapps on your server with a
focus on sysadmin automation and keeping apps updated. wallabag is
packaged as a Cloudron app and available to install directly from the
store.

[Install wallabag on your
Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp.html)
