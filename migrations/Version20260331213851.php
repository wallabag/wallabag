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

        // Generate platform-specific SQL instead of duplicating table definitions as in our older migrations
        $comparator = $this->connection->createSchemaManager()->createComparator();
        $nullableTable = clone $configTable;
        $nullableTable->addColumn('default_homepage', 'string', [
            'notnull' => false,
        ]);

        foreach ($this->platform->getAlterTableSQL($comparator->compareTables($configTable, $nullableTable)) as $sql) {
            $this->addSql($sql);
        }

        $this->addSql(
            'UPDATE ' . $this->getTable('config') . ' SET default_homepage = :defaultHomepage WHERE default_homepage IS NULL',
            ['defaultHomepage' => HomepageTarget::Unread->value]
        );

        $notNullTable = clone $nullableTable;
        $notNullTable->getColumn('default_homepage')->setNotnull(true);

        foreach ($this->platform->getAlterTableSQL($comparator->compareTables($nullableTable, $notNullTable)) as $sql) {
            $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('config'))->dropColumn('default_homepage');
    }
}
