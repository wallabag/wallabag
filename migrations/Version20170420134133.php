<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove download_pictures in craue_config_setting.
 */
class Version20170420134133 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'download_pictures';");
    }

    public function down(Schema $schema): void
    {
        $downloadPictures = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'download_pictures'");

        $this->skipIf(false !== $downloadPictures, 'It seems that you already played this migration.');

        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('download_pictures', '1', 'entry')");
    }
}
