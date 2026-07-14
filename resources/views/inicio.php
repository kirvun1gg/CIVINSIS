<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
  <link rel="stylesheet" href="css/inicio.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'inicio'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 1.5rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:1140px">
    <div id="actividadPanel">
      <div class="inicio-skeleton">
        <div class="skeleton" style="height:120px;border-radius:20px;margin-bottom:1.5rem"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
          <div class="skeleton" style="height:200px;border-radius:16px"></div>
          <div class="skeleton" style="height:200px;border-radius:16px"></div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/inicio.js"></script>
<script>CentroActividad.init();</script>
</body>
</html>
