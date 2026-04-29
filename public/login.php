<?php
require_once __DIR__.'/../app/lib/auth.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $auth=new Auth();
    if ($auth->login($email,$password)) { header('Location: /dashboard.php'); exit; }
    $error='Credenciales incorrectas';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Codexx3</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="auth-page">

    <div class="auth-brand">
        <h1>Codexx3</h1>
        <p>Panel profesional para gestionar servicios SMM. Automatiza pedidos, controla balances y escala tu negocio.</p>

        <div class="auth-pills">
            <span>🚀 Automatización</span>
            <span>💸 Monetización</span>
            <span>📊 Control total</span>
        </div>
    </div>

    <div class="auth-panel">
        <form class="auth-card" method="post">
            <h2>Iniciar sesión</h2>
            <p>Accede a tu panel</p>

            <?php if($error):?>
                <div class="alert error"><?=htmlspecialchars($error)?></div>
            <?php endif;?>

            <div class="stack-form">
                <input name="email" type="email" placeholder="Correo electrónico" required>
                <input name="password" type="password" placeholder="Contraseña" required>
                <button class="btn primary" type="submit">Entrar</button>
            </div>

            <a class="auth-link" href="/register.php">Crear cuenta</a>
            <div class="auth-small">Codexx3 © Panel SaaS</div>
        </form>
    </div>

</div>

</body>
</html>
