<?php

use Phinx\Migration\AbstractMigration;

class InitDatabase extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO config (name, value) VALUES ('foo', 'bar');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM config WHERE name = 'foo' AND value = 'bar';");
    }
}