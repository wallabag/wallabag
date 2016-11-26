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
        $this->skipIf($schema->getTable($this->getTable('config'))->hasColumn('action_mark_as_read'), 'It seems that you already played this migration.');

        $this->addSql('ALTER TABLE '.$this->getTable('config').' ADD action_mark_as_read INT DEFAULT 0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() != 'sqlite', 'This down migration can\'t be executed on SQLite databases, because SQLite don\'t support DROP COLUMN.');

        $this->addSql('ALTER TABLE '.$this->getTable('config').' DROP action_mark_as_read');
    }
}
