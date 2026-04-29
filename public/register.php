<?php
require_once __DIR__.'/../app/lib/auth.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $auth=new Auth();
    if ($auth->register($email,$password)) { $msg='Cuenta creada, ahora inicia sesión'; }
    else { $msg='Error al registrar'; }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Registro - Codexx3</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="auth-page">

    <div class="auth-brand">
        <h1>Codexx3</h1>
        <p>Crea tu cuenta y empieza a gestionar pedidos de forma automática con un panel profesional.</p>

        <div class="auth-pills">
            <span>⚡ Rápido</span>
            <span>🔐 Seguro</span>
            <span>📈 Escalable</span>
        </div>
    </div>

    <div class="auth-panel">
        <form class="auth-card" method="post">
            <h2>Crear cuenta</h2>
            <p>Empieza ahora</p>

            <?php if($msg):?>
                <div class="alert success"><?=htmlspecialchars($msg)?></div>
            <?php endif;?>

            <div class="stack-form">
                <input name="email" type="email" placeholder="Correo electrónico" required>
                <input name="password" type="password" placeholder="Contraseña" required>
                <button class="btn primary" type="submit">Registrarme</button>
            </div>

            <a class="auth-link" href="/login.php">Ya tengo cuenta</a>
            <div class="auth-small">Codexx3 © Panel SaaS</div>
        </form>
    </div>

</div>

</body>
</html>
