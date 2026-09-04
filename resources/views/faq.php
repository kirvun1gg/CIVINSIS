<?php
// $usuarioLogueado y $categorias los inyecta el View Composer global en
// TODAS las vistas (app/Providers/AppServiceProvider.php::boot()). El valor
// por defecto de abajo nunca se usa en producción - solo evita que el IDE
// marque la variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
$categorias      = $categorias ?? collect();
$activeNav = 'faq';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.faq.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero FAQ -->
<section class="faq-hero">
  <div class="faq-hero-bg">
    <div class="faq-orb faq-orb1"></div>
    <div class="faq-orb faq-orb2"></div>
    <div class="faq-grid-decor"></div>
  </div>
  <div class="container faq-hero-content">
    <div class="faq-hero-badge reveal"><i class="fas fa-question-circle"></i> <?= __('civinsis.faq.badge') ?></div>
    <h1 class="faq-hero-title reveal"><?= __('civinsis.faq.titulo') ?></h1>
    <p class="faq-hero-desc reveal"><?= __('civinsis.faq.descripcion') ?></p>
    <div class="faq-search-wrap reveal">
      <div class="faq-search-box">
        <i class="fas fa-search faq-search-icon"></i>
        <input type="text" id="faqSearch" placeholder="<?= __('civinsis.faq.buscar_placeholder') ?>" autocomplete="off">
        <button class="faq-search-clear" id="faqSearchClear" style="display:none"><i class="fas fa-times"></i></button>
      </div>
    </div>
  </div>
  <div class="faq-hero-wave">
    <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,40 C360,90 1080,0 1440,50 L1440,90 L0,90 Z" fill="var(--bg)"/>
    </svg>
  </div>
</section>

