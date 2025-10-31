<?php
require 'includes/db.php';

// --- Guardar / Editar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Limpiar y preparar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $nombre . ' ' . $apellido), '-'));

    // --- Validaciones ---
    if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
    if (empty($dni)) $errors[] = "El DNI es obligatorio.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email no válido.";
    if (!empty($telefono) && !preg_match('/^\+?[0-9\s\-]+$/', $telefono)) $errors[] = "Teléfono no válido.";

    // Validar DNI único
    if (!empty($dni)) {
        if (!empty($_POST['id'])) {
            // UPDATE: excluir el registro actual
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE dni=? AND id<>?");
            $stmt->execute([$dni, $_POST['id']]);
        } else {
            // INSERT
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE dni=?");
            $stmt->execute([$dni]);
        }
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "El DNI ya existe en otra persona.";
        }
    }

    // Validar email único
    if (!empty($email)) {
        if (!empty($_POST['id'])) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE email=? AND id<>?");
            $stmt->execute([$email, $_POST['id']]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE email=?");
            $stmt->execute([$email]);
        }
        if ($stmt->fetchColumn() > 0) $errors[] = "El email ya existe en otra persona.";
    }

    // Validar teléfono único
    if (!empty($telefono)) {
        if (!empty($_POST['id'])) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE telefono=? AND id<>?");
            $stmt->execute([$telefono, $_POST['id']]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM persona WHERE telefono=?");
            $stmt->execute([$telefono]);
        }
        if ($stmt->fetchColumn() > 0) $errors[] = "El teléfono ya existe en otra persona.";
    }

    if (empty($errors)) {
        // Guardar o actualizar
        if (!empty($_POST['id'])) {
            $stmt = $db->prepare("
                UPDATE persona 
                SET dni=?, nombre=?, apellido=?, cargo=?, email=?, telefono=?, direccion=?, compania_id=?, photo_url=?, slug=? 
                WHERE id=?
            ");
            $stmt->execute([
                $dni,
                $nombre,
                $apellido,
                $_POST['cargo'],
                $email,
                $telefono,
                $_POST['direccion'],
                $_POST['compania_id'] ?: null,
                $_POST['photo_url'],
                $slug,
                $_POST['id']
            ]);
            $success = "Persona actualizada correctamente.";
        } else {
            $stmt = $db->prepare("
                INSERT INTO persona (dni, nombre, apellido, cargo, email, telefono, direccion, compania_id, photo_url, slug) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $dni,
                $nombre,
                $apellido,
                $_POST['cargo'],
                $email,
                $telefono,
                $_POST['direccion'],
                $_POST['compania_id'] ?: null,
                $_POST['photo_url'],
                $slug
            ]);
            $success = "Persona creada correctamente.";
        }
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

// Obtener todos los DNI, CORREO, Y TELEFONO para validación JS
$allDnis = $db->query("SELECT dni FROM persona")->fetchAll(PDO::FETCH_COLUMN);
$allEmails = $db->query("SELECT email FROM persona WHERE email IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$allTelefonos = $db->query("SELECT telefono FROM persona WHERE telefono IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>👤 Personas</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPersona" onclick="nuevaPersona()">➕ Nueva persona</button>
</div>

<!-- Mensajes -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Modal Persona -->
<div class="modal fade" id="modalPersona" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="personaForm" onsubmit="return validarFormulario()">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPersonaLabel">Nueva persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="personaId">
                    <div class="row g-3">
                        <div class="col-md-2"><input type="text" name="dni" id="dni" class="form-control" placeholder="DNI" required></div>
                        <div class="col-md-3"><input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre" required></div>
                        <div class="col-md-3"><input type="text" name="apellido" id="apellido" class="form-control" placeholder="Apellido"></div>
                        <div class="col-md-4"><input type="text" name="cargo" id="cargo" class="form-control" placeholder="Cargo"></div>
                        <div class="col-md-4"><input type="email" name="email" id="email" class="form-control" placeholder="Email"></div>
                        <div class="col-md-4"><input type="text" name="telefono" id="telefono" class="form-control" placeholder="Teléfono"></div>
                        <div class="col-md-4"><input type="text" name="direccion" id="direccion" class="form-control" placeholder="Dirección"></div>
                        <div class="col-md-6">
                            <select name="compania_id" id="compania_id" class="form-select">
                                <option value="">-- Compañía --</option>
                                <?php foreach ($companias as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><input type="url" name="photo_url" id="photo_url" class="form-control" placeholder="URL Foto"></div>
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

<script>
    const existingDnis = <?= json_encode($allDnis) ?>;
    const existingEmails = <?= json_encode($allEmails) ?>;
    const existingTelefonos = <?= json_encode($allTelefonos) ?>;

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

    function validarFormulario() {
        const dni = document.getElementById('dni').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        const currentId = document.getElementById('personaId').value;

        if (!dni) {
            alert("El DNI es obligatorio.");
            return false;
        }
        if (existingDnis.includes(dni) && !currentId) {
            alert("Este DNI ya existe.");
            return false;
        }

        if (email && existingEmails.includes(email) && !currentId) {
            alert("Este email ya existe.");
            return false;
        }
        if (email && !/^\S+@\S+\.\S+$/.test(email)) {
            alert("Email no válido.");
            return false;
        }

        if (telefono && existingTelefonos.includes(telefono) && !currentId) {
            alert("Este teléfono ya existe.");
            return false;
        }
        if (telefono && !/^\+?[0-9\s\-]+$/.test(telefono)) {
            alert("Teléfono no válido.");
            return false;
        }

        return true;
    }
</script>