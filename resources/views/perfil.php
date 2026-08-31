<?php
// $usuarioNombre, $usuarioRol y $usuarioId los inyecta el View Composer
// global en TODAS las vistas (app/Providers/AppServiceProvider.php::boot()).
// El valor por defecto de abajo nunca se usa en producción - solo evita que
// el IDE marque la variable como indefinida y sirve de red de seguridad.
$usuarioNombre = $usuarioNombre ?? '';
$usuarioRol    = $usuarioRol ?? 'invitado';
$usuarioId     = $usuarioId ?? null;
$iniciales = civinsis_iniciales($usuarioNombre);
$esAdmin   = ($usuarioRol === 'admin' || $usuarioRol === 'moderador');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.perfil.titulo_pagina') ?> – CIVINSIS</title>
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
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'perfil'])->render(); ?>

<!-- ══════════════════════════════════════════════════════════
     HERO DEL PERFIL (HEADER & STATS)
     ══════════════════════════════════════════════════════════ -->
<section class="pf-hero" id="profileHeroBanner">
  <div class="container pf-hero-inner">
    <div class="pf-header-card">

      <!-- Fila Principal: Avatar + Info de Usuario + Acciones -->
      <div class="pf-header-main">
        
        <!-- Avatar y Cámara -->
        <div class="pf-avatar-cluster">
          <div class="pf-avatar-box profile-avatar" id="profileAvatarDisplay">
            <span id="profileInitials"><?= $iniciales ?></span>
          </div>
          <label class="pf-avatar-edit-btn" title="<?= __('civinsis.perfil.cambiar_foto') ?>" for="avatarInput" aria-label="<?= __('civinsis.perfil.cambiar_foto') ?>">
            <i class="fas fa-camera"></i>
          </label>
          <input type="file" id="avatarInput" class="edit-avatar-input" accept="image/*" onchange="changeAvatar(this)">
        </div>

        <!-- Información e Identidad -->
        <div class="pf-info-cluster">
          
          <div class="pf-name-row">
            <h1 class="pf-display-name" id="profileDisplayName"><?= htmlspecialchars($usuarioNombre) ?></h1>
            <span class="pf-insignia-badge" id="profileInsigniaDisplay" title="Insignia cívica">🌱</span>
          </div>

          <div class="pf-email-text" id="profileDisplayEmail">
            <i class="fas fa-envelope"></i> <?= __('civinsis.perfil.cargando_info') ?>
          </div>

          <!-- Badges de Rol y Visibilidad -->
          <div class="pf-badges-row">
            <span class="pf-role-badge <?= $esAdmin ? 'admin' : '' ?>">
              <i class="fas fa-<?= $esAdmin ? 'shield-halved' : 'user-check' ?>"></i>
              <?= ucfirst($usuarioRol) ?>
            </span>
            <span class="pf-visibility-badge" id="profileVisibilityBadge">
              <i class="fas fa-globe"></i> <?= __('civinsis.perfil.perfil_publico') ?>
            </span>
            <span id="gamTituloDisplayHero"></span>
          </div>

          <!-- Frase o lema cívico -->
          <div class="pf-frase-box" id="profileFraseBox" style="display:none">
            <i class="fas fa-quote-left"></i>
            <span id="profileDisplayFrase"></span>
          </div>

          <!-- Metadatos de perfil (ubicación, fecha de ingreso) -->
          <div class="pf-meta-row">
            <div class="pf-meta-item" id="metaUbicacionWrap" style="display:none">
              <i class="fas fa-location-dot"></i>
              <span id="metaUbicacion"></span>
            </div>
            <div class="pf-meta-item" id="metaFechaWrap">
              <i class="fas fa-calendar-check"></i>
              <span><?= __('civinsis.perfil.ciudadano_activo') ?></span>
            </div>
          </div>

          <!-- Redes Sociales y Enlaces -->
          <div class="pf-social-links" id="profileSocialWrap">
            <a href="#" target="_blank" rel="noopener noreferrer" class="pf-social-btn twitter" id="socialTwitterLink" style="display:none">
              <i class="fab fa-x-twitter"></i> <span id="socialTwitterHandle"></span>
            </a>
            <a href="#" target="_blank" rel="noopener noreferrer" class="pf-social-btn instagram" id="socialInstagramLink" style="display:none">
              <i class="fab fa-instagram"></i> <span id="socialInstagramHandle"></span>
            </a>
            <a href="#" target="_blank" rel="noopener noreferrer" class="pf-social-btn github" id="socialGithubLink" style="display:none">
              <i class="fab fa-github"></i> <span id="socialGithubHandle"></span>
            </a>
          </div>

        </div>

        <!-- Acciones rápidas de cabecera -->
        <div class="pf-header-actions">
          <a href="usuario.php?id=<?= $usuarioId ?? 1 ?>" class="btn btn-sm btn-outline" id="btnVerPublico" title="<?= __('civinsis.perfil.ver_perfil_publico_titulo') ?>">
            <i class="fas fa-eye"></i> <?= __('civinsis.perfil.ver_perfil_publico') ?>
          </a>
          <?php if ($esAdmin): ?>
          <a href="admin.php" class="btn btn-sm btn-primary" style="background:linear-gradient(135deg,var(--naranja),#d46a10)">
            <i class="fas fa-shield-alt"></i> <?= __('civinsis.perfil.panel_admin') ?>
          </a>
          <?php endif; ?>
        </div>

      </div>

      <!-- ═══ ACCESO RÁPIDO A COSMÉTICOS ═══ -->
      <div class="pf-cosmetic-actions" id="cosmeticQuickActions">
        <button class="pf-cos-quick-btn" data-cos-type="fondo" onclick="CosDrawer.open('fondo_perfil')" title="<?= __('civinsis.perfil.cambiar_fondo') ?>">
          <i class="fas fa-image"></i>
          <span><?= __('civinsis.perfil.cambiar_fondo') ?></span>
        </button>
        <button class="pf-cos-quick-btn" data-cos-type="marco" onclick="CosDrawer.open('marco_avatar')" title="<?= __('civinsis.perfil.cambiar_marco') ?>">
          <i class="fas fa-circle-notch"></i>
          <span><?= __('civinsis.perfil.cambiar_marco') ?></span>
        </button>
        <button class="pf-cos-quick-btn" data-cos-type="efecto" onclick="CosDrawer.open('efecto_avatar')" title="<?= __('civinsis.perfil.cambiar_efecto') ?>">
          <i class="fas fa-wand-magic-sparkles"></i>
          <span><?= __('civinsis.perfil.cambiar_efecto') ?></span>
        </button>
      </div>

      <!-- Tarjetas de Estadísticas de Impacto -->
      <div class="pf-stats-grid">
        
        <div class="pf-stat-card propuestas">
          <div class="pf-stat-icon-wrap">
            <i class="fas fa-lightbulb"></i>
          </div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="statMisProp">0</span>
            <span class="pf-stat-label"><?= __('civinsis.perfil.stat_propuestas') ?></span>
          </div>
        </div>

        <div class="pf-stat-card votos">
          <div class="pf-stat-icon-wrap">
            <i class="fas fa-heart"></i>
          </div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="statMisVotos">0</span>
            <span class="pf-stat-label"><?= __('civinsis.perfil.stat_votos_recibidos') ?></span>
          </div>
        </div>

        <div class="pf-stat-card vistas">
          <div class="pf-stat-icon-wrap">
            <i class="fas fa-eye"></i>
          </div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="statMisVistas">0</span>
            <span class="pf-stat-label"><?= __('civinsis.perfil.stat_vistas_totales') ?></span>
          </div>
        </div>

        <div class="pf-stat-card desafios">
          <div class="pf-stat-icon-wrap">
            <i class="fas fa-trophy"></i>
          </div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="statDesafios">0</span>
            <span class="pf-stat-label"><?= __('civinsis.perfil.stat_desafios_logrados') ?></span>
          </div>
        </div>

      </div>

    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     NAVEGACIÓN POR PESTAÑAS (SEGMENTED CONTROL) — 4 TABS
     ══════════════════════════════════════════════════════════ -->
