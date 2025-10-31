<?php
require 'includes/db.php';

// --- Guardar / Editar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCompleto = trim($_POST['nombre'] . ' ' . $_POST['apellido']);
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $nombreCompleto), '-'));

    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("
            UPDATE persona 
            SET dni=?, nombre=?, apellido=?, cargo=?, email=?, telefono=?, direccion=?, compania_id=?, photo_url=?, slug=? 
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['dni'],
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['cargo'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['direccion'],
            $_POST['compania_id'] ?: null,
            $_POST['photo_url'],
            $slug,
            $_POST['id']
        ]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO persona (dni, nombre, apellido, cargo, email, telefono, direccion, compania_id, photo_url, slug) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['dni'],
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['cargo'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['direccion'],
            $_POST['compania_id'] ?: null,
            $_POST['photo_url'],
            $slug
        ]);
    }
}

// --- Eliminar ---
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM persona WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

// --- Consultas ---
$companias = $db->query("SELECT * FROM compania ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$personas = $db->query("
    SELECT p.*, c.nombre AS compania
    FROM persona p
    LEFT JOIN compania c ON p.compania_id = c.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>👤 Personas</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPersona" onclick="nuevaPersona()">➕ Nueva persona</button>
</div>

<!-- Modal Persona -->
<div class="modal fade" id="modalPersona" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="personaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPersonaLabel">Nueva persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="personaId">
                    <div class="row g-3">
                        <div class="col-md-2"><input name="dni" id="dni" class="form-control" placeholder="DNI"></div>
                        <div class="col-md-3"><input name="nombre" id="nombre" class="form-control" placeholder="Nombre" required></div>
                        <div class="col-md-3"><input name="apellido" id="apellido" class="form-control" placeholder="Apellido"></div>
                        <div class="col-md-4"><input name="cargo" id="cargo" class="form-control" placeholder="Cargo"></div>
                        <div class="col-md-4"><input name="email" id="email" class="form-control" placeholder="Email"></div>
                        <div class="col-md-4"><input name="telefono" id="telefono" class="form-control" placeholder="Teléfono"></div>
                        <div class="col-md-4"><input name="direccion" id="direccion" class="form-control" placeholder="Dirección"></div>
                        <div class="col-md-6">
                            <select name="compania_id" id="compania_id" class="form-select">
                                <option value="">-- Compañía --</option>
                                <?php foreach ($companias as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><input name="photo_url" id="photo_url" class="form-control" placeholder="URL Foto"></div>
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

<!-- Tabla -->
<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Compañía</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Slug</th>
                <th>Ver</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($personas as $p):
                $nombreCompleto = trim($p['nombre'] . ' ' . $p['apellido']);
            ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td class="text-center"><?php if ($p['photo_url']): ?><img src="<?= htmlspecialchars($p['photo_url']) ?>" width="50" height="50" style="border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                    <td><?= htmlspecialchars($nombreCompleto) ?></td>
                    <td><?= htmlspecialchars($p['cargo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['compania'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['slug']) ?></td>
                    <td class="text-center"><a class="btn btn-sm btn-info text-white" href="persona.php?slug=<?= urlencode($p['slug']) ?>" target="_blank">Ver</a></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning text-white" onclick='editarPersona(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Editar</button>
                        <a class="btn btn-sm btn-danger" href="?page=personas&delete=<?= $p['id'] ?>" onclick="return confirm('¿Eliminar esta persona?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



</script>

<script>
    function nuevaPersona() {
        document.getElementById('personaForm').reset();
        document.getElementById('personaId').value = '';
        document.getElementById('modalPersonaLabel').textContent = 'Nueva persona';
    }

    function editarPersona(data) {
        document.getElementById('personaForm').reset();
        for (const key in data) {
            if (document.getElementById(key)) {
                document.getElementById(key).value = data[key] || '';
            }
        }
        document.getElementById('personaId').value = data.id;
        document.getElementById('modalPersonaLabel').textContent = 'Editar persona';
        new bootstrap.Modal(document.getElementById('modalPersona')).show();
    }
</script>