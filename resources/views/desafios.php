<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Desafíos – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
  <link rel="stylesheet" href="css/desafios.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'desafios'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:4rem;min-height:100vh">
  <div class="container">

    <div class="debates-hero">
      <div class="debates-hero-badge" style="background:var(--naranja-alpha);border-color:var(--naranja-alpha2);color:var(--naranja-700)">
        <i class="fas fa-flag-checkered"></i> Desafíos ciudadanos
      </div>
      <h1>¿Sin ideas? <span>Prueba uno de nuestros desafíos</span></h1>
      <p>Cada desafío representa una problemática real de tu comunidad. Acéptalo, crea una propuesta inspirada en él y gana XP, reputación y hasta insignias exclusivas.</p>
    </div>

    <div class="filters-bar">
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-ghost active" data-dif="" onclick="Desafios.filterDificultad('', this)">Todas</button>
        <button class="btn btn-sm btn-ghost" data-dif="facil" onclick="Desafios.filterDificultad('facil', this)">Fácil</button>
        <button class="btn btn-sm btn-ghost" data-dif="medio" onclick="Desafios.filterDificultad('medio', this)">Medio</button>
        <button class="btn btn-sm btn-ghost" data-dif="dificil" onclick="Desafios.filterDificultad('dificil', this)">Difícil</button>
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-left:auto">
        <button class="btn btn-sm btn-ghost active" data-cat="0" onclick="Desafios.filterCat(0, this)">Todas las categorías</button>
        <?php foreach ($categorias as $cat): ?>
        <button class="btn btn-sm btn-ghost" data-cat="<?= $cat['id'] ?>" onclick="Desafios.filterCat(<?= $cat['id'] ?>, this)" style="gap:.35rem">
          <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
          <?= htmlspecialchars($cat['nombre']) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="desafios-grid" id="desafiosGrid"></div>

  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/desafios.js"></script>
<script>Desafios.init(<?= $usuarioLogueado ? 'true' : 'false' ?>);</script>
</body>
</html>
