<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

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

            if ('postgresql' === $this->connection->getDatabasePlatform()->getName()) {
                $schema->dropSequence('ignore_origin_user_rule_id_seq');
                $schema->createSequence('ignore_origin_user_rule_id_seq');
            }
        }

        if (false === $schema->hasTable($this->getTable('ignore_origin_instance_rule'))) {
            $instanceTable = $schema->createTable($this->getTable('ignore_origin_instance_rule', true));
            $instanceTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $instanceTable->addColumn('rule', 'string', ['length' => 255]);
            $instanceTable->setPrimaryKey(['id']);

            if ('postgresql' === $this->connection->getDatabasePlatform()->getName()) {
                $schema->dropSequence('ignore_origin_instance_rule_id_seq');
                $schema->createSequence('ignore_origin_instance_rule_id_seq');
            }
        }
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->container->getParameter('wallabag_core.default_ignore_origin_instance_rules') as $entity) {
            $previous_rule = $this->container
                ->get('doctrine.orm.default_entity_manager')
                ->getConnection()
                ->fetchArray('SELECT * FROM ' . $this->getTable('ignore_origin_instance_rule') . " WHERE rule = '" . $entity['rule'] . "'");

            if (false === $previous_rule) {
                $this->addSql('INSERT INTO ' . $this->getTable('ignore_origin_instance_rule') . " (rule) VALUES ('" . $entity['rule'] . "');");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->getTable('ignore_origin_user_rule'));
        $schema->dropTable($this->getTable('ignore_origin_instance_rule'));
    }
}
