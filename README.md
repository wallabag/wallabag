# Poche v2

This is a Proof of Concept of Poche v2 using the PHP micro-framework [Silex](http://silex.sensiolabs.org).

# Installation

Get Composer and install Silex:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

Then configure your webserver to point to the `web/` directory. Some documentation is available on the [Silex documentation page](http://silex.sensiolabs.org/doc/web_servers.html).

If you are using PHP 5.4 you can run Poche v2 by using the embedded webserver:

    php -S localhost:8080 -t web web/index.php

Poche should now be running at [http://localhost:8080](http://localhost:8080).

Then you should initialize your database by running:

    ./console db:create

# Test

To run the test suite just use:

    ./console tests:unit
