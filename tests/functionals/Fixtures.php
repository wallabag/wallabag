<?php

namespace Poche\Tests\Functionals;

class Fixtures
{
    public static function loadUsers($db) {

        $db->query("INSERT INTO users (id, username) values (1, 'poche_test');");
    }


    public static function loadEntries($db) {

        $db->query("INSERT INTO entries (id, url, title, content, status, user_id) values (1, 'http://deboutlesgens.com/blog/le-courage-de-vivre-consciemment/', 'Le courage de vivre consciemment','Test content', 'unread', 1);");
    }
}
