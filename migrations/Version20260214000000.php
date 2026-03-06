<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20260214000000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('reading_progress'), 'It seems that you already played this migration.');

        $entryTable->addColumn('reading_progress', 'smallint', [
            'default' => 0,
            'notnull' => true,
        ]);
        $entryTable->addColumn('reading_progress_updated_at', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('reading_progress');
        $entryTable->dropColumn('reading_progress_updated_at');
    }
}
