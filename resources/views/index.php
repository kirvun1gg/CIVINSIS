<?php
// index.php - Página principal de CIVINSIS
// $usuarioLogueado lo inyecta el View Composer global en TODAS las vistas
// (app/Providers/AppServiceProvider.php::boot()). El valor por defecto de
// abajo nunca se usa en producción - solo evita que el IDE marque la
// variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.index.titulo_pagina') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/index.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'logo'])->render(); ?>

<!-- ── HERO ────────────────────────────────────────────────── -->
<section class="hero" id="hero">
  <div class="hero-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-grid"></div>
  </div>

  <div class="hero-content">
    <div class="hero-badge">
      <i class="fas fa-bolt"></i>
      <?= __('civinsis.index.hero_badge') ?>
    </div>

    <!-- CIVINSIS — efectos creativos por letra -->
    <div class="civinsis-word" aria-label="CIVINSIS" id="civinsisWord">
      <span class="civ-l civ-C">C</span>
      <span class="civ-l civ-I1">I</span>
      <span class="civ-l civ-V">V</span>
      <span class="civ-l civ-I2">I</span>
      <span class="civ-l civ-N">N</span>
      <span class="civ-l civ-S1">S</span>
      <span class="civ-l civ-I3">I</span>
      <span class="civ-l civ-S2">S</span>
    </div>

    <h1 class="hero-title">
      <?= __('civinsis.index.hero_titulo_pre') ?> <span class="hl"><?= __('civinsis.index.hero_titulo_hl') ?></span>
    </h1>

    <p class="hero-subtitle">
      <?= __('civinsis.index.hero_subtitulo') ?>
    </p>

    <div class="hero-actions">
      <a href="<?= $usuarioLogueado ? 'crear.php' : 'auth.php?tab=registro' ?>" class="btn btn-primary btn-lg">
        <i class="fas fa-rocket"></i> <?= __('civinsis.index.publica_propuesta') ?>
      </a>
      <a href="dashboard.php" class="btn btn-lg hero-btn-ghost">
        <i class="fas fa-compass"></i> <?= __('civinsis.index.explorar_propuestas') ?>
      </a>
    </div>

    <div class="hero-stats">
      <div class="hero-stat">
        <span class="hero-stat-num" id="statPropuestas">–</span>
        <span class="hero-stat-label"><?= __('civinsis.index.stat_propuestas') ?></span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" id="statUsuarios">–</span>
        <span class="hero-stat-label"><?= __('civinsis.index.stat_usuarios') ?></span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" id="statVotos">–</span>
        <span class="hero-stat-label"><?= __('civinsis.index.stat_votos') ?></span>
      </div>
    </div>
  </div>

  <div class="hero-wave">
    <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path
        d="M0,55 C240,90 480,25 720,55 C960,85 1200,30 1440,58 L1440,90 L0,90 Z"
        fill="var(--bg)" opacity=".85"/>
      <path
        d="M0,70 C300,40 600,85 900,60 C1100,44 1300,72 1440,65 L1440,90 L0,90 Z"
        fill="var(--bg)"/>
    </svg>
  </div>
</section>

<!-- ── PROPUESTAS DESTACADAS ───────────────────────────────── -->
<section class="section section-proposals-bg" id="destacadas">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label"><?= __('civinsis.index.destacadas_label') ?></span>
      <h2 class="section-title"><?= __('civinsis.index.destacadas_titulo') ?></h2>
      <p class="section-desc" style="margin:0 auto"><?= __('civinsis.index.destacadas_desc') ?></p>
    </div>

    <div class="cards-grid animate-stagger" id="proposalsGrid" data-limite="5"></div>
    <div id="pagination" style="margin-top:2rem"></div>

    <div class="text-center" style="margin-top:2.5rem">
      <a href="dashboard.php" class="btn btn-outline btn-lg">
        <i class="fas fa-th-large"></i> <?= __('civinsis.index.ver_todas_propuestas') ?>
      </a>
    </div>
  </div>
</section>

