<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20260505000000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $exists = $this->connection
            ->fetchOne('SELECT 1 FROM ' . $this->getTable('internal_setting') . " WHERE name = 'deleted_entries_expiration_days'");

        $this->skipIf(false !== $exists, 'It seems that you already played this migration.');

        $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('deleted_entries_expiration_days', NULL, 'entry')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('internal_setting') . " WHERE name = 'deleted_entries_expiration_days'");
    }
}
