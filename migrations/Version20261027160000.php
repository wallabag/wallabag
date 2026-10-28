<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261027160000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $this->skipIf($configTable->hasColumn('open_original_url'), 'This setting already exists.');
        $configTable->addColumn('open_original_url', 'boolean', ['default' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('config'))->dropColumn('open_original_url');
    }
}
