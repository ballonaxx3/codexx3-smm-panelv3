<?php
require_once __DIR__.'/../app/config/session.php';
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../app/lib/helpers.php';
requireLogin();

$db = new Database();
$user = current_user($db);
$services = db_all($db, 'SELECT id,category,name,description,rate,min,max,active FROM services WHERE active=1 ORDER BY category ASC, id ASC');
$categories = array_values(array_unique(array_map(fn($s) => $s['category'] ?? 'General', $services)));
$totalServices = count($services);
$totalCategories = count($categories);
$minRate = $services ? min(array_map(fn($s) => (float)$s['rate'], $services)) : 0;

panel_header('Servicios','services',$user);
?>

<div class="topbar">
    <div>
        <h1>Servicios</h1>
        <p class="muted">Catálogo completo de servicios disponibles</p>
    </div>
    <a class="btn primary" href="/order.php">Nuevo pedido</a>
</div>

<div class="stats">
    <div class="stat-card"><span>Servicios activos</span><strong><?= (int)$totalServices ?></strong></div>
    <div class="stat-card"><span>Categorías</span><strong><?= (int)$totalCategories ?></strong></div>
    <div class="stat-card"><span>Desde</span><strong><?= money($minRate) ?></strong></div>
    <div class="stat-card"><span>Balance</span><strong><?= money($user['balance'] ?? 0) ?></strong></div>
</div>

<section class="main-card table-card">
    <div class="section-head">
        <div>
            <h2>Lista de servicios</h2>
            <p class="muted">Busca por ID, nombre o categoría. Filtra como en un panel SMM profesional.</p>
        </div>
    </div>

    <div class="filters-row">
        <input id="serviceSearch" placeholder="Buscar servicio, ID o categoría...">
        <select id="categoryFilter">
            <option value="">Todas las categorías</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="button" id="clearFilters">Limpiar</button>
    </div>

    <div class="table-wrap">
        <table id="servicesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Servicio</th>
                    <th>Precio / 1000</th>
                    <th>Mínimo</th>
                    <th>Máximo</th>
                    <th>Descripción</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!$services): ?>
                <tr><td colspan="8" class="muted">No hay servicios disponibles.</td></tr>
            <?php endif; ?>
            <?php foreach($services as $s): ?>
                <tr data-category="<?= e($s['category'] ?? 'General') ?>" data-search="<?= e(strtolower(($s['id'] ?? '').' '.($s['category'] ?? '').' '.($s['name'] ?? '').' '.($s['description'] ?? ''))) ?>">
                    <td><strong>#<?= (int)$s['id'] ?></strong></td>
                    <td><span class="badge"><?= e($s['category'] ?? 'General') ?></span></td>
                    <td><strong><?= e($s['name'] ?? '') ?></strong></td>
                    <td><?= money($s['rate'] ?? 0) ?></td>
                    <td><?= (int)($s['min'] ?? 1) ?></td>
                    <td><?= (int)($s['max'] ?? 0) ?></td>
                    <td class="description-cell"><?= e($s['description'] ?: 'Sin descripción') ?></td>
                    <td><a class="btn mini primary" href="/order.php">Ordenar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
const searchInput = document.getElementById('serviceSearch');
const categoryFilter = document.getElementById('categoryFilter');
const clearFilters = document.getElementById('clearFilters');
const rows = Array.from(document.querySelectorAll('#servicesTable tbody tr[data-search]'));
function filterServices(){
    const q = (searchInput.value || '').toLowerCase().trim();
    const cat = categoryFilter.value;
    rows.forEach(row => {
        const matchesText = !q || row.dataset.search.includes(q);
        const matchesCat = !cat || row.dataset.category === cat;
        row.style.display = (matchesText && matchesCat) ? '' : 'none';
    });
}
searchInput.addEventListener('input', filterServices);
categoryFilter.addEventListener('change', filterServices);
clearFilters.addEventListener('click', () => { searchInput.value=''; categoryFilter.value=''; filterServices(); });
</script>

<?php panel_footer(); ?>
