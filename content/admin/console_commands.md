---
title: Console Commands
weight: 5
---

wallabag has a number of CLI commands to manage a number of tasks. You
can list all the commands by executing `bin/console` in the wallabag
folder.

Each command has a help accessible through `bin/console help %command%`.

> If you're in a production environment, remember to add `--env=prod` to each
command.

Notable commands
----------------

From Symfony:

 - `assets:install`: May be helpful if assets are missing.
 - `cache:clear`: should be run after each update (included in make update).
 - `doctrine:migrations:status`: Output the status of your database migrations.
 - `fos:user:activate`: Manually activate a user.
 - `fos:user:change-password`: Change a password for a user.
 - `fos:user:create`: Create a user.
 - `fos:user:deactivate`: Deactivate a user (not deleted).
 - `fos:user:demote`: Removes a role from a user, typically admin rights.
 - `fos:user:promote`: Adds a role to a user, typically admin rights.
 - `rabbitmq:*`: May be useful if you're using RabbitMQ.

Custom to wallabag:

 - `wallabag:clean-downloaded-images`: Cleans downloaded images which are no more associated to an entry
 - `wallabag:clean-duplicates`: Removes all entry duplicates for one user or all users
 - `wallabag:entry:reload`: Reload entries.
 - `wallabag:export`: Exports all entries for a user. You can choose the output path of the file.
 - `wallabag:generate-hashed-urls`: Generates hashed urls for each entry
 - `wallabag:import`: Import entries to different formats to a user account.
 - `wallabag:import:redis-worker`: Useful if you use Redis.
 - `wallabag:install`: (re)Install wallabag
 - `wallabag:tag:all`: Tag all entries for a user using his/her tagging rules.
 - `wallabag:user:show`: Shows the details for a user.
 - `wallabag:user:list`: List all existing users.

wallabag:clean-downloaded-images
-------------------------

This command cleans downloaded images which are no more associated to an entry. This is useful if you enabled "Download images locally" before 2.4.0 because there were a bug in removing images from an entry when you removed that entry.

Usage:

```
wallabag:clean-downloaded-images
```

Options:
 - `--dry-run`: Don't remove images, just dump number of images which might be removed


wallabag:clean-duplicates
-------------------------

This command helps you to clean your articles list in case of duplicates.

Usage:

```
wallabag:clean-duplicates [<username>]
```

Arguments:

 - username: User to clean


wallabag:entry:reload
---------------------

This command reload entries.

Usage:

```
wallabag:entry:reload [<username>]
```

Arguments:
 - username: Reload entries only for the given user.


wallabag:export
---------------

This command helps you to export all entries for a user.

Usage:

```
wallabag:export <username> [<filepath>]
```

Arguments:

 - username: User from which to export entries
 - filepath: Path of the exported file


wallabag:generate-hashed-urls
---------------

This command helps you to generates hashes of the url of each entry, to check through API if an URL is already saved. Only available since 2.4.0.

Usage:

```
wallabag:generate-hashed-urls <username>
```

Arguments:

 - username: User to process entries


wallabag:import
---------------

This command helps you to import entries from a JSON export.

Usage:

```
wallabag:import [--] <username> <filepath>
```

Arguments:

 - username: User to populate
 - filepath: Path to the JSON file

Options:

 - `--importer=IMPORTER`: The importer to use: v1, v2, instapaper, pinboard, readability, firefox or chrome [default: "v1"]
 - `--markAsRead=MARKASREAD`: Mark all entries as read [default: false]
 - `--useUserId`: Use user id instead of username to find account
 - `--disableContentUpdate`: Disable fetching updated content from URL


wallabag:import:redis-worker
----------------------------

This command helps you to launch Redis worker.

Usage:

```
wallabag:import:redis-worker [--] <serviceName>
```

Arguments:

 - serviceName: Service to use: wallabag_v1, wallabag_v2, pocket, readability, pinboard, firefox, chrome or instapaper

Options:

 - `--maxIterations[=MAXITERATIONS]`: Number of iterations before stopping [default: false]


wallabag:install
----------------

This command helps you to install or re-install wallabag.

Usage:

```
wallabag:install
```


wallabag:tag:all
----------------

This command helps you to tag all entries using tagging rules.

Usage:

```
wallabag:tag:all <username>
```

Arguments:
 - username: User to tag entries for.


wallabag:user:show
------------------

This command shows the details for a user.

Usage:

```
wallabag:user:show <username>
```

Arguments:
 - username: User to show details for.


wallabag:user:list
------------------

This command lists all existing users.

Usage:

```
wallabag:user:list [<search>]
```

Arguments:
 - search: Filter the list with the given search term. The search is done on users' username, name and email

Options:
 - `--limit=LIMIT`: Max number of users displayed in the list
