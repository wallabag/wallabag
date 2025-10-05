<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added `headers` field in entry table.
 */
class Version20170511115400 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('headers'), 'It seems that you already played this migration.');

        $entryTable->addColumn('headers', 'text', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('headers'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('headers');
    }
}
