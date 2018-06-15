<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Add published_at and published_by in `entry` table.
 */
class Version20170405182620 extends WallabagMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('published_at'), 'It seems that you already played this migration.');

        $entryTable->addColumn('published_at', 'datetime', [
            'notnull' => false,
        ]);

        $this->skipIf($entryTable->hasColumn('published_by'), 'It seems that you already played this migration.');

        $entryTable->addColumn('published_by', 'text', [
            'notnull' => false,
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('published_at'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('published_at');

        $this->skipIf(!$entryTable->hasColumn('published_by'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('published_by');
    }
}
