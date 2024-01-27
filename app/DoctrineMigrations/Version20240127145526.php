<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

final class Version20240127145526 extends WallabagMigration
{
    public function getDescription(): string
    {
        return 'Fix doctrine:schema:drop';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' DROP FOREIGN KEY FK_entry_tag_entry;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' ADD CONSTRAINT FK_C9F0DD7CBA364942 FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' DROP FOREIGN KEY FK_entry_tag_tag;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' ADD CONSTRAINT FK_C9F0DD7CBAD26311 FOREIGN KEY (tag_id) REFERENCES ' . $this->getTable('tag') . ' (id) ON DELETE CASCADE');

                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' DROP FOREIGN KEY FK_annotation_entry;');
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' ADD CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES ' . $this->getTable('entry') . ' (id) ON DELETE CASCADE');

                $this->addSql('ALTER TABLE ' . $this->getTable('site_credential') . ' DROP FOREIGN KEY fk_user;');
                $this->addSql('ALTER TABLE ' . $this->getTable('site_credential') . ' ADD CONSTRAINT FK_E056246CA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE');

                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_user_rule') . ' DROP FOREIGN KEY fk_config;');
                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_user_rule') . ' ADD CONSTRAINT FK_608BE7EE24DB0683 FOREIGN KEY (config_id) REFERENCES ' . $this->getTable('config') . ' (id) ON DELETE CASCADE');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' RENAME CONSTRAINT FK_entry_tag_entry TO FK_C9F0DD7CBA364942;');
                $this->addSql('ALTER TABLE ' . $this->getTable('entry_tag') . ' RENAME CONSTRAINT FK_entry_tag_tag TO FK_C9F0DD7CBAD26311;');
                $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' RENAME CONSTRAINT FK_annotation_entry TO FK_A7AED006BA364942;');
                $this->addSql('ALTER TABLE ' . $this->getTable('site_credential') . ' RENAME CONSTRAINT fk_user TO FK_E056246CA76ED395;');
                $this->addSql('ALTER TABLE ' . $this->getTable('ignore_origin_user_rule') . ' RENAME CONSTRAINT fk_config TO FK_608BE7EE24DB0683;');
                break;
        }
    }

    public function down(Schema $schema): void
    {
    }
}
