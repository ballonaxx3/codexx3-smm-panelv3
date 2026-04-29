<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();

$db = new Database();
$user = current_user($db);
$msg = '';
$error = '';

$services = db_all($db, 'SELECT id,name,rate,min,max,category FROM services WHERE active=1 ORDER BY category ASC, name ASC');

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
                $pdo->prepare('INSERT INTO orders (user_id,service_id,link,quantity,charge,status) VALUES (?,?,?,?,?,?)')
                    ->execute([$userId, $service_id, $link, $qty, $price, 'pending']);
                $pdo->commit();
                $msg = 'Pedido creado correctamente. Se descontó '.money($price).' de tu balance.';
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
    <a class="btn secondary" href="/services.php">Ver servicios</a>
</div>

<section class="main-card form-card">
    <h2>Crear orden</h2>
    <?php if($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="stack-form" id="orderForm">
        <label>Servicio</label>
        <select name="service_id" id="serviceSelect" required>
            <option value="">Selecciona un servicio</option>
            <?php foreach($services as $s): ?>
                <option value="<?= (int)$s['id'] ?>" data-rate="<?= e($s['rate']) ?>" data-min="<?= e($s['min'] ?? 1) ?>" data-max="<?= e($s['max'] ?? '') ?>">
                    <?= e(($s['category'] ?? 'General').' - '.$s['name']) ?> — <?= money($s['rate']) ?>/1000
                </option>
            <?php endforeach; ?>
        </select>

        <label>Link</label>
        <input name="link" type="url" placeholder="https://..." required>

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
const serviceSelect = document.getElementById('serviceSelect');
const quantityInput = document.getElementById('quantityInput');
const totalPrice = document.getElementById('totalPrice');
const limitsText = document.getElementById('limitsText');
function updatePrice(){
    const option = serviceSelect.options[serviceSelect.selectedIndex];
    const rate = parseFloat(option?.dataset?.rate || 0);
    const qty = parseInt(quantityInput.value || 0, 10);
    const min = option?.dataset?.min || '';
    const max = option?.dataset?.max || '';
    const total = rate && qty ? (rate * qty / 1000) : 0;
    totalPrice.textContent = '$' + total.toFixed(2);
    limitsText.textContent = serviceSelect.value ? ('Min: ' + min + (max ? ' | Max: ' + max : '')) : 'Selecciona un servicio';
}
serviceSelect.addEventListener('change', updatePrice);
quantityInput.addEventListener('input', updatePrice);
</script>

<?php panel_footer(); ?>
