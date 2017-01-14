<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renamed uuid to uid in entry table
 */
class Version20161214094402 extends AbstractMigration implements ContainerAwareInterface
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
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('uid'), 'It seems that you already played this migration.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                //
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' CHANGE uuid uid VARCHAR(23)');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' RENAME uuid TO uid');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('uuid'), 'It seems that you already played this migration.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                //
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' CHANGE uid uuid VARCHAR(23)');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' RENAME uid TO uuid');
        }
    }
}
