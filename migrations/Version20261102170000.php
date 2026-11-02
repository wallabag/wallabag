<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove pocket_consumer_key field from wallabag_config, Pocket has shut down.
 */
final class Version20261102170000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf(!$configTable->hasColumn('pocket_consumer_key'), 'It seems that you already played this migration.');

        $configTable->dropColumn('pocket_consumer_key');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('The Pocket consumer keys have been deleted.');
    }
}
