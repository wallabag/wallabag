<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added group table
 */
class Version20160906180558 extends AbstractMigration
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

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql('CREATE TABLE '.$this->getTable('group').' (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, roles CLOB NOT NULL, PRIMARY KEY(id))');
                $this->addSql('CREATE TABLE '.$this->getTable('user_group').' (user_id INTEGER NOT NULL, group_id INTEGER NOT NULL, PRIMARY KEY(user_id, group_id), CONSTRAINT FK_6E85169A76ED395 FOREIGN KEY (user_id) REFERENCES '.$this->getTable('user').' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6E85169FE54D947 FOREIGN KEY (group_id) REFERENCES '.$this->getTable('group').' (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            break;
            case 'mysql':
                $this->addSql('CREATE TABLE '.$this->getTable('group').' (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_B2305B375E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
                $this->addSql('CREATE TABLE '.$this->getTable('user_group').' (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_6E85169A76ED395 (user_id), INDEX IDX_6E85169FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
                $this->addSql('ALTER TABLE '.$this->getTable('user_group').' ADD CONSTRAINT FK_6E85169A76ED395 FOREIGN KEY (user_id) REFERENCES '.$this->getTable('user').' (id)');
                $this->addSql('ALTER TABLE '.$this->getTable('user_group').' ADD CONSTRAINT FK_6E85169FE54D947 FOREIGN KEY (group_id) REFERENCES '.$this->getTable('group').' (id)');
            break;

            case 'postgresql':
                $this->addSql('CREATE TABLE '.$this->getTable('group').' (id integer NOT NULL, name character varying(255) NOT NULL, roles text NOT NULL);');
                $this->addSql('CREATE TABLE '.$this->getTable('user_group').' (user_id integer NOT NULL, group_id integer NOT NULL);');
                $this->addSql('ALTER TABLE ONLY '.$this->getTable('user_group').' ADD CONSTRAINT fk_6e85169a76ed395 FOREIGN KEY (user_id) REFERENCES '.$this->getTable('user').'(id);');
                $this->addSql('ALTER TABLE ONLY '.$this->getTable('user_group').' ADD CONSTRAINT fk_6e85169fe54d947 FOREIGN KEY (group_id) REFERENCES '.$this->getTable('group').'(id);');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE '.$this->getTable('user_group').' DROP FOREIGN KEY FK_6E85169FE54D947');
        $this->addSql('DROP TABLE '.$this->getTable('group'));
        $this->addSql('DROP TABLE '.$this->getTable('user_group'));
    }
}
