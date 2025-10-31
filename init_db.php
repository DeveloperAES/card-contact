<?php
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
CREATE TABLE IF NOT EXISTS admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS compania (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    titulo TEXT,
    link TEXT,
    photo_url TEXT
);

CREATE TABLE IF NOT EXISTS persona (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    compania_id INTEGER,
    photo_url TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compania_id) REFERENCES compania(id)
);
");

# Inserta un usuario admin si no existe
$existe = $db->query("SELECT COUNT(*) FROM admin")->fetchColumn();
if (!$existe) {
    $db->exec("INSERT INTO admin (usuario, password) VALUES ('admin', '1234')");
}

echo "Base de datos creada correctamente con campos photo_url.";
