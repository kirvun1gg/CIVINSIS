<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ranking – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
  <link rel="stylesheet" href="css/ranking.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'ranking'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:820px">

    <div class="debates-hero">
      <div class="debates-hero-badge" style="background:rgba(255,224,102,.14);border-color:rgba(255,224,102,.3);color:#a88600">
        <i class="fas fa-ranking-star"></i> Ranking en tiempo real
      </div>
      <h1>¿Quién está <span>marcando la diferencia?</span></h1>
      <p>Explora los distintos rankings de la comunidad. Cambia de categoría para ver quién lidera en cada aspecto de la participación ciudadana.</p>
    </div>

    <div class="ranking-tabs" id="rankingTabs"></div>

    <div class="ranking-panel">
      <div class="ranking-panel-header" id="rankingPanelHeader"></div>
      <div id="rankingList"></div>
      <div id="rankingMiPosicion"></div>
    </div>

  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/ranking.js"></script>
<script>Ranking.init(<?= $usuarioLogueado ? (int)$usuarioId : 'null' ?>);</script>
</body>
</html>
