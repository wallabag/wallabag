<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add tables for the ignore origin rules.
 */
final class Version20190826204730 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if (false === $schema->hasTable($this->getTable('ignore_origin_user_rule'))) {
            $userTable = $schema->createTable($this->getTable('ignore_origin_user_rule', true));
            $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userTable->addColumn('config_id', 'integer');
            $userTable->addColumn('rule', 'string', ['length' => 255]);
            $userTable->addIndex(['config_id'], 'idx_config');
            $userTable->setPrimaryKey(['id']);
            $userTable->addForeignKeyConstraint($this->getTable('config'), ['config_id'], ['id'], [], 'fk_config');

            if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
                $schema->dropSequence('ignore_origin_user_rule_id_seq');
                $schema->createSequence('ignore_origin_user_rule_id_seq');
            }
        }

        if (false === $schema->hasTable($this->getTable('ignore_origin_instance_rule'))) {
            $instanceTable = $schema->createTable($this->getTable('ignore_origin_instance_rule', true));
            $instanceTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $instanceTable->addColumn('rule', 'string', ['length' => 255]);
            $instanceTable->setPrimaryKey(['id']);

            if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
                $schema->dropSequence('ignore_origin_instance_rule_id_seq');
                $schema->createSequence('ignore_origin_instance_rule_id_seq');
            }
        }
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->defaultIgnoreOriginInstanceRules as $entity) {
            $previous_rule = $this->connection
                ->fetchOne('SELECT * FROM ' . $this->getTable('ignore_origin_instance_rule') . " WHERE rule = '" . $entity['rule'] . "'");

            if (false === $previous_rule) {
                $this->connection->executeQuery('INSERT INTO ' . $this->getTable('ignore_origin_instance_rule') . " (rule) VALUES ('" . $entity['rule'] . "');");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->getTable('ignore_origin_user_rule'));
        $schema->dropTable($this->getTable('ignore_origin_instance_rule'));
    }
}
