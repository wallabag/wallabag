<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Adding more index to kill some slow queries:
 *     - user_language
 *     - user_archived
 *     - user_created
 *     - user_starred
 *     - tag_label
 *     - config_feed_token.
 */
final class Version20190806130304 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX uid');
                $this->addSql('DROP INDEX created_at');
                $this->addSql('DROP INDEX hashed_url_user_id');
                $this->addSql('DROP INDEX IDX_F4D18282A76ED395');
                $this->addSql('DROP INDEX hashed_given_url_user_id');
                $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url FROM ' . $this->getTable('entry', true));
                $this->addSql('DROP TABLE ' . $this->getTable('entry', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('entry', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL COLLATE BINARY --(DC2Type:array)
                , headers CLOB DEFAULT NULL COLLATE BINARY --(DC2Type:array)
                , hashed_url VARCHAR(40) DEFAULT NULL COLLATE BINARY, hashed_given_url VARCHAR(40) DEFAULT NULL COLLATE BINARY, language VARCHAR(20) DEFAULT NULL, CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('entry', true) . ' (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, domain_name, preview_picture, uid, http_status, published_at, starred_at, origin_url, archived_at, given_url, reading_time, published_by, headers, hashed_url, hashed_given_url FROM __temp__wallabag_entry');
                $this->addSql('DROP TABLE __temp__wallabag_entry');
                $this->addSql('CREATE INDEX uid ON ' . $this->getTable('entry', true) . ' (uid)');
                $this->addSql('CREATE INDEX created_at ON ' . $this->getTable('entry', true) . ' (created_at)');
                $this->addSql('CREATE INDEX hashed_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_url)');
                $this->addSql('CREATE INDEX IDX_F4D18282A76ED395 ON ' . $this->getTable('entry', true) . ' (user_id)');
                $this->addSql('CREATE INDEX hashed_given_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_given_url)');
                $this->addSql('CREATE INDEX user_language ON ' . $this->getTable('entry', true) . ' (language, user_id)');
                $this->addSql('CREATE INDEX user_archived ON ' . $this->getTable('entry', true) . ' (user_id, is_archived, archived_at)');
                $this->addSql('CREATE INDEX user_created ON ' . $this->getTable('entry', true) . ' (user_id, created_at)');
                $this->addSql('CREATE INDEX user_starred ON ' . $this->getTable('entry', true) . ' (user_id, is_starred, starred_at)');
                $this->addSql('CREATE INDEX tag_label ON ' . $this->getTable('tag', true) . ' (label)');
                $this->addSql('CREATE INDEX config_feed_token ON ' . $this->getTable('config', true) . ' (feed_token)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' MODIFY language VARCHAR(20) DEFAULT NULL');
                $this->addSql('CREATE INDEX user_language ON ' . $this->getTable('entry') . ' (language, user_id)');
                $this->addSql('CREATE INDEX user_archived ON ' . $this->getTable('entry') . ' (user_id, is_archived, archived_at)');
                $this->addSql('CREATE INDEX user_created ON ' . $this->getTable('entry') . ' (user_id, created_at)');
                $this->addSql('CREATE INDEX user_starred ON ' . $this->getTable('entry') . ' (user_id, is_starred, starred_at)');
                $this->addSql('CREATE INDEX tag_label ON ' . $this->getTable('tag') . ' (label (255))');
                $this->addSql('CREATE INDEX config_feed_token ON ' . $this->getTable('config') . ' (feed_token (255))');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER language TYPE VARCHAR(20)');
                $this->addSql('CREATE INDEX user_language ON ' . $this->getTable('entry') . ' (language, user_id)');
                $this->addSql('CREATE INDEX user_archived ON ' . $this->getTable('entry') . ' (user_id, is_archived, archived_at)');
                $this->addSql('CREATE INDEX user_created ON ' . $this->getTable('entry') . ' (user_id, created_at)');
                $this->addSql('CREATE INDEX user_starred ON ' . $this->getTable('entry') . ' (user_id, is_starred, starred_at)');
                $this->addSql('CREATE INDEX tag_label ON ' . $this->getTable('tag') . ' (label)');
                $this->addSql('CREATE INDEX config_feed_token ON ' . $this->getTable('config') . ' (feed_token)');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX IDX_F4D18282A76ED395');
                $this->addSql('DROP INDEX created_at');
                $this->addSql('DROP INDEX uid');
                $this->addSql('DROP INDEX hashed_url_user_id');
                $this->addSql('DROP INDEX hashed_given_url_user_id');
                $this->addSql('DROP INDEX user_language');
                $this->addSql('DROP INDEX user_archived');
                $this->addSql('DROP INDEX user_created');
                $this->addSql('DROP INDEX user_starred');
                $this->addSql('DROP INDEX tag_label');
                $this->addSql('DROP INDEX config_feed_token');
                $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers FROM ' . $this->getTable('entry', true));
                $this->addSql('DROP TABLE ' . $this->getTable('entry', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('entry', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL, title CLOB DEFAULT NULL, url CLOB DEFAULT NULL, hashed_url VARCHAR(40) DEFAULT NULL, origin_url CLOB DEFAULT NULL, given_url CLOB DEFAULT NULL, hashed_given_url VARCHAR(40) DEFAULT NULL, is_archived BOOLEAN NOT NULL, archived_at DATETIME DEFAULT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, published_at DATETIME DEFAULT NULL, published_by CLOB DEFAULT NULL --(DC2Type:array)
                , starred_at DATETIME DEFAULT NULL, mimetype CLOB DEFAULT NULL, reading_time INTEGER NOT NULL, domain_name CLOB DEFAULT NULL, preview_picture CLOB DEFAULT NULL, http_status VARCHAR(3) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:array)
                , language CLOB DEFAULT NULL COLLATE BINARY)');
                $this->addSql('INSERT INTO ' . $this->getTable('entry', true) . ' (id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers) SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers FROM __temp__wallabag_entry');
                $this->addSql('DROP TABLE __temp__wallabag_entry');
                $this->addSql('CREATE INDEX IDX_F4D18282A76ED395 ON ' . $this->getTable('entry', true) . ' (user_id)');
                $this->addSql('CREATE INDEX created_at ON ' . $this->getTable('entry', true) . ' (created_at)');
                $this->addSql('CREATE INDEX uid ON ' . $this->getTable('entry', true) . ' (uid)');
                $this->addSql('CREATE INDEX hashed_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_url)');
                $this->addSql('CREATE INDEX hashed_given_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_given_url)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' MODIFY language LONGTEXT DEFAULT NULL');
                $this->addSql('DROP INDEX user_language ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_archived ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_created ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_starred ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX tag_label ON ' . $this->getTable('tag'));
                $this->addSql('DROP INDEX config_feed_token ON ' . $this->getTable('config'));
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER language TYPE TEXT');
                $this->addSql('DROP INDEX user_language ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_archived ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_created ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX user_starred ON ' . $this->getTable('entry'));
                $this->addSql('DROP INDEX tag_label ON ' . $this->getTable('tag'));
                $this->addSql('DROP INDEX config_feed_token ON ' . $this->getTable('config'));
                break;
        }
    }
}
