<?php
// $usuarioNombre, $usuarioRol y $categorias los inyecta el View Composer
// global en TODAS las vistas (app/Providers/AppServiceProvider.php::boot()).
// El valor por defecto de abajo nunca se usa en producción - solo evita que
// el IDE marque la variable como indefinida y sirve de red de seguridad.
$usuarioNombre = $usuarioNombre ?? '';
$usuarioRol    = $usuarioRol ?? 'invitado';
$categorias    = $categorias ?? collect();
$iniciales = civinsis_iniciales($usuarioNombre);
$esAdmin    = ($usuarioRol === 'admin' || $usuarioRol === 'moderador');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.dashboard.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'propuestas'])->render(); ?>

<div class="dash-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="sidebar-avatar-wrap">
        <div class="sidebar-avatar"><?php if (!empty($usuarioAvatar)): ?><img src="<?= htmlspecialchars($usuarioAvatar) ?>" alt="Avatar"><?php else: ?><?= $iniciales ?><?php endif; ?></div>
        <div class="sidebar-nivel-badge"><?= __('civinsis.ranking.nivel_abrev') ?> <?= (int) ($usuarioNivel ?? 1) ?></div>
      </div>
      <div class="sidebar-user-name">
        <?= htmlspecialchars($usuarioNombre) ?>
        <?php if (!empty($usuarioInsigniaEmoji)): ?><span class="sidebar-insignia"><?= $usuarioInsigniaEmoji ?></span><?php endif; ?>
      </div>
      <?php if (!empty($usuarioTitulo)): ?>
        <span class="titulo-chip sidebar-titulo-chip <?= $usuarioTitulo->rareza ?>" style="color:<?= $usuarioTitulo->color ?>;border-color:<?= $usuarioTitulo->color ?>"><?= htmlspecialchars($usuarioTitulo->nombre) ?></span>
      <?php endif; ?>
      <div class="sidebar-user-role">
        <i class="fas fa-circle" style="font-size:.45rem;color:var(--verde-400)"></i>
        <?= __('civinsis.roles.' . $usuarioRol) ?>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label"><?= __('civinsis.dashboard.navegacion') ?></div>
      <button class="sidebar-link active" onclick="Proposals.filterCat(0, this)">
        <i class="fas fa-th-large"></i> <?= __('civinsis.dashboard.todas_propuestas') ?>
      </button>
      <a href="crear.php" class="sidebar-link">
        <i class="fas fa-plus-circle"></i> <?= __('civinsis.comun.nueva_propuesta') ?>
      </a>
      <a href="perfil.php" class="sidebar-link">
        <i class="fas fa-user"></i> <?= __('civinsis.nav.mi_perfil') ?>
      </a>
      <?php if ($esAdmin): ?>
      <a href="admin.php" class="sidebar-link" style="color:var(--naranja-600)">
        <i class="fas fa-shield-alt"></i> <?= __('civinsis.dashboard.panel_admin') ?>
      </a>
      <?php endif; ?>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label"><?= __('civinsis.footer.categorias') ?></div>
      <?php foreach ($categorias as $cat): ?>
      <button class="sidebar-link" onclick="Proposals.filterCat(<?= $cat['id'] ?>, this)" data-cat="<?= $cat['id'] ?>">
        <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
        <?= htmlspecialchars($cat->translated('nombre')) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label"><?= __('civinsis.dashboard.mi_cuenta') ?></div>
      <button class="sidebar-link" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i> <?= __('civinsis.nav.cerrar_sesion') ?>
      </button>
    </div>
  </aside>

  <!-- Main -->
  <main class="dash-main">
    <div class="dash-kpi-grid" id="kpiGrid">
      <div class="kpi-card"><div class="kpi-num" id="kpiTotal">–</div><div class="kpi-label"><i class="fas fa-file-alt"></i> <?= __('civinsis.dashboard.propuestas_totales') ?></div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiVotos">–</div><div class="kpi-label"><i class="fas fa-arrow-up"></i> <?= __('civinsis.dashboard.votos_totales') ?></div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiVistas">–</div><div class="kpi-label"><i class="fas fa-eye"></i> <?= __('civinsis.dashboard.vistas_totales') ?></div></div>
    </div>

    <div class="dash-topbar">
      <div>
        <div class="dash-title"><?= __('civinsis.dashboard.explorar_titulo') ?></div>
        <div class="dash-subtitle"><?= __('civinsis.dashboard.explorar_subtitulo') ?></div>
      </div>
      <a href="crear.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= __('civinsis.comun.nueva_propuesta') ?>
      </a>
    </div>

    <div class="filters-bar">
      <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="<?= __('civinsis.dashboard.buscar_placeholder') ?>">
      </div>
      <select id="ordenSelect" class="filter-select">
        <option value="fecha"><?= __('civinsis.comun.mas_recientes') ?></option>
        <option value="votos"><?= __('civinsis.comun.mas_votadas') ?></option>
        <option value="vistas"><?= __('civinsis.comun.mas_vistas') ?></option>
      </select>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-ghost active" data-cat="0" onclick="Proposals.filterCat(0, this)"><?= __('civinsis.comun.todas') ?></button>
        <?php foreach ($categorias as $cat): ?>
        <button class="btn btn-sm btn-ghost" data-cat="<?= $cat['id'] ?>" onclick="Proposals.filterCat(<?= $cat['id'] ?>, this)" style="gap:.35rem">
          <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
          <?= htmlspecialchars($cat->translated('nombre')) ?>
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
<script src="js/dashboard.js"></script>
</body>
</html>
