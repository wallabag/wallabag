<?php
namespace Wallabag;

use Wallabag\Util\Token;

class Schema
{

    public static function dropTables($db) {
        $db->query("DROP TABLE IF EXISTS config");
        $db->query("DROP TABLE IF EXISTS users");
        $db->query("DROP TABLE IF EXISTS entries");
        $db->query("DROP TABLE IF EXISTS tags");
        $db->query("DROP TABLE IF EXISTS tags_entries");
        $db->query("DROP TABLE IF EXISTS plugin_options");
    }

    public static function createTables($db) {

        $db->query("
            CREATE TABLE config (
                name TEXT,
                value TEXT
            )
        ");

        $db->query("
            INSERT INTO config (name, value)
            VALUES ('api_token', '". Token::generateToken()."')
        ");



        $db->query("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                username TEXT DEFAULT 'wallabag',
                password TEXT,
                roles TEXT DEFAULT '',
                email TEXT,
                language TEXT DEFAULT 'en_US',
                items_per_page INTEGER DEFAULT 100,
                theme TEXT DEFAULT 'original',
                items_sorting_direction TEXT DEFAULT 'desc',
                api_token TEXT DEFAULT '". Token::generateToken()."',
                feed_token TEXT DEFAULT '".Token::generateToken()."',
                auth_google_token TEXT DEFAULT '',
                auth_mozilla_token TEXT DEFAULT ''
            )
        ");

        $db->query("
           INSERT INTO users
           (password, roles)
           VALUES ('BFEQkknI/c+Nd7BaG7AaiyTfUFby/pkMHy3UsYqKqDcmvHoPRX/ame9TnVuOV2GrBH0JK9g4koW+CgTYI9mK+w==', 'ROLE_USER')
        ");

        $db->query('
            CREATE TABLE entries (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                url TEXT,
                title TEXT,
                content TEXT,
                updated INTEGER,
                status TEXT,
                bookmark INTEGER DEFAULT 0,
                fetched INTEGER DEFAULT 1,
                user_id INTEGER,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');

        $db->query('CREATE INDEX idx_status ON entries(status)');

        $db->query('
            CREATE TABLE tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                value TEXT
            )
        ');

        $db->query('
            CREATE TABLE tags_entries (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
                entry_id INTEGER,
                tag_id INTEGER,
                FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE,
                FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE
            )
        ');

        $db->query("
            CREATE TABLE plugin_options (
                name TEXT PRIMARY KEY,
                value TEXT
            )
        ");
    }
}
