<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add settings for rendering proxy integration
 */
final class Version20260127072053 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('rendering_proxy_url', '', 'rendering_proxy');");
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('rendering_proxy_timeout', '100', 'rendering_proxy');");
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('rendering_proxy_all', '0', 'rendering_proxy');");
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('rendering_proxy_sites_to_proxy', '', 'rendering_proxy');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'rendering_proxy_url';");
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'rendering_proxy_timeout';");
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'rendering_proxy_all';");
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'rendering_proxy_sites_to_proxy';");
    }
}
