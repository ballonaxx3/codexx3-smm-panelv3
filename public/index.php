<?php require_once __DIR__.'/../app/config/session.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Codexx3 SMM Panel</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <main class="hero">
    <div class="card center">
      <h1>Codexx3 SMM Panel</h1>
      <p>Panel SMM base en PHP y MySQL listo para Railway.</p>

      <div class="actions">
        <?php if (isLoggedIn()): ?>
          <a class="btn" href="/dashboard.php">Dashboard</a>
          <a class="btn secondary" href="/logout.php">Salir</a>
        <?php else: ?>
          <a class="btn" href="/login.php">Iniciar sesión</a>
          <a class="btn secondary" href="/register.php">Registrarse</a>
        <?php endif; ?>

        <a class="btn whatsapp" href="https://wa.me/50361605896" target="_blank">WhatsApp</a>
      </div>
    </div>
  </main>
</body>
</html>
