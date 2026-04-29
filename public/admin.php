<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();
$db = new Database();
$users = $db->query('SELECT COUNT(*) c FROM users')->fetch()['c'] ?? 0;
$orders = $db->query('SELECT COUNT(*) c FROM orders')->fetch()['c'] ?? 0;
$deposits = $db->query("SELECT COUNT(*) c FROM deposits WHERE status='pending'")->fetch()['c'] ?? 0;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><div class="card"><h1>Admin</h1><p>Usuarios: <?= (int)$users ?></p><p>Pedidos: <?= (int)$orders ?></p><p>Recargas pendientes: <?= (int)$deposits ?></p><a class="btn" href="/services.php">Servicios</a><a class="btn secondary" href="/logout.php">Salir</a></div></main></body></html>