<div class="container pf-nav-container">
  <div class="pf-tabs-pill-wrap">
    <button class="pf-tab-btn active" data-tab="editar">
      <i class="fas fa-user-pen"></i> <?= __('civinsis.perfil.tab_mi_perfil') ?>
    </button>
    <button class="pf-tab-btn" data-tab="propuestas">
      <i class="fas fa-file-lines"></i> <?= __('civinsis.perfil.tab_mis_propuestas') ?>
      <span class="pf-tab-badge" id="badgeTabPropuestas">0</span>
    </button>
    <button class="pf-tab-btn" data-tab="gamificacion">
      <i class="fas fa-award"></i> <?= __('civinsis.perfil.tab_progreso_logros') ?>
    </button>
    <button class="pf-tab-btn" data-tab="seguridad">
      <i class="fas fa-shield-halved"></i> <?= __('civinsis.perfil.tab_seguridad') ?>
    </button>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     ÁREA DE CONTENIDO DE PESTAÑAS
     ══════════════════════════════════════════════════════════ -->
<div class="container pf-content-area">

  <!-- ══════════════════════════════════════════════════════════
       PESTAÑA 1: MI PERFIL (Datos + Personalización combinados)
       ══════════════════════════════════════════════════════════ -->
  <div class="pf-section-panel active" id="tab-editar">
    <form id="editProfileForm">

      <!-- ─── ACORDEÓN 1: Información Básica ─── -->
      <div class="pf-accordion open" id="accInfoBasica">
        <button type="button" class="pf-accordion-trigger" onclick="toggleAccordion('accInfoBasica')">
          <div class="pf-accordion-icon"><i class="fas fa-id-card"></i></div>
          <div class="pf-accordion-label">
            <div class="pf-accordion-label-title"><?= __('civinsis.perfil.acc_info_basica_titulo') ?></div>
            <div class="pf-accordion-label-desc"><?= __('civinsis.perfil.acc_info_basica_desc') ?></div>
          </div>
          <i class="fas fa-chevron-down pf-accordion-chevron"></i>
        </button>
        <div class="pf-accordion-body">
          <div class="pf-form-grid-2" style="margin-bottom:1.25rem">
            <div class="form-group">
              <label class="form-label"><?= __('civinsis.perfil.campo_nombre') ?></label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editNombre" name="nombre" required placeholder="<?= __('civinsis.auth.nombre_placeholder') ?>">
                <i class="fas fa-user pf-input-icon"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><?= __('civinsis.perfil.campo_apellido') ?></label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editApellido" name="apellido" required placeholder="<?= __('civinsis.auth.apellido_placeholder') ?>">
                <i class="fas fa-user-tag pf-input-icon"></i>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><?= __('civinsis.perfil.campo_correo') ?></label>
            <div class="pf-input-group">
              <input type="email" class="form-control" id="editEmail" name="email" required placeholder="tu@correo.com">
              <i class="fas fa-envelope pf-input-icon"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── ACORDEÓN 2: Presencia Cívica & Biografía ─── -->
      <div class="pf-accordion" id="accPresencia">
        <button type="button" class="pf-accordion-trigger" onclick="toggleAccordion('accPresencia')">
          <div class="pf-accordion-icon"><i class="fas fa-feather-pointed"></i></div>
          <div class="pf-accordion-label">
            <div class="pf-accordion-label-title"><?= __('civinsis.perfil.acc_presencia_titulo') ?></div>
            <div class="pf-accordion-label-desc"><?= __('civinsis.perfil.acc_presencia_desc') ?></div>
          </div>
          <i class="fas fa-chevron-down pf-accordion-chevron"></i>
        </button>
        <div class="pf-accordion-body">
          <div class="form-group" style="margin-bottom:1.25rem">
            <label class="form-label"><?= __('civinsis.perfil.campo_lema') ?></label>
            <div class="pf-input-group">
              <input type="text" class="form-control" id="editFrase" name="frase" maxlength="120" placeholder="<?= __('civinsis.perfil.campo_lema_placeholder') ?>">
              <i class="fas fa-quote-left pf-input-icon"></i>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><?= __('civinsis.perfil.campo_biografia') ?></label>
            <textarea class="form-control" id="editBio" name="bio" rows="4" placeholder="<?= __('civinsis.perfil.campo_biografia_placeholder') ?>" maxlength="500"></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.4rem">
              <div class="pf-tag-suggestions">
                <span class="pf-tag-chip" onclick="agregarBioTag('🌱 Sostenibilidad urbana')"><?= __('civinsis.perfil.tag_sostenibilidad') ?></span>
                <span class="pf-tag-chip" onclick="agregarBioTag('🏛️ Transparencia ciudadana')"><?= __('civinsis.perfil.tag_transparencia') ?></span>
                <span class="pf-tag-chip" onclick="agregarBioTag('💡 Innovación social')"><?= __('civinsis.perfil.tag_innovacion') ?></span>
                <span class="pf-tag-chip" onclick="agregarBioTag('🚴 Movilidad limpia')"><?= __('civinsis.perfil.tag_movilidad') ?></span>
              </div>
              <div class="form-hint" style="font-size:.78rem;font-weight:700"><span id="bioCount">0</span>/500</div>
            </div>
          </div>
          <div class="pf-form-grid-2" style="margin-top:1.25rem">
            <div class="form-group">
              <label class="form-label"><?= __('civinsis.perfil.campo_ubicacion') ?></label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editUbicacion" name="ubicacion" maxlength="80" placeholder="<?= __('civinsis.perfil.campo_ubicacion_placeholder') ?>">
                <i class="fas fa-location-dot pf-input-icon"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><?= __('civinsis.perfil.campo_sitio_web') ?></label>
              <div class="pf-input-group">
                <input type="url" class="form-control" id="editSitioWeb" name="sitio_web" maxlength="120" placeholder="https://tuportafolio.com">
                <i class="fas fa-link pf-input-icon"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── ACORDEÓN 3: Redes Sociales ─── -->
      <div class="pf-accordion" id="accRedes">
        <button type="button" class="pf-accordion-trigger" onclick="toggleAccordion('accRedes')">
          <div class="pf-accordion-icon"><i class="fas fa-share-nodes"></i></div>
          <div class="pf-accordion-label">
            <div class="pf-accordion-label-title"><?= __('civinsis.perfil.acc_redes_titulo') ?></div>
            <div class="pf-accordion-label-desc"><?= __('civinsis.perfil.acc_redes_desc') ?></div>
          </div>
          <i class="fas fa-chevron-down pf-accordion-chevron"></i>
        </button>
        <div class="pf-accordion-body">
          <div class="pf-form-grid-3">
            <div class="form-group">
              <label class="form-label">Twitter / X</label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editTwitter" name="social_twitter" placeholder="<?= __('civinsis.perfil.campo_usuario_placeholder') ?>">
                <i class="fab fa-x-twitter pf-input-icon"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Instagram</label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editInstagram" name="social_instagram" placeholder="<?= __('civinsis.perfil.campo_usuario_placeholder') ?>">
                <i class="fab fa-instagram pf-input-icon"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">GitHub</label>
              <div class="pf-input-group">
                <input type="text" class="form-control" id="editGithub" name="social_github" placeholder="<?= __('civinsis.perfil.campo_usuario_placeholder') ?>">
                <i class="fab fa-github pf-input-icon"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── ACORDEÓN 4: Diseño Visual del Perfil ─── -->
      <div class="pf-accordion" id="accDiseno">
        <button type="button" class="pf-accordion-trigger" onclick="toggleAccordion('accDiseno')">
          <div class="pf-accordion-icon"><i class="fas fa-palette"></i></div>
          <div class="pf-accordion-label">
            <div class="pf-accordion-label-title"><?= __('civinsis.perfil.acc_diseno_titulo') ?></div>
            <div class="pf-accordion-label-desc"><?= __('civinsis.perfil.acc_diseno_desc') ?></div>
          </div>
          <i class="fas fa-chevron-down pf-accordion-chevron"></i>
        </button>
        <div class="pf-accordion-body">

          <!-- Tema Cromático -->
          <div style="margin-bottom:1.75rem">
            <h4 style="font-family:var(--font-display);font-size:1rem;font-weight:800;color:var(--text);margin-bottom:.4rem">
              <i class="fas fa-wand-magic-sparkles" style="color:var(--verde);margin-right:.4rem"></i> <?= __('civinsis.perfil.tema_cromatico') ?>
            </h4>
            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem"><?= __('civinsis.perfil.tema_cromatico_desc') ?></p>
            <div class="pf-theme-cards-grid" id="themeGrid">
              <div class="pf-theme-card-opt" data-tema="verde">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #36c0a1, #126045)"><i class="fas fa-seedling"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_esmeralda') ?></span>
              </div>
              <div class="pf-theme-card-opt" data-tema="naranja">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #ef7e22, #7c3a08)"><i class="fas fa-fire"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_atardecer') ?></span>
              </div>
              <div class="pf-theme-card-opt" data-tema="azul">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #3b82f6, #1e3a8a)"><i class="fas fa-water"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_metropoli') ?></span>
              </div>
              <div class="pf-theme-card-opt" data-tema="morado">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #a855f7, #581c87)"><i class="fas fa-gem"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_democracia') ?></span>
              </div>
              <div class="pf-theme-card-opt" data-tema="rosa">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #ec4899, #831843)"><i class="fas fa-sparkles"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_aurora') ?></span>
              </div>
              <div class="pf-theme-card-opt" data-tema="dark">
                <div class="pf-theme-swatch-circle" style="background:linear-gradient(135deg, #374151, #111827)"><i class="fas fa-moon"></i></div>
                <span class="pf-theme-card-name"><?= __('civinsis.perfil.tema_cyberpunk') ?></span>
              </div>
            </div>
            <input type="hidden" id="editTema" name="tema_perfil" value="verde">
          </div>

          <!-- Colores Personalizados -->
          <div style="margin-bottom:1.75rem">
            <h4 style="font-family:var(--font-display);font-size:1rem;font-weight:800;color:var(--text);margin-bottom:.4rem">
              <i class="fas fa-sliders" style="color:var(--verde);margin-right:.4rem"></i> <?= __('civinsis.perfil.ajuste_colores') ?>
            </h4>
            <div class="pf-form-grid-2">
              <div class="form-group">
                <label class="form-label"><?= __('civinsis.perfil.color_acento') ?></label>
                <div style="display:flex;align-items:center;gap:.75rem">
                  <input type="color" id="editColorPerfil" value="#36c0a1" style="width:48px;height:44px;border:none;border-radius:10px;cursor:pointer;background:none">
                  <input type="text" id="editColorPerfilHex" class="form-control" value="#36c0a1" maxlength="7" style="font-family:monospace;text-transform:uppercase">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label"><?= __('civinsis.perfil.color_banner') ?></label>
                <div style="display:flex;align-items:center;gap:.75rem">
                  <input type="color" id="editColorBanner" value="#0f1c19" style="width:48px;height:44px;border:none;border-radius:10px;cursor:pointer;background:none">
                  <input type="text" id="editColorBannerHex" class="form-control" value="#0f1c19" maxlength="7" style="font-family:monospace;text-transform:uppercase">
                </div>
              </div>
            </div>
          </div>

          <!-- Marco de Avatar -->
          <div style="margin-bottom:1.75rem">
            <h4 style="font-family:var(--font-display);font-size:1rem;font-weight:800;color:var(--text);margin-bottom:.4rem">
              <i class="fas fa-circle-notch" style="color:var(--verde);margin-right:.4rem"></i> <?= __('civinsis.perfil.forma_marco') ?>
            </h4>
            <div class="pf-frame-selector-grid" id="frameGrid">
              <div class="pf-frame-card-opt active" data-marco="circulo">
                <div class="pf-frame-demo circulo"><i class="fas fa-circle"></i></div>
                <span class="pf-frame-label"><?= __('civinsis.perfil.marco_circulo') ?></span>
              </div>
              <div class="pf-frame-card-opt" data-marco="cuadrado">
                <div class="pf-frame-demo cuadrado"><i class="fas fa-square"></i></div>
                <span class="pf-frame-label"><?= __('civinsis.perfil.marco_cuadrado') ?></span>
              </div>
              <div class="pf-frame-card-opt" data-marco="hexagono">
                <div class="pf-frame-demo hexagono"><i class="fas fa-cube"></i></div>
                <span class="pf-frame-label"><?= __('civinsis.perfil.marco_hexagono') ?></span>
              </div>
              <div class="pf-frame-card-opt" data-marco="estrella">
                <div class="pf-frame-demo estrella"><i class="fas fa-star"></i></div>
                <span class="pf-frame-label"><?= __('civinsis.perfil.marco_estrella') ?></span>
              </div>
            </div>
            <input type="hidden" id="editMarco" name="marco_avatar" value="circulo">
          </div>

          <!-- Insignia Emoji -->
          <div>
            <h4 style="font-family:var(--font-display);font-size:1rem;font-weight:800;color:var(--text);margin-bottom:.4rem">
              <i class="fas fa-icons" style="color:var(--verde);margin-right:.4rem"></i> <?= __('civinsis.perfil.insignia_civica') ?>
            </h4>
            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem"><?= __('civinsis.perfil.insignia_civica_desc') ?></p>
            <div class="pf-emoji-picker-row">
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('🌱')">🌱</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('👑')">👑</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('🚀')">🚀</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('🏛️')">🏛️</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('⚖️')">⚖️</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('💡')">💡</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('🔥')">🔥</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('⭐')">⭐</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('🌍')">🌍</button>
              <button type="button" class="pf-emoji-opt-btn" onclick="setInsignia('💎')">💎</button>
            </div>
            <div class="pf-input-group" style="max-width:200px">
              <input type="text" class="form-control" id="editInsignia" maxlength="4" placeholder="<?= __('civinsis.perfil.insignia_placeholder') ?>">
              <i class="fas fa-smile pf-input-icon"></i>
            </div>
          </div>

        </div>
      </div>

      <!-- ─── ACORDEÓN 5: Privacidad ─── -->
      <div class="pf-accordion" id="accPrivacidad">
        <button type="button" class="pf-accordion-trigger" onclick="toggleAccordion('accPrivacidad')">
          <div class="pf-accordion-icon"><i class="fas fa-lock"></i></div>
          <div class="pf-accordion-label">
            <div class="pf-accordion-label-title"><?= __('civinsis.perfil.acc_privacidad_titulo') ?></div>
            <div class="pf-accordion-label-desc"><?= __('civinsis.perfil.acc_privacidad_desc') ?></div>
          </div>
          <i class="fas fa-chevron-down pf-accordion-chevron"></i>
        </button>
        <div class="pf-accordion-body">
          <div class="pf-switch-row">
            <div>
              <div class="pf-switch-label"><?= __('civinsis.perfil.perfil_publico_visible') ?></div>
              <div class="pf-switch-desc"><?= __('civinsis.perfil.perfil_publico_visible_desc') ?></div>
            </div>
            <label class="pf-switch" for="editPerfilPublico">
              <input type="checkbox" id="editPerfilPublico" name="perfil_publico" checked>
              <span class="pf-slider"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Barra de Acciones del Formulario -->
      <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin-top:1.25rem">
        <button type="submit" class="btn btn-primary" id="btnGuardarPerfil" style="padding:.75rem 2rem;font-size:1rem">
          <i class="fas fa-floppy-disk"></i> <?= __('civinsis.perfil.guardar_cambios') ?>
        </button>
        <button type="button" class="btn btn-ghost" onclick="loadProfileData()">
          <i class="fas fa-arrow-rotate-left"></i> <?= __('civinsis.perfil.descartar') ?>
        </button>
      </div>

    </form>
  </div>

  <!-- NOTA: La pestaña de personalización visual ha sido integrada en "Mi Perfil" como acordeón -->

  <!-- ── PESTAÑA 3: MIS PROPUESTAS ─────────────────────────── -->
  <div class="pf-section-panel" id="tab-propuestas">
    
    <div class="pf-proposals-toolbar">
      <div class="pf-filter-pills" id="propuestasFiltros">
        <button class="pf-filter-btn active" onclick="filtrarMisPropuestas('todas')"><?= __('civinsis.perfil.filtro_todas') ?></button>
        <button class="pf-filter-btn" onclick="filtrarMisPropuestas('activa')"><?= __('civinsis.perfil.filtro_activas') ?></button>
        <button class="pf-filter-btn" onclick="filtrarMisPropuestas('en_revision')"><?= __('civinsis.perfil.filtro_en_revision') ?></button>
        <button class="pf-filter-btn" onclick="filtrarMisPropuestas('aprobada')"><?= __('civinsis.perfil.filtro_aprobadas') ?></button>
      </div>
      <a href="crear.php" class="btn btn-sm btn-primary">
        <i class="fas fa-plus"></i> <?= __('civinsis.comun.nueva_propuesta') ?>
      </a>
    </div>

    <div id="misProposals">
      <div class="skeleton" style="height:90px;border-radius:var(--pf-radius-md);margin-bottom:1rem"></div>
      <div class="skeleton" style="height:90px;border-radius:var(--pf-radius-md);margin-bottom:1rem;opacity:.7"></div>
      <div class="skeleton" style="height:90px;border-radius:var(--pf-radius-md);opacity:.4"></div>
    </div>
  </div>

  <!-- ── PESTAÑA 4: GAMIFICACIÓN & PROGRESO ─────────────────── -->
  <div class="pf-section-panel" id="tab-gamificacion">

    <!-- Tarjeta Principal de Nivel & XP -->
    <div class="nivel-widget" id="gamNivelWidget" style="margin-bottom:1.5rem">
      <div class="nivel-header">
        <div class="nivel-badge" id="gamNivelBadge">1</div>
        <div class="nivel-info">
          <div class="nivel-nombre"><?= __('civinsis.perfil.nivel_ciudadania') ?></div>
          <div class="nivel-num"><?= __('civinsis.perfil.nivel') ?> <span id="gamNivel">1</span></div>
        </div>
        <div id="gamTituloWrap"></div>
      </div>
      <div class="xp-bar-wrap">
        <div class="xp-bar-track">
          <div class="xp-bar-fill" id="gamXpFill" style="width:0%"></div>
        </div>
        <div class="xp-labels">
          <span id="gamXpActual">0 XP</span>
          <span class="xp-pct" id="gamXpPct">0%</span>
          <span id="gamXpSig">100 XP</span>
        </div>
      </div>
      <div class="gam-stats-row">
        <div class="gam-stat-box">
          <span class="icon">⭐</span>
          <div class="val" id="gamRepVal">0</div>
          <div class="lbl"><?= __('civinsis.perfil.reputacion') ?></div>
        </div>
        <div class="gam-stat-box">
          <span class="icon">🔥</span>
          <div class="val" id="gamRachaVal">0</div>
          <div class="lbl"><?= __('civinsis.perfil.racha_dias') ?></div>
        </div>
      </div>
    </div>

    <!-- Subpestañas de Gamificación (sin cosméticos, ahora en drawer) -->
    <div class="gam-tabs">
      <button class="gam-tab active" data-gam="misiones"><i class="fas fa-tasks"></i> <?= __('civinsis.perfil.gam_tab_misiones') ?></button>
      <button class="gam-tab" data-gam="logros"><i class="fas fa-medal"></i> <?= __('civinsis.perfil.gam_tab_logros') ?></button>
      <button class="gam-tab" data-gam="insignias"><i class="fas fa-shield-alt"></i> <?= __('civinsis.perfil.gam_tab_insignias') ?></button>
      <button class="gam-tab" data-gam="titulos"><i class="fas fa-crown"></i> <?= __('civinsis.perfil.gam_tab_titulos') ?></button>
      <button class="gam-tab" data-gam="ranking"><i class="fas fa-list-ol"></i> <?= __('civinsis.perfil.gam_tab_ranking') ?></button>
      <button class="gam-tab" data-gam="historial"><i class="fas fa-history"></i> <?= __('civinsis.perfil.gam_tab_historial') ?></button>
    </div>

    <!-- Panel Misiones -->
    <div id="gam-misiones" class="gam-panel">
      <div style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-outline" onclick="Gam.filtrarMisiones('diaria')" id="btnDiaria"><?= __('civinsis.perfil.diarias') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarMisiones('semanal')" id="btnSemanal"><?= __('civinsis.perfil.semanales') ?></button>
      </div>
      <div id="gamMisionesList"><div class="skeleton" style="height:64px;border-radius:12px;margin-bottom:.5rem"></div></div>
    </div>

    <!-- Panel Logros -->
    <div id="gam-logros" class="gam-panel" style="display:none">
      <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap" id="gamLogrosFiltros">
        <button class="btn btn-sm btn-outline" onclick="Gam.filtrarLogros('todos')"><?= __('civinsis.perfil.filtro_todos') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('propuestas')"><?= __('civinsis.perfil.filtro_propuestas') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('comunidad')"><?= __('civinsis.perfil.filtro_comunidad') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('racha')"><?= __('civinsis.perfil.filtro_racha') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('nivel')"><?= __('civinsis.perfil.filtro_nivel') ?></button>
      </div>
      <div class="logros-grid" id="gamLogrosList"></div>
    </div>

    <!-- Panel Insignias -->
    <div id="gam-insignias" class="gam-panel" style="display:none">
      <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem"><?= __('civinsis.perfil.insignias_desc') ?></p>
      <div class="insignias-grid" id="gamInsigniasList"></div>
    </div>

    <!-- Panel Títulos -->
    <div id="gam-titulos" class="gam-panel" style="display:none">
      <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem"><?= __('civinsis.perfil.titulos_desc') ?></p>
      <div style="display:flex;flex-wrap:wrap;gap:.6rem" id="gamTitulosList"></div>
    </div>

    <!-- Panel Cosméticos (movido al drawer rápido — redireccionamos al drawer) -->
    <!-- Este panel ya no se usa, los cosméticos se acceden desde el drawer lateral -->

    <!-- Panel Ranking -->
    <div id="gam-ranking" class="gam-panel" style="display:none">
      <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-outline" onclick="Gam.cargarRanking('xp')"><?= __('civinsis.perfil.por_xp') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.cargarRanking('reputacion')"><?= __('civinsis.perfil.por_reputacion') ?></button>
        <button class="btn btn-sm btn-ghost" onclick="Gam.cargarRanking('nivel')"><?= __('civinsis.perfil.por_nivel') ?></button>
      </div>
      <div class="table-wrap" style="overflow-x:auto">
        <table class="ranking-table">
          <thead><tr><th>#</th><th><?= __('civinsis.perfil.col_usuario') ?></th><th><?= __('civinsis.perfil.nivel') ?></th><th>XP</th><th><?= __('civinsis.perfil.reputacion') ?></th><th><?= __('civinsis.perfil.col_titulo_rank') ?></th></tr></thead>
          <tbody id="gamRankingBody"><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- Panel Historial XP -->
    <div id="gam-historial" class="gam-panel" style="display:none">
      <div id="gamHistorialList"><div class="skeleton" style="height:40px;margin-bottom:.5rem"></div></div>
    </div>

    <!-- Coach CIVI integrado en esta pestaña -->
    <div class="pf-civi-inline-section">
      <div class="pf-civi-inline-header" onclick="toggleCiviSection()">
        <div class="pf-civi-icon"><i class="fas fa-robot"></i></div>
        <div>
          <div class="pf-civi-label"><?= __('civinsis.perfil.coach_titulo') ?></div>
          <div class="pf-civi-sublabel"><?= __('civinsis.perfil.coach_subtitulo') ?></div>
        </div>
        <i class="fas fa-chevron-down pf-accordion-chevron" id="civiChevron"></i>
      </div>
      <div class="pf-card" id="civiInlineContent" style="display:none">
        <div class="civi-analisis" id="civiAnalisis">
          <div class="civi-an-loading">
            <i class="fas fa-robot fa-bounce" style="font-size:1.8rem;color:var(--verde);margin-bottom:.5rem;display:block"></i>
            <?= __('civinsis.perfil.coach_analizando') ?>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- ── PESTAÑA 6: SEGURIDAD & CONTRASEÑA ─────────────────── -->
  <div class="pf-section-panel" id="tab-seguridad">
    <div class="pf-card" style="max-width:680px">
      <h3 class="pf-card-title">
        <i class="fas fa-lock"></i> <?= __('civinsis.perfil.cambiar_contrasena') ?>
      </h3>
      <p class="pf-card-subtitle"><?= __('civinsis.perfil.cambiar_contrasena_desc') ?></p>

      <form id="changePassForm">
        <div class="form-group" style="margin-bottom:1.25rem">
          <label class="form-label"><?= __('civinsis.perfil.contrasena_actual') ?></label>
          <div class="pf-input-group">
            <input type="password" class="form-control" id="passActual" name="pass_actual" required placeholder="<?= __('civinsis.perfil.contrasena_actual_placeholder') ?>">
            <i class="fas fa-key pf-input-icon"></i>
            <span class="pf-input-action" onclick="togglePass('passActual')"><i class="fas fa-eye"></i></span>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:1.25rem">
          <label class="form-label"><?= __('civinsis.perfil.contrasena_nueva') ?></label>
          <div class="pf-input-group">
            <input type="password" class="form-control" id="passNueva" name="pass_nueva" required minlength="8" placeholder="<?= __('civinsis.perfil.contrasena_nueva_placeholder') ?>" oninput="evaluarPassword(this.value)">
            <i class="fas fa-lock pf-input-icon"></i>
            <span class="pf-input-action" onclick="togglePass('passNueva')"><i class="fas fa-eye"></i></span>
          </div>

          <!-- Medidor de Fuerza de Contraseña -->
          <div class="pf-strength-wrap">
            <div class="pf-strength-bars">
              <div class="pf-strength-bar" id="strBar1"></div>
              <div class="pf-strength-bar" id="strBar2"></div>
              <div class="pf-strength-bar" id="strBar3"></div>
              <div class="pf-strength-bar" id="strBar4"></div>
            </div>
            <div class="pf-strength-text" id="strText"><?= __('civinsis.perfil.strength_placeholder') ?></div>
          </div>

          <!-- Checklist de Requisitos -->
          <div class="pf-security-checklist">
            <div class="pf-check-item" id="reqLongitud"><i class="fas fa-circle-xmark"></i> <?= __('civinsis.perfil.req_longitud') ?></div>
            <div class="pf-check-item" id="reqNumero"><i class="fas fa-circle-xmark"></i> <?= __('civinsis.perfil.req_numero') ?></div>
            <div class="pf-check-item" id="reqMayus"><i class="fas fa-circle-xmark"></i> <?= __('civinsis.perfil.req_mayus') ?></div>
            <div class="pf-check-item" id="reqEspecial"><i class="fas fa-circle-xmark"></i> <?= __('civinsis.perfil.req_especial') ?></div>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:1.75rem">
          <label class="form-label"><?= __('civinsis.perfil.confirmar_contrasena') ?></label>
          <div class="pf-input-group">
            <input type="password" class="form-control" id="passConfirm" name="pass_confirm" required placeholder="<?= __('civinsis.perfil.confirmar_contrasena_placeholder') ?>">
            <i class="fas fa-lock pf-input-icon"></i>
            <span class="pf-input-action" onclick="togglePass('passConfirm')"><i class="fas fa-eye"></i></span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-shield-halved"></i> <?= __('civinsis.perfil.actualizar_contrasena') ?>
        </button>
      </form>
    </div>
  </div>

