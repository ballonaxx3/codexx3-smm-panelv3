<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();
$db = new Database();
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $service_id=(int)$_POST['service_id'];
  $link=trim($_POST['link']);
  $qty=(int)$_POST['quantity'];
  $s=$db->query('SELECT * FROM services WHERE id=?',[$service_id])->fetch();
  if ($s) {
    $price=calc_price((float)$s['rate'],$qty);
    $db->query('INSERT INTO orders (user_id,service_id,link,quantity,charge) VALUES (?,?,?,?,?)',[ $_SESSION['user_id'],$service_id,$link,$qty,$price ]);
    $msg='Pedido creado';
  }
}
$services=$db->query('SELECT * FROM services WHERE active=1')->fetchAll();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Orden</title><link rel="stylesheet" href="/assets/css/style.css"></head><body><main class="hero"><form class="card" method="post"><h1>Crear pedido</h1><?php if($msg):?><p><?=htmlspecialchars($msg)?></p><?php endif;?><select name="service_id"><?php foreach($services as $s):?><option value="<?=$s['id']?>"><?=$s['name']?></option><?php endforeach;?></select><input name="link" placeholder="Link" required><input name="quantity" type="number" placeholder="Cantidad" required><button class="btn" type="submit">Enviar</button><a href="/dashboard.php">Volver</a></form></main></body></html>
