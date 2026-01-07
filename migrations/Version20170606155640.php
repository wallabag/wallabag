<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove wallabag_url from craue_config_setting.
 * It has been moved into the parameters.yml.
 */
class Version20170606155640 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable($this->getTable('craue_config_setting'))) {
            $this->write('Table already renamed');

            return;
        }

        $apiUserRegistration = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'wallabag_url'");

        if (false === $apiUserRegistration) {
            $this->write('It seems that you already played this migration.');

            return;
        }

        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'wallabag_url'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('wallabag_url', 'wallabag.me', 'misc')");
    }
}
