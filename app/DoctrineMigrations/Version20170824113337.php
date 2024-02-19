<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add starred_at column and set its value to updated_at for is_starred entries.
 */
class Version20170824113337 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('starred_at'), 'It seems that you already played this migration.');

        $entryTable->addColumn('starred_at', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function postUp(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(!$entryTable->hasColumn('starred_at'), 'Unable to add starred_at colum');

        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('entry') . ' SET starred_at = updated_at WHERE is_starred = :is_starred',
            [
                'is_starred' => true,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('starred_at'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('starred_at');
    }
}
