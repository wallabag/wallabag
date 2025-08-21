<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added settings for RabbitMQ and Redis imports.
 */
class Version20160911214952 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $redis = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'import_with_redis'");

        if (false === $redis) {
            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('import_with_redis', 0, 'import')");
        }

        $rabbitmq = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'import_with_rabbitmq'");

        if (false === $rabbitmq) {
            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('import_with_rabbitmq', 0, 'import')");
        }

        $this->skipIf(false !== $rabbitmq && false !== $redis, 'It seems that you already played this migration.');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'import_with_redis';");
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'import_with_rabbitmq';");
    }
}
