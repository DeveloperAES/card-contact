<?php
session_start();
require 'includes/db.php';
require 'libs/qrlib.php';

if (!isset($_SESSION['persona_id'])) {
    header("Location: login_persona.php");
    exit;
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

$stmt = $db->prepare("
    SELECT p.*, c.nombre AS compania_nombre
    FROM persona p
    LEFT JOIN compania c ON p.compania_id = c.id
    WHERE p.id = ?
");
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


ob_start();  // Empiezo a capturar el contenido
?>

<section class="tarjeta-presentacion">
    <div class="container">
        <div class="card-body  w-100 d-flex flex-column align-items-center text-center p-4 shadow">
            <?php if (!empty($persona['photo_url'])): ?>
                <div class="box-image d-flex align-items-center justify-content-center">

                    <img src="<?= htmlspecialchars($persona['photo_url']) ?>" class="img-fluid rounded-circle" alt="Foto de perfil">
                </div>
            <?php endif; ?>

            <h3><?= htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']) ?></h3>
            <p>Tu tarjeta digital personal</p>
            <h4><?= htmlspecialchars($persona['cargo']) ?></h4>

            <div class="w-100 qr-container">
                <img src="tmp/<?= basename($qrFile) ?>" alt="QR de tu perfil" class="img-fluid" >

            </div>

            <p class="d-none">
                <a href="<?= htmlspecialchars($link_publico) ?>" target="_blank">
                    <?= htmlspecialchars($link_publico) ?>
                </a>
            </p>

            <button class="btn btn-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#datosPersona" aria-expanded="false" aria-controls="datosPersona">
                Ver mis datos
            </button>

            <div class="collapse mt-3 text-start" id="datosPersona">
                <hr>
                <?php if (!empty($persona['cargo'])): ?>
                    <p><strong>Cargo:</strong> <?= htmlspecialchars($persona['cargo']) ?></p>
                <?php endif; ?>

                <?php if (!empty($persona['compania_nombre'])): ?>
                    <p><strong>Empresa:</strong> <?= htmlspecialchars($persona['compania_nombre']) ?></p>
                <?php endif; ?>

                <?php if (!empty($persona['email'])): ?>
                    <p><strong>Correo:</strong> <a href="mailto:<?= htmlspecialchars($persona['email']) ?>"><?= htmlspecialchars($persona['email']) ?></a></p>
                <?php endif; ?>

                <?php if (!empty($persona['telefono'])):
                    // Asegurarse de que el número tenga solo dígitos
                    $telefono = preg_replace('/\D/', '', $persona['telefono']);
                    $telefonoConPrefijo = '+51' . $telefono; // agregar prefijo internacional
                ?>
                    <p><strong>Teléfono:</strong>
                        <a href="tel:<?= htmlspecialchars($telefonoConPrefijo) ?>">
                            <?= htmlspecialchars($persona['telefono']) ?>
                        </a>
                    </p>
                <?php endif; ?>


                <?php if (!empty($persona['direccion'])): ?>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($persona['direccion']) ?></p>
                <?php endif; ?>
            </div>

            <a href="logout_persona.php" class="btn close-sesion">Cerrar sesión</a>
        </div>
    </div>

</section>




<?php
$contenido = ob_get_clean(); // Guarda el contenido en variable
$titulo = "Tarjeta de presentación"; //Es lo que va en ventana o pestaña del navegador
include 'templates/main.php'; // Inserta dentro del layout