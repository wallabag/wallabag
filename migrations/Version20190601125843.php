<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added `given_url` & `hashed_given_url` field in entry table.
 */
class Version20190601125843 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        if (!$entryTable->hasColumn('given_url')) {
            $entryTable->addColumn('given_url', 'text', [
                'notnull' => false,
            ]);
        }

        if (!$entryTable->hasColumn('hashed_given_url')) {
            $entryTable->addColumn('hashed_given_url', 'text', [
                'length' => 40,
                'notnull' => false,
            ]);
        }

        // 40 = length of sha1 field hashed_given_url
        $entryTable->addIndex(['user_id', 'hashed_given_url'], 'hashed_given_url_user_id', [], ['lengths' => [null, 40]]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        if ($entryTable->hasColumn('given_url')) {
            $entryTable->dropColumn('given_url');
        }

        if ($entryTable->hasColumn('hashed_given_url')) {
            $entryTable->dropColumn('hashed_given_url');
        }

        $entryTable->dropIndex('hashed_given_url_user_id');
    }
}
