<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added the internal setting to share articles to unmark.it.
 */
class Version20161117071626 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $share = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'share_unmark'");

        if (false === $share) {
            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('share_unmark', 0, 'entry')");
        }

        $unmark = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'unmark_url'");

        if (false === $unmark) {
            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('unmark_url', 'https://unmark.it', 'entry')");
        }

        $this->skipIf(false !== $share && false !== $unmark, 'It seems that you already played this migration.');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'share_unmark';");
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'unmark_url';");
    }
}
