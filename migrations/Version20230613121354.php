<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added a new setting to display or not thumbnails.
 */
final class Version20230613121354 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('display_thumbnails'), 'It seems that you already played this migration.');

        $configTable->addColumn('display_thumbnails', 'integer', [
            'default' => 1,
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $configTable->dropColumn('display_thumbnails');
    }
}
