<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();
$db=new Database();$user=current_user($db);
$users=db_one($db,'SELECT COUNT(*) c FROM users')['c']??0;
$orders=db_one($db,'SELECT COUNT(*) c FROM orders')['c']??0;
$revenue=db_one($db,'SELECT COALESCE(SUM(charge),0) t FROM orders')['t']??0;
$profit=db_one($db,'SELECT COALESCE(SUM(profit),0) t FROM orders')['t']??0;
$pendingDeposits=db_one($db,"SELECT COUNT(*) c FROM deposits WHERE status='pending'")['c']??0;
$providers=db_one($db,'SELECT COUNT(*) c FROM providers WHERE active=1')['c']??0;
$services=db_one($db,'SELECT COUNT(*) c FROM services WHERE active=1')['c']??0;
$tickets=db_one($db,"SELECT COUNT(*) c FROM tickets WHERE status='open'")['c']??0;
$recentOrders=db_all($db,'SELECT o.id,o.charge,o.profit,o.status,o.created_at,u.email,s.name service_name FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN services s ON s.id=o.service_id ORDER BY o.id DESC LIMIT 8');
$recentUsers=db_all($db,'SELECT id,email,balance,status,created_at FROM users ORDER BY id DESC LIMIT 6');
panel_header('Admin','admin',$user);
?>
<div class="topbar"><div><h1>Admin dashboard</h1><p class="muted">Control general del sistema Codexx3 SMM.</p></div><a class="btn primary" href="/admin_orders.php">Ver pedidos</a></div>
<div class="stats"><div class="stat-card"><span>Usuarios</span><strong><?= (int)$users?></strong></div><div class="stat-card"><span>Pedidos</span><strong><?= (int)$orders?></strong></div><div class="stat-card"><span>Ingresos</span><strong><?=money($revenue)?></strong></div><div class="stat-card"><span>Ganancia</span><strong><?=money($profit)?></strong></div></div>
<div class="stats"><div class="stat-card"><span>Recargas pendientes</span><strong><?= (int)$pendingDeposits?></strong></div><div class="stat-card"><span>Providers activos</span><strong><?= (int)$providers?></strong></div><div class="stat-card"><span>Servicios activos</span><strong><?= (int)$services?></strong></div><div class="stat-card"><span>Tickets abiertos</span><strong><?= (int)$tickets?></strong></div></div>
<section class="main-card"><h2>Módulos admin</h2><div class="quick-grid"><a href="/admin_users.php">👤 Usuarios</a><a href="/admin_services.php">📋 Servicios</a><a href="/admin_providers.php">🔌 Providers API</a><a href="/admin_orders.php">📦 Pedidos</a><a href="/admin_deposits.php">💳 Recargas</a><a href="/admin_tickets.php">🎫 Tickets</a></div></section>
<section class="main-card table-card"><h2>Pedidos recientes</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Usuario</th><th>Servicio</th><th>Cobro</th><th>Ganancia</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($recentOrders as $o):?><tr><td>#<?= (int)$o['id']?></td><td><?=e($o['email']??'-')?></td><td><?=e($o['service_name']??'-')?></td><td><?=money($o['charge'])?></td><td><?=money($o['profit']??0)?></td><td><span class="badge <?=e($o['status'])?>"><?=e($o['status'])?></span></td><td><?=e($o['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></section>
<section class="main-card table-card"><h2>Usuarios recientes</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Email</th><th>Balance</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($recentUsers as $u):?><tr><td>#<?= (int)$u['id']?></td><td><?=e($u['email'])?></td><td><?=money($u['balance'])?></td><td><span class="badge <?=($u['status']==='active')?'completed':'failed'?>"><?=e($u['status'])?></span></td><td><?=e($u['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></section><?php panel_footer(); ?>