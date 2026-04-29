<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
require_once __DIR__.'/../app/lib/provider_api.php';
requireLogin();

$db = new Database();
$user = current_user($db);
$msg = '';
$error = '';

$services = db_all($db, 'SELECT id,name,description,rate,min,max,category FROM services WHERE active=1 ORDER BY category ASC, name ASC');
$categories = array_values(array_unique(array_map(fn($s) => $s['category'] ?? 'General', $services)));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $link = trim($_POST['link'] ?? '');
    $qty = (int)($_POST['quantity'] ?? 0);

    $service = db_one($db, 'SELECT * FROM services WHERE id=? AND active=1', [$service_id]);

    if (!$service) {
        $error = 'Servicio no válido.';
    } elseif ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) {
        $error = 'Coloca un link válido.';
    } elseif ($qty <= 0) {
        $error = 'La cantidad debe ser mayor a 0.';
    } elseif (isset($service['min']) && $qty < (int)$service['min']) {
        $error = 'La cantidad mínima para este servicio es '.(int)$service['min'].'.';
    } elseif (isset($service['max']) && (int)$service['max'] > 0 && $qty > (int)$service['max']) {
        $error = 'La cantidad máxima para este servicio es '.(int)$service['max'].'.';
    } else {
        $price = calc_price((float)$service['rate'], $qty);
        $cost = calc_price((float)($service['cost_rate'] ?? 0), $qty);
        $profit = round($price - $cost, 4);
        $userId = (int)($user['id'] ?? $_SESSION['user_id']);

        try {
            $pdo = $db->pdo();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT balance FROM users WHERE id=? FOR UPDATE');
            $stmt->execute([$userId]);
            $freshUser = $stmt->fetch();
            $balance = (float)($freshUser['balance'] ?? 0);

            if ($balance < $price) {
                $pdo->rollBack();
                $error = 'Saldo insuficiente. Necesitas '.money($price).' y tienes '.money($balance).'.';
            } else {
                $newBalance = round($balance - $price, 4);
                $pdo->prepare('UPDATE users SET balance=? WHERE id=?')->execute([$newBalance, $userId]);
                $pdo->prepare('INSERT INTO orders (user_id,service_id,link,quantity,charge,profit,status) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$userId, $service_id, $link, $qty, $price, $profit, 'pending']);
                $orderId = (int)$pdo->lastInsertId();
                $pdo->commit();

                $providerResult = send_order_to_provider($db, $orderId);
                $msg = 'Pedido creado correctamente. Se descontó '.money($price).' de tu balance.';
                if (!($providerResult['success'] ?? false)) {
                    $msg .= ' Quedó pendiente de proveedor: '.($providerResult['error'] ?? 'sin proveedor configurado');
                }
                $user = current_user($db);
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
            log_action('Order error: '.$e->getMessage());
            $error = 'No se pudo crear el pedido. Revisa la estructura de la tabla orders o intenta de nuevo.';
        }
    }
}

panel_header('Nuevo pedido','order',$user);
?>

<div class="topbar">
    <div>
        <h1>Nuevo pedido</h1>
        <p class="muted">Balance disponible: <strong><?= money($user['balance'] ?? 0) ?></strong></p>
    </div>
    <a class="btn secondary" href="/orders.php">Ver historial</a>
</div>

<section class="main-card order-pro-card">
    <h2>Crear orden</h2>
    <p class="muted">Selecciona categoría, servicio, link y cantidad. El precio se calcula automáticamente.</p>

    <?php if($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="stack-form" id="orderForm">
        <div class="form-grid">
            <div>
                <label>Categoría</label>
                <select id="categorySelect" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Servicio</label>
                <select name="service_id" id="serviceSelect" required disabled>
                    <option value="">Primero selecciona una categoría</option>
                </select>
            </div>
        </div>

        <div class="service-details" id="serviceDetails">
            <div><span>ID servicio</span><strong id="detailId">-</strong></div>
            <div><span>Precio / 1000</span><strong id="detailRate">$0.00</strong></div>
            <div><span>Mínimo</span><strong id="detailMin">-</strong></div>
            <div><span>Máximo</span><strong id="detailMax">-</strong></div>
        </div>

        <div class="description-box">
            <span>Descripción del servicio</span>
            <p id="serviceDescription">Selecciona un servicio para ver instrucciones, detalles y restricciones.</p>
        </div>

        <label>Link</label>
        <input name="link" type="url" placeholder="https://instagram.com/..." required>

        <div class="form-grid">
            <div>
                <label>Cantidad</label>
                <input name="quantity" id="quantityInput" type="number" min="1" placeholder="Ej: 1000" required>
            </div>
            <div class="price-box">
                <span>Total estimado</span>
                <strong id="totalPrice">$0.00</strong>
                <small id="limitsText">Selecciona un servicio</small>
            </div>
        </div>

        <button class="btn primary" type="submit">Enviar pedido</button>
    </form>
</section>

<script>
const services = <?= json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const categorySelect = document.getElementById('categorySelect');
const serviceSelect = document.getElementById('serviceSelect');
const quantityInput = document.getElementById('quantityInput');
const totalPrice = document.getElementById('totalPrice');
const limitsText = document.getElementById('limitsText');
const detailId = document.getElementById('detailId');
const detailRate = document.getElementById('detailRate');
const detailMin = document.getElementById('detailMin');
const detailMax = document.getElementById('detailMax');
const serviceDescription = document.getElementById('serviceDescription');

function moneyFmt(value){ return '$' + (parseFloat(value || 0)).toFixed(2); }
function selectedService(){ return services.find(s => String(s.id) === String(serviceSelect.value)); }
function fillServices(){
    const cat = categorySelect.value;
    serviceSelect.innerHTML = '<option value="">Selecciona un servicio</option>';
    const list = services.filter(s => String(s.category || 'General') === String(cat));
    list.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = '#' + s.id + ' - ' + s.name + ' — $' + parseFloat(s.rate || 0).toFixed(2) + '/1000';
        serviceSelect.appendChild(opt);
    });
    serviceSelect.disabled = !cat;
    resetDetails();
}
function resetDetails(){
    detailId.textContent='-'; detailRate.textContent='$0.00'; detailMin.textContent='-'; detailMax.textContent='-';
    serviceDescription.textContent='Selecciona un servicio para ver instrucciones, detalles y restricciones.';
    updatePrice();
}
function updateDetails(){
    const s = selectedService();
    if(!s){ resetDetails(); return; }
    detailId.textContent = '#' + s.id;
    detailRate.textContent = moneyFmt(s.rate);
    detailMin.textContent = s.min || '1';
    detailMax.textContent = s.max || '-';
    serviceDescription.textContent = s.description || 'Sin descripción. Asegúrate de colocar un link público y una cantidad válida.';
    quantityInput.min = s.min || 1;
    if(s.max){ quantityInput.max = s.max; }
    updatePrice();
}
function updatePrice(){
    const s = selectedService();
    const qty = parseInt(quantityInput.value || 0, 10);
    const rate = s ? parseFloat(s.rate || 0) : 0;
    const total = rate && qty ? (rate * qty / 1000) : 0;
    totalPrice.textContent = moneyFmt(total);
    limitsText.textContent = s ? ('Min: ' + (s.min || 1) + ' | Max: ' + (s.max || '-')) : 'Selecciona un servicio';
}
categorySelect.addEventListener('change', fillServices);
serviceSelect.addEventListener('change', updateDetails);
quantityInput.addEventListener('input', updatePrice);
</script>

<?php panel_footer(); ?>
