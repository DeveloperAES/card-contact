<?php
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verifica y agrega columna photo_url a persona
$cols = $db->query("PRAGMA table_info(persona)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');

if (!in_array('photo_url', $colNames)) {
    $db->exec("ALTER TABLE persona ADD COLUMN photo_url TEXT;");
    echo "✅ Columna photo_url agregada a persona.<br>";
}

// Verifica y agrega columna photo_url a compania
$cols = $db->query("PRAGMA table_info(compania)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');

if (!in_array('photo_url', $colNames)) {
    $db->exec("ALTER TABLE compania ADD COLUMN photo_url TEXT;");
    echo "✅ Columna photo_url agregada a compania.<br>";
}


// Persona: agregar dni si no existe
$cols = $db->query("PRAGMA table_info(persona)")->fetchAll(PDO::FETCH_ASSOC);
if (!in_array('dni', array_column($cols, 'name'))) {
    $db->exec("ALTER TABLE persona ADD COLUMN dni TEXT;");
    echo "✅ Columna dni agregada a persona.<br>";
}


// Persona: nuevos campos
$cols = $db->query("PRAGMA table_info(persona)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');

if (!in_array('apellido', $colNames)) {
    $db->exec("ALTER TABLE persona ADD COLUMN apellido TEXT;");
    echo "✅ Columna apellido agregada.<br>";
}
if (!in_array('cargo', $colNames)) {
    $db->exec("ALTER TABLE persona ADD COLUMN cargo TEXT;");
    echo "✅ Columna cargo agregada.<br>";
}
if (!in_array('direccion', $colNames)) {
    $db->exec("ALTER TABLE persona ADD COLUMN direccion TEXT;");
    echo "✅ Columna direccion agregada.<br>";
}
if (!in_array('telefono', $colNames)) {
    $db->exec("ALTER TABLE persona ADD COLUMN telefono TEXT;");
    echo "✅ Columna telefono agregada.<br>";
}


echo "🚀 Migración completa.";
