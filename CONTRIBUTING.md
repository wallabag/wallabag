# How to contribute

## You found a bug
Please [open a new issue](https://github.com/wallabag/wallabag/issues/new).

To fix the bug quickly, we need some infos:
* your wallabag version (on top of the ./index.php file, and also on config page)
* your webserver installation :
  * type of hosting (shared or dedicaced)
  * in case of a dedicaced server, the server and OS used
  * the php version used, eventually `phpinfo()`
* which storage system you choose at install (SQLite, MySQL/MariaDB or PostgreSQL)
* any problem on the `wallabag_compatibility_test.php` page
* any particular details which could be related


If relevant :
* the link you want to save and which causes problem
* the file you want to import into wallabag, or just an extract

If you have the skills :
* enable DEBUG mode and look the output at cache/log.txt
* look for errors into php and server logs

Note : If you have large portions of text, use [Github's Gist service](https://gist.github.com/) or other pastebin-like.

## You want to fix a bug or to add a feature
Please fork wallabag and work with **the dev branch** only. **Do not work on master branch**.

[Don't forget to read our guidelines](https://github.com/wallabag/wallabag/blob/dev/GUIDELINES.md).