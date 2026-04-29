<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
requireLogin();
$db = new Database();
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $amount=(float)$_POST['amount'];
  $note=trim($_POST['note']);
  $db->query('INSERT INTO deposits (user_id,amount,note) VALUES (?,?,?)',[ $_SESSION['user_id'],$amount,$note ]);
  $msg='Solicitud enviada';
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recargas</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><form class="card" method="post"><h1>Recargar saldo</h1><?php if($msg):?><p><?=htmlspecialchars($msg)?></p><?php endif;?><input name="amount" type="number" step="0.01" placeholder="Monto" required><textarea name="note" placeholder="Comprobante"></textarea><button class="btn" type="submit">Enviar</button><a href="/dashboard.php">Volver</a></form></main></body></html>
