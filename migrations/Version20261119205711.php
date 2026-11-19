<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261119205711 extends WallabagMigration
{
    public function getDescription(): string
    {
        return 'Create feed subscriptions';
    }

    public function up(Schema $schema): void
    {
        $table = $this->getTable('feed_url');
        $user = $this->getTable('user');
        $index = $this->getIndexName('feed_url', 'user_id');
        $foreignKey = $this->getForeignKeyName('feed_url', 'user_id');

        switch (true) {
            case $this->platform instanceof MySQLPlatform:
                $this->addSql("CREATE TABLE $table (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title LONGTEXT NOT NULL, url LONGTEXT NOT NULL, INDEX $index (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
                $this->addSql("ALTER TABLE $table ADD CONSTRAINT $foreignKey FOREIGN KEY (user_id) REFERENCES $user (id) ON DELETE CASCADE");
                break;
            case $this->platform instanceof PostgreSQLPlatform:
                $this->addSql('CREATE SEQUENCE feed_url_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
                $this->addSql("CREATE TABLE $table (id INT NOT NULL, user_id INT NOT NULL, title TEXT NOT NULL, url TEXT NOT NULL, PRIMARY KEY(id))");
                $this->addSql("CREATE INDEX $index ON $table (user_id)");
                $this->addSql("ALTER TABLE $table ADD CONSTRAINT $foreignKey FOREIGN KEY (user_id) REFERENCES $user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
                break;
            case $this->platform instanceof SqlitePlatform:
                $this->addSql("CREATE TABLE $table (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, title CLOB NOT NULL, url CLOB NOT NULL, CONSTRAINT $foreignKey FOREIGN KEY (user_id) REFERENCES $user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)");
                $this->addSql("CREATE INDEX $index ON $table (user_id)");
                break;
            default:
                $this->abortIf(true, 'Unsupported database platform.');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ' . $this->getTable('feed_url'));

        switch (true) {
            case $this->platform instanceof PostgreSQLPlatform:
                $this->addSql('DROP SEQUENCE feed_url_id_seq');
                break;
            case $this->platform instanceof MySQLPlatform:
            case $this->platform instanceof SqlitePlatform:
                break;
            default:
                $this->abortIf(true, 'Unsupported database platform.');
        }
    }
}
