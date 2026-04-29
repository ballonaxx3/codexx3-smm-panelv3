<?php require_once __DIR__.'/../app/config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Codexx3 SMM Panel</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">Codexx3</div>
  <nav>
    <a href="#inicio">Inicio</a>
    <a href="#beneficios">Beneficios</a>
    <a href="#planes">Planes</a>
    <a href="https://wa.me/50361605896" target="_blank">Contacto</a>
  </nav>
  <div class="nav-actions">
    <?php if (isLoggedIn()): ?>
      <a class="btn" href="/dashboard.php">Dashboard</a>
    <?php else: ?>
      <a class="btn" href="/login.php">Login</a>
    <?php endif; ?>
    <a class="btn primary" href="/register.php">Crear cuenta</a>
  </div>
</header>
<section id="inicio" class="hero landing-hero">
  <div class="hero-content">
    <span class="badge">⚡ Panel SMM profesional para revendedores</span>
    <h1>Vende servicios digitales con una plataforma <span>rápida y moderna</span></h1>
    <p>Gestiona clientes, saldo, pedidos, recargas y servicios desde un panel oscuro estilo Codexx3.</p>
    <div class="hero-buttons">
      <a href="/register.php" class="btn primary">Empezar ahora 🚀</a>
      <a href="https://wa.me/50361605896" target="_blank" class="btn outline">Hablar por WhatsApp</a>
    </div>
  </div>
</section>
<section id="beneficios" class="features">
  <h2>¿Por qué elegir Codexx3?</h2>
  <div class="cards">
    <div class="card"><h3>⚡ Rápido</h3><p>Pedidos y recargas desde un panel simple y directo.</p></div>
    <div class="card"><h3>💸 Revendedor</h3><p>Controla servicios, clientes, saldo y ganancias.</p></div>
    <div class="card"><h3>🔒 Seguro</h3><p>Login real, sesiones protegidas y base MySQL.</p></div>
  </div>
</section>
<section id="planes" class="features compact">
  <h2>Listo para empezar</h2>
  <p class="muted">Crea tu cuenta o entra al dashboard si ya estás registrado.</p>
  <div class="hero-buttons"><a class="btn primary" href="/register.php">Crear cuenta</a><a class="btn outline" href="/login.php">Iniciar sesión</a></div>
</section>
</body>
</html>
