<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();
$db=new Database();$user=current_user($db);$msg='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $reply=trim($_POST['reply']??'');
  $status=$_POST['status']??'open';
  if($id<=0){$error='Ticket inválido.';}else{
    $ok=$db->query('UPDATE tickets SET admin_reply=?,status=? WHERE id=?',[$reply,$status,$id]);
    $msg=$ok?'Ticket actualizado.':'No se pudo actualizar.';
  }
}
$tickets=db_all($db,'SELECT t.*,u.email FROM tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.id DESC LIMIT 200');
panel_header('Admin Tickets','admin',$user);
?>
<div class="topbar"><div><h1>Admin tickets</h1><p class="muted">Responder tickets de usuarios.</p></div><a class="btn secondary" href="/admin.php">Volver admin</a></div>
<?php if($msg):?><div class="alert success"><?=e($msg)?></div><?php endif;?>
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<section class="main-card table-card">
<h2>Tickets</h2>
<div class="table-wrap">
<table>
<thead><tr><th>ID</th><th>Usuario</th><th>Asunto</th><th>Mensaje</th><th>Respuesta</th><th>Estado</th><th>Acción</th></tr></thead>
<tbody>
<?php if(!$tickets):?><tr><td colspan="7" class="muted">No hay tickets.</td></tr><?php endif;?>
<?php foreach($tickets as $t):?>
<tr>
<td>#<?= (int)$t['id']?></td>
<td><?=e($t['email'])?></td>
<td><?=e($t['subject'])?></td>
<td><?=e($t['message'])?></td>
<td><?=e($t['admin_reply']??'-')?></td>
<td><?=e($t['status'])?></td>
<td>
<form method="post" class="stack-form">
<input type="hidden" name="id" value="<?= (int)$t['id']?>">
<textarea name="reply" rows="2" placeholder="Responder..."></textarea>
<select name="status"><option value="open">open</option><option value="closed">closed</option></select>
<button class="btn mini primary">Guardar</button>
</form>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</div>
</section>
<?php panel_footer(); ?>