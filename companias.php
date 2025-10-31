<?php
require 'includes/auth.php';
require 'includes/db.php';

// Agregar / Editar compañía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        // Editar
        $stmt = $db->prepare("UPDATE compania SET nombre=?, titulo=?, link=?, photo_url=? WHERE id=?");
        $stmt->execute([$_POST['nombre'], $_POST['titulo'], $_POST['link'], $_POST['photo_url'], $_POST['id']]);
    } else {
        // Insertar
        $stmt = $db->prepare("INSERT INTO compania (nombre, titulo, link, photo_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nombre'], $_POST['titulo'], $_POST['link'], $_POST['photo_url']]);
    }
}

// Eliminar
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM compania WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// Obtener compañía para edición
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM compania WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$companias = $db->query("SELECT * FROM compania ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Compañías</h1>
<a href="panel.php">← Volver</a>

<h3><?= $edit ? "Editar compañía" : "Nueva compañía" ?></h3>
<form method="post">
    <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
    <?php endif; ?>
    Nombre: <input name="nombre" required value="<?= htmlspecialchars($edit['nombre'] ?? '') ?>"><br>
    Título: <input name="titulo" value="<?= htmlspecialchars($edit['titulo'] ?? '') ?>"><br>
    Link: <input name="link" value="<?= htmlspecialchars($edit['link'] ?? '') ?>"><br>
    Foto (URL): <input name="photo_url" value="<?= htmlspecialchars($edit['photo_url'] ?? '') ?>"><br>
    <button type="submit"><?= $edit ? "Actualizar" : "Guardar" ?></button>
</form>

<h3>Listado</h3>
<table border="1" cellpadding="5">
<tr><th>ID</th><th>Nombre</th><th>Foto</th><th>Link</th><th>Acciones</th></tr>
<?php foreach ($companias as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['nombre']) ?></td>
    <td>
        <?php if ($c['photo_url']): ?>
            <img src="<?= htmlspecialchars($c['photo_url']) ?>" width="50">
        <?php endif; ?>
    </td>
    <td><a href="<?= htmlspecialchars($c['link']) ?>" target="_blank">Visitar</a></td>
    <td>
        <a href="?edit=<?= $c['id'] ?>">Editar</a> |
        <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
