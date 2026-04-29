<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();
$db=new Database();$user=current_user($db);$uid=(int)($user['id']??0);
$totalOrders=db_one($db,'SELECT COUNT(*) c FROM orders WHERE user_id=?',[$uid])['c']??0;
$activeOrders=db_one($db,"SELECT COUNT(*) c FROM orders WHERE user_id=? AND status IN ('pending','processing','inprogress')",[$uid])['c']??0;
$completed=db_one($db,"SELECT COUNT(*) c FROM orders WHERE user_id=? AND status='completed'",[$uid])['c']??0;
$spent=db_one($db,'SELECT COALESCE(SUM(charge),0) t FROM orders WHERE user_id=?',[$uid])['t']??0;
$services=db_one($db,'SELECT COUNT(*) c FROM services WHERE active=1')['c']??0;
$recent=db_all($db,'SELECT o.id,o.quantity,o.charge,o.status,o.created_at,s.name service_name FROM orders o LEFT JOIN services s ON s.id=o.service_id WHERE o.user_id=? ORDER BY o.id DESC LIMIT 8',[$uid]);
$deposits=db_all($db,'SELECT amount,status,created_at FROM deposits WHERE user_id=? ORDER BY id DESC LIMIT 5',[$uid]);
panel_header('Dashboard','dashboard',$user);
?>
<div class="topbar"><div><h1>Dashboard</h1><p class="muted">Resumen de tu cuenta y actividad reciente</p></div><a class="btn primary" href="/order.php">Crear pedido</a></div>
<div class="stats"><div class="stat-card"><span>Balance</span><strong><?=money($user['balance']??0)?></strong></div><div class="stat-card"><span>Total pedidos</span><strong><?= (int)$totalOrders?></strong></div><div class="stat-card"><span>En proceso</span><strong><?= (int)$activeOrders?></strong></div><div class="stat-card"><span>Gastado</span><strong><?=money($spent)?></strong></div></div>
<div class="stats"><div class="stat-card"><span>Completados</span><strong><?= (int)$completed?></strong></div><div class="stat-card"><span>Servicios activos</span><strong><?= (int)$services?></strong></div><div class="stat-card"><span>Tickets</span><strong><?= (int)(db_one($db,'SELECT COUNT(*) c FROM tickets WHERE user_id=?',[$uid])['c']??0)?></strong></div><div class="stat-card"><span>Estado</span><strong>Activo</strong></div></div>
<section class="main-card"><h2>Acciones rápidas</h2><div class="quick-grid"><a href="/order.php">🛒 Nuevo pedido</a><a href="/orders.php">📦 Mis pedidos</a><a href="/services.php">📋 Servicios</a><a href="/deposits.php">💳 Recargar saldo</a><a href="/tickets.php">🎫 Soporte</a></div></section>
<section class="main-card table-card"><h2>Pedidos recientes</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Servicio</th><th>Cantidad</th><th>Cobro</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php if(!$recent):?><tr><td colspan="6" class="muted">Aún no tienes pedidos.</td></tr><?php endif;?><?php foreach($recent as $o):?><tr><td>#<?= (int)$o['id']?></td><td><?=e($o['service_name']??'-')?></td><td><?= (int)$o['quantity']?></td><td><?=money($o['charge'])?></td><td><span class="badge <?=e($o['status'])?>"><?=e($o['status'])?></span></td><td><?=e($o['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></section>
<section class="main-card table-card"><h2>Recargas recientes</h2><div class="table-wrap"><table><thead><tr><th>Monto</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php if(!$deposits):?><tr><td colspan="3" class="muted">Sin recargas recientes.</td></tr><?php endif;?><?php foreach($deposits as $d):?><tr><td><?=money($d['amount'])?></td><td><span class="badge <?=e($d['status'])?>"><?=e($d['status'])?></span></td><td><?=e($d['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></section><?php panel_footer(); ?>