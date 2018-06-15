<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Added list_mode in user config.
 */
class Version20161128084725 extends WallabagMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $this->skipIf($configTable->hasColumn('list_mode'), 'It seems that you already played this migration.');

        $configTable->addColumn('list_mode', 'integer', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $configTable->dropColumn('list_mode');
    }
}
