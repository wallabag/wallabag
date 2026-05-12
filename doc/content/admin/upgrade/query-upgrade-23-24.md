---
title: From 2.3 to 2.4
weight: 3
---

You'll find all queries to run when upgrading.

We assume that the table prefix is `wallabag_`. Don't forget to backup your database before migrating.

You may encounter issues with indexes names: if so, please change queries with the correct index name.

## MySQL

```sql
ALTER TABLE wallabag_entry ADD archived_at DATETIME DEFAULT NULL;

ALTER TABLE `wallabag_oauth2_access_tokens` CHANGE `token` `token` varchar(191) NOT NULL;
ALTER TABLE `wallabag_oauth2_access_tokens` CHANGE `scope` `scope` varchar(191);
ALTER TABLE `wallabag_oauth2_auth_codes` CHANGE `token` `token` varchar(191) NOT NULL;
ALTER TABLE `wallabag_oauth2_auth_codes` CHANGE `scope` `scope` varchar(191);
ALTER TABLE `wallabag_oauth2_refresh_tokens` CHANGE `token` `token` varchar(191) NOT NULL;
ALTER TABLE `wallabag_oauth2_refresh_tokens` CHANGE `scope` `scope` varchar(191);
ALTER TABLE `wallabag_craue_config_setting` CHANGE `name` `name` varchar(191);
ALTER TABLE `wallabag_craue_config_setting` CHANGE `section` `section` varchar(191);
ALTER TABLE `wallabag_craue_config_setting` CHANGE `value` `value` varchar(191);

ALTER TABLE `wallabag_user` ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL;
ALTER TABLE `wallabag_user` CHANGE twoFactorAuthentication emailTwoFactor BOOLEAN NOT NULL;
ALTER TABLE `wallabag_user` DROP trusted;
ALTER TABLE `wallabag_user` ADD backupCodes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)';

ALTER TABLE wallabag_site_credential ADD updated_at DATETIME DEFAULT NULL;

ALTER TABLE wallabag_entry ADD hashed_url TINYTEXT DEFAULT NULL;
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url(40));

ALTER TABLE `wallabag_config` CHANGE rss_token feed_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE `wallabag_config` CHANGE rss_limit feed_limit INT DEFAULT NULL;

ALTER TABLE `wallabag_oauth2_access_tokens` DROP FOREIGN KEY FK_368A4209A76ED395;
ALTER TABLE `wallabag_oauth2_access_tokens` ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id) ON DELETE CASCADE;
ALTER TABLE `wallabag_oauth2_clients` DROP FOREIGN KEY IDX_user_oauth_client;
ALTER TABLE `wallabag_oauth2_clients` ADD CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE `wallabag_oauth2_refresh_tokens` DROP FOREIGN KEY FK_20C9FB24A76ED395;
ALTER TABLE `wallabag_oauth2_refresh_tokens` ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id) ON DELETE CASCADE;
ALTER TABLE `wallabag_oauth2_auth_codes` DROP FOREIGN KEY FK_EE52E3FAA76ED395;
ALTER TABLE `wallabag_oauth2_auth_codes` ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id) ON DELETE CASCADE;

ALTER TABLE `wallabag_tag` CHANGE `label` `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
ALTER TABLE `wallabag_tag` CHANGE `slug` `slug` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

ALTER TABLE wallabag_entry ADD given_url LONGTEXT DEFAULT NULL, ADD hashed_given_url TINYTEXT DEFAULT NULL;
CREATE INDEX hashed_given_url_user_id ON wallabag_entry (user_id, hashed_given_url(40));

UPDATE wallabag_config SET reading_speed = reading_speed*200;

ALTER TABLE `wallabag_entry` MODIFY language VARCHAR(20) DEFAULT NULL;
CREATE INDEX user_language ON `wallabag_entry` (language, user_id);
CREATE INDEX user_archived ON `wallabag_entry` (user_id, is_archived, archived_at);
CREATE INDEX user_created ON `wallabag_entry` (user_id, created_at);
CREATE INDEX user_starred ON `wallabag_entry` (user_id, is_starred, starred_at);
CREATE INDEX tag_label ON `wallabag_tag` (label (255));
CREATE INDEX config_feed_token ON `wallabag_config` (feed_token (255));

