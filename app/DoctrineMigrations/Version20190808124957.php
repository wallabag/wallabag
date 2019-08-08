<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Change the internal setting table name.
 */
final class Version20190808124957 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting', true) . ' RENAME TO ' . $this->getTable('internal_setting', true));
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' RENAME ' . $this->getTable('internal_setting'));
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' RENAME TO ' . $this->getTable('internal_setting'));
                break;
        }
    }

    public function down(Schema $schema): void
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting', true) . ' RENAME TO ' . $this->getTable('craue_config_setting', true));
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting') . ' RENAME ' . $this->getTable('craue_config_setting'));
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $this->getTable('internal_setting') . ' RENAME TO ' . $this->getTable('craue_config_setting'));
                break;
        }
    }
}
