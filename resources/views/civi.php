<?php
$usuarioLogueado = $usuarioLogueado ?? false;
$usuarioId       = $usuarioId ?? null;
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.nav.civi') ?> – CIVINSIS</title>
  <link rel="icon" type="image/png" href="<?= asset('media/logo.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/civi.css">
</head>
<body data-usuario-id="<?= $usuarioLogueado ? (int)$usuarioId : '' ?>" data-hide-civi-fab="1">

<?php echo view('layouts.navbar', ['activeNav' => 'civi'])->render(); ?>

<main class="civi-page" style="padding-top:var(--nav-height)">
  <aside class="civi-side" id="civiSide">
    <div class="civi-side-head">
      <button class="civi-nueva-btn" id="civiNuevaBtn" type="button">
        <i class="fas fa-plus"></i> <?= __('civinsis.civi_pagina.nueva_conversacion') ?>
      </button>
    </div>
    <div class="civi-side-label"><?= __('civinsis.civi_pagina.conversaciones_titulo') ?></div>
    <div class="civi-side-list" id="civiSideList">
      <div class="civi-side-loading"><?= __('civinsis.civi_pagina.cargando') ?></div>
    </div>
  </aside>

  <section class="civi-main">
    <div class="civi-main-head">
      <button class="civi-side-toggle" id="civiSideToggle" aria-label="<?= __('civinsis.civi_pagina.conversaciones_titulo') ?>">
        <i class="fas fa-bars"></i>
      </button>
      <div class="civi-main-head-ava"><i class="fas fa-robot"></i></div>
      <div>
        <h1><?= __('civinsis.civi_widget.nombre') ?></h1>
        <small><?= __('civinsis.civi_widget.subtitulo') ?></small>
      </div>
    </div>

    <div class="civi-main-body" id="civiMainBody">
      <div class="civi-vacio" id="civiVacio">
        <div class="civi-vacio-ava"><i class="fas fa-robot"></i></div>
        <h2><?= __('civinsis.civi_pagina.vacio_titulo') ?></h2>
        <p><?= __('civinsis.civi_pagina.vacio_descripcion') ?></p>
      </div>
      <div class="civi-msgs" id="civiMsgs" style="display:none"></div>
    </div>

    <form class="civi-main-input" id="civiForm">
      <input id="civiPageInput" type="text" placeholder="<?= __('civinsis.civi_pagina.placeholder') ?>" autocomplete="off">
      <button id="civiPageSend" type="submit" aria-label="<?= __('civinsis.civi_pagina.enviar') ?>"><i class="fas fa-paper-plane"></i></button>
    </form>
  </section>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/civi.js"></script>
</body>
</html>
