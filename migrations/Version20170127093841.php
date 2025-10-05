<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added indexes on wallabag_entry.is_starred and wallabag_entry.is_archived.
 */
class Version20170127093841 extends WallabagMigration
{
    private $indexStarredName = 'IDX_entry_starred';
    private $indexArchivedName = 'IDX_entry_archived';

    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf($entryTable->hasIndex($this->indexStarredName) && $entryTable->hasIndex($this->indexArchivedName), 'It seems that you already played this migration.');

        $entryTable->addIndex(['is_starred'], $this->indexStarredName);
        $entryTable->addIndex(['is_archived'], $this->indexArchivedName);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(false === $entryTable->hasIndex($this->indexStarredName) && false === $entryTable->hasIndex($this->indexArchivedName), 'It seems that you already played this migration.');

        $entryTable->dropIndex($this->indexStarredName);
        $entryTable->dropIndex($this->indexArchivedName);
    }
}
