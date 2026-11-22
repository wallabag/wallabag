<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261103120000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('mail_import_token'), 'It seems that you already played this migration.');

        $configTable->addColumn('mail_import_token', 'string', [
            'length' => 32,
            'notnull' => false,
        ]);
        $configTable->addUniqueIndex(['mail_import_token'], 'mail_import_token');
    }

    public function down(Schema $schema): void
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $configTable->dropIndex('mail_import_token');
        $configTable->dropColumn('mail_import_token');
    }
}
