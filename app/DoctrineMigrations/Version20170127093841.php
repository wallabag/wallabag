<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added indexes on wallabag_entry.is_starred and wallabag_entry.is_archived.
 */
class Version20170127093841 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $indexStarredName = 'IDX_entry_starred';
    private $indexArchivedName = 'IDX_entry_archived';

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
        $this->skipIf($entryTable->hasIndex($this->indexStarredName) && $entryTable->hasIndex($this->indexArchivedName), 'It seems that you already played this migration.');

        $entryTable->addIndex(['is_starred'], $this->indexStarredName);
        $entryTable->addIndex(['is_archived'], $this->indexArchivedName);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(false === $entryTable->hasIndex($this->indexStarredName) && false === $entryTable->hasIndex($this->indexArchivedName), 'It seems that you already played this migration.');

        $entryTable->dropIndex($this->indexStarredName);
        $entryTable->dropIndex($this->indexArchivedName);
    }
}
