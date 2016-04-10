<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160410190541 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE wallabag_entry ADD uuid LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE wallabag_entry SET uuid = uuid()');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE `wallabag_entry` DROP uuid');
    }
}
