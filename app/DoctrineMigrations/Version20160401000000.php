<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Initial database structure.
 */
class Version20160401000000 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->skipIf($schema->hasTable($this->getTable('entry')), 'Database already initialized');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $sql = <<<'SQL'
CREATE TABLE wallabag_craue_config_setting (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name));
CREATE UNIQUE INDEX UNIQ_5D9649505E237E06 ON wallabag_craue_config_setting (name);
CREATE TABLE "wallabag_tagging_rule" (id INTEGER NOT NULL, config_id INTEGER DEFAULT NULL, rule VARCHAR(255) NOT NULL, tags CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_2D9B3C5424DB0683 FOREIGN KEY (config_id) REFERENCES "wallabag_config" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_2D9B3C5424DB0683 ON "wallabag_tagging_rule" (config_id);
CREATE TABLE "wallabag_tag" (id INTEGER NOT NULL, label CLOB NOT NULL, slug VARCHAR(128) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_4CA58A8C989D9B62 ON "wallabag_tag" (slug);
CREATE TABLE "wallabag_entry" (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL, url CLOB DEFAULT NULL, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype CLOB DEFAULT NULL, language CLOB DEFAULT NULL, reading_time INTEGER DEFAULT NULL, domain_name CLOB DEFAULT NULL, preview_picture CLOB DEFAULT NULL, is_public BOOLEAN DEFAULT '0', PRIMARY KEY(id), CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_F4D18282A76ED395 ON "wallabag_entry" (user_id);
CREATE TABLE wallabag_entry_tag (entry_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(entry_id, tag_id), CONSTRAINT FK_C9F0DD7CBA364942 FOREIGN KEY (entry_id) REFERENCES "wallabag_entry" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C9F0DD7CBAD26311 FOREIGN KEY (tag_id) REFERENCES "wallabag_tag" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_C9F0DD7CBA364942 ON wallabag_entry_tag (entry_id);
CREATE INDEX IDX_C9F0DD7CBAD26311 ON wallabag_entry_tag (tag_id);
CREATE TABLE "wallabag_config" (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL, rss_token VARCHAR(255) DEFAULT NULL, rss_limit INTEGER DEFAULT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON "wallabag_config" (user_id);
CREATE TABLE wallabag_oauth2_refresh_tokens (id INTEGER NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INTEGER DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_20C9FB245F37A13B ON wallabag_oauth2_refresh_tokens (token);
CREATE INDEX IDX_20C9FB2419EB6921 ON wallabag_oauth2_refresh_tokens (client_id);
CREATE INDEX IDX_20C9FB24A76ED395 ON wallabag_oauth2_refresh_tokens (user_id);
CREATE TABLE wallabag_oauth2_access_tokens (id INTEGER NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INTEGER DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_368A42095F37A13B ON wallabag_oauth2_access_tokens (token);
CREATE INDEX IDX_368A420919EB6921 ON wallabag_oauth2_access_tokens (client_id);
CREATE INDEX IDX_368A4209A76ED395 ON wallabag_oauth2_access_tokens (user_id);
CREATE TABLE wallabag_oauth2_auth_codes (id INTEGER NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri CLOB NOT NULL, expires_at INTEGER DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_EE52E3FA5F37A13B ON wallabag_oauth2_auth_codes (token);
CREATE INDEX IDX_EE52E3FA19EB6921 ON wallabag_oauth2_auth_codes (client_id);
CREATE INDEX IDX_EE52E3FAA76ED395 ON wallabag_oauth2_auth_codes (user_id);
CREATE TABLE wallabag_oauth2_clients (id INTEGER NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris CLOB NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types CLOB NOT NULL, PRIMARY KEY(id));
CREATE TABLE "wallabag_user" (id INTEGER NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked BOOLEAN NOT NULL, expired BOOLEAN NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL, credentials_expired BOOLEAN NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, twoFactorAuthentication BOOLEAN NOT NULL, trusted CLOB DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON "wallabag_user" (username_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON "wallabag_user" (email_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON "wallabag_user" (confirmation_token);
CREATE TABLE wallabag_annotation (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, entry_id INTEGER DEFAULT NULL, text CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, quote VARCHAR(255) NOT NULL, ranges CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A7AED006A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES "wallabag_entry" (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_A7AED006A76ED395 ON wallabag_annotation (user_id);
CREATE INDEX IDX_A7AED006BA364942 ON wallabag_annotation (entry_id);
SQL
                ;

                foreach (explode("\n", $sql) as $query) {
                    $this->addSql($query);
                }

                break;
            case 'mysql':
                $sql = <<<'SQL'
CREATE TABLE wallabag_craue_config_setting (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5D9649505E237E06 (name), PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `wallabag_entry` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title LONGTEXT DEFAULT NULL, url LONGTEXT DEFAULT NULL, is_archived TINYINT(1) NOT NULL, is_starred TINYINT(1) NOT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mimetype LONGTEXT DEFAULT NULL, language LONGTEXT DEFAULT NULL, reading_time INT DEFAULT NULL, domain_name LONGTEXT DEFAULT NULL, preview_picture LONGTEXT DEFAULT NULL, is_public TINYINT(1) DEFAULT '0', INDEX IDX_F4D18282A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_entry_tag (entry_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C9F0DD7CBA364942 (entry_id), INDEX IDX_C9F0DD7CBAD26311 (tag_id), PRIMARY KEY(entry_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `wallabag_config` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, theme VARCHAR(255) NOT NULL, items_per_page INT NOT NULL, language VARCHAR(255) NOT NULL, rss_token VARCHAR(255) DEFAULT NULL, rss_limit INT DEFAULT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_87E64C53A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `wallabag_tagging_rule` (id INT AUTO_INCREMENT NOT NULL, config_id INT DEFAULT NULL, rule VARCHAR(255) NOT NULL, tags LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', INDEX IDX_2D9B3C5424DB0683 (config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `wallabag_tag` (id INT AUTO_INCREMENT NOT NULL, `label` LONGTEXT NOT NULL, slug VARCHAR(128) NOT NULL, UNIQUE INDEX UNIQ_4CA58A8C989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_oauth2_clients (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT '(DC2Type:array)', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_oauth2_access_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_368A42095F37A13B (token), INDEX IDX_368A420919EB6921 (client_id), INDEX IDX_368A4209A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_oauth2_refresh_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_20C9FB245F37A13B (token), INDEX IDX_20C9FB2419EB6921 (client_id), INDEX IDX_20C9FB24A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_oauth2_auth_codes (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_EE52E3FA5F37A13B (token), INDEX IDX_EE52E3FA19EB6921 (client_id), INDEX IDX_EE52E3FAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE `wallabag_user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INT DEFAULT NULL, twoFactorAuthentication TINYINT(1) NOT NULL, trusted LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', UNIQUE INDEX UNIQ_1D63E7E592FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1D63E7E5C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE wallabag_annotation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, text LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, quote VARCHAR(255) NOT NULL, ranges LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_A7AED006A76ED395 (user_id), INDEX IDX_A7AED006BA364942 (entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE `wallabag_entry` ADD CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_C9F0DD7CBA364942 FOREIGN KEY (entry_id) REFERENCES `wallabag_entry` (id);
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_C9F0DD7CBAD26311 FOREIGN KEY (tag_id) REFERENCES `wallabag_tag` (id);
ALTER TABLE `wallabag_config` ADD CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE `wallabag_tagging_rule` ADD CONSTRAINT FK_2D9B3C5424DB0683 FOREIGN KEY (config_id) REFERENCES `wallabag_config` (id);
ALTER TABLE wallabag_oauth2_access_tokens ADD CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id);
ALTER TABLE wallabag_oauth2_access_tokens ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE wallabag_oauth2_refresh_tokens ADD CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id);
ALTER TABLE wallabag_oauth2_refresh_tokens ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE wallabag_oauth2_auth_codes ADD CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id);
ALTER TABLE wallabag_oauth2_auth_codes ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_A7AED006A76ED395 FOREIGN KEY (user_id) REFERENCES `wallabag_user` (id);
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES `wallabag_entry` (id);
SQL
                ;
                foreach (explode("\n", $sql) as $query) {
                    $this->addSql($query);
                }
                break;
            case 'postgresql':
                $sql = <<<'SQL'
CREATE TABLE wallabag_craue_config_setting (name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name));
CREATE UNIQUE INDEX UNIQ_5D9649505E237E06 ON wallabag_craue_config_setting (name);
CREATE TABLE "wallabag_entry" (id INT NOT NULL, user_id INT DEFAULT NULL, title TEXT DEFAULT NULL, url TEXT DEFAULT NULL, is_archived BOOLEAN NOT NULL, is_starred BOOLEAN NOT NULL, content TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, mimetype TEXT DEFAULT NULL, language TEXT DEFAULT NULL, reading_time INT DEFAULT NULL, domain_name TEXT DEFAULT NULL, preview_picture TEXT DEFAULT NULL, is_public BOOLEAN DEFAULT 'false', PRIMARY KEY(id));
CREATE INDEX IDX_F4D18282A76ED395 ON "wallabag_entry" (user_id);
CREATE TABLE wallabag_entry_tag (entry_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(entry_id, tag_id));
CREATE INDEX IDX_C9F0DD7CBA364942 ON wallabag_entry_tag (entry_id);
CREATE INDEX IDX_C9F0DD7CBAD26311 ON wallabag_entry_tag (tag_id);
CREATE TABLE "wallabag_config" (id INT NOT NULL, user_id INT DEFAULT NULL, theme VARCHAR(255) NOT NULL, items_per_page INT NOT NULL, language VARCHAR(255) NOT NULL, rss_token VARCHAR(255) DEFAULT NULL, rss_limit INT DEFAULT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON "wallabag_config" (user_id);
CREATE TABLE "wallabag_tagging_rule" (id INT NOT NULL, config_id INT DEFAULT NULL, rule VARCHAR(255) NOT NULL, tags TEXT NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_2D9B3C5424DB0683 ON "wallabag_tagging_rule" (config_id);
COMMENT ON COLUMN "wallabag_tagging_rule".tags IS '(DC2Type:simple_array)';
CREATE TABLE "wallabag_tag" (id INT NOT NULL, label TEXT NOT NULL, slug VARCHAR(128) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_4CA58A8C989D9B62 ON "wallabag_tag" (slug);
CREATE TABLE wallabag_oauth2_clients (id INT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris TEXT NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types TEXT NOT NULL, PRIMARY KEY(id));
COMMENT ON COLUMN wallabag_oauth2_clients.redirect_uris IS '(DC2Type:array)';
COMMENT ON COLUMN wallabag_oauth2_clients.allowed_grant_types IS '(DC2Type:array)';
CREATE TABLE wallabag_oauth2_access_tokens (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_368A42095F37A13B ON wallabag_oauth2_access_tokens (token);
CREATE INDEX IDX_368A420919EB6921 ON wallabag_oauth2_access_tokens (client_id);
CREATE INDEX IDX_368A4209A76ED395 ON wallabag_oauth2_access_tokens (user_id);
CREATE TABLE wallabag_oauth2_refresh_tokens (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_20C9FB245F37A13B ON wallabag_oauth2_refresh_tokens (token);
CREATE INDEX IDX_20C9FB2419EB6921 ON wallabag_oauth2_refresh_tokens (client_id);
CREATE INDEX IDX_20C9FB24A76ED395 ON wallabag_oauth2_refresh_tokens (user_id);
CREATE TABLE wallabag_oauth2_auth_codes (id INT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri TEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_EE52E3FA5F37A13B ON wallabag_oauth2_auth_codes (token);
CREATE INDEX IDX_EE52E3FA19EB6921 ON wallabag_oauth2_auth_codes (client_id);
CREATE INDEX IDX_EE52E3FAA76ED395 ON wallabag_oauth2_auth_codes (user_id);
CREATE TABLE "wallabag_user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expired BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles TEXT NOT NULL, credentials_expired BOOLEAN NOT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, authCode INT DEFAULT NULL, twoFactorAuthentication BOOLEAN NOT NULL, trusted TEXT DEFAULT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON "wallabag_user" (username_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON "wallabag_user" (email_canonical);
CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON "wallabag_user" (confirmation_token);
COMMENT ON COLUMN "wallabag_user".roles IS '(DC2Type:array)';
COMMENT ON COLUMN "wallabag_user".trusted IS '(DC2Type:json_array)';
CREATE TABLE wallabag_annotation (id INT NOT NULL, user_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, text TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, quote VARCHAR(255) NOT NULL, ranges TEXT NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_A7AED006A76ED395 ON wallabag_annotation (user_id);
CREATE INDEX IDX_A7AED006BA364942 ON wallabag_annotation (entry_id);
COMMENT ON COLUMN wallabag_annotation.ranges IS '(DC2Type:array)';
CREATE SEQUENCE "entry_id_seq" INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE "config_id_seq" INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE "tagging_rule_id_seq" INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE "tag_id_seq" INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE oauth2_clients_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE oauth2_access_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE oauth2_refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE oauth2_auth_codes_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE annotation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
ALTER TABLE "wallabag_entry" ADD CONSTRAINT FK_F4D18282A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_C9F0DD7CBA364942 FOREIGN KEY (entry_id) REFERENCES "wallabag_entry" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_entry_tag ADD CONSTRAINT FK_C9F0DD7CBAD26311 FOREIGN KEY (tag_id) REFERENCES "wallabag_tag" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE "wallabag_config" ADD CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE "wallabag_tagging_rule" ADD CONSTRAINT FK_2D9B3C5424DB0683 FOREIGN KEY (config_id) REFERENCES "wallabag_config" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_access_tokens ADD CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_access_tokens ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_refresh_tokens ADD CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_refresh_tokens ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_auth_codes ADD CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES wallabag_oauth2_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_oauth2_auth_codes ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_A7AED006A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE wallabag_annotation ADD CONSTRAINT FK_A7AED006BA364942 FOREIGN KEY (entry_id) REFERENCES "wallabag_entry" (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
SQL
                ;
                foreach (explode("\n", $sql) as $query) {
                    $this->addSql($query);
                }
                break;
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE wallabag_craue_config_setting');
        $this->addSql('DROP TABLE "wallabag_tagging_rule"');
        $this->addSql('DROP TABLE "wallabag_config"');
        $this->addSql('DROP TABLE "wallabag_entry"');
        $this->addSql('DROP TABLE wallabag_entry_tag');
        $this->addSql('DROP TABLE "wallabag_tag"');
        $this->addSql('DROP TABLE wallabag_oauth2_refresh_tokens');
        $this->addSql('DROP TABLE wallabag_oauth2_access_tokens');
        $this->addSql('DROP TABLE wallabag_oauth2_clients');
        $this->addSql('DROP TABLE wallabag_oauth2_auth_codes');
        $this->addSql('DROP TABLE "wallabag_user"');
        $this->addSql('DROP TABLE wallabag_annotation');
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
