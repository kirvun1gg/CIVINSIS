<?php
// $usuarioLogueado y $categorias los inyecta el View Composer global en
// TODAS las vistas (app/Providers/AppServiceProvider.php::boot()). El valor
// por defecto de abajo nunca se usa en producción - solo evita que el IDE
// marque la variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
$categorias      = $categorias ?? collect();
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.nav.debates') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'debates'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:4rem;min-height:100vh">
  <div class="container">

    <div class="debates-hero">
      <div class="debates-hero-badge"><i class="fas fa-comments"></i> <?= __('civinsis.debates.badge') ?></div>
      <h1><?= __('civinsis.debates.titulo') ?></h1>
      <p><?= __('civinsis.debates.descripcion') ?></p>
    </div>

    <div class="dash-topbar">
      <div>
        <div class="dash-title"><?= __('civinsis.debates.explorar_titulo') ?></div>
        <div class="dash-subtitle"><?= __('civinsis.debates.explorar_subtitulo') ?></div>
      </div>
      <?php if ($usuarioLogueado): ?>
      <button class="btn btn-primary" onclick="Modal.open('modalNuevoDebate')">
        <i class="fas fa-plus"></i> <?= __('civinsis.debates.nuevo_debate') ?>
      </button>
      <?php else: ?>
      <a href="auth.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> <?= __('civinsis.debates.inicia_sesion_debatir') ?></a>
      <?php endif; ?>
    </div>

    <div class="filters-bar">
      <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" id="debateSearchInput" placeholder="<?= __('civinsis.debates.buscar_placeholder') ?>">
      </div>
      <select id="debateOrdenSelect" class="filter-select">
        <option value="recientes"><?= __('civinsis.debates.orden_recientes') ?></option>
        <option value="populares"><?= __('civinsis.debates.orden_populares') ?></option>
        <option value="participacion"><?= __('civinsis.debates.orden_participacion') ?></option>
      </select>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-ghost active" data-cat="0" onclick="Debates.filterCat(0, this)"><?= __('civinsis.comun.todas') ?></button>
        <?php foreach ($categorias as $cat): ?>
        <button class="btn btn-sm btn-ghost" data-cat="<?= $cat['id'] ?>" onclick="Debates.filterCat(<?= $cat['id'] ?>, this)" style="gap:.35rem">
          <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
          <?= htmlspecialchars($cat->translated('nombre')) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="debates-grid" id="debatesGrid"></div>
    <div id="debatesPagination" class="pagination"></div>

  </div>
</main>

<!-- Modal: nuevo debate -->
<div class="modal-backdrop" id="modalNuevoDebate">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-comments" style="color:var(--verde)"></i> <?= __('civinsis.debates.modal_titulo') ?></h3>
      <button class="modal-close" onclick="Modal.close('modalNuevoDebate')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.debates.modal_pregunta') ?></label>
        <input type="text" id="debateTitulo" class="form-control" placeholder="<?= __('civinsis.debates.modal_pregunta_placeholder') ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.debates.modal_contexto') ?></label>
        <textarea id="debateDescripcion" class="form-control" rows="4" placeholder="<?= __('civinsis.debates.modal_contexto_placeholder') ?>"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.comun.categoria') ?></label>
        <select id="debateCategoria" class="form-control">
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat->translated('nombre')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modalNuevoDebate')"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-primary" onclick="Debates.crear()">
        <i class="fas fa-paper-plane"></i> <?= __('civinsis.debates.modal_publicar') ?>
      </button>
    </div>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/utils.js"></script>
<script src="js/debates.js"></script>
</body>
</html>
