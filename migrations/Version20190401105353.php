<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add hashed_url in entry.
 */
class Version20190401105353 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('hashed_url'), 'It seems that you already played this migration.');

        $entryTable->addColumn('hashed_url', 'text', [
            'length' => 40,
            'notnull' => false,
        ]);

        $entryTable->addIndex(['user_id', 'hashed_url'], 'hashed_url_user_id', [], ['lengths' => [null, 40]]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('hashed_url'), 'It seems that you already played this migration.');

        $entryTable->dropIndex('hashed_url_user_id');
        $entryTable->dropColumn('hashed_url');
    }
}
