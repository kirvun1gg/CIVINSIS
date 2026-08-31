<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.terminos.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/terminos.css">
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero -->
<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-file-contract"></i> <?= __('civinsis.terminos.badge') ?></div>
    <h1><?= __('civinsis.terminos.titulo') ?></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> <?= __('civinsis.terminos.meta_actualizacion') ?></span>
      <span><i class="fas fa-clock"></i> <?= __('civinsis.terminos.meta_lectura') ?></span>
      <span><i class="fas fa-globe-americas"></i> <?= __('civinsis.terminos.meta_aplicable') ?></span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <!-- Sidebar TOC -->
    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i><?= __('civinsis.terminos.toc_contenido') ?></div>
      <nav id="tocNav">
        <a href="#aceptacion"><i class="fas fa-check-circle"></i> <?= __('civinsis.terminos.toc_aceptacion') ?></a>
        <a href="#uso-plataforma"><i class="fas fa-laptop"></i> <?= __('civinsis.terminos.toc_uso_plataforma') ?></a>
        <a href="#cuentas"><i class="fas fa-user-circle"></i> <?= __('civinsis.terminos.toc_cuentas') ?></a>
        <a href="#contenido"><i class="fas fa-file-alt"></i> <?= __('civinsis.terminos.toc_contenido_pub') ?></a>
        <a href="#propiedad"><i class="fas fa-copyright"></i> <?= __('civinsis.terminos.toc_propiedad') ?></a>
        <a href="#prohibiciones"><i class="fas fa-ban"></i> <?= __('civinsis.terminos.toc_prohibiciones') ?></a>
        <a href="#privacidad"><i class="fas fa-shield-alt"></i> <?= __('civinsis.terminos.toc_privacidad') ?></a>
        <a href="#responsabilidad"><i class="fas fa-balance-scale"></i> <?= __('civinsis.terminos.toc_responsabilidad') ?></a>
        <a href="#modificaciones"><i class="fas fa-edit"></i> <?= __('civinsis.terminos.toc_modificaciones') ?></a>
        <a href="#contacto"><i class="fas fa-envelope"></i> <?= __('civinsis.terminos.toc_contacto') ?></a>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="legal-content">

      <div class="legal-section" id="aceptacion">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s01_num') ?></div>
            <h2><?= __('civinsis.terminos.s01_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s01_p1') ?></p>

      </div>

      <div class="legal-section" id="uso-plataforma">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-laptop"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s02_num') ?></div>
            <h2><?= __('civinsis.terminos.s02_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s02_p1') ?></p>
        <ul>
          <li><?= __('civinsis.terminos.s02_li1') ?></li>
          <li><?= __('civinsis.terminos.s02_li2') ?></li>
          <li><?= __('civinsis.terminos.s02_li3') ?></li>
          <li><?= __('civinsis.terminos.s02_li4') ?></li>
          <li><?= __('civinsis.terminos.s02_li5') ?></li>
        </ul>
        <p><?= __('civinsis.terminos.s02_p2') ?></p>
      </div>

      <div class="legal-section" id="cuentas">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-user-circle"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s03_num') ?></div>
            <h2><?= __('civinsis.terminos.s03_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s03_p1') ?></p>
        <ul>
          <li><?= __('civinsis.terminos.s03_li1') ?></li>
          <li><?= __('civinsis.terminos.s03_li2') ?></li>
          <li><?= __('civinsis.terminos.s03_li3') ?></li>
          <li><?= __('civinsis.terminos.s03_li4') ?></li>
        </ul>
        <p><?= __('civinsis.terminos.s03_p2') ?></p>
      </div>

      <div class="legal-section" id="contenido">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-file-alt"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s04_num') ?></div>
            <h2><?= __('civinsis.terminos.s04_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s04_p1') ?></p>
        <p><?= __('civinsis.terminos.s04_p2') ?></p>
        <ul>
          <li><?= __('civinsis.terminos.s04_li1') ?></li>
          <li><?= __('civinsis.terminos.s04_li2') ?></li>
          <li><?= __('civinsis.terminos.s04_li3') ?></li>
          <li><?= __('civinsis.terminos.s04_li4') ?></li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-exclamation-triangle" style="color:var(--naranja)"></i>
          <div><?= __('civinsis.terminos.s04_tip') ?></div>
        </div>
      </div>

      <div class="legal-section" id="propiedad">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-copyright"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s05_num') ?></div>
            <h2><?= __('civinsis.terminos.s05_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s05_p1') ?></p>
        <p><?= __('civinsis.terminos.s05_p2') ?></p>
      </div>

      <div class="legal-section" id="prohibiciones">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-ban"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s06_num') ?></div>
            <h2><?= __('civinsis.terminos.s06_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s06_p1') ?></p>
        <ul>
          <li><?= __('civinsis.terminos.s06_li1') ?></li>
          <li><?= __('civinsis.terminos.s06_li2') ?></li>
          <li><?= __('civinsis.terminos.s06_li3') ?></li>
          <li><?= __('civinsis.terminos.s06_li4') ?></li>
          <li><?= __('civinsis.terminos.s06_li5') ?></li>
          <li><?= __('civinsis.terminos.s06_li6') ?></li>
        </ul>
      </div>

      <div class="legal-section" id="privacidad">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-shield-alt"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s07_num') ?></div>
            <h2><?= __('civinsis.terminos.s07_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s07_p1_pre') ?> <a href="privacidad.php" style="color:var(--verde);text-decoration:underline"><?= __('civinsis.terminos.s07_p1_link') ?></a><?= __('civinsis.terminos.s07_p1_post') ?></p>
        <p><?= __('civinsis.terminos.s07_p2') ?></p>
      </div>

      <div class="legal-section" id="responsabilidad">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-balance-scale"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s08_num') ?></div>
            <h2><?= __('civinsis.terminos.s08_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s08_p1') ?></p>
        <p><?= __('civinsis.terminos.s08_p2') ?></p>
      </div>

      <div class="legal-section" id="modificaciones">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-edit"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s09_num') ?></div>
            <h2><?= __('civinsis.terminos.s09_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s09_p1') ?></p>
        <p><?= __('civinsis.terminos.s09_p2') ?></p>
      </div>

      <div class="legal-section" id="contacto">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="legal-section-num"><?= __('civinsis.terminos.s10_num') ?></div>
            <h2><?= __('civinsis.terminos.s10_titulo') ?></h2>
          </div>
        </div>
        <p><?= __('civinsis.terminos.s10_p1_pre') ?> <a href="contacto.php" style="color:var(--verde);text-decoration:underline"><?= __('civinsis.terminos.s10_p1_link') ?></a></p>
      </div>

      <!-- Related pages -->
      <div class="legal-related">
        <a href="privacidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-lock"></i></div>
          <div><strong><?= __('civinsis.terminos.rel_privacidad_titulo') ?></strong><span><?= __('civinsis.terminos.rel_privacidad_desc') ?></span></div>
        </a>
        <a href="comunidad.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-users"></i></div>
          <div><strong><?= __('civinsis.terminos.rel_comunidad_titulo') ?></strong><span><?= __('civinsis.terminos.rel_comunidad_desc') ?></span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-envelope"></i></div>
          <div><strong><?= __('civinsis.terminos.rel_contacto_titulo') ?></strong><span><?= __('civinsis.terminos.rel_contacto_desc') ?></span></div>
        </a>
      </div>

    </main>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>

<script src="js/legal-pages.js"></script>
</body>
</html>
