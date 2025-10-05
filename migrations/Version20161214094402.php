<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Renamed uuid to uid in entry table.
 */
class Version20161214094402 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        if ($entryTable->hasColumn('uid')) {
            $this->write('It seems that you already played this migration.');

            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_entry AS SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM ' . $this->getTable('entry'));
                $this->addSql('DROP TABLE ' . $this->getTable('entry'));
                $this->addSql('CREATE TABLE ' . $this->getTable('entry') . ' (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, uid CLOB DEFAULT NULL COLLATE BINARY, title CLOB DEFAULT NULL COLLATE BINARY, url CLOB DEFAULT NULL COLLATE BINARY, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL COLLATE BINARY, language CLOB DEFAULT NULL COLLATE BINARY, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL COLLATE BINARY, preview_picture CLOB DEFAULT NULL COLLATE BINARY, is_public BOOLEAN DEFAULT "0", PRIMARY KEY(id));');
                $this->addSql('INSERT INTO ' . $this->getTable('entry') . ' (id, user_id, uid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public) SELECT id, user_id, uuid, title, url, is_archived, is_starred, content, created_at, updated_at, mimetype, language, reading_time, domain_name, preview_picture, is_public FROM __temp__wallabag_entry;');
                $this->addSql('DROP TABLE __temp__wallabag_entry');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE uuid uid VARCHAR(23)');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' RENAME uuid TO uid');
        }
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        if ($entryTable->hasColumn('uuid')) {
            $this->write('It seems that you already played this migration.');

            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                throw new SkipMigrationException('Too complex ...');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE uid uuid VARCHAR(23)');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' RENAME uid TO uuid');
        }
    }
}
