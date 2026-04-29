<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
requireLogin();
$db = new Database();
$userId = (int)$_SESSION['user_id'];
$user = $db->query('SELECT email,balance,is_admin FROM users WHERE id=?', [$userId])->fetch();
$totalOrders = $db->query('SELECT COUNT(*) c FROM orders WHERE user_id=?', [$userId])->fetch()['c'] ?? 0;
$pendingOrders = $db->query("SELECT COUNT(*) c FROM orders WHERE user_id=? AND status='pending'", [$userId])->fetch()['c'] ?? 0;
$services = $db->query('SELECT COUNT(*) c FROM services WHERE active=1')->fetch()['c'] ?? 0;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title><link rel="stylesheet" href="/assets/css/style.css"></head><body>
<div class="panel">
<aside class="sidebar"><div class="side-logo">⚡ Codexx3</div><a class="active" href="/dashboard.php">📊 Dashboard</a><a href="/order.php">🛒 Nuevo pedido</a><a href="/services.php">📋 Servicios</a><a href="/deposits.php">💳 Recargas</a><?php if(($user['is_admin'] ?? 0)==1): ?><a href="/admin.php">👑 Admin</a><?php endif; ?><a href="https://wa.me/50361605896" target="_blank">💬 Soporte</a><a href="/logout.php">🚪 Salir</a></aside>
<main class="content"><div class="topbar"><div><h1>Dashboard</h1><p class="muted"><?= htmlspecialchars($user['email'] ?? '') ?></p></div><a class="btn primary" href="/order.php">Crear pedido</a></div>
<div class="stats"><div class="stat-card"><span>Balance</span><strong>$<?= number_format((float)($user['balance'] ?? 0),2) ?></strong></div><div class="stat-card"><span>Pedidos</span><strong><?= (int)$totalOrders ?></strong></div><div class="stat-card"><span>Pendientes</span><strong><?= (int)$pendingOrders ?></strong></div><div class="stat-card"><span>Servicios</span><strong><?= (int)$services ?></strong></div></div>
<section class="main-card"><h2>Acciones rápidas</h2><div class="quick-grid"><a href="/order.php">🛒 Crear nuevo pedido</a><a href="/services.php">📋 Ver servicios</a><a href="/deposits.php">💳 Solicitar recarga</a><a href="https://wa.me/50361605896" target="_blank">💬 Soporte WhatsApp</a></div></section>
</main></div></body></html>