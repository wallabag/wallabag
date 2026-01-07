<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added the internal setting to export articles in markdown.
 */
final class Version20241112193044 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('export_md', '1', 'export');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM' . $this->getTable('internal_setting') . " WHERE name = 'export_md';");
    }
}
