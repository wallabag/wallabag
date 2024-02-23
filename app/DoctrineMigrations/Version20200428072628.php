<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Renamed Piwik to Matomo in configuration.
 */
final class Version20200428072628 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'matomo_enabled' where name = 'piwik_enabled';");
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'matomo_host' where name = 'piwik_host';");
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'matomo_site_id' where name = 'piwik_site_id';");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'piwik_enabled' where name = 'matomo_enabled';");
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'piwik_host' where name = 'matomo_host';");
        $this->addSql('UPDATE ' . $this->getTable('internal_setting', true) . " SET name = 'piwik_site_id' where name = 'matomo_site_id';");
    }
}
