# How to contribute

## You found a bug
Please [open a new issue](https://github.com/wallabag/wallabag/issues/new).

To fix the bug quickly, we need some infos:
* your wallabag version (in `app/config/config.yml`, see `wallabag_core.version`)
* your webserver installation :
  * type of hosting (shared or dedicated)
  * in case of a dedicated server, the server and OS used
  * the php version used, eventually `phpinfo()`
* which storage system you choose at install (SQLite, MySQL/MariaDB or PostgreSQL)
* any particular details which could be related

If relevant :
* the link you want to save and which causes problem
* the file you want to import into wallabag, or just an extract

If you have the skills, look for errors into php, server and application (see `var/logs`) logs

Note : If you have large portions of text, use [Github's Gist service](https://gist.github.com/) or other pastebin-like.

## You want to fix a bug or to add a feature
Please fork wallabag and work with **the v2 branch** only. **Do not work on master branch**.
