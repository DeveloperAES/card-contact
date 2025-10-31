<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);

    $stmt = $db->prepare("SELECT * FROM persona WHERE dni = ?");
    $stmt->execute([$dni]);
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($persona) {
        $_SESSION['persona_id'] = $persona['id'];
        header("Location: mi_tarjeta.php");
        exit;
    } else {
        $error = "DNI no encontrado.";
    }
}


ob_start();  // Empiezo a capturar el contenido
?>

<section class="register-login-dni">
    <div class="container">
        <h2 class="mb-3 text-center">Ingresar con tu DNI</h2>
        <div class="box-image">
            <img src="assets/images/login.webp" class="img-fluid rounded-2" alt="Login Image">
        </div>

        <form method="post" class="login-form" style="max-width:450px;">

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="input-group">
                <input name="dni"  placeholder="DNI" required minlength="8" maxlength="10">
            </div>

            <button class="btn btn-login">Ingresar</button>
        </form>
    </div>

</section>



<?php
$contenido = ob_get_clean(); // Guarda el contenido en variable
$titulo = "Login con DNI"; //Es lo que va en ventana o pestaña del navegador
include 'templates/main.php'; // Inserta dentro del layout