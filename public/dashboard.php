<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();
$db = new Database();
$user = current_user($db);

$totalOrders = db_one($db,'SELECT COUNT(*) c FROM orders WHERE user_id=?',[$user['id'] ?? 0])['c'] ?? 0;
$pendingOrders = db_one($db,"SELECT COUNT(*) c FROM orders WHERE user_id=? AND status='pending'",[$user['id'] ?? 0])['c'] ?? 0;
$services = db_one($db,'SELECT COUNT(*) c FROM services WHERE active=1')['c'] ?? 0;

panel_header('Dashboard','dashboard',$user);
?>

<div class="topbar">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Bienvenido</p>
    </div>
    <a class="btn primary" href="/order.php">Crear pedido</a>
</div>

<div class="stats">
    <div class="stat-card"><span>Balance</span><strong><?= money($user['balance'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Pedidos</span><strong><?= (int)$totalOrders ?></strong></div>
    <div class="stat-card"><span>Pendientes</span><strong><?= (int)$pendingOrders ?></strong></div>
    <div class="stat-card"><span>Servicios</span><strong><?= (int)$services ?></strong></div>
</div>

<section class="main-card">
    <h2>Acciones rápidas</h2>
    <div class="quick-grid">
        <a href="/order.php">🛒 Crear pedido</a>
        <a href="/services.php">📋 Ver servicios</a>
        <a href="/deposits.php">💳 Recargar saldo</a>
    </div>
</section>

<?php panel_footer(); ?>