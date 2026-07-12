<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Debates – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
      <div class="debates-hero-badge"><i class="fas fa-comments"></i> Debates ciudadanos</div>
      <h1>Escucha otras <span>perspectivas</span></h1>
      <p>Las propuestas buscan resolver un problema. Los debates buscan entender los distintos puntos de vista de tu comunidad. Participa, responde y descubre cómo piensan otros jóvenes salvadoreños.</p>
    </div>

    <div class="dash-topbar">
      <div>
        <div class="dash-title">Explorar debates</div>
        <div class="dash-subtitle">Únete a la conversación o inicia un debate con tu propia pregunta</div>
      </div>
      <?php if ($usuarioLogueado): ?>
      <button class="btn btn-primary" onclick="Modal.open('modalNuevoDebate')">
        <i class="fas fa-plus"></i> Nuevo debate
      </button>
      <?php else: ?>
      <a href="auth.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Inicia sesión para debatir</a>
      <?php endif; ?>
    </div>

    <div class="filters-bar">
      <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" id="debateSearchInput" placeholder="Buscar debates...">
      </div>
      <select id="debateOrdenSelect" class="filter-select">
        <option value="recientes">Más recientes</option>
        <option value="populares">Más respuestas</option>
        <option value="participacion">Más participación</option>
      </select>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-ghost active" data-cat="0" onclick="Debates.filterCat(0, this)">Todas</button>
        <?php foreach ($categorias as $cat): ?>
        <button class="btn btn-sm btn-ghost" data-cat="<?= $cat['id'] ?>" onclick="Debates.filterCat(<?= $cat['id'] ?>, this)" style="gap:.35rem">
          <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
          <?= htmlspecialchars($cat['nombre']) ?>
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
      <h3 class="modal-title"><i class="fas fa-comments" style="color:var(--verde)"></i> Iniciar un debate</h3>
      <button class="modal-close" onclick="Modal.close('modalNuevoDebate')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Pregunta del debate</label>
        <input type="text" id="debateTitulo" class="form-control" placeholder="Ej. ¿Debería prohibirse el uso del celular en las escuelas?">
      </div>
      <div class="form-group">
        <label class="form-label">Contexto / descripción</label>
        <textarea id="debateDescripcion" class="form-control" rows="4" placeholder="Da un poco de contexto para orientar la discusión..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Categoría</label>
        <select id="debateCategoria" class="form-control">
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modalNuevoDebate')">Cancelar</button>
      <button class="btn btn-primary" onclick="Debates.crear()">
        <i class="fas fa-paper-plane"></i> Publicar debate
      </button>
    </div>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/debates.js"></script>
<script>Debates.init();</script>
</body>
</html>
