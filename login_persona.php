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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acceso Persona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh">
    <form method="post" class="card p-4 shadow" style="width:320px;">
        <h4 class="mb-3 text-center">Ingresar con DNI</h4>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <input name="dni" class="form-control mb-3" placeholder="DNI" required>
        <button class="btn btn-primary w-100">Ingresar</button>
    </form>
</body>
</html>
