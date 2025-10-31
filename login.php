<?php
session_start();
require 'includes/db.php';

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
<h2>Login</h2>
<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
    <label>Usuario: <input type="text" name="usuario" required></label><br>
    <label>Contraseña: <input type="password" name="password" required></label><br>
    <button type="submit">Entrar</button>
</form>