</div>

<!-- ══════════════════════════════════════════════════════════
     DRAWER LATERAL DE COSMÉTICOS
     ══════════════════════════════════════════════════════════ -->
<div class="pf-cos-drawer-overlay" id="cosDrawerOverlay" onclick="CosDrawer.close()"></div>
<div class="pf-cos-drawer" id="cosDrawer">
  <div class="pf-cos-drawer-header">
    <div class="pf-cos-drawer-icon" id="cosDrawerIcon"><i class="fas fa-palette"></i></div>
    <div class="pf-cos-drawer-title-wrap">
      <div class="pf-cos-drawer-title" id="cosDrawerTitle"><?= __('civinsis.perfil.mis_cosmeticos') ?></div>
      <div class="pf-cos-drawer-subtitle" id="cosDrawerSubtitle"><?= __('civinsis.perfil.mis_cosmeticos_desc') ?></div>
    </div>
    <button class="pf-cos-drawer-close" onclick="CosDrawer.close()" title="<?= __('civinsis.comun.cerrar') ?>">
      <i class="fas fa-xmark"></i>
    </button>
  </div>
  <div class="pf-cos-drawer-filters">
    <button class="pf-cos-drawer-filter-btn active" data-drawer-filter="marco_avatar" onclick="CosDrawer.filter('marco_avatar')">
      <i class="fas fa-circle-notch"></i> <?= __('civinsis.perfil.marcos') ?>
    </button>
    <button class="pf-cos-drawer-filter-btn" data-drawer-filter="fondo_perfil" onclick="CosDrawer.filter('fondo_perfil')">
      <i class="fas fa-image"></i> <?= __('civinsis.perfil.fondos') ?>
    </button>
    <button class="pf-cos-drawer-filter-btn" data-drawer-filter="efecto_avatar" onclick="CosDrawer.filter('efecto_avatar')">
      <i class="fas fa-wand-magic-sparkles"></i> <?= __('civinsis.perfil.efectos') ?>
    </button>
  </div>
  <div class="pf-cos-drawer-body">
    <div class="pf-cos-drawer-grid" id="cosDrawerGrid">
      <div class="pf-cos-drawer-empty">
        <i class="fas fa-spinner fa-spin"></i>
        <p><?= __('civinsis.perfil.cargando_cosmeticos') ?></p>
      </div>
    </div>
  </div>
</div>

<div class="toast-container"></div>
<?php echo view('layouts.footer')->render(); ?>

<script src="js/app.js"></script>
<script src="js/perfil.js"></script>
</body>
</html>