<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
require_once __DIR__.'/../app/lib/provider_api.php';
requireAdmin();
$db=new Database();
$user=current_user($db);
$msg='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';$id=(int)($_POST['id']??0);
  if($id<=0){$error='Pedido inválido.';}
  elseif($action==='update_status'){
    $status=$_POST['status']??'pending';
    $allowed=['pending','processing','inprogress','completed','partial','canceled','failed'];
    if(!in_array($status,$allowed,true)){$error='Estado inválido.';}else{$ok=$db->query('UPDATE orders SET status=? WHERE id=?',[$status,$id]);$msg=$ok?'Estado actualizado.':'No se pudo actualizar.';}
  } elseif($action==='resend_provider'){
    $result=send_order_to_provider($db,$id);
    $msg=($result['success']??false)?'Pedido reenviado al proveedor.':'No se pudo reenviar: '.($result['error']??'error');
  }
}
$status=trim($_GET['status']??'');$search=trim($_GET['q']??'');$where='WHERE 1=1';$params=[];
if($status!==''){$where.=' AND o.status=?';$params[]=$status;}
if($search!==''){$where.=' AND (u.email LIKE ? OR s.name LIKE ? OR o.link LIKE ? OR o.provider_order_id LIKE ? OR o.id=?)';$like='%'.$search.'%';array_push($params,$like,$like,$like,$like,(int)$search);}
$orders=db_all($db,"SELECT o.*,u.email,s.name service_name,s.category,p.name provider_name FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN services s ON s.id=o.service_id LEFT JOIN providers p ON p.id=s.provider_id {$where} ORDER BY o.id DESC LIMIT 250",$params);
$total=db_one($db,'SELECT COUNT(*) c FROM orders')['c']??0;$revenue=db_one($db,'SELECT COALESCE(SUM(charge),0) t FROM orders')['t']??0;$profit=db_one($db,'SELECT COALESCE(SUM(profit),0) t FROM orders')['t']??0;$pending=db_one($db,"SELECT COUNT(*) c FROM orders WHERE status IN ('pending','processing','inprogress')")['c']??0;
panel_header('Admin Pedidos','admin',$user);
?>
<div class="topbar"><div><h1>Admin pedidos</h1><p class="muted">Control total de órdenes, estados y respuestas API.</p></div><a class="btn secondary" href="/admin.php">Volver admin</a></div>
<?php if($msg):?><div class="alert success"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<div class="stats"><div class="stat-card"><span>Total</span><strong><?= (int)$total?></strong></div><div class="stat-card"><span>Ingresos</span><strong><?=money($revenue)?></strong></div><div class="stat-card"><span>Ganancia</span><strong><?=money($profit)?></strong></div><div class="stat-card"><span>Activos</span><strong><?= (int)$pending?></strong></div></div>
<section class="main-card table-card"><h2>Pedidos</h2><form method="get" class="filters-row"><input name="q" value="<?=e($search)?>" placeholder="Buscar ID, usuario, servicio, link..."><select name="status"><option value="">Todos</option><?php foreach(['pending','processing','inprogress','completed','partial','canceled','failed'] as $st):?><option value="<?=e($st)?>" <?=$status===$st?'selected':''?>><?=e($st)?></option><?php endforeach;?></select><button class="btn secondary" type="submit">Filtrar</button><a class="btn secondary" href="/admin_orders.php">Limpiar</a></form><div class="table-wrap"><table><thead><tr><th>ID</th><th>Usuario</th><th>Servicio</th><th>Proveedor</th><th>Link</th><th>Cant.</th><th>Cobro</th><th>Ganancia</th><th>Estado</th><th>Provider ID</th><th>Acción</th></tr></thead><tbody><?php if(!$orders):?><tr><td colspan="11" class="muted">No hay pedidos.</td></tr><?php endif;?><?php foreach($orders as $o):?><tr><td>#<?= (int)$o['id']?></td><td><?=e($o['email']??'-')?></td><td><strong><?=e($o['service_name']??'-')?></strong><br><span class="muted"><?=e($o['category']??'')?></span></td><td><?=e($o['provider_name']??'-')?></td><td class="link-cell"><a href="<?=e($o['link'])?>" target="_blank"><?=e($o['link'])?></a></td><td><?= (int)$o['quantity']?></td><td><?=money($o['charge'])?></td><td><?=money($o['profit']??0)?></td><td><span class="badge <?=e($o['status'])?>"><?=e($o['status'])?></span></td><td><?=e($o['provider_order_id']??'-')?></td><td class="actions-cell"><form method="post" class="inline-form"><input type="hidden" name="id" value="<?= (int)$o['id']?>"><select name="status"><option value="pending">pending</option><option value="processing">processing</option><option value="inprogress">inprogress</option><option value="completed">completed</option><option value="partial">partial</option><option value="canceled">canceled</option><option value="failed">failed</option></select><button class="btn mini secondary" name="action" value="update_status">Estado</button><button class="btn mini primary" name="action" value="resend_provider">Reenviar</button></form></td></tr><?php endforeach;?></tbody></table></div></section><?php panel_footer(); ?>