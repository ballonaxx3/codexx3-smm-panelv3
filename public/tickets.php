<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();
$db=new Database();$user=current_user($db);$uid=(int)($user['id']??$_SESSION['user_id']);$msg='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){$subject=trim($_POST['subject']??'');$message=trim($_POST['message']??'');if($subject===''||$message===''){$error='Completa asunto y mensaje.';}else{$ok=$db->query('INSERT INTO tickets (user_id,subject,message,status) VALUES (?,?,?,?)',[$uid,$subject,$message,'open']);$msg=$ok?'Ticket creado.':'No se pudo crear ticket. Ejecuta la tabla tickets en DB.';}}
$tickets=db_all($db,'SELECT * FROM tickets WHERE user_id=? ORDER BY id DESC LIMIT 100',[$uid]);
panel_header('Tickets','tickets',$user);
?>
<div class="topbar"><div><h1>Tickets</h1><p class="muted">Soporte y comunicación con administración.</p></div></div><?php if($msg):?><div class="alert success"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<section class="main-card form-card"><h2>Crear ticket</h2><form method="post" class="stack-form"><label>Asunto</label><input name="subject" placeholder="Ej: Problema con pedido #123" required><label>Mensaje</label><textarea name="message" rows="4" placeholder="Explica tu caso" required></textarea><button class="btn primary">Enviar ticket</button></form></section>
<section class="main-card table-card"><h2>Mis tickets</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Asunto</th><th>Mensaje</th><th>Respuesta</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php if(!$tickets):?><tr><td colspan="6" class="muted">No hay tickets.</td></tr><?php endif;?><?php foreach($tickets as $t):?><tr><td>#<?= (int)$t['id']?></td><td><?=e($t['subject'])?></td><td><?=e($t['message'])?></td><td><?=e($t['admin_reply']??'-')?></td><td><span class="badge <?=e($t['status'])?>"><?=e($t['status'])?></span></td><td><?=e($t['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></section><?php panel_footer(); ?>