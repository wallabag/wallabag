<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161001072726 extends AbstractMigration implements ContainerAwareInterface
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
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP FOREIGN KEY FK_F035C9E5BA364942');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP FOREIGN KEY FK_F035C9E5BAD26311');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_F035C9E5BA364942 FOREIGN KEY (entry_id) REFERENCES `entry` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_F035C9E5BAD26311 FOREIGN KEY (tag_id) REFERENCES `tag` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE '.$this->getTable('annotation').' DROP FOREIGN KEY FK_2E443EF2BA364942');
        $this->addSql('ALTER TABLE '.$this->getTable('annotation').' ADD CONSTRAINT FK_2E443EF2BA364942 FOREIGN KEY (entry_id) REFERENCES `entry` (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        $this->addSql('ALTER TABLE '.$this->getTable('annotation').' DROP FOREIGN KEY FK_2E443EF2BA364942');
        $this->addSql('ALTER TABLE '.$this->getTable('annotation').' ADD CONSTRAINT FK_2E443EF2BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id)');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP FOREIGN KEY FK_F035C9E5BA364942');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP FOREIGN KEY FK_F035C9E5BAD26311');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_F035C9E5BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id)');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_F035C9E5BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
    }
}
