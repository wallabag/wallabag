<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add custom_css column to config table.
 */
final class Version20230729093853 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('custom_css'), 'It seems that you already played this migration.');

        $configTable->addColumn('custom_css', 'text', [
            'notnull' => false,
        ]);

        $configTable->addColumn('font', 'text', [
            'notnull' => false,
        ]);

        $configTable->addColumn('fontsize', 'float', [
            'notnull' => false,
        ]);

        $configTable->addColumn('line_height', 'float', [
            'notnull' => false,
        ]);

        $configTable->addColumn('max_width', 'float', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $configTable->dropColumn('custom_css');
    }
}