ALTER TABLE `wallabag_craue_config_setting` RENAME `wallabag_internal_setting`;

CREATE TABLE wallabag_ignore_origin_user_rule (id INT AUTO_INCREMENT NOT NULL, config_id INT NOT NULL, rule VARCHAR(255) NOT NULL, INDEX idx_config (config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE wallabag_ignore_origin_instance_rule (id INT AUTO_INCREMENT NOT NULL, rule VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE wallabag_ignore_origin_user_rule ADD CONSTRAINT fk_config FOREIGN KEY (config_id) REFERENCES `wallabag_config` (id);

UPDATE wallabag_internal_setting SET name = 'matomo_enabled' where name = 'piwik_enabled';
UPDATE wallabag_internal_setting SET name = 'matomo_host' where name = 'piwik_host';
UPDATE wallabag_internal_setting SET name = 'matomo_site_id' where name = 'piwik_site_id';
```

## PostgreSQL

```sql
ALTER TABLE wallabag_entry ADD archived_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;

ALTER TABLE "wallabag_user" ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL;
ALTER TABLE "wallabag_user" RENAME COLUMN twofactorauthentication TO emailTwoFactor;
ALTER TABLE "wallabag_user" DROP trusted;
ALTER TABLE "wallabag_user" ADD backupCodes TEXT DEFAULT NULL;

ALTER TABLE wallabag_site_credential ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;

ALTER TABLE wallabag_entry ADD hashed_url TEXT DEFAULT NULL;
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url);

ALTER TABLE "wallabag_config" RENAME COLUMN rss_token TO feed_token;
ALTER TABLE "wallabag_config" RENAME COLUMN rss_limit TO feed_limit;

ALTER TABLE "wallabag_oauth2_access_tokens" DROP CONSTRAINT FK_368A4209A76ED395;
ALTER TABLE "wallabag_oauth2_access_tokens" ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE "wallabag_oauth2_clients" DROP CONSTRAINT idx_user_oauth_client;
ALTER TABLE "wallabag_oauth2_clients" ADD CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE "wallabag_oauth2_refresh_tokens" DROP CONSTRAINT FK_20C9FB24A76ED395;
ALTER TABLE "wallabag_oauth2_refresh_tokens" ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE "wallabag_oauth2_auth_codes" DROP CONSTRAINT FK_EE52E3FAA76ED395;
ALTER TABLE "wallabag_oauth2_auth_codes" ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE wallabag_entry ADD given_url TEXT DEFAULT NULL;
ALTER TABLE wallabag_entry ADD hashed_given_url TEXT DEFAULT NULL;
CREATE INDEX hashed_given_url_user_id ON wallabag_entry (user_id, hashed_given_url);

UPDATE wallabag_config SET reading_speed = reading_speed*200;

ALTER TABLE "wallabag_entry" ALTER language TYPE VARCHAR(20);
CREATE INDEX user_language ON "wallabag_entry" (language, user_id);
CREATE INDEX user_archived ON "wallabag_entry" (user_id, is_archived, archived_at);
CREATE INDEX user_created ON "wallabag_entry" (user_id, created_at);
CREATE INDEX user_starred ON "wallabag_entry" (user_id, is_starred, starred_at);
CREATE INDEX tag_label ON "wallabag_tag" (label);
CREATE INDEX config_feed_token ON "wallabag_config" (feed_token);

ALTER TABLE "wallabag_craue_config_setting" RENAME TO "wallabag_internal_setting";

CREATE SEQUENCE ignore_origin_user_rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE ignore_origin_instance_rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE wallabag_ignore_origin_user_rule (id SERIAL NOT NULL, config_id INT NOT NULL, rule VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE INDEX idx_config ON wallabag_ignore_origin_user_rule (config_id);
CREATE TABLE wallabag_ignore_origin_instance_rule (id SERIAL NOT NULL, rule VARCHAR(255) NOT NULL, PRIMARY KEY(id));
ALTER TABLE wallabag_ignore_origin_user_rule ADD CONSTRAINT fk_config FOREIGN KEY (config_id) REFERENCES "wallabag_config" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;

UPDATE wallabag_internal_setting SET name = 'matomo_enabled' where name = 'piwik_enabled';
UPDATE wallabag_internal_setting SET name = 'matomo_host' where name = 'piwik_host';
UPDATE wallabag_internal_setting SET name = 'matomo_site_id' where name = 'piwik_site_id';
```

## SQLite

```sql
ALTER TABLE wallabag_entry ADD COLUMN archived_at DATETIME DEFAULT NULL;

DROP INDEX UNIQ_1D63E7E5C05FB297;
DROP INDEX UNIQ_1D63E7E5A0D96FBF;
DROP INDEX UNIQ_1D63E7E592FC23A8;
CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication FROM wallabag_user;
DROP TABLE wallabag_user;
CREATE TABLE wallabag_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, username_canonical VARCHAR(180) NOT NULL COLLATE BINARY, email VARCHAR(180) NOT NULL COLLATE BINARY, email_canonical VARCHAR(180) NOT NULL COLLATE BINARY, enabled BOOLEAN NOT NULL, password VARCHAR(255) NOT NULL COLLATE BINARY, last_login DATETIME DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, name CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, emailTwoFactor BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
  , googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json_array)
  );
INSERT INTO  wallabag_user (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, emailTwoFactor) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication FROM __temp__user;
DROP TABLE __temp__user;
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON wallabag_user (confirmation_token);
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON wallabag_user (email_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON wallabag_user (username_canonical);

ALTER TABLE wallabag_site_credential ADD COLUMN updated_at DATETIME DEFAULT NULL;

DROP INDEX IDX_entry_uid;
DROP INDEX IDX_F4D18282A76ED395;
DROP INDEX IDX_entry_created_at;
DROP INDEX IDX_entry_starred;
DROP INDEX IDX_entry_archived;
CREATE TEMPORARY TABLE __temp__entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at FROM wallabag_entry;
DROP TABLE wallabag_entry;
CREATE TABLE wallabag_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, published_by CLOB DEFAULT NULL COLLATE BINARY, headers CLOB DEFAULT NULL COLLATE BINARY, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, hashed_url CLOB DEFAULT NULL);
INSERT INTO  wallabag_entry (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at FROM __temp__entry;
DROP TABLE __temp__entry;
CREATE INDEX IDX_entry_uid ON wallabag_entry (uid);
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id);
CREATE INDEX IDX_entry_created_at ON wallabag_entry (created_at);
CREATE INDEX IDX_entry_starred ON wallabag_entry (is_starred);
CREATE INDEX IDX_entry_archived ON wallabag_entry (is_archived);
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url);

