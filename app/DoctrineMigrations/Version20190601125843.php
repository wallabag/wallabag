<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Added `given_url` & `hashed_given_url` field in entry table.
 */
class Version20190601125843 extends WallabagMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
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

        $entryTable->dropIndex('hashed_url_user_id');
        $entryTable->addIndex(
            [
                'user_id',
                'hashed_url',
                'hashed_given_url',
            ],
            'hashed_urls_user_id',
            [],
            [
                // specify length for index which is required by MySQL on text field
                'lengths' => [
                    // user_id
                    null,
                    // hashed_url
                    40,
                    // hashed_given_url
                    40,
                ],
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        if ($entryTable->hasColumn('given_url')) {
            $entryTable->dropColumn('given_url');
        }

        if ($entryTable->hasColumn('hashed_given_url')) {
            $entryTable->dropColumn('hashed_given_url');
        }

        $entryTable->dropIndex('hashed_urls_user_id');
        $entryTable->addIndex(['user_id', 'hashed_url'], 'hashed_url_user_id', [], ['lengths' => [null, 40]]);
    }
}
