<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Fix varchar field from vendor to work with utf8mb4.
 */
class Version20181128203230 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration can only be applied on \'mysql\'.');

            return;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' CHANGE `token` `token` varchar(191) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' CHANGE `scope` `scope` varchar(191)');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' CHANGE `token` `token` varchar(191) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' CHANGE `scope` `scope` varchar(191)');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' CHANGE `token` `token` varchar(191) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' CHANGE `scope` `scope` varchar(191)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `name` `name` varchar(191)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `section` `section` varchar(191)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `value` `value` varchar(191)');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration can only be applied on \'mysql\'.');

            return;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' CHANGE `token` `token` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' CHANGE `scope` `scope` varchar(255)');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' CHANGE `token` `token` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' CHANGE `scope` `scope` varchar(255)');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' CHANGE `token` `token` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' CHANGE `scope` `scope` varchar(255)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `name` `name` varchar(255)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `section` `section` varchar(255)');
        $this->addSql('ALTER TABLE ' . $this->getTable('craue_config_setting') . ' CHANGE `value` `value` varchar(255)');
    }
}
