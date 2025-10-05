<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Changed reading_time field to prevent null value.
 */
class Version20171008195606 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

            return;
        }

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('UPDATE ' . $this->getTable('entry') . ' SET reading_time = 0 WHERE reading_time IS NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11) NOT NULL;');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('UPDATE ' . $this->getTable('entry') . ' SET reading_time = 0 WHERE reading_time IS NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time SET NOT NULL;');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

            return;
        }

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11);');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time DROP NOT NULL;');
                break;
        }
    }
}
