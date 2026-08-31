<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.privacidad.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/privacidad.css">
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-lock"></i> <?= __('civinsis.privacidad.badge') ?></div>
    <h1><?= __('civinsis.privacidad.titulo') ?></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> <?= __('civinsis.privacidad.meta_actualizacion') ?></span>
      <span><i class="fas fa-clock"></i> <?= __('civinsis.privacidad.meta_lectura') ?></span>
      <span><i class="fas fa-shield-alt"></i> <?= __('civinsis.privacidad.meta_protegidos') ?></span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i><?= __('civinsis.privacidad.toc_contenido') ?></div>
      <nav id="tocNav">
        <a href="#introduccion"><i class="fas fa-info-circle"></i> <?= __('civinsis.privacidad.toc_introduccion') ?></a>
        <a href="#datos-recopilados"><i class="fas fa-database"></i> <?= __('civinsis.privacidad.toc_datos_recopilados') ?></a>
        <a href="#uso-datos"><i class="fas fa-cogs"></i> <?= __('civinsis.privacidad.toc_uso_datos') ?></a>
        <a href="#base-legal"><i class="fas fa-gavel"></i> <?= __('civinsis.privacidad.toc_base_legal') ?></a>
        <a href="#compartir"><i class="fas fa-share-alt"></i> <?= __('civinsis.privacidad.toc_compartir') ?></a>
        <a href="#cookies"><i class="fas fa-cookie-bite"></i> <?= __('civinsis.privacidad.toc_cookies') ?></a>
        <a href="#derechos"><i class="fas fa-user-shield"></i> <?= __('civinsis.privacidad.toc_derechos') ?></a>
        <a href="#retencion"><i class="fas fa-clock"></i> <?= __('civinsis.privacidad.toc_retencion') ?></a>
        <a href="#seguridad"><i class="fas fa-lock"></i> <?= __('civinsis.privacidad.toc_seguridad') ?></a>
        <a href="#contacto-dpo"><i class="fas fa-envelope"></i> <?= __('civinsis.privacidad.toc_contacto_dpo') ?></a>
      </nav>
    </aside>

    <main class="legal-content">


      <div class="legal-section" id="introduccion">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-info-circle"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s01_num') ?></div><h2><?= __('civinsis.privacidad.s01_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s01_p1') ?></p>
        <p><?= __('civinsis.privacidad.s01_p2') ?></p>
      </div>

      <div class="legal-section" id="datos-recopilados">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-database"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s02_num') ?></div><h2><?= __('civinsis.privacidad.s02_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s02_intro') ?></p>
        <table class="data-table">
          <thead>
            <tr><th><?= __('civinsis.privacidad.tabla_tipo_dato') ?></th><th><?= __('civinsis.privacidad.tabla_ejemplos') ?></th><th><?= __('civinsis.privacidad.tabla_finalidad') ?></th></tr>
          </thead>
          <tbody>
            <tr><td><strong><?= __('civinsis.privacidad.dato_registro') ?></strong></td><td><?= __('civinsis.privacidad.dato_registro_ej') ?></td><td><?= __('civinsis.privacidad.dato_registro_fin') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.dato_perfil') ?></strong></td><td><?= __('civinsis.privacidad.dato_perfil_ej') ?></td><td><?= __('civinsis.privacidad.dato_perfil_fin') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.dato_contenido') ?></strong></td><td><?= __('civinsis.privacidad.dato_contenido_ej') ?></td><td><?= __('civinsis.privacidad.dato_contenido_fin') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.dato_tecnico') ?></strong></td><td><?= __('civinsis.privacidad.dato_tecnico_ej') ?></td><td><?= __('civinsis.privacidad.dato_tecnico_fin') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.dato_cookies') ?></strong></td><td><?= __('civinsis.privacidad.dato_cookies_ej') ?></td><td><?= __('civinsis.privacidad.dato_cookies_fin') ?></td></tr>
          </tbody>
        </table>
        <p><?= __('civinsis.privacidad.s02_p2') ?></p>
      </div>

      <div class="legal-section" id="uso-datos">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-cogs"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s03_num') ?></div><h2><?= __('civinsis.privacidad.s03_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s03_intro') ?></p>
        <ul>
          <li><?= __('civinsis.privacidad.s03_li1') ?></li>
          <li><?= __('civinsis.privacidad.s03_li2') ?></li>
          <li><?= __('civinsis.privacidad.s03_li3') ?></li>
          <li><?= __('civinsis.privacidad.s03_li4') ?></li>
          <li><?= __('civinsis.privacidad.s03_li5') ?></li>
        </ul>
        <p><?= __('civinsis.privacidad.s03_p2') ?></p>
      </div>

      <div class="legal-section" id="base-legal">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-gavel"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s04_num') ?></div><h2><?= __('civinsis.privacidad.s04_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s04_intro') ?></p>
        <ul>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s04_li1_strong') ?></strong> <?= __('civinsis.privacidad.s04_li1') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s04_li2_strong') ?></strong> <?= __('civinsis.privacidad.s04_li2') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s04_li3_strong') ?></strong> <?= __('civinsis.privacidad.s04_li3') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s04_li4_strong') ?></strong> <?= __('civinsis.privacidad.s04_li4') ?></li>
        </ul>
      </div>

      <div class="legal-section" id="compartir">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-share-alt"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s05_num') ?></div><h2><?= __('civinsis.privacidad.s05_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s05_intro') ?></p>
        <ul>
          <li><?= __('civinsis.privacidad.s05_li1') ?></li>
          <li><?= __('civinsis.privacidad.s05_li2') ?></li>
          <li><?= __('civinsis.privacidad.s05_li3') ?></li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-shield-alt"></i>
          <div><?= __('civinsis.privacidad.s05_tip') ?></div>
        </div>
      </div>

      <div class="legal-section" id="cookies">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-cookie-bite"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s06_num') ?></div><h2><?= __('civinsis.privacidad.s06_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s06_intro') ?></p>
        <table class="data-table">
          <thead><tr><th><?= __('civinsis.privacidad.tabla_tipo') ?></th><th><?= __('civinsis.privacidad.tabla_proposito') ?></th><th><?= __('civinsis.privacidad.tabla_duracion') ?></th></tr></thead>
          <tbody>
            <tr><td><strong><?= __('civinsis.privacidad.cookie_sesion') ?></strong></td><td><?= __('civinsis.privacidad.cookie_sesion_prop') ?></td><td><?= __('civinsis.privacidad.cookie_sesion_dur') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.cookie_pref') ?></strong></td><td><?= __('civinsis.privacidad.cookie_pref_prop') ?></td><td><?= __('civinsis.privacidad.cookie_pref_dur') ?></td></tr>
            <tr><td><strong><?= __('civinsis.privacidad.cookie_analisis') ?></strong></td><td><?= __('civinsis.privacidad.cookie_analisis_prop') ?></td><td><?= __('civinsis.privacidad.cookie_analisis_dur') ?></td></tr>
          </tbody>
        </table>
        <p><?= __('civinsis.privacidad.s06_p2') ?></p>
      </div>

      <div class="legal-section" id="derechos">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-user-shield"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s07_num') ?></div><h2><?= __('civinsis.privacidad.s07_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s07_intro') ?></p>
        <ul>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s07_li1_strong') ?></strong> <?= __('civinsis.privacidad.s07_li1') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s07_li2_strong') ?></strong> <?= __('civinsis.privacidad.s07_li2') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s07_li3_strong') ?></strong> <?= __('civinsis.privacidad.s07_li3') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s07_li4_strong') ?></strong> <?= __('civinsis.privacidad.s07_li4') ?></li>
          <li><strong style="color:var(--text)"><?= __('civinsis.privacidad.s07_li5_strong') ?></strong> <?= __('civinsis.privacidad.s07_li5') ?></li>
        </ul>
        <p><?= __('civinsis.privacidad.s07_p2_pre') ?> <a href="contacto.php" style="color:var(--naranja);text-decoration:underline"><?= __('civinsis.privacidad.s07_p2_link') ?></a>.</p>
      </div>

      <div class="legal-section" id="retencion">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-clock"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s08_num') ?></div><h2><?= __('civinsis.privacidad.s08_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s08_p1') ?></p>
        <p><?= __('civinsis.privacidad.s08_p2') ?></p>
      </div>

      <div class="legal-section" id="seguridad">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-lock"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s09_num') ?></div><h2><?= __('civinsis.privacidad.s09_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s09_intro') ?></p>
        <ul>
          <li><?= __('civinsis.privacidad.s09_li1') ?></li>
          <li><?= __('civinsis.privacidad.s09_li2') ?></li>
          <li><?= __('civinsis.privacidad.s09_li3') ?></li>
          <li><?= __('civinsis.privacidad.s09_li4') ?></li>
        </ul>
      </div>

      <div class="legal-section" id="contacto-dpo">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-envelope"></i></div>
          <div><div class="legal-section-num"><?= __('civinsis.privacidad.s10_num') ?></div><h2><?= __('civinsis.privacidad.s10_titulo') ?></h2></div>
        </div>
        <p><?= __('civinsis.privacidad.s10_p1_pre') ?> <a href="contacto.php" style="color:var(--naranja);text-decoration:underline"><?= __('civinsis.privacidad.s10_p1_link') ?></a>.</p>
      </div>

      <div class="legal-related">
        <a href="terminos.php" class="legal-related-card">
          <div class="icon green"><i class="fas fa-file-contract"></i></div>
          <div><strong><?= __('civinsis.privacidad.rel_terminos_titulo') ?></strong><span><?= __('civinsis.privacidad.rel_terminos_desc') ?></span></div>
        </a>
        <a href="comunidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-users"></i></div>
          <div><strong><?= __('civinsis.privacidad.rel_comunidad_titulo') ?></strong><span><?= __('civinsis.privacidad.rel_comunidad_desc') ?></span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon green"><i class="fas fa-envelope"></i></div>
          <div><strong><?= __('civinsis.privacidad.rel_contacto_titulo') ?></strong><span><?= __('civinsis.privacidad.rel_contacto_desc') ?></span></div>
        </a>
      </div>

    </main>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>
<script src="js/legal-pages.js"></script>
</body>
</html>
