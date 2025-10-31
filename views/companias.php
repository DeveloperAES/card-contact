<?php
require 'includes/db.php';

// --- Guardar / Editar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("
            UPDATE compania 
            SET nombre=?, titulo=?, link=?, photo_url=? 
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['titulo'],
            $_POST['link'],
            $_POST['photo_url'],
            $_POST['id']
        ]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO compania (nombre, titulo, link, photo_url)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['titulo'],
            $_POST['link'],
            $_POST['photo_url']
        ]);
    }
}

// --- Eliminar ---
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM compania WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

// --- Consultar todas ---
$companias = $db->query("SELECT * FROM compania ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🏢 Compañías</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCompania" onclick="nuevaCompania()">➕ Nueva compañía</button>
</div>

<!-- Modal Compañía -->
<div class="modal fade" id="modalCompania" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="companiaForm">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCompaniaLabel">Nueva compañía</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="companiaId">
          <div class="row g-3">
            <div class="col-md-6">
              <input name="nombre" id="nombre" class="form-control" placeholder="Nombre de la compañía" required>
            </div>
            <div class="col-md-6">
              <input name="titulo" id="titulo" class="form-control" placeholder="Título o descripción corta">
            </div>
            <div class="col-md-6">
              <input name="link" id="link" class="form-control" placeholder="Enlace (https://...)" >
            </div>
            <div class="col-md-6">
              <input name="photo_url" id="photo_url" class="form-control" placeholder="URL del logo">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Tabla de compañías -->
<div class="table-responsive">
  <table class="table table-striped table-bordered align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Logo</th>
        <th>Nombre</th>
        <th>Título</th>
        <th>Link</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($companias as $c): ?>
        <tr>
          <td class="text-center"><?= $c['id'] ?></td>
          <td class="text-center">
            <?php if ($c['photo_url']): ?>
              <img src="<?= htmlspecialchars($c['photo_url']) ?>" width="50" height="50" style="object-fit:contain;">
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td><?= htmlspecialchars($c['titulo']) ?></td>
          <td>
            <?php if (!empty($c['link'])): ?>
              <a href="<?= htmlspecialchars($c['link']) ?>" target="_blank"><?= htmlspecialchars($c['link']) ?></a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <button class="btn btn-sm btn-warning text-white" onclick='editarCompania(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Editar</button>
            <a class="btn btn-sm btn-danger" href="?page=companias&delete=<?= $c['id'] ?>" onclick="return confirm('¿Eliminar esta compañía?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
function nuevaCompania() {
    document.getElementById('companiaForm').reset();
    document.getElementById('companiaId').value = '';
    document.getElementById('modalCompaniaLabel').textContent = 'Nueva compañía';
}

function editarCompania(data) {
    document.getElementById('companiaForm').reset();
    for (const key in data) {
        if (document.getElementById(key)) {
            document.getElementById(key).value = data[key] || '';
        }
    }
    document.getElementById('companiaId').value = data.id;
    document.getElementById('modalCompaniaLabel').textContent = 'Editar compañía';
    new bootstrap.Modal(document.getElementById('modalCompania')).show();
}
</script>
