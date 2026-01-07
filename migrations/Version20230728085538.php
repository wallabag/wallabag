<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove demonstration mode settings.
 */
final class Version20230728085538 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'demo_mode_enabled';");
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'demo_mode_username';");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('demo_mode_enabled', '0', 'misc');");
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('demo_mode_username', 'wallabag', 'misc');");
    }
}