DROP INDEX UNIQ_87E64C53A76ED395;
CREATE TEMPORARY TABLE __temp__config AS SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM wallabag_config;
DROP TABLE wallabag_config;
CREATE TABLE wallabag_config (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL COLLATE BINARY, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL COLLATE BINARY, action_mark_as_read INTEGER DEFAULT 0, list_mode INTEGER DEFAULT NULL, feed_token VARCHAR(255) DEFAULT NULL, feed_limit INTEGER DEFAULT NULL, CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_config (id, user_id, theme, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode) SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM __temp__config;
DROP TABLE __temp__config;
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON wallabag_config (user_id);

DROP INDEX IDX_368A4209A76ED395;
DROP INDEX IDX_368A420919EB6921;
DROP INDEX UNIQ_368A42095F37A13B;
CREATE TEMPORARY TABLE __temp__oauth2_access_tokens AS SELECT id, client_id, user_id, token, expires_at, scope FROM wallabag_oauth2_access_tokens;
DROP TABLE wallabag_oauth2_access_tokens;
CREATE TABLE wallabag_oauth2_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_oauth2_clients" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_oauth2_access_tokens (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__oauth2_access_tokens;
DROP TABLE __temp__oauth2_access_tokens;
CREATE INDEX IDX_368A4209A76ED395 ON wallabag_oauth2_access_tokens (user_id);
CREATE INDEX IDX_368A420919EB6921 ON wallabag_oauth2_access_tokens (client_id);
DROP INDEX IDX_635D765EA76ED395;
CREATE TEMPORARY TABLE __temp__oauth2_clients AS SELECT id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name FROM wallabag_oauth2_clients;
DROP TABLE wallabag_oauth2_clients;
CREATE TABLE wallabag_oauth2_clients (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL, allowed_grant_types CLOB NOT NULL, CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_oauth2_clients" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_oauth2_clients (id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name) SELECT id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name FROM __temp__oauth2_clients;
DROP TABLE __temp__oauth2_clients;
CREATE INDEX IDX_635D765EA76ED395 ON wallabag_oauth2_clients (user_id);
DROP INDEX IDX_20C9FB24A76ED395;
DROP INDEX IDX_20C9FB2419EB6921;
DROP INDEX UNIQ_20C9FB245F37A13B;
CREATE TEMPORARY TABLE __temp__oauth2_refresh_tokens AS SELECT id, client_id, user_id, token, expires_at, scope FROM wallabag_oauth2_refresh_tokens;
DROP TABLE wallabag_oauth2_refresh_tokens;
CREATE TABLE wallabag_oauth2_refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_oauth2_clients" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_oauth2_refresh_tokens (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__oauth2_refresh_tokens;
DROP TABLE __temp__oauth2_refresh_tokens;
CREATE INDEX IDX_20C9FB24A76ED395 ON wallabag_oauth2_refresh_tokens (user_id);
CREATE INDEX IDX_20C9FB2419EB6921 ON wallabag_oauth2_refresh_tokens (client_id);
DROP INDEX IDX_EE52E3FAA76ED395;
DROP INDEX IDX_EE52E3FA19EB6921;
DROP INDEX UNIQ_EE52E3FA5F37A13B;
CREATE TEMPORARY TABLE __temp__oauth2_auth_codes AS SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM wallabag_oauth2_auth_codes;
DROP TABLE wallabag_oauth2_auth_codes;
CREATE TABLE wallabag_oauth2_auth_codes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, redirect_uri CLOB NOT NULL COLLATE BINARY, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_oauth2_clients" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_oauth2_auth_codes (id, client_id, user_id, token, redirect_uri, expires_at, scope) SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM __temp__oauth2_auth_codes;
DROP TABLE __temp__oauth2_auth_codes;
CREATE INDEX IDX_EE52E3FAA76ED395 ON wallabag_oauth2_auth_codes (user_id);
CREATE INDEX IDX_EE52E3FA19EB6921 ON wallabag_oauth2_auth_codes (client_id);

DROP INDEX hashed_url_user_id;
DROP INDEX IDX_entry_archived;
DROP INDEX IDX_entry_starred;
DROP INDEX IDX_entry_created_at;
DROP INDEX IDX_F4D18282A76ED395;
DROP INDEX IDX_entry_uid;
CREATE TEMPORARY TABLE __temp__entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url FROM wallabag_entry;
DROP TABLE wallabag_entry;
CREATE TABLE wallabag_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, published_by CLOB DEFAULT NULL COLLATE BINARY, headers CLOB DEFAULT NULL COLLATE BINARY, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, hashed_url CLOB DEFAULT NULL COLLATE BINARY, given_url CLOB DEFAULT NULL, hashed_given_url CLOB DEFAULT NULL);
INSERT INTO  wallabag_entry (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url FROM __temp__entry;
DROP TABLE __temp__entry;
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url);
CREATE INDEX IDX_entry_archived ON wallabag_entry (is_archived);
CREATE INDEX IDX_entry_starred ON wallabag_entry (is_starred);
CREATE INDEX IDX_entry_created_at ON wallabag_entry (created_at);
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id);
CREATE INDEX IDX_entry_uid ON wallabag_entry (uid);
CREATE INDEX hashed_given_url_user_id ON wallabag_entry (user_id, hashed_given_url);

