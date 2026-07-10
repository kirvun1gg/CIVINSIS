<?php
$iniciales  = strtoupper(substr($usuarioNombre, 0, 1));
$esAdmin    = ($usuarioRol === 'admin' || $usuarioRol === 'moderador');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'propuestas'])->render(); ?>

<div class="dash-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= $iniciales ?></div>
      <div class="sidebar-user-name"><?= htmlspecialchars($usuarioNombre) ?></div>
      <div class="sidebar-user-role">
        <i class="fas fa-circle" style="font-size:.45rem;color:var(--verde-400)"></i>
        <?= ucfirst($usuarioRol) ?>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Navegación</div>
      <button class="sidebar-link active" onclick="Proposals.filterCat(0, this)">
        <i class="fas fa-th-large"></i> Todas las propuestas
      </button>
      <a href="crear.php" class="sidebar-link">
        <i class="fas fa-plus-circle"></i> Nueva propuesta
      </a>
      <a href="perfil.php" class="sidebar-link">
        <i class="fas fa-user"></i> Mi perfil
      </a>
      <?php if ($esAdmin): ?>
      <a href="admin.php" class="sidebar-link" style="color:var(--naranja-600)">
        <i class="fas fa-shield-alt"></i> Panel Admin
      </a>
      <?php endif; ?>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Categorías</div>
      <?php foreach ($categorias as $cat): ?>
      <button class="sidebar-link" onclick="Proposals.filterCat(<?= $cat['id'] ?>, this)" data-cat="<?= $cat['id'] ?>">
        <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
        <?= htmlspecialchars($cat['nombre']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Mi cuenta</div>
      <button class="sidebar-link" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
      </button>
    </div>
  </aside>

  <!-- Main -->
  <main class="dash-main">
    <div class="dash-kpi-grid" id="kpiGrid">
      <div class="kpi-card"><div class="kpi-num" id="kpiTotal">–</div><div class="kpi-label"><i class="fas fa-file-alt"></i> Propuestas totales</div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiVotos">–</div><div class="kpi-label"><i class="fas fa-arrow-up"></i> Votos totales</div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiVistas">–</div><div class="kpi-label"><i class="fas fa-eye"></i> Vistas totales</div></div>
      <div class="kpi-card">
        <a href="crear.php" style="display:block;text-decoration:none">
          <div class="kpi-num" style="font-size:1.5rem"><i class="fas fa-plus"></i></div>
          <div class="kpi-label" style="color:var(--verde-500);font-weight:600">Crear propuesta</div>
        </a>
      </div>
    </div>

    <div class="dash-topbar">
      <div>
        <div class="dash-title">Explorar propuestas</div>
        <div class="dash-subtitle">Descubre, vota y participa en las propuestas de tu comunidad</div>
      </div>
      <a href="crear.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva propuesta
      </a>
    </div>

    <div class="filters-bar">
      <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar propuestas...">
      </div>
      <select id="ordenSelect" class="filter-select">
        <option value="fecha">Más recientes</option>
        <option value="votos">Más votadas</option>
        <option value="vistas">Más vistas</option>
      </select>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-ghost active" data-cat="0" onclick="Proposals.filterCat(0, this)">Todas</button>
        <?php foreach ($categorias as $cat): ?>
        <button class="btn btn-sm btn-ghost" data-cat="<?= $cat['id'] ?>" onclick="Proposals.filterCat(<?= $cat['id'] ?>, this)" style="gap:.35rem">
          <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
          <?= htmlspecialchars($cat['nombre']) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="cards-grid" id="proposalsGrid"></div>
    <div id="pagination"></div>
  </main>
</div>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
Proposals.filterCat = function(cat, btn) {
  document.querySelectorAll('[data-cat]').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  document.querySelectorAll('.sidebar-link').forEach(b => b.classList.remove('active'));
  if (btn && btn.closest('.sidebar')) btn.classList.add('active');
  this.currentCat = cat;
  this.currentPage = 1;
  this.load();
};

(async () => {
  const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
  const d = await r.json();
  if (d.success) {
    document.getElementById('kpiTotal').textContent = d.total;
    const r2 = await fetch('php/propuestas.php?accion=top&limit=100');
    const d2 = await r2.json();
    if (d2.success) {
      const votos  = d2.propuestas.reduce((s, p) => s + parseInt(p.votos),  0);
      const vistas = d2.propuestas.reduce((s, p) => s + parseInt(p.vistas), 0);
      document.getElementById('kpiVotos').textContent  = votos.toLocaleString('es');
      document.getElementById('kpiVistas').textContent = vistas.toLocaleString('es');
    }
  }
})();
</script>
</body>
</html>
