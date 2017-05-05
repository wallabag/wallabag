Console Commands
================

wallabag has a number of CLI commands to manage a number of tasks. You can list all the commands by executing `bin/console` in the wallabag folder.

Each command has a help accessible through `bin/console help %command%`.

.. note::

    If you're in a production environment, remember to add `-e prod` to each command.

Notable commands
----------------

* `assets:install`: May be helpful if assets are missing.
* `cache:clear`: should be run after each update (included in `make update`).
* `doctrine:migrations:status`: Output the status of your database migrations.
* `fos:user:activate`: Manually activate an user.
* `fos:user:change-password`: Change a password for an user.
* `fos:user:create`: Create an user.
* `fos:user:deactivate`: Deactivate an user (not deleted).
* `fos:user:demote`: Removes a role from an user, typically admin rights.
* `fos:user:promote`: Adds a role to an user, typically admin rights.
* `rabbitmq:*`: May be useful if you're using RabbitMQ.
* `wallabag:clean-duplicates`: Removes all entry duplicates for one user or all users
* `wallabag:export`: Exports all entries for an user. You can choose the output path of the file.
* `wallabag:import`: Import entries to different formats to an user account.
* `wallabag:import:redis-worker`: Useful if you use Redis.
* `wallabag:install`: (re)Install wallabag
* `wallabag:tag:all`: Tag all entries for an user using his/her tagging rules.
