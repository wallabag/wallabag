<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Increase the length of the "quote" column of "annotation" table.
 */
class Version20170511211659 extends WallabagMigration
{
    public function up(Schema $schema)
    {
        $tableName = $this->getTable('annotation');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql(<<<EOD
CREATE TEMPORARY TABLE __temp__wallabag_annotation AS
    SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
    FROM ${tableName}
EOD
                );
                $this->addSql('DROP TABLE ' . $tableName);
                $this->addSql(<<<EOD
CREATE TABLE ${tableName}
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
EOD
                );

                $this->addSql(<<<EOD
INSERT INTO ${tableName} (id, user_id, entry_id, text, created_at, updated_at, quote, ranges)
SELECT id, user_id, entry_id, text, created_at, updated_at, quote, ranges
FROM __temp__wallabag_annotation;
EOD
                );
                $this->addSql('DROP TABLE __temp__wallabag_annotation');
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $tableName . ' MODIFY quote TEXT NOT NULL');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $tableName . ' ALTER COLUMN quote TYPE TEXT');
                break;
        }
    }

    public function down(Schema $schema)
    {
        $tableName = $this->getTable('annotation');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                throw new SkipMigrationException('Too complex ...');
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $tableName . ' MODIFY quote VARCHAR(255) NOT NULL');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $tableName . ' ALTER COLUMN quote TYPE VARCHAR(255)');
                break;
        }
    }
}
