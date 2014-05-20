# wallabag v2 [![Build Status](https://api.travis-ci.org/wallabag/wallabag.png?branch=v2-silex)](https://travis-ci.org/wallabag/wallabag)

This is a Proof of Concept of wallabag v2 using the PHP micro-framework [Silex](http://silex.sensiolabs.org).

# Installation

Get Composer and install Silex:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

Then configure your webserver to point to the `web/` directory. Some documentation is available on the [Silex documentation page](http://silex.sensiolabs.org/doc/web_servers.html).

If you are using PHP 5.4 you can run wallabag v2 by using the embedded webserver:

    php -S localhost:8080 -t web web/index.php

wallabag should now be running at [http://localhost:8080](http://localhost:8080).

Then you should initialize your database by running:

    ./console db:create

# Test

For unit tests (using Atoum) use:

    ./console tests:unit

For functional tests you'll need phpunit:

    phpunit
