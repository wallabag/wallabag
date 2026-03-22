---
title: From 2.5 to 2.6
weight: 4
---

You'll find all queries to run when upgrading.

We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.

You may encounter issues with indexes names: if so, please change queries with the correct index name.

## MySQL

```sql
ALTER TABLE wallabag_config DROP theme;
ALTER TABLE wallabag_user CHANGE backupCodes backupCodes JSON DEFAULT NULL;
ALTER TABLE wallabag_config ADD display_thumbnails INT(11) NOT NULL DEFAULT 1;
```

## PostgreSQL

```sql
ALTER TABLE wallabag_config DROP theme;
ALTER TABLE wallabag_user ALTER backupcodes TYPE JSON USING backupcodes::json
ALTER TABLE wallabag_config ADD display_thumbnails INT NOT NULL DEFAULT 1;
```

## SQLite

```sql

CREATE TEMPORARY TABLE __temp__wallabag_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes FROM wallabag_user;
DROP TABLE wallabag_user;
CREATE TABLE wallabag_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
                , name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json)
                , emailTwoFactor BOOLEAN NOT NULL);
INSERT INTO wallabag_user (id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes) SELECT id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes FROM __temp__wallabag_user;
DROP TABLE __temp__wallabag_user;
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON wallabag_user (username_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON wallabag_user (email_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON wallabag_user (confirmation_token);

CREATE TABLE "__temp__wallabag_config" (
                                     "id"	INTEGER NOT NULL,
                                     "user_id"	INTEGER DEFAULT NULL,
                                     "items_per_page"	INTEGER NOT NULL,
                                     "language"	VARCHAR(255) NOT NULL,
                                     "reading_speed"	DOUBLE PRECISION DEFAULT NULL,
                                     "pocket_consumer_key"	VARCHAR(255) DEFAULT NULL,
                                     "action_mark_as_read"	INTEGER DEFAULT 0,
                                     "list_mode"	INTEGER DEFAULT NULL,
                                     "feed_token"	VARCHAR(255) DEFAULT NULL,
                                     "feed_limit"	INTEGER DEFAULT NULL,
                                     "display_thumbnails"	INTEGER DEFAULT 1,
                                     CONSTRAINT "FK_87E64C53A76ED395" FOREIGN KEY("user_id") REFERENCES "wallabag_user"("id") ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE,
                                     PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO __temp__wallabag_config ("action_mark_as_read","display_thumbnails","feed_limit","feed_token","id","items_per_page","language","list_mode","pocket_consumer_key","reading_speed","user_id") SELECT "action_mark_as_read","display_thumbnails","feed_limit","feed_token","id","items_per_page","language","list_mode","pocket_consumer_key","reading_speed","user_id" FROM wallabag_config;
DROP TABLE wallabag_config;
ALTER TABLE __temp__wallabag_config RENAME TO wallabag_config;
CREATE INDEX config_feed_token ON wallabag_config ("feed_token");
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON wallabag_config ("user_id");
```