<!-- Categorías de FAQ -->
<section class="section" style="background:var(--bg);padding-top:3rem">
  <div class="container">

    <!-- Tabs de categorías -->
    <div class="faq-tabs reveal" id="faqTabs">
      <button class="faq-tab active" data-tab="general"><i class="fas fa-star"></i> <?= __('civinsis.faq.tab_general') ?></button>
      <button class="faq-tab" data-tab="cuenta"><i class="fas fa-user"></i> <?= __('civinsis.faq.tab_cuenta') ?></button>
      <button class="faq-tab" data-tab="propuestas"><i class="fas fa-lightbulb"></i> <?= __('civinsis.faq.tab_propuestas') ?></button>
      <button class="faq-tab" data-tab="comunidad"><i class="fas fa-users"></i> <?= __('civinsis.faq.tab_comunidad') ?></button>
      <button class="faq-tab" data-tab="gamificacion"><i class="fas fa-trophy"></i> <?= __('civinsis.faq.tab_gamificacion') ?></button>
      <button class="faq-tab" data-tab="tecnico"><i class="fas fa-cog"></i> <?= __('civinsis.faq.tab_tecnico') ?></button>
    </div>

    <div class="faq-layout">
      <!-- Lista de preguntas -->
      <div class="faq-main">

        <!-- GENERAL -->
        <div class="faq-category-group" data-cat="general">
          <div class="faq-cat-label"><i class="fas fa-star"></i> <?= __('civinsis.faq.tab_general') ?></div>
          <div class="faq-list" id="faqList">

            <div class="faq-item reveal" data-keywords="civitas plataforma para quien juvenil que es">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="gratis costo precio registro pago">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g2_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="impacto real propuestas llegan autoridades cambio">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="civi asistente ia inteligencia artificial chatbot robot">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g4_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="moderacion automatica censurado contenido ia revision">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g5_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g5_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="salvador el salvador pais local regional">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.g6_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.g6_a') ?></div>
            </div>

          </div>
        </div>

        <!-- CUENTA -->
        <div class="faq-category-group" data-cat="cuenta" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-user"></i> <?= __('civinsis.faq.tab_cuenta') ?></div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="crear cuenta registro pasos como registrarse">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="foto perfil avatar cambiar imagen subir">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c2_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="contrasena cambiar olvide actualizar seguridad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="datos privacidad seguridad personal informacion">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c4_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="eliminar cuenta borrar perfil baja">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c5_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c5_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="titulo marco fondo cosmetico personalizar perfil gamificacion apariencia">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c6_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c6_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="dos cuentas multiples usuarios misma persona duplicada">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.c7_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.c7_a') ?></div>
            </div>

          </div>
        </div>

        <!-- PROPUESTAS -->
        <div class="faq-category-group" data-cat="propuestas" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-lightbulb"></i> <?= __('civinsis.faq.tab_propuestas') ?></div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="cuantas propuestas crear limite publicar cantidad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen foto portada subir propuesta tarjeta personalizar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p2_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="votar voto vez unica retirar quitar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="eliminar borrar propuesta propia autor">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p4_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="editar modificar propuesta publicada cambiar actualizar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p5_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p5_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="xp ganar puntos propuesta comentario voto gamificacion recompensa">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p6_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p6_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="propuesta revision censurada estado moderacion bloqueada">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p7_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.p7_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="categorias tipos propuesta temas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.p8_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem;margin-top:.5rem">
                  <?php foreach ($categorias as $cat): ?>
                  <span style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;padding:.4rem .7rem;background:var(--surface);border-radius:8px">
                    <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
                    <?= htmlspecialchars($cat->translated('nombre')) ?>
                  </span>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- COMUNIDAD -->
        <div class="faq-category-group" data-cat="comunidad" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-users"></i> <?= __('civinsis.faq.tab_comunidad') ?></div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="comentar comentarios responder opinion debate">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.co1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.co1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="reportar contenido inapropiado normas reglas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.co2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <?= __('civinsis.faq.co2_a') ?>
                <a href="contacto.php?asunto=Reporte de contenido" class="btn btn-sm btn-outline" style="margin-top:.75rem"><i class="fas fa-flag"></i> <?= __('civinsis.faq.co2_link') ?></a>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="bloqueo ban cuenta suspendida sancion">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.co3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.co3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="normas reglas comunidad comportamiento conducta">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.co4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.co4_a') ?></div>
            </div>

          </div>
        </div>

        <!-- GAMIFICACIÓN -->
        <div class="faq-category-group" data-cat="gamificacion" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-trophy"></i> <?= __('civinsis.faq.tab_gamificacion') ?></div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="gamificacion xp nivel puntos reputacion logros insignias titulos que es">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="nivel subir xp experiencia como funciona niveles">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam2_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="reputacion que es diferencia xp independiente">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="racha dias consecutivos bonus login acceso streak">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam4_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="misiones diarias semanales completar recompensa objetivos tareas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam5_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam5_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="logros desbloquear como obtener requisitos condiciones">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam6_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam6_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="titulos equipar cambiar color nombre perfil">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam7_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam7_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="ranking top usuarios posicion clasificacion tabla">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam8_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam8_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="cosmeticos marcos fondos desbloquear nivel avatar perfil">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.gam9_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.gam9_a') ?></div>
            </div>

          </div>
        </div>

        <!-- TÉCNICO -->
        <div class="faq-category-group" data-cat="tecnico" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-cog"></i> <?= __('civinsis.faq.tab_tecnico') ?></div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="navegador compatible funciona soporte chrome firefox">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t1_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.t1_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="movil celular app android ios aplicacion telefono">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t2_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.t2_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen no carga error upload subir problema">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t3_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.t3_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="sesion cerrada expiro volver atras seguridad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t4_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.t4_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="lento carga lentitud rendimiento velocidad problema demora">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t5_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer"><?= __('civinsis.faq.t5_a') ?></div>
            </div>

            <div class="faq-item reveal" data-keywords="error pagina no carga problema tecnico bug fallo">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span><?= __('civinsis.faq.t6_q') ?></span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <?= __('civinsis.faq.t6_a') ?>
                <a href="contacto.php?asunto=Problema técnico" class="btn btn-sm btn-outline" style="margin-top:.75rem"><i class="fas fa-bug"></i> <?= __('civinsis.faq.t6_link') ?></a>
              </div>
            </div>

          </div>
        </div>

        <!-- No results -->
        <div class="faq-no-results" id="faqNoResults" style="display:none">
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <p><?= __('civinsis.faq.no_results_pre') ?> "<span id="searchTermDisplay"></span>"</p>
            <a href="contacto.php" class="btn btn-outline" style="margin-top:1rem"><i class="fas fa-envelope"></i> <?= __('civinsis.faq.preguntanos') ?></a>
          </div>
        </div>

      </div>

      <!-- Sidebar de contacto -->
      <aside class="faq-sidebar">
        <div class="faq-sidebar-card">
          <div class="faq-sidebar-icon"><i class="fas fa-headset"></i></div>
          <h3><?= __('civinsis.faq.sidebar_titulo') ?></h3>
          <p><?= __('civinsis.faq.sidebar_desc') ?></p>
          <a href="contacto.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.25rem">
            <i class="fas fa-envelope"></i> <?= __('civinsis.faq.sidebar_contactar') ?>
          </a>
          <button type="button" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:.5rem" onclick="document.getElementById('civiFab')?.click()">
            <i class="fas fa-robot"></i> <?= __('civinsis.faq.sidebar_escribir') ?>
          </button>
        </div>

        <div class="faq-sidebar-card faq-sidebar-stats">
          <h3><i class="fas fa-chart-bar"></i> <?= __('civinsis.faq.numeros_titulo') ?></h3>
          <div class="faq-stat-row">
            <span class="faq-stat-num" id="faqStatProp">–</span>
            <span class="faq-stat-label"><?= __('civinsis.faq.stat_propuestas_publicadas') ?></span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">8</span>
            <span class="faq-stat-label"><?= __('civinsis.faq.stat_categorias_activas') ?></span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">25</span>
            <span class="faq-stat-label"><?= __('civinsis.faq.stat_niveles') ?></span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">100%</span>
            <span class="faq-stat-label"><?= __('civinsis.faq.stat_gratuito') ?></span>
          </div>
        </div>

        <div class="faq-sidebar-card faq-sidebar-tip">
          <div style="font-size:1.75rem;margin-bottom:.75rem">🌿</div>
          <h3><?= __('civinsis.faq.sabias_que') ?></h3>
          <p id="faqTipText"><?= __('civinsis.faq.sabias_que_texto') ?></p>
        </div>
      </aside>
    </div>

    <!-- CTA Final -->
    <?php if (!$usuarioLogueado): ?>
    <div class="faq-cta reveal">
      <div class="faq-cta-inner">
        <h2><?= __('civinsis.faq.cta_titulo') ?></h2>
        <p><?= __('civinsis.faq.cta_desc') ?></p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
          <a href="auth.php?tab=registro" class="btn btn-primary btn-lg"><i class="fas fa-rocket"></i> <?= __('civinsis.faq.cta_crear_cuenta') ?></a>
          <a href="dashboard.php" class="btn btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.4)"><i class="fas fa-compass"></i> <?= __('civinsis.faq.cta_explorar_primero') ?></a>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/faq.js"></script>
</body>
</html>