<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161104073720 extends AbstractMigration implements ContainerAwareInterface
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
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql('CREATE INDEX `created_at` ON `'.$this->getTable('entry').'` (`created_at` DESC)');
                break;

            case 'mysql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' ADD INDEX created_at (created_at);');
                break;

            case 'postgresql':
                $this->addSql('CREATE INDEX created_at ON '.$this->getTable('entry').' (created_at DESC)');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
