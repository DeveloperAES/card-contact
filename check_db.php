<?php
$db = new PDO('sqlite:database.db');
$personaCols = $db->query("PRAGMA table_info(persona)")->fetchAll(PDO::FETCH_ASSOC);
$companiaCols = $db->query("PRAGMA table_info(compania)")->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>PERSONA:\n";
print_r($personaCols);
echo "\nCOMPANIA:\n";
print_r($companiaCols);
