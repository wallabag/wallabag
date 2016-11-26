<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20160911214952 extends AbstractMigration implements ContainerAwareInterface
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
        $this->addSql('INSERT INTO "'.$this->getTable('craue_config_setting').'" (name, value, section) VALUES (\'import_with_redis\', \'0\', \'import\')');
        $this->addSql('INSERT INTO "'.$this->getTable('craue_config_setting').'" (name, value, section) VALUES (\'import_with_rabbitmq\', \'0\', \'import\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
