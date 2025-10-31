<?php
require 'includes/auth.php';
include 'includes/header.php';

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'personas':
        include 'views/personas.php';
        break;
    case 'companias':
        include 'views/companias.php';
        break;
    default:
        echo "<h2>Bienvenido al panel de administración</h2>
              <p>Selecciona una opción en el menú lateral.</p>";
}

echo "</div></body></html>";
