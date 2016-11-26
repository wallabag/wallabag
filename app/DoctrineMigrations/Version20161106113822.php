<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161106113822 extends AbstractMigration implements ContainerAwareInterface
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
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('action_mark_as_read'), 'It seems that you already played this migration.');

        $configTable->addColumn('action_mark_as_read', 'integer', [
            'default' => 0,
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $userTable->dropColumn('action_mark_as_read');
    }
}
