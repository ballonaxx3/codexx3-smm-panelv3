<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();
$db=new Database();
$user=current_user($db);
$msg='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';$id=(int)($_POST['id']??0);
  if($id<=0){$error='Usuario inválido.';}
  elseif($action==='update_user'){
    $balance=round((float)($_POST['balance']??0),4);$is_admin=isset($_POST['is_admin'])?1:0;$status=($_POST['status']??'active')==='blocked'?'blocked':'active';
    $ok=$db->query('UPDATE users SET balance=?,is_admin=?,status=? WHERE id=?',[$balance,$is_admin,$status,$id]);
    $msg=$ok?'Usuario actualizado.':'No se pudo actualizar usuario.';
  } elseif($action==='add_balance'){
    $amount=round((float)($_POST['amount']??0),4);
    if($amount==0){$error='Monto inválido.';} else {$ok=$db->query('UPDATE users SET balance=balance+? WHERE id=?',[$amount,$id]);$msg=$ok?'Balance ajustado.':'No se pudo ajustar balance.';}
  }
}
$search=trim($_GET['q']??'');$where='';$params=[];
if($search!==''){$where='WHERE email LIKE ? OR id=?';$params=['%'.$search.'%',(int)$search];}
$users=db_all($db,"SELECT u.*,COUNT(o.id) total_orders,COALESCE(SUM(o.charge),0) total_spent FROM users u LEFT JOIN orders o ON o.user_id=u.id {$where} GROUP BY u.id ORDER BY u.id DESC LIMIT 200",$params);
$editId=(int)($_GET['edit']??0);$edit=$editId?db_one($db,'SELECT * FROM users WHERE id=?',[$editId]):[];
panel_header('Admin Usuarios','admin',$user);
?>
<div class="topbar"><div><h1>Admin usuarios</h1><p class="muted">Gestiona balances, roles y estado de cuentas.</p></div><a class="btn secondary" href="/admin.php">Volver admin</a></div>
<?php if($msg):?><div class="alert success"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<?php if($edit):?><section class="main-card form-card"><h2>Editar usuario #<?= (int)$edit['id']?></h2><form method="post" class="stack-form"><input type="hidden" name="action" value="update_user"><input type="hidden" name="id" value="<?= (int)$edit['id']?>"><label>Email</label><input value="<?=e($edit['email'])?>" disabled><div class="form-grid"><div><label>Balance</label><input name="balance" type="number" step="0.0001" value="<?=e($edit['balance'])?>"></div><div><label>Estado</label><select name="status"><option value="active" <?=($edit['status']??'active')==='active'?'selected':''?>>Activo</option><option value="blocked" <?=($edit['status']??'')==='blocked'?'selected':''?>>Bloqueado</option></select></div></div><label class="check-row"><input type="checkbox" name="is_admin" <?=((int)($edit['is_admin']??0)===1)?'checked':''?>> Administrador</label><button class="btn primary" type="submit">Guardar usuario</button></form><form method="post" class="stack-form"><input type="hidden" name="action" value="add_balance"><input type="hidden" name="id" value="<?= (int)$edit['id']?>"><label>Ajustar balance (+ o -)</label><input name="amount" type="number" step="0.0001" placeholder="Ej: 10 o -5"><button class="btn secondary" type="submit">Aplicar ajuste</button></form></section><?php endif;?>
<section class="main-card table-card"><h2>Usuarios</h2><form method="get" class="filters-row"><input name="q" value="<?=e($search)?>" placeholder="Buscar email o ID"><button class="btn secondary" type="submit">Buscar</button><a class="btn secondary" href="/admin_users.php">Limpiar</a></form><div class="table-wrap"><table><thead><tr><th>ID</th><th>Email</th><th>Balance</th><th>Pedidos</th><th>Gastado</th><th>Rol</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody><?php if(!$users):?><tr><td colspan="9" class="muted">No hay usuarios.</td></tr><?php endif;?><?php foreach($users as $u):?><tr><td>#<?= (int)$u['id']?></td><td><?=e($u['email'])?></td><td><?=money($u['balance'])?></td><td><?= (int)$u['total_orders']?></td><td><?=money($u['total_spent'])?></td><td><span class="badge <?=((int)$u['is_admin']===1)?'completed':''?>"><?=((int)$u['is_admin']===1)?'admin':'usuario'?></span></td><td><span class="badge <?=($u['status']==='active')?'completed':'failed'?>"><?=e($u['status'])?></span></td><td><?=e($u['created_at'])?></td><td><a class="btn mini primary" href="/admin_users.php?edit=<?= (int)$u['id']?>">Editar</a></td></tr><?php endforeach;?></tbody></table></div></section><?php panel_footer(); ?>