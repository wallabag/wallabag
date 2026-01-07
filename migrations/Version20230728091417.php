<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove mobi export.
 */
final class Version20230728091417 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'export_mobi';");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('export_mobi', '1', 'export');");
    }
}
