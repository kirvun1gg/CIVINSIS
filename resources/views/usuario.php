<?php
$perfilId = $perfilId ?? intval(request('id'));
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.usuario.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/cosmeticos.css">
  <link rel="stylesheet" href="css/marcos-gsap.css">
  <link rel="stylesheet" href="css/fondos.css">
  <link rel="stylesheet" href="css/efectos.css">
  <link rel="stylesheet" href="css/perfil.css">
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
  <script src="js/fondos.js" defer></script>
  <script src="js/efectos-gsap.js" defer></script>
  <script src="js/efectos-eventos.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/MotionPathPlugin.min.js" defer></script>
  <script src="js/marcos-gsap.js" defer></script>
  <script src="js/marcos-descubrimiento.js" defer></script>
</head>
<body data-perfil-id="<?= $perfilId ?>">

<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero de perfil público -->
<section class="pf-hero" id="publicHero">
  <div class="container pf-hero-inner">
    <div class="pf-header-card">

      <div class="pf-header-main">
        <div class="pf-avatar-cluster">
          <div class="pf-avatar-box profile-avatar" id="pubAvatar">
            <span id="pubInitials">?</span>
          </div>
        </div>

        <div class="pf-info-cluster">
          <div class="pf-name-row">
            <h1 class="pf-display-name" id="pubName"><?= __('civinsis.usuario.cargando') ?></h1>
            <span class="pf-insignia-badge" id="pubInsigniaDisplay">🌱</span>
          </div>

          <div class="pf-badges-row" id="pubTitleWrap"></div>

          <div class="pf-frase-box" id="pubBioBox" style="display:none">
            <i class="fas fa-quote-left"></i>
            <span id="pubBio"></span>
          </div>

          <div class="pf-meta-row">
            <div class="pf-meta-item">
              <i class="fas fa-calendar-check"></i>
              <span id="pubMiembroDesde"><?= __('civinsis.usuario.ciudadano_activo') ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats de impacto -->
      <div class="pf-stats-grid">
        <div class="pf-stat-card propuestas">
          <div class="pf-stat-icon-wrap"><i class="fas fa-lightbulb"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatProp">0</span>
            <span class="pf-stat-label"><?= __('civinsis.nav.propuestas') ?></span>
          </div>
        </div>

        <div class="pf-stat-card votos">
          <div class="pf-stat-icon-wrap"><i class="fas fa-heart"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatVotos">0</span>
            <span class="pf-stat-label"><?= __('civinsis.usuario.votos_recibidos') ?></span>
          </div>
        </div>

        <div class="pf-stat-card vistas">
          <div class="pf-stat-icon-wrap"><i class="fas fa-comments"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatCom">0</span>
            <span class="pf-stat-label"><?= __('civinsis.propuesta.comentarios') ?></span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Contenido del perfil público -->
<div class="container pf-content-area" style="max-width:960px">

  <!-- Widget de nivel y ciudadanía -->
  <div class="nivel-widget" id="pubNivelWidget" style="margin-bottom:1.5rem">
    <div class="nivel-header">
      <div class="nivel-badge" id="pubNivelBadge">1</div>
      <div class="nivel-info">
        <div class="nivel-nombre"><?= __('civinsis.usuario.nivel_ciudadania') ?></div>
        <div class="nivel-num"><?= __('civinsis.usuario.nivel') ?> <span id="pubNivel">1</span></div>
      </div>
    </div>
    <div class="xp-bar-wrap">
      <div class="xp-bar-track">
        <div class="xp-bar-fill" id="pubXpFill" style="width:0%"></div>
      </div>
      <div class="xp-labels">
        <span id="pubXpActual">0 XP</span>
        <span class="xp-pct" id="pubXpPct">0%</span>
        <span id="pubXpSig">100 XP</span>
      </div>
    </div>
    <div class="gam-stats-row">
      <div class="gam-stat-box">
        <span class="icon">⭐</span>
        <div class="val" id="pubRep">0</div>
        <div class="lbl"><?= __('civinsis.usuario.reputacion') ?></div>
      </div>
      <div class="gam-stat-box">
        <span class="icon">🔥</span>
        <div class="val" id="pubRacha">0</div>
        <div class="lbl"><?= __('civinsis.usuario.racha_dias') ?></div>
      </div>
    </div>
  </div>

  <!-- Insignias obtenidas -->
  <div class="pf-card" style="margin-bottom:1.5rem">
    <h3 class="pf-card-title">
      <i class="fas fa-shield-alt" style="color:var(--verde)"></i> <?= __('civinsis.usuario.insignias_desbloqueadas') ?>
    </h3>
    <p class="pf-card-subtitle"><?= __('civinsis.usuario.insignias_desc') ?></p>
    <div class="insignias-grid" id="pubInsignias">
      <p style="color:var(--text-muted);font-size:.85rem"><?= __('civinsis.usuario.sin_insignias') ?></p>
    </div>
  </div>

  <!-- Logros desbloqueados -->
  <div class="pf-card">
    <h3 class="pf-card-title">
      <i class="fas fa-medal" style="color:var(--naranja)"></i> <?= __('civinsis.usuario.logros_ciudadania') ?>
      (<span id="pubLogrosCount">0</span>)
    </h3>
    <p class="pf-card-subtitle"><?= __('civinsis.usuario.logros_desc') ?></p>
    <div class="logros-grid" id="pubLogros"></div>
  </div>

</div>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/utils.js"></script>
<script src="js/usuario.js"></script>
</body>
</html>
