<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
requireLogin();
$db = new Database();
$services = $db->query('SELECT * FROM services WHERE active=1')->fetchAll();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Servicios</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><div class="card"><h1>Servicios</h1><ul><?php foreach($services as $s): ?><li><?= htmlspecialchars($s['name']) ?> - $<?= $s['rate'] ?> / 1000</li><?php endforeach; ?></ul><a class="btn" href="/dashboard.php">Volver</a></div></main></body></html>
