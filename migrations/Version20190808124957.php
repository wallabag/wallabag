<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Change the internal setting table name.
 */
final class Version20190808124957 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting', true) . ' RENAME TO ' . $this->getTable('internal_setting', true));
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' RENAME ' . $this->getTable('internal_setting'));
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' RENAME TO ' . $this->getTable('internal_setting'));
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting', true) . ' RENAME TO ' . $this->getTable('craue_config_setting', true));
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting') . ' RENAME ' . $this->getTable('craue_config_setting'));
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting') . ' RENAME TO ' . $this->getTable('craue_config_setting'));
                break;
        }
    }
}
