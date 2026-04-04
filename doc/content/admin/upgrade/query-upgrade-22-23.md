---
title: From 2.2 to 2.3
weight: 2
---

You'll find all queries to run when upgrading.

We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.

You may encounter issues with indexes names: if so, please change queries with the correct index name.

## MySQL

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_scuttle', '1', 'entry');
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('scuttle_url', 'http://scuttle.org', 'entry');

ALTER TABLE wallabag_entry ADD published_at DATETIME DEFAULT NULL, ADD published_by LONGTEXT DEFAULT NULL;

ALTER TABLE wallabag_entry DROP is_public;

DELETE FROM wallabag_craue_config_setting WHERE name = 'download_pictures';

CREATE TABLE wallabag_site_credential (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, host VARCHAR(255) NOT NULL, username LONGTEXT NOT NULL, password LONGTEXT NOT NULL, createdAt DATETIME NOT NULL, INDEX idx_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE wallabag_site_credential ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES wallabag_user (id);

ALTER TABLE wallabag_user CHANGE username username VARCHAR(180) NOT NULL;
ALTER TABLE wallabag_user CHANGE username_canonical username_canonical VARCHAR(180) NOT NULL;
ALTER TABLE wallabag_user CHANGE email email VARCHAR(180) NOT NULL;
ALTER TABLE wallabag_user CHANGE email_canonical email_canonical VARCHAR(180) NOT NULL;

ALTER TABLE wallabag_entry ADD headers LONGTEXT DEFAULT NULL;

ALTER TABLE wallabag_annotation MODIFY quote TEXT NOT NULL;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('api_user_registration', '0', 'api');

DELETE FROM wallabag_craue_config_setting WHERE name = 'wallabag_url';

UPDATE wallabag_tag SET label = LOWER(label);

ALTER TABLE wallabag_entry ADD starred_at DATETIME DEFAULT NULL;

UPDATE wallabag_entry SET reading_time = 0 WHERE reading_time IS NULL;
ALTER TABLE wallabag_entry CHANGE reading_time reading_time INT(11) NOT NULL;

ALTER TABLE wallabag_entry ADD origin_url LONGTEXT DEFAULT NULL;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('store_article_headers', '0', 'entry');

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('shaarli_share_origin_url', '0', 'entry');
```

## PostgreSQL

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_scuttle', '1', 'entry');
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('scuttle_url', 'http://scuttle.org', 'entry');

ALTER TABLE wallabag_entry ADD published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;
ALTER TABLE wallabag_entry ADD published_by TEXT DEFAULT NULL;

ALTER TABLE wallabag_entry DROP is_public;

DELETE FROM wallabag_craue_config_setting WHERE name = 'download_pictures';

CREATE SEQUENCE site_credential_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE wallabag_site_credential (id SERIAL NOT NULL, user_id INT NOT NULL, host VARCHAR(255) NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL, createdAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));
CREATE INDEX idx_user ON wallabag_site_credential (user_id);
ALTER TABLE wallabag_site_credential ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES wallabag_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE wallabag_entry ADD headers TEXT DEFAULT NULL;

ALTER TABLE wallabag_annotation ALTER COLUMN quote TYPE TEXT;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('api_user_registration', '0', 'api');

DELETE FROM wallabag_craue_config_setting WHERE name = 'wallabag_url';

UPDATE wallabag_tag SET label = LOWER(label);

ALTER TABLE wallabag_entry ADD starred_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;

UPDATE wallabag_entry SET reading_time = 0 WHERE reading_time IS NULL;
ALTER TABLE wallabag_entry ALTER COLUMN reading_time SET NOT NULL;

ALTER TABLE wallabag_entry ADD origin_url TEXT DEFAULT NULL;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('store_article_headers', '0', 'entry');

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('shaarli_share_origin_url', '0', 'entry');
```

## SQLite

```sql
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('share_scuttle', '1', 'entry');
INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('scuttle_url', 'http://scuttle.org', 'entry');

ALTER TABLE wallabag_entry ADD COLUMN published_at DATETIME DEFAULT NULL;
ALTER TABLE wallabag_entry ADD COLUMN published_by CLOB DEFAULT NULL;

DROP INDEX uid;
DROP INDEX created_at;
DROP INDEX IDX_F4D18282A76ED395;
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, published_at, published_by FROM wallabag_entry;
DROP TABLE wallabag_entry;
CREATE TABLE wallabag_entry (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DEFAULT NULL, published_by CLOB DEFAULT NULL, PRIMARY KEY(id));
INSERT INTO wallabag_entry (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, published_at, published_by) SELECT id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, published_at, published_by FROM __temp__wallabag_entry;
DROP TABLE __temp__wallabag_entry;
CREATE INDEX uid ON wallabag_entry (uid);
CREATE INDEX created_at ON wallabag_entry (created_at);
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id);

DELETE FROM wallabag_craue_config_setting WHERE name = 'download_pictures';

CREATE TABLE wallabag_site_credential (id INTEGER NOT NULL, user_id INTEGER NOT NULL, host VARCHAR(255) NOT NULL, username CLOB NOT NULL, password CLOB NOT NULL, createdAt DATETIME NOT NULL, PRIMARY KEY(id));
CREATE INDEX idx_user ON wallabag_site_credential (user_id);

ALTER TABLE wallabag_entry ADD COLUMN headers CLOB DEFAULT NULL;

CREATE TEMPORARY TABLE __temp__wallabag_annotation AS
SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
FROM wallabag_annotation;
DROP TABLE wallabag_annotation;
CREATE TABLE wallabag_annotation
(
id INTEGER PRIMARY KEY NOT NULL,
user_id INTEGER DEFAULT NULL,
entry_id INTEGER DEFAULT NULL,
text CLOB NOT NULL,
created_at DATETIME NOT NULL,
updated_at DATETIME NOT NULL,
quote CLOB NOT NULL,
ranges CLOB NOT NULL,
CONSTRAINT FK_A7AED006A76ED395 FOREIGN KEY (user_id) REFERENCES wallabag_user (id),
CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES wallabag_entry (id) ON DELETE CASCADE
);
CREATE INDEX IDX_A7AED006A76ED395 ON wallabag_annotation (user_id);
CREATE INDEX IDX_A7AED006BA364942 ON wallabag_annotation (entry_id);
INSERT INTO wallabag_annotation (id, user_id, entry_id, text, created_at, updated_at, quote, ranges) SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
FROM __temp__wallabag_annotation;
DROP TABLE __temp__wallabag_annotation;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('api_user_registration', '0', 'api');

DELETE FROM wallabag_craue_config_setting WHERE name = 'wallabag_url';

ALTER TABLE wallabag_entry ADD COLUMN starred_at DATETIME DEFAULT NULL;

ALTER TABLE wallabag_entry ADD COLUMN origin_url CLOB DEFAULT NULL;

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('store_article_headers', '0', 'entry');

INSERT INTO wallabag_craue_config_setting (name, value, section) VALUES ('shaarli_share_origin_url', '0', 'entry');
```