UPDATE  wallabag_entry SET reading_time = 0 WHERE reading_time IS NULL;
DROP INDEX hashed_given_url_user_id;
DROP INDEX IDX_entry_uid;
DROP INDEX IDX_F4D18282A76ED395;
DROP INDEX IDX_entry_created_at;
DROP INDEX IDX_entry_starred;
DROP INDEX IDX_entry_archived;
DROP INDEX hashed_url_user_id;
CREATE TEMPORARY TABLE __temp__entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url FROM wallabag_entry;
DROP TABLE wallabag_entry;
CREATE TABLE wallabag_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL --(DC2Type:array)
  , headers CLOB DEFAULT NULL --(DC2Type:array)
  , hashed_url VARCHAR(40) DEFAULT NULL, hashed_given_url VARCHAR(40) DEFAULT NULL, CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_entry (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url FROM __temp__entry;
DROP TABLE __temp__entry;
CREATE INDEX hashed_given_url_user_id ON wallabag_entry (user_id, hashed_given_url);
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id);
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url);
CREATE INDEX created_at ON wallabag_entry (created_at);
CREATE INDEX uid ON wallabag_entry (uid);

UPDATE  wallabag_config SET reading_speed = reading_speed*200;

DROP INDEX uid;
DROP INDEX created_at;
DROP INDEX hashed_url_user_id;
DROP INDEX IDX_F4D18282A76ED395;
DROP INDEX hashed_given_url_user_id;
CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url FROM wallabag_entry;
DROP TABLE wallabag_entry;
CREATE TABLE wallabag_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL COLLATE BINARY --(DC2Type:array)
  , headers CLOB DEFAULT NULL COLLATE BINARY --(DC2Type:array)
  , hashed_url VARCHAR(40) DEFAULT NULL COLLATE BINARY, hashed_given_url VARCHAR(40) DEFAULT NULL COLLATE BINARY, language VARCHAR(20) DEFAULT NULL, CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
