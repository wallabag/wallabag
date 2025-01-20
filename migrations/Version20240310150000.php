<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20240310150000 extends WallabagMigration
{
    public function getDescription(): string
    {
        return 'Fix schema';
    }

    public function up(Schema $schema): void
    {
        switch (true) {
            case $this->platform instanceof MySQLPlatform:
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('annotation') . ' WHERE text IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('annotation') . '" with null value on "text" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('entry') . ' WHERE is_not_parsed IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('entry') . '" with null value on "is_not_parsed" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('tag') . ' WHERE label IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('tag') . '" with null value on "label" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('tag') . ' WHERE slug IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('tag') . '" with null value on "slug" column.');
                break;
            case $this->platform instanceof PostgreSQLPlatform:
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('entry') . ' WHERE is_not_parsed IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('entry') . '" with null value on "is_not_parsed" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('ignore_origin_user_rule') . ' WHERE id IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('ignore_origin_user_rule') . '" with null value on "id" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('ignore_origin_instance_rule') . ' WHERE id IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('ignore_origin_instance_rule') . '" with null value on "id" column.');
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('site_credential') . ' WHERE id IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('site_credential') . '" with null value on "id" column.');
                break;
            case $this->platform instanceof SqlitePlatform:
                $this->abortIf($this->connection->executeQuery('SELECT COUNT(*) FROM ' . $this->getTable('entry') . ' WHERE is_not_parsed IS NULL')->fetchOne() > 0, 'There are rows in the table "' . $this->getTable('entry') . '" with null value on "is_not_parsed" column.');
                break;
        }

        switch (true) {
            case $this->platform instanceof MySQLPlatform:
                $this->write('Align database schema with Doctrine metadata.');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' CHANGE name name LONGTEXT NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE salt salt VARCHAR(255) DEFAULT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE password password VARCHAR(255) NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE googleAuthenticatorSecret googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE published_by published_by LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\';');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE headers headers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\';');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE hashed_url hashed_url VARCHAR(40) DEFAULT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE hashed_given_url hashed_given_url VARCHAR(40) DEFAULT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE is_not_parsed is_not_parsed TINYINT(1) DEFAULT 0 NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;');
                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE label label LONGTEXT NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE slug slug VARCHAR(128) NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE text text LONGTEXT NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE quote quote LONGTEXT NOT NULL;');
                break;
            case $this->platform instanceof PostgreSQLPlatform:
                $this->write('Align database schema with Doctrine metadata.');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' ALTER name TYPE TEXT;');

                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_instance_rule') . ' ALTER id DROP DEFAULT;');

                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER salt DROP NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER confirmation_token TYPE VARCHAR(180);');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER googleauthenticatorsecret TYPE VARCHAR(255);');

                $this->addSql('COMMENT ON COLUMN ' . $this->getTable('entry') . '.published_by IS \'(DC2Type:array)\';');
                $this->addSql('COMMENT ON COLUMN ' . $this->getTable('entry') . '.headers IS \'(DC2Type:array)\';');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER hashed_url TYPE VARCHAR(40);');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER hashed_given_url TYPE VARCHAR(40);');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER is_not_parsed SET NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('site_credential') . ' ALTER id DROP DEFAULT;');

                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_user_rule') . ' ALTER id DROP DEFAULT;');
                break;
        }

        if ($this->platform instanceof MySQLPlatform || $this->platform instanceof PostgreSQLPlatform) {
            $this->write('Synchronize indexes and foreign keys with the default naming convention.');

            $this->renameForeignKey('annotation', 'FK_A7AED006A76ED395', 'user', 'user_id');
            $this->renameForeignKey('annotation', 'FK_annotation_entry', 'entry', 'entry_id', true);
            $this->renameForeignKey('config', 'FK_87E64C53A76ED395', 'user', 'user_id');
            $this->renameForeignKey('entry', 'FK_F4D18282A76ED395', 'user', 'user_id');
            $this->renameForeignKey('entry_tag', 'FK_entry_tag_entry', 'entry', 'entry_id', true);
            $this->renameForeignKey('entry_tag', 'FK_entry_tag_tag', 'tag', 'tag_id', true);
            $this->renameForeignKey('ignore_origin_user_rule', 'fk_config', 'config', 'config_id');
            $this->renameForeignKey('oauth2_access_tokens', 'FK_368A4209A76ED395', 'user', 'user_id', true);
            $this->renameForeignKey('oauth2_access_tokens', 'FK_368A420919EB6921', 'oauth2_clients', 'client_id');
            $this->renameForeignKey('oauth2_auth_codes', 'FK_EE52E3FA19EB6921', 'oauth2_clients', 'client_id');
            $this->renameForeignKey('oauth2_auth_codes', 'FK_EE52E3FAA76ED395', 'user', 'user_id', true);
            $this->renameForeignKey('oauth2_clients', 'FK_635D765EA76ED395', 'user', 'user_id');
            $this->renameForeignKey('oauth2_refresh_tokens', 'FK_20C9FB24A76ED395', 'user', 'user_id', true);
            $this->renameForeignKey('oauth2_refresh_tokens', 'FK_20C9FB2419EB6921', 'oauth2_clients', 'client_id');
            $this->renameForeignKey('site_credential', 'fk_user', 'user', 'user_id');
            $this->renameForeignKey('tagging_rule', 'FK_2D9B3C5424DB0683', 'config', 'config_id');

            $this->renameIndex('annotation', 'idx_a7aed006a76ed395', 'user_id');
            $this->renameIndex('annotation', 'idx_a7aed006ba364942', 'entry_id');
            $this->renameIndex('config', 'config_feed_token', 'feed_token');
            $this->renameIndex('entry', 'hashed_given_url_user_id', ['user_id', 'hashed_given_url']);
            $this->renameIndex('entry', 'hashed_url_user_id', ['user_id', 'hashed_url']);
            $this->renameIndex('entry', 'idx_entry_created_at', 'created_at');
            $this->renameIndex('entry', 'idx_entry_uid', 'uid');
            $this->renameIndex('entry', 'idx_f4d18282a76ed395', 'user_id');
            $this->renameIndex('entry', 'user_archived', ['user_id', 'is_archived', 'archived_at']);
            $this->renameIndex('entry', 'user_created', ['user_id', 'created_at']);
            $this->renameIndex('entry', 'user_language', ['language', 'user_id']);
            $this->renameIndex('entry', 'user_starred', ['user_id', 'is_starred', 'starred_at']);
            $this->renameIndex('entry_tag', 'idx_c9f0dd7cba364942', 'entry_id');
            $this->renameIndex('entry_tag', 'idx_c9f0dd7cbad26311', 'tag_id');
            $this->renameIndex('ignore_origin_user_rule', 'idx_config', 'config_id');
            $this->renameIndex('oauth2_access_tokens', 'idx_368a4209a76ed395', 'user_id');
            $this->renameIndex('oauth2_access_tokens', 'idx_368a420919eb6921', 'client_id');
            $this->renameIndex('oauth2_auth_codes', 'IDX_EE52E3FA19EB6921', 'client_id');
            $this->renameIndex('oauth2_auth_codes', 'IDX_EE52E3FAA76ED395', 'user_id');
            $this->renameIndex('oauth2_refresh_tokens', 'idx_20c9fb24a76ed395', 'user_id');
            $this->renameIndex('oauth2_refresh_tokens', 'idx_20c9fb2419eb6921', 'client_id');
            $this->renameIndex('site_credential', 'idx_user', 'user_id');
            $this->renameIndex('tag', 'tag_label', 'label');
            $this->renameIndex('tagging_rule', 'idx_2d9b3c5424db0683', 'config_id');

            $this->renameUniqueIndex('config', 'uniq_87e64c53a76ed395', 'user_id');
            $this->renameUniqueIndex('tag', 'uniq_4ca58a8c989d9b62', 'slug');
            $this->renameUniqueIndex('user', 'uniq_1d63e7e5a0d96fbf', 'email_canonical');
            $this->renameUniqueIndex('user', 'uniq_1d63e7e5c05fb297', 'confirmation_token');
            $this->renameUniqueIndex('user', 'uniq_1d63e7e592fc23a8', 'username_canonical');
            $this->renameUniqueIndex('oauth2_access_tokens', 'UNIQ_368A42095F37A13B', 'token');
            $this->renameUniqueIndex('oauth2_auth_codes', 'UNIQ_EE52E3FA5F37A13B', 'token');
            $this->renameUniqueIndex('oauth2_refresh_tokens', 'UNIQ_20C9FB245F37A13B', 'token');

            $this->dropIndex('entry', 'is_archived', 'IDX_entry_archived');
            $this->dropIndex('entry', 'is_starred', 'IDX_entry_starred');

            $this->dropUniqueIndex('internal_setting', 'name', 'UNIQ_5D9649505E237E06');
        }

        if ($this->platform instanceof SqlitePlatform) {
            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_annotation AS SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges FROM ' . $this->getTable('annotation'));
            $this->addSql('DROP TABLE ' . $this->getTable('annotation'));
            $this->addSql('CREATE TABLE ' . $this->getTable('annotation') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, entry_id INTEGER DEFAULT NULL, text CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, quote CLOB NOT NULL, ranges CLOB NOT NULL --(DC2Type:array)
        , CONSTRAINT ' . $this->getForeignKeyName('annotation', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('annotation', 'entry_id') . ' FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('annotation') . ' (id, user_id, entry_id, text, created_at, updated_at, quote, ranges) SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges FROM __temp__wallabag_annotation');
            $this->addSql('DROP TABLE __temp__wallabag_annotation');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('annotation', 'user_id') . ' ON ' . $this->getTable('annotation') . ' (user_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('annotation', 'entry_id') . ' ON ' . $this->getTable('annotation') . ' (entry_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_config AS SELECT id, user_id, items_per_page, language, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, feed_token, feed_limit, display_thumbnails, custom_css, font, fontsize, line_height, max_width FROM ' . $this->getTable('config'));
            $this->addSql('DROP TABLE ' . $this->getTable('config'));
            $this->addSql('CREATE TABLE ' . $this->getTable('config') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL, action_mark_as_read INTEGER DEFAULT 0, list_mode INTEGER DEFAULT NULL, feed_token VARCHAR(255) DEFAULT NULL, feed_limit INTEGER DEFAULT NULL, display_thumbnails INTEGER DEFAULT 1, custom_css CLOB DEFAULT NULL, font CLOB DEFAULT NULL, fontsize DOUBLE PRECISION DEFAULT NULL, line_height DOUBLE PRECISION DEFAULT NULL, max_width DOUBLE PRECISION DEFAULT NULL, CONSTRAINT ' . $this->getForeignKeyName('config', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('config') . ' (id, user_id, items_per_page, language, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, feed_token, feed_limit, display_thumbnails, custom_css, font, fontsize, line_height, max_width) SELECT id, user_id, items_per_page, language, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, feed_token, feed_limit, display_thumbnails, custom_css, font, fontsize, line_height, max_width FROM __temp__wallabag_config');
            $this->addSql('DROP TABLE __temp__wallabag_config');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('config', 'user_id') . ' ON ' . $this->getTable('config') . ' (user_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('config', 'feed_token') . ' ON ' . $this->getTable('config') . ' (feed_token)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url, language, is_not_parsed FROM ' . $this->getTable('entry'));
            $this->addSql('DROP TABLE ' . $this->getTable('entry'));
            $this->addSql('CREATE TABLE ' . $this->getTable('entry') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL, url CLOB DEFAULT NULL, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL, domain_name CLOB DEFAULT NULL, preview_picture CLOB DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL, http_status VARCHAR(3) DEFAULT NULL, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL --(DC2Type:array)
        , headers CLOB DEFAULT NULL --(DC2Type:array)
        , hashed_url VARCHAR(40) DEFAULT NULL, hashed_given_url VARCHAR(40) DEFAULT NULL, language VARCHAR(20) DEFAULT NULL, is_not_parsed BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT ' . $this->getForeignKeyName('entry', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('entry') . ' (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url, language, is_not_parsed) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url, language, is_not_parsed FROM __temp__wallabag_entry');
            $this->addSql('DROP TABLE __temp__wallabag_entry');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', 'user_id') . ' ON ' . $this->getTable('entry') . ' (user_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', 'created_at') . ' ON ' . $this->getTable('entry') . ' (created_at)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', 'uid') . ' ON ' . $this->getTable('entry') . ' (uid)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['user_id', 'hashed_url']) . ' ON ' . $this->getTable('entry') . ' (user_id, hashed_url)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['user_id', 'hashed_given_url']) . ' ON ' . $this->getTable('entry') . ' (user_id, hashed_given_url)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['language', 'user_id']) . ' ON ' . $this->getTable('entry') . ' (language, user_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['user_id', 'is_archived', 'archived_at']) . ' ON ' . $this->getTable('entry') . ' (user_id, is_archived, archived_at)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['user_id', 'created_at']) . ' ON ' . $this->getTable('entry') . ' (user_id, created_at)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry', ['user_id', 'is_starred', 'starred_at']) . ' ON ' . $this->getTable('entry') . ' (user_id, is_starred, starred_at)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry_tag AS SELECT entry_id, tag_id FROM ' . $this->getTable('entry_tag'));
            $this->addSql('DROP TABLE ' . $this->getTable('entry_tag'));
            $this->addSql('CREATE TABLE ' . $this->getTable('entry_tag') . ' (entry_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(entry_id, tag_id), CONSTRAINT ' . $this->getForeignKeyName('entry_tag', 'entry_id') . ' FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('entry_tag', 'tag_id') . ' FOREIGN KEY (tag_id) REFERENCES ' . $this->getTable('tag') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('entry_tag') . ' (entry_id, tag_id) SELECT entry_id, tag_id FROM __temp__wallabag_entry_tag');
            $this->addSql('DROP TABLE __temp__wallabag_entry_tag');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry_tag', 'tag_id') . ' ON ' . $this->getTable('entry_tag') . ' (tag_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('entry_tag', 'entry_id') . ' ON ' . $this->getTable('entry_tag') . ' (entry_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_ignore_origin_user_rule AS SELECT id, config_id, rule FROM ' . $this->getTable('ignore_origin_user_rule'));
            $this->addSql('DROP TABLE ' . $this->getTable('ignore_origin_user_rule'));
            $this->addSql('CREATE TABLE ' . $this->getTable('ignore_origin_user_rule') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_id INTEGER NOT NULL, rule VARCHAR(255) NOT NULL, CONSTRAINT ' . $this->getForeignKeyName('ignore_origin_user_rule', 'config_id') . ' FOREIGN KEY (config_id) REFERENCES ' . $this->getTable('config') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('ignore_origin_user_rule') . ' (id, config_id, rule) SELECT id, config_id, rule FROM __temp__wallabag_ignore_origin_user_rule');
            $this->addSql('DROP TABLE __temp__wallabag_ignore_origin_user_rule');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('ignore_origin_user_rule', 'config_id') . ' ON ' . $this->getTable('ignore_origin_user_rule') . ' (config_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_internal_setting AS SELECT name, value, section FROM ' . $this->getTable('internal_setting'));
            $this->addSql('DROP TABLE ' . $this->getTable('internal_setting'));
            $this->addSql('CREATE TABLE ' . $this->getTable('internal_setting') . ' (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name))');
            $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . ' (name, value, section) SELECT name, value, section FROM __temp__wallabag_internal_setting');
            $this->addSql('DROP TABLE __temp__wallabag_internal_setting');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_access_tokens AS SELECT id, client_id, user_id, expires_at, token, scope FROM ' . $this->getTable('oauth2_access_tokens'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_access_tokens'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_access_tokens') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, scope VARCHAR(255) DEFAULT NULL, CONSTRAINT ' . $this->getForeignKeyName('oauth2_access_tokens', 'client_id') . ' FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('oauth2_access_tokens', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_access_tokens') . ' (id, client_id, user_id, expires_at, token, scope) SELECT id, client_id, user_id, expires_at, token, scope FROM __temp__wallabag_oauth2_access_tokens');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_access_tokens');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_access_tokens', 'client_id') . ' ON ' . $this->getTable('oauth2_access_tokens') . ' (client_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_access_tokens', 'user_id') . ' ON ' . $this->getTable('oauth2_access_tokens') . ' (user_id)');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('oauth2_access_tokens', 'token') . ' ON ' . $this->getTable('oauth2_access_tokens') . ' (token)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_auth_codes AS SELECT id, client_id, user_id, redirect_uri, expires_at, token, scope FROM ' . $this->getTable('oauth2_auth_codes'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_auth_codes'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_auth_codes') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, redirect_uri CLOB NOT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, scope VARCHAR(255) DEFAULT NULL, CONSTRAINT ' . $this->getForeignKeyName('oauth2_auth_codes', 'client_id') . ' FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('oauth2_auth_codes', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_auth_codes') . ' (id, client_id, user_id, redirect_uri, expires_at, token, scope) SELECT id, client_id, user_id, redirect_uri, expires_at, token, scope FROM __temp__wallabag_oauth2_auth_codes');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_auth_codes');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_auth_codes', 'client_id') . ' ON ' . $this->getTable('oauth2_auth_codes') . ' (client_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_auth_codes', 'user_id') . ' ON ' . $this->getTable('oauth2_auth_codes') . ' (user_id)');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('oauth2_auth_codes', 'token') . ' ON ' . $this->getTable('oauth2_auth_codes') . ' (token)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_clients AS SELECT id, user_id, random_id, secret, name, redirect_uris, allowed_grant_types FROM ' . $this->getTable('oauth2_clients'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_clients'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_clients') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, name CLOB NOT NULL, redirect_uris CLOB NOT NULL --(DC2Type:array)
        , allowed_grant_types CLOB NOT NULL --(DC2Type:array)
        , CONSTRAINT ' . $this->getForeignKeyName('oauth2_clients', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_clients') . ' (id, user_id, random_id, secret, name, redirect_uris, allowed_grant_types) SELECT id, user_id, random_id, secret, name, redirect_uris, allowed_grant_types FROM __temp__wallabag_oauth2_clients');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_clients');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_clients', 'user_id') . ' ON ' . $this->getTable('oauth2_clients') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_refresh_tokens AS SELECT id, client_id, user_id, expires_at, token, scope FROM ' . $this->getTable('oauth2_refresh_tokens'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_refresh_tokens'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, scope VARCHAR(255) DEFAULT NULL, CONSTRAINT ' . $this->getForeignKeyName('oauth2_refresh_tokens', 'client_id') . ' FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('oauth2_refresh_tokens', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_refresh_tokens') . ' (id, client_id, user_id, expires_at, token, scope) SELECT id, client_id, user_id, expires_at, token, scope FROM __temp__wallabag_oauth2_refresh_tokens');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_refresh_tokens');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_refresh_tokens', 'client_id') . ' ON ' . $this->getTable('oauth2_refresh_tokens') . ' (client_id)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('oauth2_refresh_tokens', 'user_id') . ' ON ' . $this->getTable('oauth2_refresh_tokens') . ' (user_id)');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('oauth2_refresh_tokens', 'token') . ' ON ' . $this->getTable('oauth2_refresh_tokens') . ' (token)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_site_credential AS SELECT id, user_id, host, username, password, createdAt, updated_at FROM ' . $this->getTable('site_credential'));
            $this->addSql('DROP TABLE ' . $this->getTable('site_credential'));
            $this->addSql('CREATE TABLE ' . $this->getTable('site_credential') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, host VARCHAR(255) NOT NULL, username CLOB NOT NULL, password CLOB NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT ' . $this->getForeignKeyName('site_credential', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('site_credential') . ' (id, user_id, host, username, password, createdAt, updated_at) SELECT id, user_id, host, username, password, createdAt, updated_at FROM __temp__wallabag_site_credential');
            $this->addSql('DROP TABLE __temp__wallabag_site_credential');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('site_credential', 'user_id') . ' ON ' . $this->getTable('site_credential') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_tag AS SELECT id, label, slug FROM ' . $this->getTable('tag'));
            $this->addSql('DROP TABLE ' . $this->getTable('tag'));
            $this->addSql('CREATE TABLE ' . $this->getTable('tag') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label CLOB NOT NULL, slug VARCHAR(128) NOT NULL)');
            $this->addSql('INSERT INTO ' . $this->getTable('tag') . ' (id, label, slug) SELECT id, label, slug FROM __temp__wallabag_tag');
            $this->addSql('DROP TABLE __temp__wallabag_tag');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('tag', 'slug') . ' ON ' . $this->getTable('tag') . ' (slug)');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('tag', 'label') . ' ON ' . $this->getTable('tag') . ' (label)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_tagging_rule AS SELECT id, config_id, rule, tags FROM ' . $this->getTable('tagging_rule'));
            $this->addSql('DROP TABLE ' . $this->getTable('tagging_rule'));
            $this->addSql('CREATE TABLE ' . $this->getTable('tagging_rule') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_id INTEGER DEFAULT NULL, rule VARCHAR(255) NOT NULL, tags CLOB NOT NULL --(DC2Type:simple_array)
        , CONSTRAINT ' . $this->getForeignKeyName('tagging_rule', 'config_id') . ' FOREIGN KEY (config_id) REFERENCES ' . $this->getTable('config') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('tagging_rule') . ' (id, config_id, rule, tags) SELECT id, config_id, rule, tags FROM __temp__wallabag_tagging_rule');
            $this->addSql('DROP TABLE __temp__wallabag_tagging_rule');
            $this->addSql('CREATE INDEX ' . $this->getIndexName('tagging_rule', 'config_id') . ' ON ' . $this->getTable('tagging_rule') . ' (config_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM ' . $this->getTable('user'));
            $this->addSql('DROP TABLE ' . $this->getTable('user'));
            $this->addSql('CREATE TABLE ' . $this->getTable('user') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
        , name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json)
        , emailTwoFactor BOOLEAN NOT NULL)');
            $this->addSql('INSERT INTO ' . $this->getTable('user') . ' (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM __temp__user');
            $this->addSql('DROP TABLE __temp__user');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('user', 'username_canonical') . ' ON ' . $this->getTable('user') . ' (username_canonical)');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('user', 'email_canonical') . ' ON ' . $this->getTable('user') . ' (email_canonical)');
            $this->addSql('CREATE UNIQUE INDEX ' . $this->getUniqueIndexName('user', 'confirmation_token') . ' ON ' . $this->getTable('user') . ' (confirmation_token)');
        }
    }

    public function down(Schema $schema): void
    {
        switch (true) {
            case $this->platform instanceof MySQLPlatform:
                $this->write('Revert: Align database schema with Doctrine metadata.');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' CHANGE name name LONGBLOB NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE salt salt VARCHAR(180) NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE password password VARCHAR(180) NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE googleAuthenticatorSecret googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE published_by published_by LONGTEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE headers headers LONGTEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE hashed_url hashed_url TINYTEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE hashed_given_url hashed_given_url TINYTEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE is_not_parsed is_not_parsed TINYINT(1) DEFAULT 0;');

                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE label label LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;');
                $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;');

                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE text text LONGTEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE quote quote TEXT NOT NULL;');
                break;
            case $this->platform instanceof PostgreSQLPlatform:
                $this->write('Revert: Align database schema with Doctrine metadata.');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' ALTER name TYPE BYTEA USING name::bytea;');

                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_instance_rule') . ' ALTER id SET DEFAULT nextval(\'' . $this->getTable('ignore_origin_instance_rule_id_seq', true) . '\');');

                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER salt SET NOT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER confirmation_token TYPE VARCHAR(255);');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER confirmation_token SET DEFAULT NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER googleauthenticatorsecret TYPE VARCHAR(191);');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ALTER googleauthenticatorsecret SET DEFAULT NULL;');

                $this->addSql('COMMENT ON COLUMN ' . $this->getTable('entry') . '.published_by IS NULL;');
                $this->addSql('COMMENT ON COLUMN ' . $this->getTable('entry') . '.headers IS NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER hashed_url TYPE TEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER hashed_given_url TYPE TEXT;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER is_not_parsed DROP NOT NULL;');

                $this->addSql('ALTER TABLE ' . $this->getTable('site_credential') . ' ALTER id SET DEFAULT nextval(\'' . $this->getTable('site_credential_id_seq', true) . '\');');

                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_user_rule') . ' ALTER id SET DEFAULT nextval(\'' . $this->getTable('ignore_origin_user_rule_id_seq', true) . '\');');
                break;
        }

        if ($this->platform instanceof MySQLPlatform || $this->platform instanceof PostgreSQLPlatform) {
            $this->write('Revert: Synchronize indexes and foreign keys with the default naming convention.');

            $this->revertRenameForeignKey('annotation', 'FK_A7AED006A76ED395', 'user', 'user_id');
            $this->revertRenameForeignKey('annotation', 'FK_annotation_entry', 'entry', 'entry_id', true);
            $this->revertRenameForeignKey('config', 'FK_87E64C53A76ED395', 'user', 'user_id');
            $this->revertRenameForeignKey('entry', 'FK_F4D18282A76ED395', 'user', 'user_id');
            $this->revertRenameForeignKey('entry_tag', 'FK_entry_tag_entry', 'entry', 'entry_id', true);
            $this->revertRenameForeignKey('entry_tag', 'FK_entry_tag_tag', 'tag', 'tag_id', true);
            $this->revertRenameForeignKey('ignore_origin_user_rule', 'fk_config', 'config', 'config_id');
            $this->revertRenameForeignKey('oauth2_access_tokens', 'FK_368A4209A76ED395', 'user', 'user_id', true);
            $this->revertRenameForeignKey('oauth2_access_tokens', 'FK_368A420919EB6921', 'oauth2_clients', 'client_id');
            $this->revertRenameForeignKey('oauth2_auth_codes', 'FK_EE52E3FA19EB6921', 'oauth2_clients', 'client_id');
            $this->revertRenameForeignKey('oauth2_auth_codes', 'FK_EE52E3FAA76ED395', 'user', 'user_id', true);
            $this->revertRenameForeignKey('oauth2_clients', 'FK_635D765EA76ED395', 'user', 'user_id');
            $this->revertRenameForeignKey('oauth2_refresh_tokens', 'FK_20C9FB24A76ED395', 'user', 'user_id', true);
            $this->revertRenameForeignKey('oauth2_refresh_tokens', 'FK_20C9FB2419EB6921', 'oauth2_clients', 'client_id');
            $this->revertRenameForeignKey('site_credential', 'fk_user', 'user', 'user_id');
            $this->revertRenameForeignKey('tagging_rule', 'FK_2D9B3C5424DB0683', 'config', 'config_id');

            $this->revertRenameIndex('annotation', 'idx_a7aed006a76ed395', 'user_id');
            $this->revertRenameIndex('annotation', 'idx_a7aed006ba364942', 'entry_id');
            $this->revertRenameIndex('config', 'config_feed_token', 'feed_token');
            $this->revertRenameIndex('entry', 'hashed_given_url_user_id', ['user_id', 'hashed_given_url']);
            $this->revertRenameIndex('entry', 'hashed_url_user_id', ['user_id', 'hashed_url']);
            $this->revertRenameIndex('entry', 'IDX_entry_created_at', 'created_at');
            $this->revertRenameIndex('entry', 'IDX_entry_uid', 'uid');
            $this->revertRenameIndex('entry', 'idx_f4d18282a76ed395', 'user_id');
            $this->revertRenameIndex('entry', 'user_archived', ['user_id', 'is_archived', 'archived_at']);
            $this->revertRenameIndex('entry', 'user_created', ['user_id', 'created_at']);
            $this->revertRenameIndex('entry', 'user_language', ['language', 'user_id']);
            $this->revertRenameIndex('entry', 'user_starred', ['user_id', 'is_starred', 'starred_at']);
            $this->revertRenameIndex('entry_tag', 'idx_c9f0dd7cba364942', 'entry_id');
            $this->revertRenameIndex('entry_tag', 'idx_c9f0dd7cbad26311', 'tag_id');
            $this->revertRenameIndex('ignore_origin_user_rule', 'idx_config', 'config_id');
            $this->revertRenameIndex('oauth2_access_tokens', 'idx_368a4209a76ed395', 'user_id');
            $this->revertRenameIndex('oauth2_access_tokens', 'idx_368a420919eb6921', 'client_id');
            $this->revertRenameIndex('oauth2_auth_codes', 'IDX_EE52E3FA19EB6921', 'client_id');
            $this->revertRenameIndex('oauth2_auth_codes', 'IDX_EE52E3FAA76ED395', 'user_id');
            $this->revertRenameIndex('oauth2_refresh_tokens', 'idx_20c9fb2419eb6921', 'client_id');
            $this->revertRenameIndex('oauth2_refresh_tokens', 'idx_20c9fb24a76ed395', 'user_id');
            $this->revertRenameIndex('site_credential', 'idx_user', 'user_id');
            $this->revertRenameIndex('tag', 'tag_label', 'label');
            $this->revertRenameIndex('tagging_rule', 'idx_2d9b3c5424db0683', 'config_id');

            $this->revertRenameUniqueIndex('config', 'uniq_87e64c53a76ed395', 'user_id');
            $this->revertRenameUniqueIndex('tag', 'uniq_4ca58a8c989d9b62', 'slug');
            $this->revertRenameUniqueIndex('user', 'uniq_1d63e7e5a0d96fbf', 'email_canonical');
            $this->revertRenameUniqueIndex('user', 'uniq_1d63e7e5c05fb297', 'confirmation_token');
            $this->revertRenameUniqueIndex('user', 'uniq_1d63e7e592fc23a8', 'username_canonical');
            $this->revertRenameUniqueIndex('oauth2_access_tokens', 'UNIQ_368A42095F37A13B', 'token');
            $this->revertRenameUniqueIndex('oauth2_auth_codes', 'UNIQ_EE52E3FA5F37A13B', 'token');
            $this->revertRenameUniqueIndex('oauth2_refresh_tokens', 'UNIQ_20C9FB245F37A13B', 'token');

            $this->createIndex('entry', 'is_archived', 'IDX_entry_archived');
            $this->createIndex('entry', 'is_starred', 'IDX_entry_starred');

            $this->createUniqueIndex('internal_setting', 'name', 'UNIQ_5D9649505E237E06');
        }

        if ($this->platform instanceof SqlitePlatform) {
            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_annotation AS SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges FROM ' . $this->getTable('annotation'));
            $this->addSql('DROP TABLE ' . $this->getTable('annotation'));
            $this->addSql('CREATE TABLE ' . $this->getTable('annotation') . ' (id INTEGER PRIMARY KEY NOT NULL, user_id INTEGER DEFAULT NULL, entry_id INTEGER DEFAULT NULL, text CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, quote CLOB NOT NULL, ranges CLOB NOT NULL, CONSTRAINT ' . $this->getForeignKeyName('annotation', 'user_id') . ' FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT ' . $this->getForeignKeyName('annotation', 'entry_id') . ' FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('annotation') . ' (id, user_id, entry_id, text, created_at, updated_at, quote, ranges) SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges FROM __temp__wallabag_annotation');
            $this->addSql('DROP TABLE __temp__wallabag_annotation');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_config AS SELECT id, user_id, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, display_thumbnails, font, fontsize, line_height, max_width, custom_css FROM ' . $this->getTable('config'));
            $this->addSql('DROP TABLE ' . $this->getTable('config'));
            $this->addSql('CREATE TABLE ' . $this->getTable('config') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL, action_mark_as_read INTEGER DEFAULT 0, list_mode INTEGER DEFAULT NULL, feed_token VARCHAR(255) DEFAULT NULL, feed_limit INTEGER DEFAULT NULL, display_thumbnails INTEGER DEFAULT 1, custom_css CLOB DEFAULT NULL, font CLOB DEFAULT NULL, fontsize DOUBLE PRECISION DEFAULT NULL, line_height DOUBLE PRECISION DEFAULT NULL, max_width DOUBLE PRECISION DEFAULT NULL, CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('config') . ' (id, user_id, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, display_thumbnails, font, fontsize, line_height, max_width, custom_css) SELECT id, user_id, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode, display_thumbnails, font, fontsize, line_height, max_width, custom_css FROM __temp__wallabag_config');
            $this->addSql('DROP TABLE __temp__wallabag_config');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON ' . $this->getTable('config') . ' (user_id)');
            $this->addSql('CREATE INDEX config_feed_token ON ' . $this->getTable('config') . ' (feed_token)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers, is_not_parsed FROM ' . $this->getTable('entry'));
            $this->addSql('DROP TABLE ' . $this->getTable('entry'));
            $this->addSql('CREATE TABLE ' . $this->getTable('entry') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL, url CLOB DEFAULT NULL, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL, domain_name CLOB DEFAULT NULL, preview_picture CLOB DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL, http_status VARCHAR(3) DEFAULT NULL, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL --(DC2Type:array)
        , headers CLOB DEFAULT NULL --(DC2Type:array)
        , hashed_url VARCHAR(40) DEFAULT NULL, hashed_given_url VARCHAR(40) DEFAULT NULL, language VARCHAR(20) DEFAULT NULL, is_not_parsed BOOLEAN DEFAULT 0, CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('entry') . ' (id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers, is_not_parsed) SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers, is_not_parsed FROM __temp__wallabag_entry');
            $this->addSql('DROP TABLE __temp__wallabag_entry');
            $this->addSql('CREATE INDEX IDX_F4D18282A76ED395 ON ' . $this->getTable('entry') . ' (user_id)');
            $this->addSql('CREATE INDEX user_starred ON ' . $this->getTable('entry') . ' (user_id, is_starred, starred_at)');
            $this->addSql('CREATE INDEX user_created ON ' . $this->getTable('entry') . ' (user_id, created_at)');
            $this->addSql('CREATE INDEX user_archived ON ' . $this->getTable('entry') . ' (user_id, is_archived, archived_at)');
            $this->addSql('CREATE INDEX user_language ON ' . $this->getTable('entry') . ' (language, user_id)');
            $this->addSql('CREATE INDEX hashed_given_url_user_id ON ' . $this->getTable('entry') . ' (user_id, hashed_given_url)');
            $this->addSql('CREATE INDEX hashed_url_user_id ON ' . $this->getTable('entry') . ' (user_id, hashed_url)');
            $this->addSql('CREATE INDEX created_at ON ' . $this->getTable('entry') . ' (created_at)');
            $this->addSql('CREATE INDEX uid ON ' . $this->getTable('entry') . ' (uid)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry_tag AS SELECT entry_id, tag_id FROM ' . $this->getTable('entry_tag'));
            $this->addSql('DROP TABLE ' . $this->getTable('entry_tag'));
            $this->addSql('CREATE TABLE ' . $this->getTable('entry_tag') . ' (entry_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(entry_id, tag_id), CONSTRAINT FK_C9F0DD7CBA364942 FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C9F0DD7CBAD26311 FOREIGN KEY (tag_id) REFERENCES ' . $this->getTable('tag') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('entry_tag') . ' (entry_id, tag_id) SELECT entry_id, tag_id FROM __temp__wallabag_entry_tag');
            $this->addSql('DROP TABLE __temp__wallabag_entry_tag');
            $this->addSql('CREATE INDEX IDX_C9F0DD7CBA364942 ON ' . $this->getTable('entry_tag') . ' (entry_id)');
            $this->addSql('CREATE INDEX IDX_C9F0DD7CBAD26311 ON ' . $this->getTable('entry_tag') . ' (tag_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_ignore_origin_user_rule AS SELECT id, config_id, rule FROM ' . $this->getTable('ignore_origin_user_rule'));
            $this->addSql('DROP TABLE ' . $this->getTable('ignore_origin_user_rule'));
            $this->addSql('CREATE TABLE ' . $this->getTable('ignore_origin_user_rule') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_id INTEGER NOT NULL, rule VARCHAR(255) NOT NULL, CONSTRAINT fk_config FOREIGN KEY (config_id) REFERENCES ' . $this->getTable('config') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('ignore_origin_user_rule') . ' (id, config_id, rule) SELECT id, config_id, rule FROM __temp__wallabag_ignore_origin_user_rule');
            $this->addSql('DROP TABLE __temp__wallabag_ignore_origin_user_rule');
            $this->addSql('CREATE INDEX idx_config ON ' . $this->getTable('ignore_origin_user_rule') . ' (config_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_internal_setting AS SELECT name, section, value FROM ' . $this->getTable('internal_setting'));
            $this->addSql('DROP TABLE ' . $this->getTable('internal_setting'));
            $this->addSql('CREATE TABLE ' . $this->getTable('internal_setting') . ' (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name))');
            $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . ' (name, section, value) SELECT name, section, value FROM __temp__wallabag_internal_setting');
            $this->addSql('DROP TABLE __temp__wallabag_internal_setting');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_5D9649505E237E06 ON ' . $this->getTable('internal_setting') . ' (name)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_access_tokens AS SELECT id, client_id, user_id, token, expires_at, scope FROM ' . $this->getTable('oauth2_access_tokens'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_access_tokens'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_access_tokens') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191), CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_access_tokens') . ' (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__wallabag_oauth2_access_tokens');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_access_tokens');
            $this->addSql('CREATE INDEX IDX_368A420919EB6921 ON ' . $this->getTable('oauth2_access_tokens') . ' (client_id)');
            $this->addSql('CREATE INDEX IDX_368A4209A76ED395 ON ' . $this->getTable('oauth2_access_tokens') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_auth_codes AS SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM ' . $this->getTable('oauth2_auth_codes'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_auth_codes'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_auth_codes') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, redirect_uri CLOB NOT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191), CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_auth_codes') . ' (id, client_id, user_id, token, redirect_uri, expires_at, scope) SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM __temp__wallabag_oauth2_auth_codes');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_auth_codes');
            $this->addSql('CREATE INDEX IDX_EE52E3FA19EB6921 ON ' . $this->getTable('oauth2_auth_codes') . ' (client_id)');
            $this->addSql('CREATE INDEX IDX_EE52E3FAA76ED395 ON ' . $this->getTable('oauth2_auth_codes') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_clients AS SELECT id, user_id, random_id, redirect_uris, secret, allowed_grant_types, name FROM ' . $this->getTable('oauth2_clients'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_clients'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_clients') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, name CLOB NOT NULL, redirect_uris CLOB NOT NULL, allowed_grant_types CLOB NOT NULL, CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_clients') . ' (id, user_id, random_id, redirect_uris, secret, allowed_grant_types, name) SELECT id, user_id, random_id, redirect_uris, secret, allowed_grant_types, name FROM __temp__wallabag_oauth2_clients');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_clients');
            $this->addSql('CREATE INDEX IDX_635D765EA76ED395 ON ' . $this->getTable('oauth2_clients') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_oauth2_refresh_tokens AS SELECT id, client_id, user_id, token, expires_at, scope FROM ' . $this->getTable('oauth2_refresh_tokens'));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_refresh_tokens'));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191), CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('oauth2_clients') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_refresh_tokens') . ' (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__wallabag_oauth2_refresh_tokens');
            $this->addSql('DROP TABLE __temp__wallabag_oauth2_refresh_tokens');
            $this->addSql('CREATE INDEX IDX_20C9FB2419EB6921 ON ' . $this->getTable('oauth2_refresh_tokens') . ' (client_id)');
            $this->addSql('CREATE INDEX IDX_20C9FB24A76ED395 ON ' . $this->getTable('oauth2_refresh_tokens') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_site_credential AS SELECT id, user_id, host, username, password, createdAt, updated_at FROM ' . $this->getTable('site_credential'));
            $this->addSql('DROP TABLE ' . $this->getTable('site_credential'));
            $this->addSql('CREATE TABLE ' . $this->getTable('site_credential') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, host VARCHAR(255) NOT NULL, username CLOB NOT NULL, password CLOB NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('site_credential') . ' (id, user_id, host, username, password, createdAt, updated_at) SELECT id, user_id, host, username, password, createdAt, updated_at FROM __temp__wallabag_site_credential');
            $this->addSql('DROP TABLE __temp__wallabag_site_credential');
            $this->addSql('CREATE INDEX idx_user ON ' . $this->getTable('site_credential') . ' (user_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_tag AS SELECT id, label, slug FROM ' . $this->getTable('tag'));
            $this->addSql('DROP TABLE ' . $this->getTable('tag'));
            $this->addSql('CREATE TABLE ' . $this->getTable('tag') . ' (id INTEGER PRIMARY KEY NOT NULL, label CLOB NOT NULL, slug VARCHAR(128) NOT NULL)');
            $this->addSql('INSERT INTO ' . $this->getTable('tag') . ' (id, label, slug) SELECT id, label, slug FROM __temp__wallabag_tag');
            $this->addSql('DROP TABLE __temp__wallabag_tag');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_4CA58A8C989D9B62 ON ' . $this->getTable('tag') . ' (slug)');
            $this->addSql('CREATE INDEX tag_label ON ' . $this->getTable('tag') . ' (label)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_tagging_rule AS SELECT id, config_id, rule, tags FROM ' . $this->getTable('tagging_rule'));
            $this->addSql('DROP TABLE ' . $this->getTable('tagging_rule'));
            $this->addSql('CREATE TABLE ' . $this->getTable('tagging_rule') . ' (id INTEGER PRIMARY KEY NOT NULL, config_id INTEGER DEFAULT NULL, rule VARCHAR(255) NOT NULL, tags CLOB NOT NULL, CONSTRAINT FK_2D9B3C5424DB0683 FOREIGN KEY (config_id) REFERENCES ' . $this->getTable('config') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('tagging_rule') . ' (id, config_id, rule, tags) SELECT id, config_id, rule, tags FROM __temp__wallabag_tagging_rule');
            $this->addSql('DROP TABLE __temp__wallabag_tagging_rule');
            $this->addSql('CREATE INDEX IDX_2D9B3C5424DB0683 ON ' . $this->getTable('tagging_rule') . ' (config_id)');

            $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM ' . $this->getTable('user'));
            $this->addSql('DROP TABLE ' . $this->getTable('user'));
            $this->addSql('CREATE TABLE ' . $this->getTable('user') . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
        , name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json)
        , emailTwoFactor BOOLEAN NOT NULL)');
            $this->addSql('INSERT INTO ' . $this->getTable('user') . ' (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM __temp__user');
            $this->addSql('DROP TABLE __temp__user');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON ' . $this->getTable('user') . ' (confirmation_token)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON ' . $this->getTable('user') . ' (email_canonical)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON ' . $this->getTable('user') . ' (username_canonical)');
        }
    }

    private function renameForeignKey(string $tableName, string $oldName, string $foreignTableName, string $foreignColumnName, bool $onDeleteCascade = false): void
    {
        $newName = $this->getForeignKeyName($tableName, $foreignColumnName);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' DROP FOREIGN KEY ' . $oldName . ';');
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' ADD CONSTRAINT ' . $newName . ' FOREIGN KEY (' . $foreignColumnName . ') REFERENCES ' . $this->getTable($foreignTableName) . ' (id)' . ($onDeleteCascade ? ' ON DELETE CASCADE' : '') . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME CONSTRAINT ' . $oldName . ' TO ' . $newName . ';');
                break;
        }
    }

    private function revertRenameForeignKey(string $tableName, string $oldName, string $foreignTableName, string $foreignColumnName, bool $onDeleteCascade = false): void
    {
        $newName = $this->getForeignKeyName($tableName, $foreignColumnName);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' DROP FOREIGN KEY ' . $newName . ';');
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' ADD CONSTRAINT ' . $oldName . ' FOREIGN KEY (' . $foreignColumnName . ') REFERENCES ' . $this->getTable($foreignTableName) . ' (id)' . ($onDeleteCascade ? ' ON DELETE CASCADE' : '') . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME CONSTRAINT ' . $newName . ' TO ' . $oldName . ';');
                break;
        }
    }

    private function renameIndex(string $tableName, string $oldName, $indexedColumnNames): void
    {
        $indexedColumnNames = (array) $indexedColumnNames;

        $newName = $this->getIndexName($tableName, $indexedColumnNames);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME INDEX ' . $oldName . ' TO ' . $newName . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER INDEX ' . $oldName . ' RENAME TO ' . $newName . ';');
                break;
        }
    }

    private function revertRenameIndex(string $tableName, string $oldName, $indexedColumnNames): void
    {
        $indexedColumnNames = (array) $indexedColumnNames;

        $newName = $this->getIndexName($tableName, $indexedColumnNames);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME INDEX ' . $newName . ' TO ' . $oldName . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $oldName = strtolower($oldName);
                $this->addSql('ALTER INDEX ' . $newName . ' RENAME TO ' . $oldName . ';');
                break;
        }
    }

    private function dropIndex(string $tableName, string $indexedColumnName, ?string $name = null): void
    {
        if (null === $name) {
            $name = $this->getIndexName($tableName, [$indexedColumnName]);
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('DROP INDEX ' . $name . ' ON ' . $this->getTable($tableName) . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('DROP INDEX ' . $name . ';');
                break;
        }
    }

    private function createIndex(string $tableName, string $indexedColumnName, ?string $name = null): void
    {
        if (null === $name) {
            $name = $this->getIndexName($tableName, [$indexedColumnName]);
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('CREATE INDEX ' . $name . ' ON ' . $this->getTable($tableName) . ' (' . $indexedColumnName . ');');
                break;
        }
    }

    private function renameUniqueIndex(string $tableName, string $oldName, string $indexedColumnName): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $newName = $this->getUniqueIndexName($tableName, $indexedColumnName);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME INDEX ' . $oldName . ' TO ' . $newName . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER INDEX ' . $oldName . ' RENAME TO ' . $newName . ';');
                break;
        }
    }

    private function revertRenameUniqueIndex(string $tableName, string $oldName, string $indexedColumnName): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $newName = $this->getUniqueIndexName($tableName, $indexedColumnName);

        if (strtolower($oldName) === strtolower($newName)) {
            return;
        }

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable($tableName) . ' RENAME INDEX ' . $newName . ' TO ' . $oldName . ';');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER INDEX ' . $newName . ' RENAME TO ' . $oldName . ';');
                break;
        }
    }

    private function dropUniqueIndex(string $tableName, string $indexedColumnName, ?string $name = null): void
    {
        if (null === $name) {
            $name = $this->getUniqueIndexName($tableName, $indexedColumnName);
        }

        $this->dropIndex($tableName, $indexedColumnName, $name);
    }

    private function createUniqueIndex(string $tableName, string $indexedColumnName, ?string $name = null): void
    {
        if (null === $name) {
            $name = $this->getUniqueIndexName($tableName, $indexedColumnName);
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('CREATE UNIQUE INDEX ' . $name . ' ON ' . $this->getTable($tableName) . ' (' . $indexedColumnName . ');');
                break;
        }
    }
}
