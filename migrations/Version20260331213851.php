<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;
use Wallabag\Enum\HomepageTarget;

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
            'notnull' => false,
        ]);
    }

    public function postUp(Schema $schema): void
    {
        $this->skipIf(!$schema->getTable($this->getTable('config'))->hasColumn('default_homepage'), 'Unable to update default_homepage column');

        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('config') . ' SET default_homepage = :defaultHomepage WHERE default_homepage IS NULL',
            ['defaultHomepage' => HomepageTarget::Unread->value]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('config'))->dropColumn('default_homepage');
    }
}
