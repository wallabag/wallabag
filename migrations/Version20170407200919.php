<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove isPublic in Entry Table.
 */
class Version20170407200919 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(!$entryTable->hasColumn('is_public'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('is_public');
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf($entryTable->hasColumn('is_public'), 'It seems that you already played this migration.');

        $entryTable->addColumn('is_public', 'boolean', ['notnull' => false, 'default' => 0]);
    }
}
