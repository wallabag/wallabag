<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Remove wallabag_url from craue_config_setting.
 * It has been moved into the parameters.yml.
 */
class Version20170606155640 extends WallabagMigration
{
    public function up(Schema $schema)
    {
        $apiUserRegistration = $this->container
            ->get('doctrine.orm.default_entity_manager')
            ->getConnection()
            ->fetchArray('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'wallabag_url'");

        $this->skipIf(false === $apiUserRegistration, 'It seems that you already played this migration.');

        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'wallabag_url'");
    }

    public function down(Schema $schema)
    {
        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('wallabag_url', 'wallabag.me', 'misc')");
    }
}
