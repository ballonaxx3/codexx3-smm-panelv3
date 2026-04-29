<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();

$db = new Database();
$user = current_user($db);
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $apiUrl = trim($_POST['api_url'] ?? '');
    $apiKey = trim($_POST['api_key'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if ($action === 'save_provider') {
        if ($name === '' || $apiUrl === '' || $apiKey === '') {
            $error = 'Nombre, API URL y API Key son obligatorios.';
        } elseif (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            $error = 'La API URL no es válida.';
        } else {
            if ($id > 0) {
                $ok = $db->query('UPDATE providers SET name=?, api_url=?, api_key=?, active=? WHERE id=?', [$name, $apiUrl, $apiKey, $active, $id]);
                $msg = $ok ? 'Proveedor actualizado.' : 'No se pudo actualizar el proveedor.';
            } else {
                $ok = $db->query('INSERT INTO providers (name, api_url, api_key, active) VALUES (?,?,?,?)', [$name, $apiUrl, $apiKey, $active]);
                $msg = $ok ? 'Proveedor creado.' : 'No se pudo crear el proveedor.';
            }
        }
    }

    if ($action === 'toggle_provider' && $id > 0) {
        $db->query('UPDATE providers SET active = IF(active=1,0,1) WHERE id=?', [$id]);
        $msg = 'Estado del proveedor actualizado.';
    }
}

$providers = db_all($db, 'SELECT * FROM providers ORDER BY id DESC');
$editId = (int)($_GET['edit'] ?? 0);
$edit = $editId ? db_one($db, 'SELECT * FROM providers WHERE id=?', [$editId]) : [];

panel_header('Admin Providers','admin',$user);
?>
<div class="topbar"><div><h1>Providers API</h1><p class="muted">Gestiona proveedores externos /api/v2 desde el panel.</p></div><a class="btn secondary" href="/admin.php">Volver admin</a></div>
<?php if($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<section class="main-card form-card"><h2><?= $edit ? 'Editar proveedor' : 'Agregar proveedor' ?></h2><form method="post" class="stack-form"><input type="hidden" name="action" value="save_provider"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"><label>Nombre</label><input name="name" value="<?= e($edit['name'] ?? '') ?>" placeholder="Ej: SMMGrandpa" required><label>API URL</label><input name="api_url" value="<?= e($edit['api_url'] ?? '') ?>" placeholder="https://proveedor.com/api/v2" required><label>API Key</label><input name="api_key" value="<?= e($edit['api_key'] ?? '') ?>" placeholder="Tu API key" required><label class="check-row"><input type="checkbox" name="active" <?= (($edit['active'] ?? 1)==1)?'checked':'' ?>> Activo</label><button class="btn primary" type="submit">Guardar proveedor</button></form></section>
<section class="main-card table-card"><h2>Proveedores</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Nombre</th><th>API URL</th><th>Activo</th><th>Acciones</th></tr></thead><tbody><?php if(!$providers): ?><tr><td colspan="5" class="muted">No hay proveedores.</td></tr><?php endif; ?><?php foreach($providers as $p): ?><tr><td>#<?= (int)$p['id'] ?></td><td><?= e($p['name']) ?></td><td><?= e($p['api_url']) ?></td><td><span class="badge <?= ((int)$p['active']===1)?'completed':'failed' ?>"><?= ((int)$p['active']===1)?'activo':'inactivo' ?></span></td><td class="actions-cell"><a class="btn mini secondary" href="/admin_providers.php?edit=<?= (int)$p['id'] ?>">Editar</a><form method="post" class="inline-form"><input type="hidden" name="action" value="toggle_provider"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn mini danger" type="submit">Cambiar estado</button></form></td></tr><?php endforeach; ?></tbody></table></div></section><?php panel_footer(); ?>