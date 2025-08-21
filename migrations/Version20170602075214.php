<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add api_user_registration in craue_config_setting.
 */
class Version20170602075214 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $apiUserRegistration = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'api_user_registration'");

        $this->skipIf(false !== $apiUserRegistration, 'It seems that you already played this migration.');

        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('api_user_registration', '0', 'api')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'api_user_registration';");
    }
}
