<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20260426000000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf($entryTable->hasColumn('deleted_at'), 'Column deleted_at already exists');
        $entryTable->addColumn('deleted_at', 'datetime', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('deleted_at');
    }
}