<!-- ── TOP VOTADAS ─────────────────────────────────────────── -->
<section class="section top-votadas-bg" id="top-votadas">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start" class="top-votadas-grid">
      <div class="section-header reveal">
        <span class="section-label"><?= __('civinsis.index.ranking_label') ?></span>
        <h2 class="section-title"><?= __('civinsis.index.ranking_titulo') ?></h2>
        <p class="section-desc"><?= __('civinsis.index.ranking_desc') ?></p>
        <a href="dashboard.php?orden=votos" class="btn btn-outline" style="margin-top:1.5rem">
          <i class="fas fa-trophy"></i> <?= __('civinsis.index.ver_ranking_completo') ?>
        </a>
      </div>
      <div class="reveal">
        <div class="top-ranking-list" id="topProposals">
          <div class="skeleton" style="height:72px;border-radius:16px"></div>
          <div class="skeleton" style="height:72px;border-radius:16px;opacity:.7;margin-top:.85rem"></div>
          <div class="skeleton" style="height:72px;border-radius:16px;opacity:.5;margin-top:.85rem"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CÓMO FUNCIONA ───────────────────────────────────────── -->
<section class="section section-como-bg" id="como-funciona">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label"><?= __('civinsis.index.proceso_label') ?></span>
      <h2 class="section-title"><?= __('civinsis.index.proceso_titulo') ?></h2>
      <p class="section-desc" style="margin:0 auto"><?= __('civinsis.index.proceso_desc') ?></p>
    </div>

    <div class="features-grid animate-stagger">
      <div class="feature-card reveal">
        <div class="feature-icon"><i class="fas fa-user-plus"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.paso1_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.paso1_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon orange"><i class="fas fa-lightbulb"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.paso2_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.paso2_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon"><i class="fas fa-thumbs-up"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.paso3_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.paso3_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon orange"><i class="fas fa-chart-line"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.paso4_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.paso4_desc') ?></p>
      </div>
    </div>
  </div>
</section>

<!-- ── FAQ TEASER ──────────────────────────────────────────── -->
<section class="section" id="faq" style="background:var(--surface)">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label"><?= __('civinsis.index.ayuda_label') ?></span>
      <h2 class="section-title"><?= __('civinsis.index.ayuda_titulo') ?></h2>
      <p class="section-desc" style="margin:0 auto"><?= __('civinsis.index.ayuda_desc') ?></p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;max-width:820px;margin:0 auto 2.5rem" class="animate-stagger">
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon" style="margin:0 auto 1rem"><i class="fas fa-user-plus"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.faq1_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.faq1_desc') ?></p>
      </div>
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon orange" style="margin:0 auto 1rem"><i class="fas fa-shield-alt"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.faq2_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.faq2_desc') ?></p>
      </div>
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon" style="margin:0 auto 1rem"><i class="fas fa-robot"></i></div>
        <h3 class="feature-title"><?= __('civinsis.index.faq3_titulo') ?></h3>
        <p class="feature-desc"><?= __('civinsis.index.faq3_desc') ?></p>
      </div>
    </div>
    <div class="text-center reveal">
      <a href="faq.php" class="btn btn-primary btn-lg">
        <i class="fas fa-question-circle"></i> <?= __('civinsis.index.ver_todas_preguntas') ?>
      </a>
      <a href="contacto.php" class="btn btn-outline btn-lg" style="margin-left:1rem">
        <i class="fas fa-envelope"></i> <?= __('civinsis.index.contactar_equipo') ?>
      </a>
    </div>
  </div>
</section>

<!-- ── CTA ─────────────────────────────────────────────────── -->
<?php if (!$usuarioLogueado): ?>
<section class="section-sm" style="background:var(--grad-primary);color:#fff;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 20% 50%,rgba(255,255,255,.1) 0%,transparent 60%);pointer-events:none"></div>
  <div class="container text-center" style="position:relative;z-index:1">
    <h2 class="reveal" style="font-family:var(--font-display);font-size:clamp(1.4rem,3vw,2.1rem);font-weight:800;margin-bottom:.6rem">
      <?= __('civinsis.index.cta_titulo') ?>
    </h2>
    <p class="reveal" style="opacity:.82;margin-bottom:1.75rem;font-size:.95rem">
      <?= __('civinsis.index.cta_desc') ?>
    </p>
    <div class="reveal" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="auth.php?tab=registro" class="btn btn-lg" style="background:#fff;color:var(--verde-600);font-weight:700">
        <i class="fas fa-rocket"></i> <?= __('civinsis.index.cta_crear_cuenta') ?>
      </a>
      <a href="dashboard.php" class="btn btn-lg" style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.3)">
        <i class="fas fa-compass"></i> <?= __('civinsis.index.cta_explorar_primero') ?>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="js/app.js"></script>
<script src="js/index.js"></script>
</body>
</html>
