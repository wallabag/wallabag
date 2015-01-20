<?php

use Phinx\Migration\AbstractMigration;

class InitDatabase extends AbstractMigration
{    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `montest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DROP DATABASE montest;");
    }
}