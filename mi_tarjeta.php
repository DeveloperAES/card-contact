<?php
session_start();
require 'includes/db.php';
require 'libs/qrlib.php';

if (!isset($_SESSION['persona_id'])) {
    header("Location: login_persona.php");
    exit;
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

$stmt = $db->prepare("SELECT * FROM persona WHERE id = ?");
$stmt->execute([$_SESSION['persona_id']]);
$persona = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$persona) {
    echo "Persona no encontrada.";
    exit;
}

// Detectar protocolo y dominio automáticamente
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

// Construir link público dinámico
$link_publico = "$protocolo://$host$basePath/persona.php?slug=" . urlencode($persona['slug']);

// Generar QR temporal
$qrTempDir = __DIR__ . '/tmp/';
if (!file_exists($qrTempDir)) mkdir($qrTempDir);
$qrFile = $qrTempDir . 'qr_' . $persona['id'] . '.png';
QRcode::png($link_publico, $qrFile, QR_ECLEVEL_L, 4);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mi Tarjeta Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            max-width: 450px;
            margin: 50px auto;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .photo {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
        }
    </style>
</head>
<body class="p-4">

    <div class="card p-4 text-center">
        <?php if (!empty($persona['photo_url'])): ?>
            <img src="<?= htmlspecialchars($persona['photo_url']) ?>" class="photo mb-3" alt="Foto de perfil">
        <?php endif; ?>

        <h3><?= htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']) ?></h3>
        <p class="text-muted mb-3">Tu tarjeta digital personal</p>

        <img src="tmp/<?= basename($qrFile) ?>" alt="QR de tu perfil" class="mb-3" width="150"><br>

        <p>
            <a href="<?= htmlspecialchars($link_publico) ?>" target="_blank">
                <?= htmlspecialchars($link_publico) ?>
            </a>
        </p>

        <button class="btn btn-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#datosPersona" aria-expanded="false" aria-controls="datosPersona">
            👤 Ver mis datos
        </button>

        <div class="collapse mt-3 text-start" id="datosPersona">
            <hr>
            <?php if (!empty($persona['cargo'])): ?>
                <p><strong>Cargo:</strong> <?= htmlspecialchars($persona['cargo']) ?></p>
            <?php endif; ?>

            <?php if (!empty($persona['compania_id'])): ?>
                <p><strong>Compañía ID:</strong> <?= htmlspecialchars($persona['compania_id']) ?></p>
            <?php endif; ?>

            <?php if (!empty($persona['email'])): ?>
                <p><strong>Correo:</strong> <a href="mailto:<?= htmlspecialchars($persona['email']) ?>"><?= htmlspecialchars($persona['email']) ?></a></p>
            <?php endif; ?>

            <?php if (!empty($persona['telefono'])): ?>
                <p><strong>Teléfono:</strong> <a href="tel:<?= htmlspecialchars($persona['telefono']) ?>"><?= htmlspecialchars($persona['telefono']) ?></a></p>
            <?php endif; ?>

            <?php if (!empty($persona['direccion'])): ?>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($persona['direccion']) ?></p>
            <?php endif; ?>
        </div>

        <a href="logout_persona.php" class="btn btn-outline-secondary mt-4">Cerrar sesión</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
