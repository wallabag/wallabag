<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add shaarli_share_origin_url in craue_config_setting.
 */
class Version20171125164500 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $shaarliShareOriginUrl = $this->connection
            ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'shaarli_share_origin_url'");

        $this->skipIf(false !== $shaarliShareOriginUrl, 'It seems that you already played this migration.');

        $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('shaarli_share_origin_url', '0', 'entry')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'shaarli_share_origin_url';");
    }
}
