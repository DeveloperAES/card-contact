<?php
require 'includes/db.php';

$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare("
    SELECT 
        p.*, 
        c.nombre AS compania_nombre, 
        c.link, 
        c.titulo, 
        c.photo_url AS compania_foto
    FROM persona p
    LEFT JOIN compania c ON p.compania_id = c.id
    WHERE p.slug = ?
");
$stmt->execute([$slug]);
$persona = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$persona) {
    echo "Persona no encontrada.";
    exit;
}

$nombreCompleto = trim($persona['nombre'] . ' ' . $persona['apellido']);


$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$link_publico = "$protocolo://$host$basePath/persona.php?slug=" . urlencode($persona['slug']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($nombreCompleto) ?> | Tarjeta Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
        }

        .card {
            max-width: 450px;
            margin: 40px auto;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background: white;
        }

        .qr-container {
            margin-top: 15px;
            text-align: center;
        }

        img.photo {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="card text-center">
        <?php if ($persona['photo_url']): ?>
            <img src="<?= htmlspecialchars($persona['photo_url']) ?>" class="photo" alt="Foto de <?= htmlspecialchars($nombreCompleto) ?>">
        <?php endif; ?>

        <?php if ($persona['compania_foto']): ?>
            <div style="margin-top:-10px;margin-bottom:15px;">
                <img src="<?= htmlspecialchars($persona['compania_foto']) ?>" alt="Logo de <?= htmlspecialchars($persona['compania_nombre']) ?>" style="max-width:100px;height:auto;">
            </div>
        <?php endif; ?>


        <h3><?= htmlspecialchars($nombreCompleto) ?></h3>
        <?php if ($persona['cargo']): ?><p><strong><?= htmlspecialchars($persona['cargo']) ?></strong></p><?php endif; ?>
        <?php if ($persona['compania_nombre']): ?><p><?= htmlspecialchars($persona['compania_nombre']) ?></p><?php endif; ?>
        <?php if ($persona['email']): ?><p>📧 <a href="mailto:<?= htmlspecialchars($persona['email']) ?>"><?= htmlspecialchars($persona['email']) ?></a></p><?php endif; ?>
        <?php if ($persona['telefono']): ?><p>📞 <a href="tel:<?= htmlspecialchars($persona['telefono']) ?>"><?= htmlspecialchars($persona['telefono']) ?></a></p><?php endif; ?>
        <?php if ($persona['direccion']): ?><p>📍 <?= htmlspecialchars($persona['direccion']) ?></p><?php endif; ?>

        <div class="qr-container">
            <div id="qrcode"></div>
        </div>

        <button id="saveContact" class="btn btn-primary mt-3">💾 Guardar contacto</button>
    </div>

    <script>
        // Generar el QR con el link público
        new QRCode(document.getElementById("qrcode"), {
            text: <?= json_encode($link_publico) ?>,
            width: 150,
            height: 150
        });

        // Guardar contacto como archivo vCard (.vcf)
        document.getElementById("saveContact").addEventListener("click", function() {
            const vcard = `BEGIN:VCARD
VERSION:3.0
N:<?= addslashes($persona['apellido'] ?? '') ?>;<?= addslashes($persona['nombre'] ?? '') ?>

FN:<?= addslashes($nombreCompleto) ?>

ORG:<?= addslashes($persona['compania_nombre'] ?? '') ?>

TITLE:<?= addslashes($persona['cargo'] ?? '') ?>

EMAIL:<?= addslashes($persona['email'] ?? '') ?>

TEL:<?= addslashes($persona['telefono'] ?? '') ?>

ADR:;;<?= addslashes($persona['direccion'] ?? '') ?>;;;
URL:<?= addslashes($link_publico) ?>

END:VCARD`;

            const blob = new Blob([vcard], {
                type: 'text/vcard'
            });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = "<?= strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombreCompleto)) ?>.vcf";
            a.click();
        });
    </script>

</body>

</html>