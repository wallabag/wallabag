<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160120200534_settings extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE craue_config_setting (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B95BA9425E237E06 (name), PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql("INSERT INTO `craue_config_setting` (`name`, `value`, `section`) VALUES
            ('download_pictures',     '1',                                              'entry'),
            ('carrot',                '1',                                              'entry'),
            ('share_diaspora',        '1',                                              'entry'),
            ('diaspora_url',          'http://diasporapod.com',                         'entry'),
            ('share_shaarli',         '1',                                              'entry'),
            ('shaarli_url',           'http://myshaarli.com',                           'entry'),
            ('share_mail',            '1',                                              'entry'),
            ('share_twitter',         '1',                                              'entry'),
            ('export_epub',           '1',                                              'export'),
            ('export_mobi',           '1',                                              'export'),
            ('export_pdf',            '1',                                              'export'),
            ('pocket_consumer_key',   NULL,                                             'import'),
            ('show_printlink',        '1',                                              'entry'),
            ('wallabag_support_url',  'https://www.wallabag.org/pages/support.html',    'misc'),
            ('wallabag_url',          'http://v2.wallabag.org',                         'misc')"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE craue_config_setting');
    }
}
