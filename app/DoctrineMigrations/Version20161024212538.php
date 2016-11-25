<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161024212538 extends AbstractMigration implements ContainerAwareInterface
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
        $this->skipIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        $this->skipIf($schema->getTable($this->getTable('oauth2_clients'))->hasColumn('user_id'), 'It seems that you already played this migration.');

        $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD user_id INT(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD CONSTRAINT FK_clients_user_clients FOREIGN KEY (user_id) REFERENCES '.$this->getTable('user').' (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
