<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.comunidad.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/comunidad.css">
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-users"></i> <?= __('civinsis.comunidad.badge') ?></div>
    <h1><?= __('civinsis.comunidad.titulo') ?></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> <?= __('civinsis.comunidad.meta_actualizacion') ?></span>
      <span><i class="fas fa-clock"></i> <?= __('civinsis.comunidad.meta_lectura') ?></span>
      <span><i class="fas fa-heart"></i> <?= __('civinsis.comunidad.meta_sana') ?></span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i><?= __('civinsis.comunidad.toc_contenido') ?></div>
      <nav id="tocNav">
        <a href="#bienvenida"><i class="fas fa-hand-wave"></i> <?= __('civinsis.comunidad.toc_bienvenida') ?></a>
        <a href="#valores"><i class="fas fa-heart"></i> <?= __('civinsis.comunidad.toc_valores') ?></a>
        <a href="#propuestas"><i class="fas fa-file-alt"></i> <?= __('civinsis.comunidad.toc_propuestas') ?></a>
        <a href="#comentarios"><i class="fas fa-comments"></i> <?= __('civinsis.comunidad.toc_comentarios') ?></a>
        <a href="#conducta"><i class="fas fa-check-circle"></i> <?= __('civinsis.comunidad.toc_conducta') ?></a>
        <a href="#prohibido"><i class="fas fa-ban"></i> <?= __('civinsis.comunidad.toc_prohibido') ?></a>
        <a href="#moderacion"><i class="fas fa-shield-alt"></i> <?= __('civinsis.comunidad.toc_moderacion') ?></a>
        <a href="#sanciones"><i class="fas fa-gavel"></i> <?= __('civinsis.comunidad.toc_sanciones') ?></a>
        <a href="#reportar"><i class="fas fa-flag"></i> <?= __('civinsis.comunidad.toc_reportar') ?></a>
      </nav>
    </aside>

    <main class="legal-content">
      <div class="legal-section" id="bienvenida">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-door-open"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s01_num') ?></div><h2><?= __('civinsis.comunidad.s01_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s01_p1') ?></p>
        <p><?= __('civinsis.comunidad.s01_p2') ?></p>
      </div>

      <div class="legal-section" id="valores">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-heart"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s02_num') ?></div><h2><?= __('civinsis.comunidad.s02_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s02_intro') ?></p>
        <div class="values-grid">
          <div class="value-card"><div class="vi">🤝</div><strong><?= __('civinsis.comunidad.valor_respeto_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_respeto_desc') ?></span></div>
          <div class="value-card"><div class="vi">💡</div><strong><?= __('civinsis.comunidad.valor_creatividad_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_creatividad_desc') ?></span></div>
          <div class="value-card"><div class="vi">🌍</div><strong><?= __('civinsis.comunidad.valor_inclusion_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_inclusion_desc') ?></span></div>
          <div class="value-card"><div class="vi">⚖️</div><strong><?= __('civinsis.comunidad.valor_honestidad_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_honestidad_desc') ?></span></div>
          <div class="value-card"><div class="vi">🚀</div><strong><?= __('civinsis.comunidad.valor_accion_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_accion_desc') ?></span></div>
          <div class="value-card"><div class="vi">🛡️</div><strong><?= __('civinsis.comunidad.valor_seguridad_titulo') ?></strong><span><?= __('civinsis.comunidad.valor_seguridad_desc') ?></span></div>
        </div>
      </div>

      <div class="legal-section" id="propuestas">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-file-alt"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s03_num') ?></div><h2><?= __('civinsis.comunidad.s03_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s03_intro') ?></p>
        <ul>
          <li><?= __('civinsis.comunidad.s03_li1') ?></li>
          <li><?= __('civinsis.comunidad.s03_li2') ?></li>
          <li><?= __('civinsis.comunidad.s03_li3') ?></li>
          <li><?= __('civinsis.comunidad.s03_li4') ?></li>
          <li><?= __('civinsis.comunidad.s03_li5') ?></li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-lightbulb"></i>
          <div><?= __('civinsis.comunidad.s03_tip') ?></div>
        </div>
      </div>

      <div class="legal-section" id="comentarios">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-comments"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s04_num') ?></div><h2><?= __('civinsis.comunidad.s04_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s04_intro') ?></p>
        <ul>
          <li><?= __('civinsis.comunidad.s04_li1') ?></li>
          <li><?= __('civinsis.comunidad.s04_li2') ?></li>
          <li><?= __('civinsis.comunidad.s04_li3') ?></li>
          <li><?= __('civinsis.comunidad.s04_li4') ?></li>
          <li><?= __('civinsis.comunidad.s04_li5') ?></li>
        </ul>
      </div>

      <div class="legal-section" id="conducta">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-check-circle"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s05_num') ?></div><h2><?= __('civinsis.comunidad.s05_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s05_intro') ?></p>
        <ul>
          <li><?= __('civinsis.comunidad.s05_li1') ?></li>
          <li><?= __('civinsis.comunidad.s05_li2') ?></li>
          <li><?= __('civinsis.comunidad.s05_li3') ?></li>
          <li><?= __('civinsis.comunidad.s05_li4') ?></li>
          <li><?= __('civinsis.comunidad.s05_li5') ?></li>
        </ul>
      </div>

      <div class="legal-section" id="prohibido">
        <div class="legal-section-header">
          <div class="legal-section-icon red"><i class="fas fa-ban"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s06_num') ?></div><h2><?= __('civinsis.comunidad.s06_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s06_intro_pre') ?> <strong style="color:#e74c3c"><?= __('civinsis.comunidad.s06_intro_strong') ?></strong> <?= __('civinsis.comunidad.s06_intro_post') ?></p>
        <ul class="warn">
          <li><?= __('civinsis.comunidad.s06_li1') ?></li>
          <li><?= __('civinsis.comunidad.s06_li2') ?></li>
          <li><?= __('civinsis.comunidad.s06_li3') ?></li>
          <li><?= __('civinsis.comunidad.s06_li4') ?></li>
          <li><?= __('civinsis.comunidad.s06_li5') ?></li>
          <li><?= __('civinsis.comunidad.s06_li6') ?></li>
          <li><?= __('civinsis.comunidad.s06_li7') ?></li>
        </ul>
        <div class="legal-highlight danger">
          <i class="fas fa-exclamation-circle"></i>
          <div><?= __('civinsis.comunidad.s06_warn') ?></div>
        </div>
      </div>

      <div class="legal-section" id="moderacion">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-shield-alt"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s07_num') ?></div><h2><?= __('civinsis.comunidad.s07_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s07_intro') ?></p>
        <ul>
          <li><?= __('civinsis.comunidad.s07_li1') ?></li>
          <li><?= __('civinsis.comunidad.s07_li2') ?></li>
          <li><?= __('civinsis.comunidad.s07_li3') ?></li>
          <li><?= __('civinsis.comunidad.s07_li4') ?></li>
        </ul>
        <p><?= __('civinsis.comunidad.s07_p2') ?></p>
      </div>

      <div class="legal-section" id="sanciones">
        <div class="legal-section-header">
          <div class="legal-section-icon red"><i class="fas fa-gavel"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s08_num') ?></div><h2><?= __('civinsis.comunidad.s08_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s08_intro') ?></p>
        <ul>
          <li><strong style="color:var(--text)"><?= __('civinsis.comunidad.s08_li1_strong') ?></strong> <?= __('civinsis.comunidad.s08_li1') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.comunidad.s08_li2_strong') ?></strong> <?= __('civinsis.comunidad.s08_li2') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.comunidad.s08_li3_strong') ?></strong> <?= __('civinsis.comunidad.s08_li3') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.comunidad.s08_li4_strong') ?></strong> <?= __('civinsis.comunidad.s08_li4') ?></li>
        </ul>
        <div class="legal-highlight warn">
          <i class="fas fa-info-circle"></i>
          <div><?= __('civinsis.comunidad.s08_tip') ?></div>
        </div>
      </div>

      <div class="legal-section" id="reportar">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-flag"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.comunidad.s09_num') ?></div><h2><?= __('civinsis.comunidad.s09_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.comunidad.s09_intro') ?></p>
        <ul>
          <li><?= __('civinsis.comunidad.s09_li1') ?></li>
          <li><?= __('civinsis.comunidad.s09_li2_pre') ?> <a href="contacto.php?asunto=Reporte" style="color:var(--verde);text-decoration:underline"><?= __('civinsis.comunidad.s09_li2_link') ?></a>.</li>
          <li><?= __('civinsis.comunidad.s09_li3') ?></li>
        </ul>
        <p><?= __('civinsis.comunidad.s09_p2') ?></p>
      </div>

      <div class="legal-related">
        <a href="terminos.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-file-contract"></i></div>
          <div><strong><?= __('civinsis.comunidad.rel_terminos_titulo') ?></strong><span><?= __('civinsis.comunidad.rel_terminos_desc') ?></span></div>
        </a>
        <a href="privacidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-lock"></i></div>
          <div><strong><?= __('civinsis.comunidad.rel_privacidad_titulo') ?></strong><span><?= __('civinsis.comunidad.rel_privacidad_desc') ?></span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-envelope"></i></div>
          <div><strong><?= __('civinsis.comunidad.rel_contacto_titulo') ?></strong><span><?= __('civinsis.comunidad.rel_contacto_desc') ?></span></div>
        </a>
      </div>

    </main>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>
<script src="js/legal-pages.js"></script>
</body>
</html>
