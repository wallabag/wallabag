<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Add missing entries in craue_config_setting.
 */
class Version20190129120000 extends WallabagMigration
{
    var $settings = array(
        array("name" => "carrot", "value" => "1", "section" => "entry"),
        array("name" => "share_diaspora", "value" => "1", "section" => "entry"),
        array("name" => "diaspora_url", "value" => "http://diasporapod.com", "section" => "entry"),
        array("name" => "share_shaarli", "value" => "1", "section" => "entry"),
        array("name" => "shaarli_url", "value" => "http://myshaarli.com", "section" => "entry"),
        array("name" => "share_mail", "value" => "1", "section" => "entry"),
        array("name" => "share_twitter", "value" => "1", "section" => "entry"),
        array("name" => "show_printlink", "value" => "1", "section" => "entry"),
        array("name" => "export_epub", "value" => "1", "section" => "export"),
        array("name" => "export_mobi", "value" => "1", "section" => "export"),
        array("name" => "export_pdf", "value" => "1", "section" => "export"),
        array("name" => "export_csv", "value" => "1", "section" => "export"),
        array("name" => "export_json", "value" => "1", "section" => "export"),
        array("name" => "export_txt", "value" => "1", "section" => "export"),
        array("name" => "export_xml", "value" => "1", "section" => "export"),
        array("name" => "piwik_enabled", "value" => "0", "section" => "analytics"),
        array("name" => "piwik_host", "value" => "v2.wallabag.org", "section" => "analytics"),
        array("name" => "piwik_site_id", "value" => "1", "section" => "analytics"),
        array("name" => "demo_mode_enabled", "value" => "0", "section" => "misc"),
        array("name" => "demo_mode_username", "value" => "wallabag", "section" => "misc"),
        array("name" => "wallabag_support_url", "value" => "https://www.wallabag.org/pages/support.html", "section" => "misc"),
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $piwikEnabled = $this->container
            ->get('doctrine.orm.default_entity_manager')
            ->getConnection()
            ->fetchArray('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = 'piwik_enabled'");

        $this->skipIf(false !== $piwikEnabled, 'It seems that you already played this migration, or user the wallabag:install command.');

        foreach ($this->settings as $setting) {
            $this->addSql("
                INSERT INTO " . $this->getTable('craue_config_setting') . "
                    (name, value, section)
                    VALUES (
                        '" . $setting['name'] . "',
                        '" . $setting['value'] . "',
                        '" . $setting['section'] . "'
                    );
            ");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        foreach ($this->settings as $setting) {
            $this->addSql("
                DELETE FROM " . $this->getTable('craue_config_setting') . "
                WHERE name = '" . $setting['name'] . "';
            ");
        }
    }
}
