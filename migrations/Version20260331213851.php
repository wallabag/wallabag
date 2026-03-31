<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add default_homepage column to config table.
 */
final class Version20260331213851 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('default_homepage'), 'It seems that you already played this migration.');

        $configTable->addColumn('default_homepage', 'string', [
            'notnull' => true,
            'default' => 'unread',
        ]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('config'))->dropColumn('default_homepage');
    }
}
