<?php
require_once __DIR__.'/../app/config/session.php';
requireLogin();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="hero">
  <div class="card">
    <div class="topbar">
      <div class="brand">Codexx3 Panel</div>
      <div class="nav">
        <a class="btn" href="/services.php">Servicios</a>
        <a class="btn" href="/order.php">Nuevo pedido</a>
        <a class="btn" href="/deposits.php">Recargar</a>
        <a class="btn secondary" href="/admin.php">Admin</a>
        <a class="btn danger" href="/logout.php">Salir</a>
      </div>
    </div>

    <h1>Dashboard</h1>
    <p class="muted">Usuario ID: <?= $_SESSION['user_id'] ?></p>

    <div class="grid">
      <div class="stat">Pedidos<br><b>0</b></div>
      <div class="stat">Balance<br><b>$0</b></div>
      <div class="stat">Servicios<br><b>0</b></div>
    </div>

  </div>
</main>
</body>
</html>