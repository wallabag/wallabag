<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190806130304 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE wallabag_entry CHANGE language language VARCHAR(20) DEFAULT NULL');
        $this->addSql('CREATE INDEX user_language ON wallabag_entry (language, user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX user_language ON `wallabag_entry`');
        $this->addSql('ALTER TABLE `wallabag_entry` CHANGE language language LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
