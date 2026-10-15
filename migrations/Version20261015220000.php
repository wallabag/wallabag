<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261015220000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $config = $schema->getTable($this->getTable('config', true));
        $config->addColumn('x_grabber_enabled', 'boolean', ['default' => true]);
        $config->addColumn('medium_grabber_enabled', 'boolean', ['default' => true]);
    }

    public function down(Schema $schema): void
    {
        $config = $schema->getTable($this->getTable('config', true));
        $config->dropColumn('x_grabber_enabled');
        $config->dropColumn('medium_grabber_enabled');
    }
}
