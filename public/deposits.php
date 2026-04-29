<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();

$db = new Database();
$user = current_user($db);
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = round((float)($_POST['amount'] ?? 0), 2);
    $note = trim($_POST['note'] ?? '');
    $userId = (int)($user['id'] ?? $_SESSION['user_id']);

    if ($amount < 1) {
        $error = 'El monto mínimo de recarga es $1.00.';
    } elseif ($amount > 10000) {
        $error = 'El monto es demasiado alto. Contacta soporte.';
    } elseif ($note === '') {
        $error = 'Agrega una nota o referencia del pago.';
    } else {
        try {
            $stmt = $db->query('INSERT INTO deposits (user_id,amount,note,status) VALUES (?,?,?,?)', [$userId, $amount, $note, 'pending']);
            if ($stmt) {
                $msg = 'Solicitud enviada. Un admin revisará tu recarga.';
            } else {
                $error = 'No se pudo guardar la solicitud.';
            }
        } catch (Throwable $e) {
            log_action('Deposit error: '.$e->getMessage());
            $error = 'No se pudo crear la recarga. Revisa la tabla deposits.';
        }
    }
}

$deposits = db_all($db, 'SELECT amount,note,status,created_at FROM deposits WHERE user_id=? ORDER BY id DESC LIMIT 12', [(int)($user['id'] ?? 0)]);

panel_header('Recargas','deposits',$user);
?>

<div class="topbar">
    <div>
        <h1>Recargas</h1>
        <p class="muted">Balance actual: <strong><?= money($user['balance'] ?? 0) ?></strong></p>
    </div>
    <a class="btn secondary" href="https://wa.me/50361605896" target="_blank" rel="noopener">Soporte</a>
</div>

<section class="main-card form-card">
    <h2>Solicitar recarga manual</h2>
    <?php if($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="stack-form">
        <label>Monto</label>
        <input name="amount" type="number" step="0.01" min="1" placeholder="Ej: 25.00" required>

        <label>Referencia / comprobante</label>
        <textarea name="note" rows="4" placeholder="Escribe el método, referencia, número de comprobante o detalle del pago" required></textarea>

        <button class="btn primary" type="submit">Enviar solicitud</button>
    </form>
</section>

<section class="main-card table-card">
    <h2>Mis recargas recientes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Monto</th><th>Estado</th><th>Nota</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php if(!$deposits): ?>
                <tr><td colspan="4" class="muted">Aún no tienes recargas.</td></tr>
            <?php endif; ?>
            <?php foreach($deposits as $d): ?>
                <tr>
                    <td><?= money($d['amount'] ?? 0) ?></td>
                    <td><span class="badge <?= e($d['status'] ?? 'pending') ?>"><?= e($d['status'] ?? 'pending') ?></span></td>
                    <td><?= e($d['note'] ?? '') ?></td>
                    <td><?= e($d['created_at'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php panel_footer(); ?>
