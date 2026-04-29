<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireAdmin();

$db = new Database();
$user = current_user($db);
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $depositId = (int)($_POST['deposit_id'] ?? 0);

    if ($depositId <= 0 || !in_array($action, ['approve_deposit', 'reject_deposit'], true)) {
        $error = 'Acción no válida.';
    } else {
        try {
            $pdo = $db->pdo();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT id,user_id,amount,status FROM deposits WHERE id=? FOR UPDATE');
            $stmt->execute([$depositId]);
            $deposit = $stmt->fetch();

            if (!$deposit) {
                $pdo->rollBack();
                $error = 'Recarga no encontrada.';
            } elseif (($deposit['status'] ?? '') !== 'pending') {
                $pdo->rollBack();
                $error = 'Esta recarga ya fue procesada.';
            } elseif ($action === 'approve_deposit') {
                $amount = (float)$deposit['amount'];
                $depositUserId = (int)$deposit['user_id'];
                $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id=?')->execute([$amount, $depositUserId]);
                $pdo->prepare('UPDATE deposits SET status=? WHERE id=?')->execute(['completed', $depositId]);
                $pdo->commit();
                $msg = 'Recarga aprobada y balance actualizado.';
            } else {
                $pdo->prepare('UPDATE deposits SET status=? WHERE id=?')->execute(['rejected', $depositId]);
                $pdo->commit();
                $msg = 'Recarga rechazada correctamente.';
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
            log_action('Admin deposit error: '.$e->getMessage());
            $error = 'No se pudo procesar la recarga.';
        }
    }
}

$users = db_one($db, 'SELECT COUNT(*) c FROM users')['c'] ?? 0;
$orders = db_one($db, 'SELECT COUNT(*) c FROM orders')['c'] ?? 0;
$pendingDepositsCount = db_one($db, "SELECT COUNT(*) c FROM deposits WHERE status='pending'")['c'] ?? 0;
$totalBalance = db_one($db, 'SELECT COALESCE(SUM(balance),0) total FROM users')['total'] ?? 0;

$pendingDeposits = db_all($db, 'SELECT d.id,d.amount,d.note,d.status,d.created_at,u.email FROM deposits d LEFT JOIN users u ON u.id=d.user_id WHERE d.status=? ORDER BY d.id DESC LIMIT 25', ['pending']);
$recentOrders = db_all($db, 'SELECT o.id,o.quantity,o.charge,o.status,o.created_at,u.email,s.name service_name FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN services s ON s.id=o.service_id ORDER BY o.id DESC LIMIT 10');

panel_header('Admin','admin',$user);
?>

<div class="topbar">
    <div>
        <h1>Admin</h1>
        <p class="muted">Centro de control Codexx3 SMM</p>
    </div>
    <a class="btn primary" href="/services.php">Servicios</a>
</div>

<?php if($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="stats">
    <div class="stat-card"><span>Usuarios</span><strong><?= (int)$users ?></strong></div>
    <div class="stat-card"><span>Pedidos</span><strong><?= (int)$orders ?></strong></div>
    <div class="stat-card"><span>Recargas pendientes</span><strong><?= (int)$pendingDepositsCount ?></strong></div>
    <div class="stat-card"><span>Balance usuarios</span><strong><?= money($totalBalance) ?></strong></div>
</div>

<section class="main-card table-card">
    <h2>Recargas pendientes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Usuario</th><th>Monto</th><th>Nota</th><th>Fecha</th><th>Acción</th></tr></thead>
            <tbody>
            <?php if(!$pendingDeposits): ?>
                <tr><td colspan="6" class="muted">No hay recargas pendientes.</td></tr>
            <?php endif; ?>
            <?php foreach($pendingDeposits as $d): ?>
                <tr>
                    <td>#<?= (int)$d['id'] ?></td>
                    <td><?= e($d['email'] ?? 'Usuario') ?></td>
                    <td><?= money($d['amount'] ?? 0) ?></td>
                    <td><?= e($d['note'] ?? '') ?></td>
                    <td><?= e($d['created_at'] ?? '') ?></td>
                    <td class="actions-cell">
                        <form method="post" class="inline-form">
                            <input type="hidden" name="deposit_id" value="<?= (int)$d['id'] ?>">
                            <button class="btn mini primary" name="action" value="approve_deposit" type="submit">Aprobar</button>
                            <button class="btn mini danger" name="action" value="reject_deposit" type="submit">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="main-card table-card">
    <h2>Pedidos recientes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Usuario</th><th>Servicio</th><th>Cantidad</th><th>Cobro</th><th>Estado</th></tr></thead>
            <tbody>
            <?php if(!$recentOrders): ?>
                <tr><td colspan="6" class="muted">No hay pedidos recientes.</td></tr>
            <?php endif; ?>
            <?php foreach($recentOrders as $o): ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= e($o['email'] ?? 'Usuario') ?></td>
                    <td><?= e($o['service_name'] ?? 'Servicio') ?></td>
                    <td><?= (int)($o['quantity'] ?? 0) ?></td>
                    <td><?= money($o['charge'] ?? 0) ?></td>
                    <td><span class="badge <?= e($o['status'] ?? 'pending') ?>"><?= e($o['status'] ?? 'pending') ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php panel_footer(); ?>
