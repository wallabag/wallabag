<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Drop theme fields from config table.
 */
final class Version20221123132612 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf(!$configTable->hasColumn('theme'), 'It seems that you already played this migration.');

        $configTable->dropColumn('theme');
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('theme'), 'It seems that you already played this migration.');

        $configTable->addColumn('theme', 'string', [
            'notnull' => true,
        ]);
    }
}
