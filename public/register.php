<?php
require_once __DIR__.'/../app/lib/auth.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $auth=new Auth();
    if ($auth->register($email,$password)) { $msg='Cuenta creada, inicia sesión'; }
    else { $msg='Error al registrar'; }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registro</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><form class="card" method="post"><h1>Registro</h1><?php if($msg):?><p><?=htmlspecialchars($msg)?></p><?php endif;?><input name="email" type="email" placeholder="Email" required><input name="password" type="password" placeholder="Password" required><button class="btn" type="submit">Crear cuenta</button><a href="/login.php">Ir a login</a></form></main></body></html>
