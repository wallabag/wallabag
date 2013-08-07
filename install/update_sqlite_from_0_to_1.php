<?php

$db_path = 'sqlite:../db/poche.sqlite';
$handle = new PDO($db_path);
$handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

# Requêtes à exécuter pour mettre à jour poche.sqlite en 1.x

# ajout d'un champ user_id sur la table entries
$sql = 'ALTER TABLE entries RENAME TO tempEntries;';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'CREATE TABLE entries (id INTEGER PRIMARY KEY, title TEXT, url TEXT, is_read NUMERIC DEFAULT 0, is_fav NUMERIC DEFAULT 0, content BLOB, user_id NUMERIC);';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'INSERT INTO entries (id, title, url, is_read, is_fav, content) SELECT id, title, url, is_read, is_fav, content FROM tempEntries;';
$query = $handle->prepare($sql);
$query->execute();

# Update tout pour mettre user_id = 1
$sql = 'UPDATE entries SET user_id = 1;';
$query = $handle->prepare($sql);
$query->execute();

# Changement des flags pour les lus / favoris
$sql = 'UPDATE entries SET is_read = 1 WHERE is_read = -1;';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'UPDATE entries SET is_fav = 1 WHERE is_fav = -1;';
$query = $handle->prepare($sql);
$query->execute();

# Création de la table users
$sql = 'CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password TEXT, name TEXT, email TEXT);';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'INSERT INTO users (username) SELECT value FROM config WHERE name = "login";';
$query = $handle->prepare($sql);
$query->execute();

$sql = "UPDATE users SET password = (SELECT value FROM config WHERE name = 'password')";
$query = $handle->prepare($sql);
$query->execute();

# Création de la table users_config
$sql = 'CREATE TABLE users_config (id INTEGER PRIMARY KEY, user_id NUMERIC, name TEXT, value TEXT);';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'INSERT INTO users_config (user_id, name, value) VALUES (1, "pager", "10");';
$query = $handle->prepare($sql);
$query->execute();

$sql = 'INSERT INTO users_config (user_id, name, value) VALUES (1, "language", "en_EN.UTF8");';
$query = $handle->prepare($sql);
$query->execute();

# Suppression de la table temporaire
$sql = 'DROP TABLE tempEntries;';
$query = $handle->prepare($sql);
$query->execute();

# Vidage de la table de config
$sql = 'DELETE FROM config;';
$query = $handle->prepare($sql);
$query->execute();

echo 'welcome to poche 1.0 !';