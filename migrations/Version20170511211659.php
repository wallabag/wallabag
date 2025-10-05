<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Increase the length of the "quote" column of "annotation" table.
 */
class Version20170511211659 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $annotationTableName = $this->getTable('annotation', true);
                $userTableName = $this->getTable('user', true);
                $entryTableName = $this->getTable('entry', true);

                $this->addSql(<<<EOD
CREATE TEMPORARY TABLE __temp__wallabag_annotation AS
    SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
    FROM {$annotationTableName}
EOD
                );
                $this->addSql('DROP TABLE ' . $annotationTableName);
                $this->addSql(<<<EOD
CREATE TABLE {$annotationTableName}
(
    id INTEGER PRIMARY KEY NOT NULL,
    user_id INTEGER DEFAULT NULL,
    entry_id INTEGER DEFAULT NULL,
    text CLOB NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    quote CLOB NOT NULL,
    ranges CLOB NOT NULL,
    CONSTRAINT FK_A7AED006A76ED395 FOREIGN KEY (user_id) REFERENCES {$userTableName} (id),
    CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES {$entryTableName} (id) ON DELETE CASCADE
);
CREATE INDEX IDX_A7AED006A76ED395 ON {$annotationTableName} (user_id);
CREATE INDEX IDX_A7AED006BA364942 ON {$annotationTableName} (entry_id);
EOD
                );

                $this->addSql(<<<EOD
INSERT INTO {$annotationTableName} (id, user_id, entry_id, text, created_at, updated_at, quote, ranges)
SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
FROM __temp__wallabag_annotation;
EOD
                );
                $this->addSql('DROP TABLE __temp__wallabag_annotation');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' MODIFY quote TEXT NOT NULL');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' ALTER COLUMN quote TYPE TEXT');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $tableName = $this->getTable('annotation');

        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                throw new SkipMigration('Too complex ...');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $tableName . ' MODIFY quote VARCHAR(255) NOT NULL');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $tableName . ' ALTER COLUMN quote TYPE VARCHAR(255)');
                break;
        }
    }
}
