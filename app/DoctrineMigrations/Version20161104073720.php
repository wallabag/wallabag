<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added created_at index on entry table.
 */
class Version20161104073720 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $indexName = 'IDX_entry_created_at';

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
        $this->skipIf($entryTable->hasIndex($this->indexName), 'It seems that you already played this migration.');

        $entryTable->addIndex(['created_at'], $this->indexName);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(false === $entryTable->hasIndex($this->indexName), 'It seems that you already played this migration.');

        $entryTable->dropIndex($this->indexName);
    }
}
