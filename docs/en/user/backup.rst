Backup Wallabag
===============
Because sometimes you may do a mistake with your Wallabag and lose data or in case you need to move your Wallabag to another server you want to backup your data.
This articles describes what you need to backup.


Basic Settings
--------------
Wallabag stores some basic parameters (like SMTP server or database backend) in the file `app/config/parameters.yml`.

Database
--------
As Wallabag supports different kinds of database, the way to perform the backup depends on the database you use, so you need to refer to the vendor documentation.

Here's some examples:

- Mysql: http://dev.mysql.com/doc/refman/5.7/en/backup-methods.html
- Posgresql: https://www.postgresql.org/docs/current/static/backup.html

sqlite
~~~~~~
To backup the sqlite database, you just need to copy the directory `data/db` from the Wallabag application directory

Images
------
The images retrieved by Wallabag are stored under `data/assets/images`.
