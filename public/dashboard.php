<?php
require_once __DIR__.'/../app/config/session.php';
requireLogin();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><div class="card"><h1>Dashboard</h1><p>Bienvenido usuario ID: <?= $_SESSION['user_id'] ?></p><a class="btn" href="/logout.php">Salir</a></div></main></body></html>
