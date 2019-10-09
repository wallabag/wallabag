# Tips for back-end developers

## Use PHP’s built-in web server

[PHP’s built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php) allows you to develop without installing a web server like Nginx or Apache.

To install PHP dependencies, build the `dev` version of the front-end, configure Wallabag and start the built-in server, do:

```
make dev
```

Note that you will need [npm](https://nodejs.org/en/download/) and [yarn](https://yarnpkg.com/en/docs/install) to build the front-end `dev` version.
