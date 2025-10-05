<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added foreign keys for account resetting.
 */
class Version20160410190541 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('uid') || $entryTable->hasColumn('uuid'), 'It seems that you already played this migration.');

        $entryTable->addColumn('uid', 'string', [
            'notnull' => false,
            'length' => 23,
        ]);

        $sharePublic = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'share_public'");

        if (false === $sharePublic) {
            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('share_public', '1', 'entry')");
        }
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('uid');

        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'share_public'");
    }
}
