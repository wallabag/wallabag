<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class db {
    var $handle;
    function __construct($path) 
    {
    	$this->handle->exec('CREATE TABLE IF NOT EXISTS "entries" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL  UNIQUE , "title" VARCHAR, "url" VARCHAR UNIQUE , "is_read" INTEGER DEFAULT 0, "is_fav" INTEGER DEFAULT 0, "content" BLOB)');
        $this->handle = new PDO($path);
        $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getHandle() 
    {
        return $this->handle;
    }
}