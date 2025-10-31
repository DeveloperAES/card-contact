<?php
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- Helper para revisar columnas ---
function hasColumn($db, $table, $column) {
    $cols = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
    return in_array($column, array_column($cols, 'name'));
}

// --- Agregar columnas si no existen ---
$columnsToAdd = [
    'persona' => ['photo_url', 'dni', 'apellido', 'cargo', 'direccion', 'telefono'],
    'compania' => ['photo_url']
];

foreach ($columnsToAdd as $table => $cols) {
    foreach ($cols as $col) {
        if (!hasColumn($db, $table, $col)) {
            $db->exec("ALTER TABLE $table ADD COLUMN $col TEXT;");
            echo "✅ Columna $col agregada a $table.<br>";
        }
    }
}

// --- Crear índice único en persona(dni) para evitar duplicados ---
$existingIndex = $db->query("PRAGMA index_list('persona')")->fetchAll(PDO::FETCH_ASSOC);
$indexNames = array_column($existingIndex, 'name');

if (!in_array('idx_persona_dni', $indexNames)) {
    try {
        $db->exec("CREATE UNIQUE INDEX idx_persona_dni ON persona(dni);");
        echo "✅ Índice único creado en persona(dni).<br>";
    } catch (Exception $e) {
        echo "⚠️ No se pudo crear índice único en persona(dni): " . $e->getMessage() . "<br>";
    }
}

echo "🚀 Migración completa.";
