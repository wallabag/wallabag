<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Change reading speed value.
 */
final class Version20190708122957 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE ' . $this->getTable('config', true) . ' SET reading_speed = reading_speed*200');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE ' . $this->getTable('config', true) . ' SET reading_speed = reading_speed/200');
    }
}
