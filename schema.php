<?php
/**
 * Schema of database
 *
 * @package poche
 * @subpackage schema
 * @license    http://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 */
namespace Schema;

/**
 * First version of database
 *
 * @param  pdo $pdo PDO instanciation
 */
function version_1($pdo)
{
    $pdo->exec("
        CREATE TABLE config (
            name TEXT,
            value TEXT
        )
    ");

    $pdo->exec("
        INSERT INTO config (name, value)
        VALUES ('api_token', '".\Model\generate_token()."')
    ");

    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            username TEXT DEFAULT 'poche',
            password TEXT,
            email TEXT,
            language TEXT DEFAULT 'en_US',
            items_per_page INTEGER DEFAULT 100,
            theme TEXT DEFAULT 'original',
            items_sorting_direction TEXT DEFAULT 'desc',
            api_token TEXT DEFAULT '".\Model\generate_token()."',
            feed_token TEXT DEFAULT '".\Model\generate_token()."',
            auth_google_token TEXT DEFAULT '',
            auth_mozilla_token TEXT DEFAULT ''
        )
    ");

    $pdo->exec("
        INSERT INTO users
        (password)
        VALUES ('".\password_hash('poche', PASSWORD_BCRYPT)."')
    ");

    $pdo->exec('
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

    $pdo->exec('CREATE INDEX idx_status ON entries(status)');

    $pdo->exec('
        CREATE TABLE tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            value TEXT
        )
    ');

    $pdo->exec('
        CREATE TABLE tags_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            entry_id INTEGER,
            tag_id INTEGER,
            FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE,
            FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ');

    $pdo->exec("
        CREATE TABLE plugin_options (
            name TEXT PRIMARY KEY,
            value TEXT
        )
    ");
}
