Maintenance mode
================

If you have some long tasks to do on your wallabag instance, you can enable a maintenance mode.
Nobody will have access to your instance.

Enable maintenance mode
-----------------------

To enable maintenance mode, execute this command:

::

    bin/console lexik:maintenance:lock --no-interaction -e=prod

You can set your IP address in ``app/config/config.yml`` if you want to access to wallabag even if maintenance mode is enabled. For example:

::

    lexik_maintenance:
        authorized:
            ips: ['127.0.0.1']


Disable maintenance mode
------------------------

To disable maintenance mode, execute this command:

::

    bin/console lexik:maintenance:unlock -e=prod
