<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added the internal setting to share articles to linkding.
 */
final class Version20240521152037 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $share = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('internal_setting') . " WHERE name = 'share_linkding'");

        if (false === $share) {
            $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('share_linkding', 0, 'entry')");
        }

        $linkding = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('internal_setting') . " WHERE name = 'linkding_url'");

        if (false === $linkding) {
            $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('linkding_url', 'https://linkding.example.com', 'entry')");
        }

        $this->skipIf(false !== $share && false !== $linkding, 'It seems that you already played this migration.');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('internal_setting') . " WHERE name = 'share_linkding';");
        $this->addSql('DELETE FROM ' . $this->getTable('internal_setting') . " WHERE name = 'linkding_url';");
    }
}
