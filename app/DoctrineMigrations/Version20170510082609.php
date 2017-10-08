<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Changed length for username, username_canonical, email and email_canonical fields in wallabag_user table.
 */
class Version20170510082609 extends AbstractMigration implements ContainerAwareInterface
{
    private $fields = [
        'username',
        'username_canonical',
        'email',
        'email_canonical',
    ];

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
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'This migration only apply to MySQL');

        foreach ($this->fields as $field) {
            $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE ' . $field . ' ' . $field . ' VARCHAR(180) NOT NULL;');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'This migration only apply to MySQL');

        foreach ($this->fields as $field) {
            $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE ' . $field . ' ' . $field . ' VARCHAR(255) NOT NULL;');
        }
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
