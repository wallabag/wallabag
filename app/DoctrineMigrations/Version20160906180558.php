<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160906180558 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `wallabag_group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_B2305B375E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallabag_user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_6E85169A76ED395 (user_id), INDEX IDX_6E85169FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wallabag_user_group ADD CONSTRAINT FK_6E85169A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id)');
        $this->addSql('ALTER TABLE wallabag_user_group ADD CONSTRAINT FK_6E85169FE54D947 FOREIGN KEY (group_id) REFERENCES `wallabag_group` (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wallabag_user_group DROP FOREIGN KEY FK_6E85169FE54D947');
        $this->addSql('DROP TABLE `wallabag_group`');
        $this->addSql('DROP TABLE wallabag_user_group');
    }
}
