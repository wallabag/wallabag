---
title: From 2.1 to 2.2
weight: 1
---

You'll find all queries to run when upgrading.

We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.

You may encounter issues with indexes names: if so, please change queries with the correct index name.

# Migration 20161001072726

## MySQL

### Migration up

```sql
ALTER TABLE wallabag_entry_tag DROP FOREIGN KEY FK_C9F0DD7CBA364942
ALTER TABLE wallabag_entry_tag DROP FOREIGN KEY FK_C9F0DD7CBAD26311
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_entry_tag_entry FOREIGN KEY (entry_id) REFERENCES wallabag_entry (id) ON DELETE CASCADE
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_entry_tag_tag FOREIGN KEY (tag_id) REFERENCES wallabag_tag (id) ON DELETE CASCADE
ALTER TABLE wallabag_annotation DROP FOREIGN KEY FK_A7AED006BA364942
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_annotation_entry FOREIGN KEY (entry_id) REFERENCES wallabag_entry (id) ON DELETE CASCADE
```

### Migration down

We didn't write down migration for `20161001072726`.

## PostgreSQL

### Migration up

```sql
ALTER TABLE wallabag_entry_tag DROP CONSTRAINT fk_c9f0dd7cba364942
ALTER TABLE wallabag_entry_tag DROP CONSTRAINT fk_c9f0dd7cbad26311
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_entry_tag_entry FOREIGN KEY (entry_id) REFERENCES wallabag_entry (id) ON DELETE CASCADE
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_entry_tag_tag FOREIGN KEY (tag_id) REFERENCES wallabag_tag (id) ON DELETE CASCADE
ALTER TABLE wallabag_annotation DROP CONSTRAINT fk_a7aed006ba364942
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_annotation_entry FOREIGN KEY (entry_id) REFERENCES wallabag_entry (id) ON DELETE CASCADE
```

### Migration down

We didn't write down migration for `20161001072726`.

## SQLite


This migration can only be executed safely on MySQL or PostgreSQL.

# Migration 20161022134138

## MySQL

### Migration up

```sql
ALTER DATABASE wallabag CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
ALTER TABLE wallabag_user CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL;
ALTER TABLE wallabag_user CHANGE salt salt VARCHAR(180) NOT NULL;
ALTER TABLE wallabag_user CHANGE password password VARCHAR(180) NOT NULL;
ALTER TABLE wallabag_annotation CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_entry CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_tag CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_annotation CHANGE `text` `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_annotation CHANGE `quote` `quote` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_entry CHANGE `title` `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_entry CHANGE `content` `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_tag CHANGE `label` `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wallabag_user CHANGE `name` `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Migration down