INSERT INTO  wallabag_entry (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url FROM __temp__wallabag_entry;
DROP TABLE __temp__wallabag_entry;
CREATE INDEX uid ON wallabag_entry (uid);
CREATE INDEX created_at ON wallabag_entry (created_at);
CREATE INDEX hashed_url_user_id ON wallabag_entry (user_id, hashed_url);
CREATE INDEX IDX_F4D18282A76ED395 ON wallabag_entry (user_id);
CREATE INDEX hashed_given_url_user_id ON wallabag_entry (user_id, hashed_given_url);
CREATE INDEX user_language ON wallabag_entry (language, user_id);
CREATE INDEX user_archived ON wallabag_entry (user_id, is_archived, archived_at);
CREATE INDEX user_created ON wallabag_entry (user_id, created_at);
CREATE INDEX user_starred ON wallabag_entry (user_id, is_starred, starred_at);
CREATE INDEX tag_label ON wallabag_tag (label);
CREATE INDEX config_feed_token ON wallabag_config (feed_token);

ALTER TABLE wallabag_craue_config_setting RENAME TO wallabag_internal_setting;

CREATE TABLE wallabag_ignore_origin_user_rule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_id INTEGER NOT NULL, rule VARCHAR(255) NOT NULL);
CREATE INDEX idx_config ON wallabag_ignore_origin_user_rule (config_id);
CREATE TABLE wallabag_ignore_origin_instance_rule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rule VARCHAR(255) NOT NULL);

UPDATE  wallabag_internal_setting SET name = 'matomo_enabled' where name = 'piwik_enabled';
UPDATE  wallabag_internal_setting SET name = 'matomo_host' where name = 'piwik_host';
UPDATE  wallabag_internal_setting SET name = 'matomo_site_id' where name = 'piwik_site_id';
```
