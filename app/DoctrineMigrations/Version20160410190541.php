<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20160410190541 extends AbstractMigration implements ContainerAwareInterface
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

    private function hasColumn($tableName, $columnName)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $rows = $this->connection->executeQuery('pragma table_info('.$tableName.')')->fetchAll();
                foreach ($rows as $column) {
                    if (strcasecmp($column['name'], $columnName) === 0) {
                        return true;
                    }
                }

                return false;
            case 'mysql':
                $rows = $this->connection->executeQuery('SHOW COLUMNS FROM '.$tableName)->fetchAll();
                foreach ($rows as $column) {
                    if (strcasecmp($column['Field'], $columnName) === 0) {
                        return true;
                    }
                }

                return false;
            case 'postgresql':
                $sql = sprintf("SELECT count(*)
                    FROM information_schema.columns
                    WHERE table_schema = 'public' AND table_name = '%s' AND column_name = '%s'",
                    $tableName,
                    $columnName
                );
                $result = $this->connection->executeQuery($sql)->fetch();

                return  $result['count'] > 0;
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->skipIf($this->hasColumn($this->getTable('entry'), 'uuid'), 'It seems that you already played this migration.');

        if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql('ALTER TABLE "'.$this->getTable('entry').'" ADD uuid UUID DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE "'.$this->getTable('entry').'" ADD uuid LONGTEXT DEFAULT NULL');
        }

        $this->addSql('INSERT INTO "'.$this->getTable('craue_config_setting')."\" (name, value, section) VALUES ('share_public', '1', 'entry')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() != 'sqlite', 'This down migration can\'t be executed on SQLite databases, because SQLite don\'t support DROP COLUMN.');

        $this->addSql('ALTER TABLE "'.$this->getTable('entry').'" DROP uuid');
        $this->addSql('DELETE FROM "'.$this->getTable('craue_config_setting')."\" WHERE name = 'share_public'");
    }
}
