<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Add http_status in `entry_table`.
 */
class Version20161118134328 extends WallabagMigration
{
    public function up(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('http_status'), 'It seems that you already played this migration.');

        $entryTable->addColumn('http_status', 'string', [
            'length' => 3,
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('http_status'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('http_status');
    }
}
