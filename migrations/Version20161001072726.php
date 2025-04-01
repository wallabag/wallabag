<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added pocket_consumer_key field on wallabag_config.
 */
class Version20161001072726 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

            return;
        }

        // remove all FK from entry_tag
        switch (true) {
            case $platform instanceof MySQLPlatform:
                $query = $this->connection->executeQuery("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.key_column_usage
                    WHERE TABLE_NAME = '" . $this->getTable('entry_tag', WallabagMigration::UN_ESCAPED_TABLE) . "' AND CONSTRAINT_NAME LIKE 'FK_%'
                    AND TABLE_SCHEMA = '" . $this->connection->getDatabase() . "'"
                );

                foreach ($query->fetchAllAssociative() as $fk) {
                    $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' DROP FOREIGN KEY ' . $fk['CONSTRAINT_NAME']);
                }
                break;
            case $platform instanceof PostgreSQLPlatform:
                // http://dba.stackexchange.com/questions/36979/retrieving-all-pk-and-fk
                $query = $this->connection->executeQuery("
                    SELECT conrelid::regclass AS table_from
                          ,conname
                          ,pg_get_constraintdef(c.oid)
                    FROM   pg_constraint c
                    JOIN   pg_namespace n ON n.oid = c.connamespace
                    WHERE  contype = 'f'
                    AND    conrelid::regclass::text = '" . $this->getTable('entry_tag', WallabagMigration::UN_ESCAPED_TABLE) . "'
                    AND    n.nspname = 'public';"
                );

                foreach ($query->fetchAllAssociative() as $fk) {
                    $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' DROP CONSTRAINT ' . $fk['conname']);
                }
                break;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' ADD CONSTRAINT FK_entry_tag_entry FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' ADD CONSTRAINT FK_entry_tag_tag FOREIGN KEY (tag_id) REFERENCES ' . $this->getTable('tag') . ' (id) ON DELETE CASCADE');

        // remove entry FK from annotation

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $query = $this->connection->executeQuery("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.key_column_usage
                    WHERE TABLE_NAME = '" . $this->getTable('annotation', WallabagMigration::UN_ESCAPED_TABLE) . "'
                    AND CONSTRAINT_NAME LIKE 'FK_%'
                    AND COLUMN_NAME = 'entry_id'
                    AND TABLE_SCHEMA = '" . $this->connection->getDatabase() . "'"
                );

                foreach ($query->fetchAllAssociative() as $fk) {
                    $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' DROP FOREIGN KEY ' . $fk['CONSTRAINT_NAME']);
                }
                break;
            case $platform instanceof PostgreSQLPlatform:
                // http://dba.stackexchange.com/questions/36979/retrieving-all-pk-and-fk
                $query = $this->connection->executeQuery("
                    SELECT conrelid::regclass AS table_from
                          ,conname
                          ,pg_get_constraintdef(c.oid)
                    FROM   pg_constraint c
                    JOIN   pg_namespace n ON n.oid = c.connamespace
                    WHERE  contype = 'f'
                    AND    conrelid::regclass::text = '" . $this->getTable('annotation', WallabagMigration::UN_ESCAPED_TABLE) . "'
                    AND    n.nspname = 'public'
                    AND    pg_get_constraintdef(c.oid) LIKE '%entry_id%';"
                );

                foreach ($query->fetchAllAssociative() as $fk) {
                    $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' DROP CONSTRAINT ' . $fk['conname']);
                }
                break;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' ADD CONSTRAINT FK_annotation_entry FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        throw new SkipMigration('Too complex ...');
    }
}
