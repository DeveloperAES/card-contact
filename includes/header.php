<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CMS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap 5 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: #ccc;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #495057;
            color: #fff;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 class="text-center">Admin CMS</h4>
        <a href="panel.php?page=personas" class="<?= ($_GET['page'] ?? '') == 'personas' ? 'active' : '' ?>">👤 Personas</a>
        <a href="panel.php?page=companias" class="<?= ($_GET['page'] ?? '') == 'companias' ? 'active' : '' ?>">🏢 Compañías</a>
        <a href="logout.php">🚪 Cerrar sesión</a>
    </div>
    <div class="content">