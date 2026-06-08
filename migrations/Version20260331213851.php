<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
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

        $table = $this->getTable('config');
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof SqlitePlatform) {
            $this->addSql('ALTER TABLE ' . $table . ' ADD COLUMN default_homepage VARCHAR(255) NOT NULL DEFAULT \'' . HomepageTarget::Unread->value . '\'');
        } else {
            $this->addSql('ALTER TABLE ' . $table . ' ADD COLUMN default_homepage VARCHAR(255)');
            $this->addSql('UPDATE ' . $table . ' SET default_homepage = \'' . HomepageTarget::Unread->value . '\'');

            if ($platform instanceof PostgreSQLPlatform) {
                $this->addSql('ALTER TABLE ' . $table . ' ALTER COLUMN default_homepage SET NOT NULL, ALTER COLUMN default_homepage SET DEFAULT \'' . HomepageTarget::Unread->value . '\'');
            } elseif ($platform instanceof MySQLPlatform) {
                $this->addSql('ALTER TABLE ' . $table . ' MODIFY COLUMN default_homepage VARCHAR(255) NOT NULL DEFAULT \'' . HomepageTarget::Unread->value . '\'');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getTable('config'))->dropColumn('default_homepage');
    }
}
