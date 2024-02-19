<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add origin_url column.
 */
class Version20171105202000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('origin_url'), 'It seems that you already played this migration.');

        $entryTable->addColumn('origin_url', 'text', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('origin_url'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('origin_url');
    }
}
