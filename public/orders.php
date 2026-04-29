<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();

$db = new Database();
$user = current_user($db);
$userId = (int)($user['id'] ?? $_SESSION['user_id']);
$status = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = 'WHERE o.user_id=?';
$params = [$userId];

if ($status !== '') {
    $where .= ' AND o.status=?';
    $params[] = $status;
}

if ($search !== '') {
    $where .= ' AND (o.link LIKE ? OR s.name LIKE ? OR o.provider_order_id LIKE ?)';
    $like = '%'.$search.'%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$orders = db_all($db, "SELECT o.*,s.name service_name,s.category FROM orders o LEFT JOIN services s ON s.id=o.service_id {$where} ORDER BY o.id DESC LIMIT 100", $params);
$totalOrders = db_one($db, 'SELECT COUNT(*) c FROM orders WHERE user_id=?', [$userId])['c'] ?? 0;
$totalSpent = db_one($db, 'SELECT COALESCE(SUM(charge),0) total FROM orders WHERE user_id=?', [$userId])['total'] ?? 0;
$processing = db_one($db, "SELECT COUNT(*) c FROM orders WHERE user_id=? AND status IN ('pending','processing','inprogress')", [$userId])['c'] ?? 0;
$completed = db_one($db, "SELECT COUNT(*) c FROM orders WHERE user_id=? AND status='completed'", [$userId])['c'] ?? 0;

panel_header('Mis pedidos','orders',$user);
?>

<div class="topbar">
    <div>
        <h1>Mis pedidos</h1>
        <p class="muted">Historial completo de órdenes y estados</p>
    </div>
    <a class="btn primary" href="/order.php">Nuevo pedido</a>
</div>

<div class="stats">
    <div class="stat-card"><span>Total pedidos</span><strong><?= (int)$totalOrders ?></strong></div>
    <div class="stat-card"><span>Gastado</span><strong><?= money($totalSpent) ?></strong></div>
    <div class="stat-card"><span>En proceso</span><strong><?= (int)$processing ?></strong></div>
    <div class="stat-card"><span>Completados</span><strong><?= (int)$completed ?></strong></div>
</div>

<section class="main-card table-card">
    <div class="section-head">
        <div>
            <h2>Historial</h2>
            <p class="muted">Busca por link, servicio o ID proveedor.</p>
        </div>
    </div>

    <form method="get" class="filters-row">
        <input name="q" value="<?= e($search) ?>" placeholder="Buscar pedido...">
        <select name="status">
            <option value="">Todos los estados</option>
            <?php foreach(['pending','processing','inprogress','completed','partial','canceled','failed'] as $st): ?>
                <option value="<?= e($st) ?>" <?= $status===$st?'selected':'' ?>><?= e($st) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="submit">Filtrar</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Servicio</th><th>Link</th><th>Cantidad</th><th>Cobro</th><th>Estado</th><th>ID proveedor</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php if(!$orders): ?>
                <tr><td colspan="8" class="muted">Aún no hay pedidos.</td></tr>
            <?php endif; ?>
            <?php foreach($orders as $o): ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><strong><?= e($o['service_name'] ?? 'Servicio') ?></strong><br><span class="muted"><?= e($o['category'] ?? 'General') ?></span></td>
                    <td class="link-cell"><a href="<?= e($o['link'] ?? '#') ?>" target="_blank" rel="noopener"><?= e($o['link'] ?? '') ?></a></td>
                    <td><?= (int)($o['quantity'] ?? 0) ?></td>
                    <td><?= money($o['charge'] ?? 0) ?></td>
                    <td><span class="badge <?= e($o['status'] ?? 'pending') ?>"><?= e($o['status'] ?? 'pending') ?></span></td>
                    <td><?= e($o['provider_order_id'] ?? '-') ?></td>
                    <td><?= e($o['created_at'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php panel_footer(); ?>
