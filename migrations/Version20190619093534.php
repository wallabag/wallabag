<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Change reading_time field on SQLite to be integer NOT NULL
 * It was forgotten in a previous migration (Version20171008195606.php).
 */
final class Version20190619093534 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'sqlite\'.');

            return;
        }

        $this->addSql('UPDATE ' . $this->getTable('entry', true) . ' SET reading_time = 0 WHERE reading_time IS NULL;');

        $this->addSql('DROP INDEX hashed_given_url_user_id');
        $this->addSql('DROP INDEX IDX_entry_uid');
        $this->addSql('DROP INDEX IDX_F4D18282A76ED395');
        $this->addSql('DROP INDEX IDX_entry_created_at');
        $this->addSql('DROP INDEX IDX_entry_starred');
        $this->addSql('DROP INDEX IDX_entry_archived');
        $this->addSql('DROP INDEX hashed_url_user_id');
        $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('entry', true) . ' AS SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url FROM ' . $this->getTable('entry', true) . '');
        $this->addSql('DROP TABLE ' . $this->getTable('entry', true) . '');
        $this->addSql('CREATE TABLE ' . $this->getTable('entry', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, uid VARCHAR(23) DEFAULT NULL COLLATE BINARY, http_status VARCHAR(3) DEFAULT NULL COLLATE BINARY, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, origin_url CLOB DEFAULT NULL COLLATE BINARY, archived_at DATETIME DEFAULT NULL, given_url CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER NOT NULL, published_by CLOB DEFAULT NULL --(DC2Type:array)
        , headers CLOB DEFAULT NULL --(DC2Type:array)
        , hashed_url VARCHAR(40) DEFAULT NULL, hashed_given_url VARCHAR(40) DEFAULT NULL, CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('user', true) . '" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ' . $this->getTable('entry', true) . ' (id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url) SELECT id, user_id, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, uid, http_status, published_at, published_by, headers, starred_at, origin_url, archived_at, hashed_url, given_url, hashed_given_url FROM __temp__' . $this->getTable('entry', true) . '');
        $this->addSql('DROP TABLE __temp__' . $this->getTable('entry', true) . '');
        $this->addSql('CREATE INDEX hashed_given_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_given_url)');
        $this->addSql('CREATE INDEX IDX_F4D18282A76ED395 ON ' . $this->getTable('entry', true) . ' (user_id)');
        $this->addSql('CREATE INDEX hashed_url_user_id ON ' . $this->getTable('entry', true) . ' (user_id, hashed_url)');
        $this->addSql('CREATE INDEX created_at ON ' . $this->getTable('entry', true) . ' (created_at)');
        $this->addSql('CREATE INDEX uid ON ' . $this->getTable('entry', true) . ' (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if (!$this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'sqlite\'.');

            return;
        }

        $this->addSql('DROP INDEX IDX_F4D18282A76ED395');
        $this->addSql('DROP INDEX created_at');
        $this->addSql('DROP INDEX uid');
        $this->addSql('DROP INDEX hashed_url_user_id');
        $this->addSql('DROP INDEX hashed_given_url_user_id');
        $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('entry', true) . ' AS SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers FROM "' . $this->getTable('entry', true) . '"');
        $this->addSql('DROP TABLE "' . $this->getTable('entry', true) . '"');
        $this->addSql('CREATE TABLE "' . $this->getTable('entry', true) . '" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, uid VARCHAR(23) DEFAULT NULL, title CLOB DEFAULT NULL, url CLOB DEFAULT NULL, origin_url CLOB DEFAULT NULL, given_url CLOB DEFAULT NULL, is_archived BOOLEAN NOT NULL, archived_at DATETIME DEFAULT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, published_at DATETIME DEFAULT NULL, starred_at DATETIME DEFAULT NULL, mimetype CLOB DEFAULT NULL, language CLOB DEFAULT NULL, domain_name CLOB DEFAULT NULL, preview_picture CLOB DEFAULT NULL, http_status VARCHAR(3) DEFAULT NULL, hashed_url CLOB DEFAULT NULL COLLATE BINARY, hashed_given_url CLOB DEFAULT NULL COLLATE BINARY, published_by CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, headers CLOB DEFAULT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO "' . $this->getTable('entry', true) . '" (id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers) SELECT id, user_id, uid, title, url, hashed_url, origin_url, given_url, hashed_given_url, is_archived, archived_at, is_starred, content, created_at, updated_at, published_at, published_by, starred_at, mimetype, language, reading_time, domain_name, preview_picture, http_status, headers FROM __temp__' . $this->getTable('entry', true) . '');
        $this->addSql('DROP TABLE __temp__' . $this->getTable('entry', true) . '');
        $this->addSql('CREATE INDEX IDX_F4D18282A76ED395 ON "' . $this->getTable('entry', true) . '" (user_id)');
        $this->addSql('CREATE INDEX hashed_url_user_id ON "' . $this->getTable('entry', true) . '" (user_id, hashed_url)');
        $this->addSql('CREATE INDEX hashed_given_url_user_id ON "' . $this->getTable('entry', true) . '" (user_id, hashed_given_url)');
        $this->addSql('CREATE INDEX IDX_entry_starred ON "' . $this->getTable('entry', true) . '" (is_starred)');
        $this->addSql('CREATE INDEX IDX_entry_archived ON "' . $this->getTable('entry', true) . '" (is_archived)');
        $this->addSql('CREATE INDEX IDX_entry_uid ON "' . $this->getTable('entry', true) . '" (uid)');
        $this->addSql('CREATE INDEX IDX_entry_created_at ON "' . $this->getTable('entry', true) . '" (created_at)');
    }
}
