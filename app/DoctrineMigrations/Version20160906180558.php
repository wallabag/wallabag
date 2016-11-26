<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added group table.
 */
class Version20160906180558 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix').$tableName;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $groupTable = $schema->createTable($this->getTable('group'));
        $groupTable->addColumn('id', 'integer');
        $groupTable->addColumn('name', 'string');
        $groupTable->addColumn('roles', 'blob');
        $groupTable->setPrimaryKey(['id']);

        $userGroupTable = $schema->createTable($this->getTable('user_group'));
        $userGroupTable->addColumn('user_id', 'integer');
        $userGroupTable->addColumn('group_id', 'integer');
        $userGroupTable->setPrimaryKey(['user_id', 'group_id']);

        $userGroupTable->addForeignKeyConstraint(
            $groupTable,
            array('group_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );

        $userGroupTable->addForeignKeyConstraint(
            $this->getTable('user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\'.');

        $schema->dropTable($this->getTable('group'));
        $schema->dropTable($this->getTable('user_group'));
    }
}
