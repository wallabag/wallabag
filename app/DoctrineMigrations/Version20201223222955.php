<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

final class Version20201223222955 extends WallabagMigration
{
    const NEW_FIELD = 'feed_use_source';

    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn(self::NEW_FIELD), 'It seems that you already played this migration.');

        $configTable->addColumn(self::NEW_FIELD, 'boolean', ['notnull' => true, 'default' => 0]);
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf(!$configTable->hasColumn(self::NEW_FIELD), 'It seems that you already played this migration.');

        $configTable->dropColumn(self::NEW_FIELD);
    }
}
