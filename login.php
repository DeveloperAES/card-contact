<?php
session_start();
require 'includes/db.php';
ob_start();  // Empiezo a capturar el contenido

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM admin WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && $password === $admin['password']) {
        $_SESSION['admin'] = true;
        header("Location: panel.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<section class="login-main-section bg-dark">
    <div class="container">
        <h2 class="text-white">Inicie sesión</h2>
        <div class="box-image">
            <img src="assets/images/login.webp" class="img-fluid rounded-2" alt ="Login Image">    
        </div>

        <form method="post" class="login-form">
            <div class="input-group">
                <input type="text" name="usuario" placeholder="Usuario" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>
</section>




<?php
$contenido = ob_get_clean(); // Guarda el contenido en variable
$titulo = "Login"; //Es lo que va en ventana o pestaña del navegador
include 'templates/main.php'; // Inserta dentro del layout
