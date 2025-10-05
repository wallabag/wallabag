<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add archived_at column and set its value to updated_at for is_archived entries.
 */
class Version20180405182455 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('archived_at'), 'It seems that you already played this migration.');

        $entryTable->addColumn('archived_at', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function postUp(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(!$entryTable->hasColumn('archived_at'), 'Unable to add archived_at colum');

        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('entry') . ' SET archived_at = updated_at WHERE is_archived = :is_archived',
            [
                'is_archived' => true,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('archived_at'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('archived_at');
    }
}
