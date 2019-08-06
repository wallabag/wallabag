<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190806131006 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE INDEX user_starred ON wallabag_entry (user_id, is_starred, starred_at)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX user_starred ON `wallabag_entry`');
    }
}
