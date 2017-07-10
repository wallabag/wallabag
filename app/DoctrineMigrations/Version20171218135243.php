<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Added indexes on wallabag_entry.url and wallabag_entry.given_url and wallabag_entry.user_id.
 */
class Version20171218135243 extends WallabagMigration
{
    private $indexGivenUrl = 'IDX_entry_given_url';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf($entryTable->hasIndex($this->indexGivenUrl), 'It seems that you already played this migration.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $sql = 'CREATE UNIQUE INDEX ' . $this->indexGivenUrl . ' ON ' . $this->getTable('entry') . ' (url, given_url, user_id);';
                break;
            case 'mysql':
                $sql = 'CREATE UNIQUE INDEX ' . $this->indexGivenUrl . ' ON ' . $this->getTable('entry') . ' (url (255), given_url (255), user_id);';
                break;
            case 'postgresql':
                $sql = 'CREATE UNIQUE INDEX ' . $this->indexGivenUrl . ' ON ' . $this->getTable('entry') . ' (url, given_url, user_id);';
                break;
        }

        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(false === $entryTable->hasIndex($this->indexGivenUrl), 'It seems that you already played this migration.');

        $entryTable->dropIndex($this->indexGivenUrl);
    }
}
