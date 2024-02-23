<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added pocket_consumer_key field on wallabag_config.
 */
class Version20160916201049 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('pocket_consumer_key'), 'It seems that you already played this migration.');

        $configTable->addColumn('pocket_consumer_key', 'string', ['notnull' => false]);
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'pocket_consumer_key';");
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $configTable->dropColumn('pocket_consumer_key');
        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('pocket_consumer_key', NULL, 'import')");
    }
}
