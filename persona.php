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

ob_start();  // Empiezo a capturar el contenido

?>

<section class="person-section-page">
    <div class="container">
        <div class="person-card">
            <?php if ($persona['photo_url']): ?>
                <div class="box-image d-flex align-items-center justify-content-center">
                    <img src="<?= htmlspecialchars($persona['photo_url']) ?>" class="photo" alt="Foto de <?= htmlspecialchars($nombreCompleto) ?>">
                </div>
                <?php endif; ?>

                <?php if ($persona['compania_foto']): ?>
                    <div class="logo-empresa d-flex justify-content-center align-items-center">
                        <img src="<?= htmlspecialchars($persona['compania_foto']) ?>" alt="Logo de <?= htmlspecialchars($persona['compania_nombre']) ?>" style="max-width:100px;height:auto;">
                    </div>
                <?php endif; ?>


                <h3><?= htmlspecialchars($nombreCompleto) ?></h3>
                <?php if ($persona['cargo']): ?><p><strong>Cargo: </strong><?= htmlspecialchars($persona['cargo']) ?></p><?php endif; ?>
                <?php if ($persona['compania_nombre']): ?><p><strong>Empresa: </strong><?= htmlspecialchars($persona['compania_nombre']) ?></p><?php endif; ?>
                <?php if ($persona['email']): ?><p><strong>Correo: </strong><a href="mailto:<?= htmlspecialchars($persona['email']) ?>"><?= htmlspecialchars($persona['email']) ?></a></p><?php endif; ?>
                <?php if ($persona['telefono']): ?><p><strong>Celular: </strong><a href="tel:<?= htmlspecialchars($persona['telefono']) ?>"><?= htmlspecialchars($persona['telefono']) ?></a></p><?php endif; ?>
                <?php if ($persona['direccion']): ?><p><strong>Dirección: </strong><?= htmlspecialchars($persona['direccion']) ?></p><?php endif; ?>

                <div class="qr-container d-none">
                    <div id="qrcode"></div>
                </div>

                <button id="saveContact" class="btn btn-primary mt-3">Guardar contacto</button>
                </div>
        </div>

</section>



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


<?php
$contenido = ob_get_clean(); // Guarda el contenido en variable
$titulo = "Página personal"; //Es lo que va en ventana o pestaña del navegador
include 'templates/main.php'; // Inserta dentro del layout
