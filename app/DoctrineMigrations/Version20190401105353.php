<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Add hashed_url in entry.
 */
class Version20190401105353 extends WallabagMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('hashed_url'), 'It seems that you already played this migration.');

        $entryTable->addColumn('hashed_url', 'text', [
            'length' => 32,
            'notnull' => false,
        ]);

        // sqlite doesn't have the MD5 function by default
        if ('sqlite' !== $this->connection->getDatabasePlatform()->getName()) {
            $this->addSql('UPDATE ' . $this->getTable('entry') . ' SET hashed_url = MD5(url)');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('hashed_url'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('hashed_url');
    }
}
