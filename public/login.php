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
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><form class="card" method="post"><h1>Login</h1><?php if($error):?><p class="error"><?=htmlspecialchars($error)?></p><?php endif;?><input name="email" type="email" placeholder="Email" required><input name="password" type="password" placeholder="Password" required><button class="btn" type="submit">Entrar</button><a href="/register.php">Crear cuenta</a></form></main></body></html>
