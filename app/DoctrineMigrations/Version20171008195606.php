<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Changed reading_time field to prevent null value
 */
class Version20171008195606 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() === 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11) NOT NULL;');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time SET NOT NULL;');
                break;
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() === 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11);');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time DROP NOT NULL;');
                break;
        }
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
