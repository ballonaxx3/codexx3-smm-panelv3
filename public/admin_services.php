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
  if($action==='save_service'){
    $provider_id=($_POST['provider_id']??'')!==''?(int)$_POST['provider_id']:null;
    $provider_service_id=trim($_POST['provider_service_id']??'');
    $category=trim($_POST['category']??'General');$name=trim($_POST['name']??'');$description=trim($_POST['description']??'');
    $rate=(float)($_POST['rate']??0);$cost_rate=(float)($_POST['cost_rate']??0);$min=(int)($_POST['min']??1);$max=(int)($_POST['max']??1000);$active=isset($_POST['active'])?1:0;
    if($name===''||$category===''||$rate<=0||$min<=0||$max<$min){$error='Revisa nombre, categoría, precio, mínimo y máximo.';}
    else{
      if($id>0){$ok=$db->query('UPDATE services SET provider_id=?,provider_service_id=?,category=?,name=?,description=?,rate=?,cost_rate=?,min=?,max=?,active=? WHERE id=?',[$provider_id,$provider_service_id,$category,$name,$description,$rate,$cost_rate,$min,$max,$active,$id]);$msg=$ok?'Servicio actualizado.':'No se pudo actualizar.';}
      else{$ok=$db->query('INSERT INTO services (provider_id,provider_service_id,category,name,description,rate,cost_rate,min,max,active) VALUES (?,?,?,?,?,?,?,?,?,?)',[$provider_id,$provider_service_id,$category,$name,$description,$rate,$cost_rate,$min,$max,$active]);$msg=$ok?'Servicio creado.':'No se pudo crear.';}
    }
  }
  if($action==='toggle_service'&&$id>0){$db->query('UPDATE services SET active=IF(active=1,0,1) WHERE id=?',[$id]);$msg='Estado actualizado.';}
}
$providers=db_all($db,'SELECT id,name FROM providers WHERE active=1 ORDER BY name ASC');
$services=db_all($db,'SELECT s.*,p.name provider_name FROM services s LEFT JOIN providers p ON p.id=s.provider_id ORDER BY s.id DESC LIMIT 200');
$editId=(int)($_GET['edit']??0);$edit=$editId?db_one($db,'SELECT * FROM services WHERE id=?',[$editId]):[];
panel_header('Admin Services','admin',$user);
?>
<div class="topbar"><div><h1>Admin servicios</h1><p class="muted">Crea, edita y conecta servicios con proveedores API.</p></div><a class="btn secondary" href="/admin.php">Volver admin</a></div>
<?php if($msg):?><div class="alert success"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<section class="main-card form-card"><h2><?= $edit?'Editar servicio':'Agregar servicio' ?></h2><form method="post" class="stack-form"><input type="hidden" name="action" value="save_service"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><div class="form-grid"><div><label>Categoría</label><input name="category" value="<?=e($edit['category']??'')?>" placeholder="Instagram" required></div><div><label>Nombre</label><input name="name" value="<?=e($edit['name']??'')?>" placeholder="Instagram Likes" required></div></div><label>Descripción</label><textarea name="description" rows="3" placeholder="Instrucciones del servicio"><?=e($edit['description']??'')?></textarea><div class="form-grid"><div><label>Proveedor</label><select name="provider_id"><option value="">Sin proveedor</option><?php foreach($providers as $p):?><option value="<?= (int)$p['id']?>" <?= ((int)($edit['provider_id']??0)===(int)$p['id'])?'selected':'' ?>><?=e($p['name'])?></option><?php endforeach;?></select></div><div><label>ID servicio proveedor</label><input name="provider_service_id" value="<?=e($edit['provider_service_id']??'')?>" placeholder="2008"></div></div><div class="form-grid"><div><label>Precio venta / 1000</label><input name="rate" type="number" step="0.0001" value="<?=e($edit['rate']??'')?>" required></div><div><label>Costo proveedor / 1000</label><input name="cost_rate" type="number" step="0.0001" value="<?=e($edit['cost_rate']??'0')?>"></div></div><div class="form-grid"><div><label>Mínimo</label><input name="min" type="number" value="<?=e($edit['min']??1)?>" required></div><div><label>Máximo</label><input name="max" type="number" value="<?=e($edit['max']??1000)?>" required></div></div><label class="check-row"><input type="checkbox" name="active" <?= (($edit['active']??1)==1)?'checked':'' ?>> Activo</label><button class="btn primary" type="submit">Guardar servicio</button></form></section>
<section class="main-card table-card"><h2>Servicios</h2><div class="filters-row"><input id="serviceSearch" placeholder="Buscar servicio..."></div><div class="table-wrap"><table id="adminServicesTable"><thead><tr><th>ID</th><th>Categoría</th><th>Servicio</th><th>Proveedor</th><th>Provider ID</th><th>Venta</th><th>Costo</th><th>Min/Max</th><th>Estado</th><th>Acciones</th></tr></thead><tbody><?php foreach($services as $s):?><tr data-search="<?=e(strtolower(($s['id']??'').' '.($s['category']??'').' '.($s['name']??'').' '.($s['provider_name']??'')))?>"><td>#<?= (int)$s['id']?></td><td><?=e($s['category'])?></td><td><strong><?=e($s['name'])?></strong></td><td><?=e($s['provider_name']??'-')?></td><td><?=e($s['provider_service_id']??'-')?></td><td><?=money($s['rate'])?></td><td><?=money($s['cost_rate']??0)?></td><td><?= (int)$s['min']?> / <?= (int)$s['max']?></td><td><span class="badge <?=((int)$s['active']===1)?'completed':'failed'?>"><?=((int)$s['active']===1)?'activo':'inactivo'?></span></td><td class="actions-cell"><a class="btn mini secondary" href="/admin_services.php?edit=<?= (int)$s['id']?>">Editar</a><form method="post" class="inline-form"><input type="hidden" name="action" value="toggle_service"><input type="hidden" name="id" value="<?= (int)$s['id']?>"><button class="btn mini danger" type="submit">Estado</button></form></td></tr><?php endforeach;?></tbody></table></div></section><script>const q=document.getElementById('serviceSearch');const rows=[...document.querySelectorAll('#adminServicesTable tbody tr')];q.addEventListener('input',()=>{const v=q.value.toLowerCase();rows.forEach(r=>r.style.display=r.dataset.search.includes(v)?'':'none')});</script><?php panel_footer(); ?>