```sql
ALTER DATABASE wallabag CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;
ALTER TABLE wallabag_annotation CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_entry CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_tag CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_user CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_annotation CHANGE `text` `text` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_annotation CHANGE `quote` `quote` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_entry CHANGE `title` `title` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_entry CHANGE `content` `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_tag CHANGE `label` `label` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE wallabag_user CHANGE `name` `name` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;
```

## PostgreSQL and SQLite

This migration only applies to MySQL.

# Migration 20161024212538

## MySQL

### Migration up

```sql
ALTER TABLE wallabag_oauth2_clients ADD user_id INT NOT NULL
ALTER TABLE wallabag_oauth2_clients ADD CONSTRAINT IDX_user_oauth_client FOREIGN KEY (user_id) REFERENCES wallabag_user (id) ON DELETE CASCADE
CREATE INDEX IDX_635D765EA76ED395 ON wallabag_oauth2_clients (user_id)
```

### Migration down

```sql
ALTER TABLE wallabag_oauth2_clients DROP FOREIGN KEY IDX_user_oauth_client
ALTER TABLE wallabag_oauth2_clients DROP user_id
```

## PostgreSQL

### Migration up

```sql
ALTER TABLE wallabag_oauth2_clients ADD user_id INT DEFAULT NULL
ALTER TABLE wallabag_oauth2_clients ADD CONSTRAINT IDX_user_oauth_client FOREIGN KEY (user_id) REFERENCES wallabag_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
CREATE INDEX IDX_635D765EA76ED395 ON wallabag_oauth2_clients (user_id)
```

### Migration down

```sql
ALTER TABLE wallabag_oauth2_clients DROP CONSTRAINT idx_user_oauth_client
ALTER TABLE wallabag_oauth2_clients DROP user_id
```

## SQLite

### Migration up

```sql
CREATE TEMPORARY TABLE __temp__wallabag_oauth2_clients AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types, name FROM wallabag_oauth2_clients
DROP TABLE wallabag_oauth2_clients
CREATE TABLE wallabag_oauth2_clients (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, allowed_grant_types CLOB NOT NULL COLLATE BINARY, name CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT IDX_user_oauth_client FOREIGN KEY (user_id) REFERENCES wallabag_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
INSERT INTO wallabag_oauth2_clients (id, random_id, redirect_uris, secret, allowed_grant_types, name) SELECT id, random_id, redirect_uris, secret, allowed_grant_types, name FROM __temp__wallabag_oauth2_clients
DROP TABLE __temp__wallabag_oauth2_clients
CREATE INDEX IDX_635D765EA76ED395 ON wallabag_oauth2_clients (user_id)
```

### Migration down

```sql
DROP INDEX IDX_635D765EA76ED395
CREATE TEMPORARY TABLE __temp__wallabag_oauth2_clients AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types, name FROM wallabag_oauth2_clients
DROP TABLE wallabag_oauth2_clients
CREATE TABLE wallabag_oauth2_clients (id INTEGER NOT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, allowed_grant_types CLOB NOT NULL COLLATE BINARY, name CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_oauth2_clients (id, random_id, redirect_uris, secret, allowed_grant_types, name) SELECT id, random_id, redirect_uris, secret, allowed_grant_types, name FROM __temp__wallabag_oauth2_clients
DROP TABLE __temp__wallabag_oauth2_clients
```

# Migration 20161031132655

## MySQL

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('download_images_enabled', 0, 'misc')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'download_images_enabled';
```

PostgreSQL
----------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('download_images_enabled', 0, 'misc')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'download_images_enabled';
```

SQLite
------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('download_images_enabled', 0, 'misc')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'download_images_enabled';
```

Migration 20161104073720
========================

MySQL
-----

### Migration up

```sql
CREATE INDEX IDX_entry_created_at ON wallabag_entry (created_at)
```

### Migration down

```sql
DROP INDEX IDX_entry_created_at ON wallabag_entry
```

PostgreSQL
----------

### Migration up

```sql
CREATE INDEX IDX_entry_created_at ON wallabag_entry (created_at)
```

### Migration down

```sql
DROP INDEX idx_entry_created_at
```

SQLite
------

### Migration up

```sql
DROP INDEX created_at_idx
DROP INDEX IDX_F4D18282A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uuid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX created_at_idx ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
CREATE INDEX IDX_entry_created_at ON wallabag_entry (created_at)
```

### Migration down

```sql
DROP INDEX IDX_entry_created_at
DROP INDEX IDX_F4D18282A76ED395
DROP INDEX created_at_idx
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uuid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
CREATE INDEX created_at_idx ON wallabag_entry (created_at)
```

Migration 20161106113822
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_config ADD action_mark_as_read INT DEFAULT 0
```

### Migration down

```sql
ALTER TABLE wallabag_config DROP action_mark_as_read
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_config ADD action_mark_as_read INT DEFAULT 0
```

### Migration down

```sql
ALTER TABLE wallabag_config DROP action_mark_as_read
```

SQLite
------

### Migration up

```sql
ALTER TABLE wallabag_config ADD COLUMN action_mark_as_read INTEGER DEFAULT 0
```

### Migration down

```sql
DROP INDEX UNIQ_87E64C53A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_config AS SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key FROM wallabag_config
DROP TABLE wallabag_config
CREATE TABLE wallabag_config (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL COLLATE BINARY, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, rss_token VARCHAR(255) DEFAULT NULL COLLATE BINARY, rss_limit INTEGER DEFAULT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_config (id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key) SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key FROM __temp__wallabag_config
DROP TABLE __temp__wallabag_config
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON wallabag_config (user_id)
```

Migration 20161117071626
========================

MySQL
-----

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_unmark', 0, 'entry')
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('unmark_url', 'https://unmark.it', 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'share_unmark';
DELETE FROM wallabag_craue_config_setting WHERE name = 'unmark_url';
```

PostgreSQL
----------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_unmark', 0, 'entry')
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('unmark_url', 'https://unmark.it', 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'share_unmark';
DELETE FROM wallabag_craue_config_setting WHERE name = 'unmark_url';
```

SQLite
------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_unmark', 0, 'entry')
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('unmark_url', 'https://unmark.it', 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'share_unmark';
DELETE FROM wallabag_craue_config_setting WHERE name = 'unmark_url';
```

Migration 20161118134328
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_entry ADD http_status VARCHAR(3) DEFAULT NULL
```

### Migration down

```sql
ALTER TABLE wallabag_entry DROP http_status
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_entry ADD http_status VARCHAR(3) DEFAULT NULL
```

### Migration down

```sql
ALTER TABLE wallabag_entry DROP http_status
```

SQLite
------

### Migration up

```sql
ALTER TABLE wallabag_entry ADD COLUMN http_status VARCHAR(3) DEFAULT NULL
```

### Migration down

```sql
DROP INDEX created_at_idx
DROP INDEX IDX_F4D18282A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uuid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX created_at_idx ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
```

Migration 20161122144743
========================

MySQL
-----

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('restricted_access', 0, 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'restricted_access';
```

PostgreSQL
----------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('restricted_access', 0, 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'restricted_access';
```

SQLite
------

### Migration up

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('restricted_access', 0, 'entry')
```

### Migration down

```sql
DELETE FROM wallabag_craue_config_setting WHERE name = 'restricted_access';
```

Migration 20161122203647
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_user DROP expired, DROP credentials_expired
```

### Migration down

```sql
ALTER TABLE wallabag_user ADD expired SMALLINT DEFAULT NULL, ADD credentials_expired SMALLINT DEFAULT NULL
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_user DROP expired
ALTER TABLE wallabag_user DROP credentials_expired
```

### Migration down

```sql
ALTER TABLE wallabag_user ADD expired SMALLINT DEFAULT NULL
ALTER TABLE wallabag_user ADD credentials_expired SMALLINT DEFAULT NULL
```

SQLite
------

### Migration up

```sql
DROP INDEX UNIQ_1D63E7E5C05FB297
DROP INDEX UNIQ_1D63E7E5A0D96FBF
DROP INDEX UNIQ_1D63E7E592FC23A8
CREATE TEMPORARY TABLE __temp__wallabag_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, locked, expires_at, confirmation_token, password_requested_at, roles, credentials_expire_at, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted FROM wallabag_user
DROP TABLE wallabag_user
CREATE TABLE wallabag_user (id INTEGER NOT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, username_canonical VARCHAR(180) NOT NULL COLLATE BINARY, email VARCHAR(180) NOT NULL COLLATE BINARY, email_canonical VARCHAR(180) NOT NULL COLLATE BINARY, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, last_login DATETIME DEFAULT NULL, locked BOOLEAN NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL COLLATE BINARY, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL COLLATE BINARY, credentials_expire_at DATETIME DEFAULT NULL, name CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, twoFactorAuthentication BOOLEAN NOT NULL, trusted CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, locked, expires_at, confirmation_token, password_requested_at, roles, credentials_expire_at, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, locked, expires_at, confirmation_token, password_requested_at, roles, credentials_expire_at, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted FROM __temp__wallabag_user
DROP TABLE __temp__wallabag_user
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON wallabag_user (confirmation_token)
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON wallabag_user (email_canonical)
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON wallabag_user (username_canonical)
```

### Migration down

```sql
ALTER TABLE wallabag_user ADD COLUMN expired SMALLINT DEFAULT NULL
ALTER TABLE wallabag_user ADD COLUMN credentials_expired SMALLINT DEFAULT NULL
```

Migration 20161128084725
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_config ADD list_mode INT DEFAULT NULL
```

### Migration down

```sql
ALTER TABLE wallabag_config DROP list_mode
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_config ADD list_mode INT DEFAULT NULL
```

### Migration down

```sql
ALTER TABLE wallabag_config DROP list_mode
```

SQLite
------

### Migration up

```sql
ALTER TABLE wallabag_config ADD COLUMN list_mode INTEGER DEFAULT NULL
```

### Migration down

```sql
DROP INDEX UNIQ_87E64C53A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_config AS SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key FROM wallabag_config
DROP TABLE wallabag_config
CREATE TABLE wallabag_config (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL COLLATE BINARY, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, rss_token VARCHAR(255) DEFAULT NULL COLLATE BINARY, rss_limit INTEGER DEFAULT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_config (id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key) SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key FROM __temp__wallabag_config
DROP TABLE __temp__wallabag_config
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON wallabag_config (user_id)
```

Migration 20161128131503
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_user DROP locked, DROP credentials_expire_at, DROP expires_at
```

### Migration down

```sql
ALTER TABLE wallabag_user ADD locked SMALLINT DEFAULT NULL, ADD credentials_expire_at DATETIME DEFAULT NULL, ADD expires_at DATETIME DEFAULT NULL
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_user DROP locked
ALTER TABLE wallabag_user DROP credentials_expire_at
ALTER TABLE wallabag_user DROP expires_at
```

### Migration down

```sql
ALTER TABLE wallabag_user ADD locked SMALLINT DEFAULT NULL
ALTER TABLE wallabag_user ADD credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
ALTER TABLE wallabag_user ADD expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
```

SQLite
------

### Migration up

```sql
ALTER TABLE wallabag_user ADD COLUMN locked SMALLINT DEFAULT NULL
ALTER TABLE wallabag_user ADD COLUMN credentials_expire_at DATETIME DEFAULT NULL
ALTER TABLE wallabag_user ADD COLUMN expires_at DATETIME DEFAULT NULL
```

### Migration down

```sql
DROP INDEX UNIQ_1D63E7E592FC23A8
DROP INDEX UNIQ_1D63E7E5A0D96FBF
DROP INDEX UNIQ_1D63E7E5C05FB297
CREATE TEMPORARY TABLE __temp__wallabag_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted, expired, credentials_expired FROM wallabag_user
DROP TABLE wallabag_user
CREATE TABLE wallabag_user (id INTEGER NOT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, username_canonical VARCHAR(180) NOT NULL COLLATE BINARY, email VARCHAR(180) NOT NULL COLLATE BINARY, email_canonical VARCHAR(180) NOT NULL COLLATE BINARY, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL COLLATE BINARY, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL COLLATE BINARY, name CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, twoFactorAuthentication BOOLEAN NOT NULL, trusted CLOB DEFAULT NULL COLLATE BINARY, expired SMALLINT DEFAULT NULL, credentials_expired SMALLINT DEFAULT NULL, PRIMARY KEY(id))
INSERT INTO wallabag_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted, expired, credentials_expired) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication, trusted, expired, credentials_expired FROM __temp__wallabag_user
DROP TABLE __temp__wallabag_user
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON wallabag_user (username_canonical)
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON wallabag_user (email_canonical)
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON wallabag_user (confirmation_token)
```

Migration 20161214094402
========================

MySQL
-----

### Migration up

```sql
ALTER TABLE wallabag_entry CHANGE uuid uid VARCHAR(23)
```

### Migration down

```sql
ALTER TABLE wallabag_entry CHANGE uid uuid VARCHAR(23)
```

PostgreSQL
----------

### Migration up

```sql
ALTER TABLE wallabag_entry RENAME uuid TO uid
```

### Migration down

```sql
ALTER TABLE wallabag_entry RENAME uid TO uuid
```

SQLite
------

### Migration up

```sql
CREATE TABLE __temp__wallabag_entry (
    id    INTEGER NOT NULL,
    user_id   INTEGER DEFAULT NULL,
    uid  VARCHAR(23) DEFAULT NULL,
    title CLOB DEFAULT NULL,
    url   CLOB DEFAULT NULL,
    is_archived   BOOLEAN NOT NULL,
    is_starred    BOOLEAN NOT NULL,
    content   CLOB DEFAULT NULL,
    created_at    DATETIME NOT NULL,
    updated_at    DATETIME NOT NULL,
    mimetype  CLOB DEFAULT NULL,
    language  CLOB DEFAULT NULL,
    reading_time  INTEGER DEFAULT NULL,
    domain_name   CLOB DEFAULT NULL,
    preview_picture   CLOB DEFAULT NULL,
    is_public BOOLEAN DEFAULT '0',
    http_status   VARCHAR(3) DEFAULT NULL,
    PRIMARY KEY(id)
);
INSERT INTO __temp__wallabag_entry SELECT id,user_id,uuid,title,url,is_archived,is_starred,content,created_at,updated_at,mimetype,language,reading_time,domain_name,preview_picture,is_public,http_status FROM wallabag_entry;
DROP TABLE wallabag_entry;
ALTER TABLE __temp__wallabag_entry RENAME TO wallabag_entry
CREATE INDEX uid ON wallabag_entry (uid)
CREATE INDEX created_at ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
```

### Migration down

```sql
CREATE TABLE __temp__wallabag_entry (
    id    INTEGER NOT NULL,
    user_id   INTEGER DEFAULT NULL,
    uuid  VARCHAR(23) DEFAULT NULL,
    title CLOB DEFAULT NULL,
    url   CLOB DEFAULT NULL,
    is_archived   BOOLEAN NOT NULL,
    is_starred    BOOLEAN NOT NULL,
    content   CLOB DEFAULT NULL,
    created_at    DATETIME NOT NULL,
    updated_at    DATETIME NOT NULL,
    mimetype  CLOB DEFAULT NULL,
    language  CLOB DEFAULT NULL,
    reading_time  INTEGER DEFAULT NULL,
    domain_name   CLOB DEFAULT NULL,
    preview_picture   CLOB DEFAULT NULL,
    is_public BOOLEAN DEFAULT '0',
    http_status   VARCHAR(3) DEFAULT NULL,
    PRIMARY KEY(id)
);
INSERT INTO __temp__wallabag_entry SELECT id,user_id,uid,title,url,is_archived,is_starred,content,created_at,updated_at,mimetype,language,reading_time,domain_name,preview_picture,is_public,http_status FROM wallabag_entry;
DROP TABLE wallabag_entry;
ALTER TABLE __temp__wallabag_entry RENAME TO wallabag_entry
CREATE INDEX uid ON wallabag_entry (uid)
CREATE INDEX created_at ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
```

Migration 20161214094403
========================

MySQL
-----

### Migration up

```sql
CREATE INDEX IDX_entry_uid ON wallabag_entry (uid)
```

### Migration down

```sql
DROP INDEX IDX_entry_uid ON wallabag_entry
```

PostgreSQL
----------

### Migration up

```sql
CREATE INDEX IDX_entry_uid ON wallabag_entry (uid)
```

### Migration down

```sql
DROP INDEX idx_entry_uid
```

SQLite
------

### Migration up

```sql
DROP INDEX IDX_F4D18282A76ED395
DROP INDEX created_at_idx
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
CREATE INDEX created_at_idx ON wallabag_entry (created_at)
CREATE INDEX IDX_entry_uid ON wallabag_entry (uid)
```

### Migration down

```sql
DROP INDEX IDX_entry_uid
DROP INDEX created_at_idx
DROP INDEX IDX_F4D18282A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX created_at_idx ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
```

Migration 20170127093841
========================

MySQL
-----

### Migration up

```sql
CREATE INDEX IDX_entry_starred ON wallabag_entry (is_starred)
CREATE INDEX IDX_entry_archived ON wallabag_entry (is_archived)
```

### Migration down

```sql
DROP INDEX IDX_entry_starred ON wallabag_entry
DROP INDEX IDX_entry_archived ON wallabag_entry
```

PostgreSQL
----------

### Migration up

```sql
CREATE INDEX IDX_entry_starred ON wallabag_entry (is_starred)
CREATE INDEX IDX_entry_archived ON wallabag_entry (is_archived)
```

### Migration down

```sql
DROP INDEX IDX_entry_starred
DROP INDEX IDX_entry_archived
```

SQLite
------

### Migration up

```sql
DROP INDEX uid
DROP INDEX created_at
DROP INDEX IDX_F4D18282A76ED395
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status) SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX uid ON wallabag_entry (uid)
CREATE INDEX created_at ON wallabag_entry (created_at)
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
CREATE INDEX IDX_entry_starred ON wallabag_entry (is_starred)
CREATE INDEX IDX_entry_archived ON wallabag_entry (is_archived)
```

### Migration down

```sql
DROP INDEX IDX_entry_archived
DROP INDEX IDX_entry_starred
DROP INDEX IDX_F4D18282A76ED395
DROP INDEX created_at
DROP INDEX uid
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status FROM wallabag_entry
DROP TABLE wallabag_entry
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT '0', http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))
INSERT INTO wallabag_entry (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status) SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public, http_status FROM __temp__wallabag_entry
DROP TABLE __temp__wallabag_entry
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id)
CREATE INDEX created_at ON wallabag_entry (created_at)
CREATE INDEX uid ON wallabag_entry (uid)
```
