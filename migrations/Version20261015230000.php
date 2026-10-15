<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261015230000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $schema->getTable($this->getTable('entry', true))->addColumn('x_capture_type', 'string', ['length' => 16, 'notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('entry', true))->dropColumn('x_capture_type');
    }
}
