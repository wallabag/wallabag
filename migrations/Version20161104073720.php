<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added created_at index on entry table.
 */
class Version20161104073720 extends WallabagMigration
{
    private $indexName = 'IDX_entry_created_at';

    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf($entryTable->hasIndex($this->indexName), 'It seems that you already played this migration.');

        $entryTable->addIndex(['created_at'], $this->indexName);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(false === $entryTable->hasIndex($this->indexName), 'It seems that you already played this migration.');

        $entryTable->dropIndex($this->indexName);
    }
}
