<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Changed reading_time field to prevent null value.
 */
class Version20171008195606 extends WallabagMigration
{
    public function up(Schema $schema)
    {
        $this->skipIf('sqlite' === $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $this->addSql('UPDATE ' . $this->getTable('entry') . ' SET reading_time = 0 WHERE reading_time IS NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11) NOT NULL;');
                break;
            case 'postgresql':
                $this->addSql('UPDATE ' . $this->getTable('entry') . ' SET reading_time = 0 WHERE reading_time IS NULL;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time SET NOT NULL;');
                break;
        }
    }

    public function down(Schema $schema)
    {
        $this->skipIf('sqlite' === $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE reading_time reading_time INT(11);');
                break;
            case 'postgresql':
                $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' ALTER COLUMN reading_time DROP NOT NULL;');
                break;
        }
    }
}
