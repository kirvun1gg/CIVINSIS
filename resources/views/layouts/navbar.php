<?php
// layouts/navbar.php  (renderizado por Laravel; las variables vienen del View Composer)
// $usuarioLogueado, $usuarioNombre, $usuarioRol y $usuarioAvatar los inyecta
// el View Composer global en TODAS las vistas
// (app/Providers/AppServiceProvider.php::boot()). El valor por defecto de
// abajo nunca se usa en producción - solo evita que el IDE marque la
// variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
$usuarioNombre   = $usuarioNombre ?? '';
$usuarioRol      = $usuarioRol ?? 'invitado';
$usuarioAvatar   = $usuarioAvatar ?? null;
$activeNav    = $activeNav ?? '';
// Propuestas, debates y desafíos comparten una misma idea (son las tres
// secciones donde el usuario puede crear algo), así que en el navbar de
// escritorio van agrupadas en un solo desplegable "Participar" en vez de
// tres pastillas sueltas. En el drawer móvil se listan igual, aparte.
$navLinkInicio = ['href'=> !empty($usuarioLogueado) ? 'inicio.php' : 'index.php', 'icon'=>'fa-home', 'label'=>__('civinsis.nav.inicio'), 'key'=>'inicio'];
$navLinksParticipar = [
  ['href'=>'dashboard.php','icon'=>'fa-layer-group','label'=>__('civinsis.nav.propuestas'),'key'=>'propuestas'],
  ['href'=>'debates.php','icon'=>'fa-comments','label'=>__('civinsis.nav.debates'),'key'=>'debates'],
  ['href'=>'desafios.php','icon'=>'fa-flag-checkered','label'=>__('civinsis.nav.desafios'),'key'=>'desafios'],
];
$navLinksResto = [
  ['href'=>'ranking.php','icon'=>'fa-ranking-star','label'=>__('civinsis.nav.ranking'),'key'=>'ranking'],
  ['href'=>'faq.php','icon'=>'fa-question-circle','label'=>__('civinsis.nav.faq'),'key'=>'faq'],
  ['href'=>'contacto.php','icon'=>'fa-envelope','label'=>__('civinsis.nav.contacto'),'key'=>'contacto'],
];
$navLinksMobile = array_merge([$navLinkInicio], $navLinksParticipar, $navLinksResto);
$participarActivo = in_array($activeNav, ['propuestas', 'debates', 'desafios']);
$navAvatar    = $usuarioAvatar ?? null;
$navIniciales = !empty($usuarioLogueado) ? strtoupper(mb_substr($usuarioNombre, 0, 1)) : '';
$esAdminNav   = in_array($usuarioRol ?? '', ['admin','moderador']);
$idiomasDisponibles = config('locales.supported', []);
$idiomaActual       = app()->getLocale();
?>
<nav class="navbar" id="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="nav-logo <?= ($activeNav === 'logo') ? 'active' : '' ?>" aria-label="CIVINSIS - Inicio"<?= ($activeNav === 'logo') ? ' aria-current="page"' : '' ?>>
      <div class="nav-logo-box"><img src="<?= asset('media/logo.png') ?>" alt="CIVINSIS"></div>
      <span class="nav-logo-text"><span class="nav-logo-text-inner">CIVINSIS</span></span>
    </a>
    <div class="nav-links">
      <a href="<?= $navLinkInicio['href'] ?>" class="nav-link <?= ($activeNav === 'inicio') ? 'active' : '' ?>"<?= ($activeNav === 'inicio') ? ' aria-current="page"' : '' ?>>
        <i class="fas <?= $navLinkInicio['icon'] ?>"></i> <?= $navLinkInicio['label'] ?>
      </a>
      <div class="nav-dropdown-wrap" id="navParticiparWrap">
        <button class="nav-link nav-dropdown-btn <?= $participarActivo ? 'active' : '' ?>" id="navParticiparBtn" type="button"
          aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-layer-group"></i> <?= __('civinsis.nav.participar') ?>
          <i class="fas fa-chevron-down nav-dropdown-caret"></i>
        </button>
        <div class="nav-dropdown-menu" id="navParticiparMenu">
          <?php foreach ($navLinksParticipar as $l): ?>
            <a href="<?= $l['href'] ?>" class="nav-dropdown-item <?= ($activeNav === $l['key']) ? 'active' : '' ?>"<?= ($activeNav === $l['key']) ? ' aria-current="page"' : '' ?>>
              <i class="fas <?= $l['icon'] ?>"></i> <?= $l['label'] ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($navLinksResto as $l): ?>
        <a href="<?= $l['href'] ?>" class="nav-link <?= ($activeNav === $l['key']) ? 'active' : '' ?>"<?= ($activeNav === $l['key']) ? ' aria-current="page"' : '' ?>>
          <i class="fas <?= $l['icon'] ?>"></i> <?= $l['label'] ?>
        </a>
      <?php endforeach; ?>
      <?php if ($esAdminNav): ?>
        <a href="admin.php" class="nav-link nav-link-admin <?= ($activeNav === 'admin') ? 'active' : '' ?>">
          <i class="fas fa-shield-alt"></i> <?= __('civinsis.nav.admin') ?>
        </a>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <?php if (!empty($usuarioLogueado)): ?>
      <div class="notif-bell-wrap" id="notifBellWrap">
        <button class="notif-bell-btn" id="notifBellBtn" aria-label="<?= __('civinsis.nav.notificaciones') ?>">
          <i class="fas fa-bell"></i>
          <span class="notif-badge" id="notifBadge" style="display:none">0</span>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-dropdown-header">
            <span><?= __('civinsis.nav.notificaciones') ?></span>
            <button class="notif-mark-all" id="notifMarkAll"><?= __('civinsis.nav.marcar_todas_leidas') ?></button>
          </div>
          <div class="notif-dropdown-list" id="notifDropdownList">
            <div class="notif-empty">Cargando...</div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <div class="idioma-toggle-wrap" id="idiomaToggleWrap">
        <button class="idioma-toggle" id="idiomaToggleBtn" type="button"
          aria-label="<?= __('civinsis.nav.idioma') ?>: <?= htmlspecialchars($idiomasDisponibles[$idiomaActual]['nombre'] ?? '') ?>"
          aria-haspopup="true" aria-expanded="false"
          title="<?= __('civinsis.nav.idioma') ?>">
          <span class="idioma-toggle-codigo"><?= strtoupper($idiomaActual) ?></span>
          <i class="fas fa-chevron-down idioma-toggle-caret" aria-hidden="true"></i>
        </button>
        <div class="idioma-dropdown" id="idiomaDropdown">
          <div class="idioma-dropdown-label"><?= __('civinsis.nav.idioma') ?></div>
          <?php foreach ($idiomasDisponibles as $codigo => $meta): ?>
            <a href="<?= route('idioma.cambiar', $codigo) ?>" class="idioma-opcion <?= $codigo === $idiomaActual ? 'active' : '' ?>">
              <span><?= $meta['bandera'] ?></span> <?= htmlspecialchars($meta['nombre']) ?>
              <?php if ($codigo === $idiomaActual): ?><i class="fas fa-check idioma-opcion-check"></i><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="dark-toggle-wrap" id="darkToggleBtn" type="button" data-dark-toggle
        aria-label="<?= __('civinsis.nav.cambiar_tema') ?>" title="<?= __('civinsis.nav.cambiar_tema') ?>">
        <i class="fas fa-sun" aria-hidden="true"></i>
        <span class="dark-toggle"></span>
        <i class="fas fa-moon" aria-hidden="true"></i>
      </button>
      <?php if (!empty($usuarioLogueado)): ?>
        <a href="perfil.php" class="nav-user-pill">
          <div class="nav-user-avatar" id="navUserAvatar">
            <?php if ($navAvatar): ?><img src="<?= htmlspecialchars($navAvatar) ?>" alt="Avatar"><?php else: ?><?= $navIniciales ?><?php endif; ?>
          </div>
          <span class="nav-user-name"><?= htmlspecialchars($usuarioNombre) ?></span>
        </a>
        <button class="btn btn-outline btn-sm" onclick="logout()"><i class="fas fa-sign-out-alt"></i> <?= __('civinsis.nav.salir') ?></button>
      <?php else: ?>
        <a href="auth.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> <?= __('civinsis.nav.ingresar') ?></a>
        <a href="auth.php?tab=registro" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> <?= __('civinsis.nav.registrarse') ?></a>
      <?php endif; ?>
      <button class="hamburger" aria-label="<?= __('civinsis.nav.menu') ?>" id="hamburger">
        <span class="ham-line ham-top"></span>
        <span class="ham-line ham-mid"></span>
        <span class="ham-line ham-bot"></span>
      </button>
    </div>
  </div>
</nav>
<div class="mobile-drawer" id="mobileMenu">
  <div class="mobile-drawer-header">
    <span class="mobile-drawer-brand">CIVINSIS</span>
    <button class="mobile-drawer-close" id="mobileMenuClose"><i class="fas fa-times"></i></button>
  </div>
  <div class="mobile-drawer-body">
    <?php if (!empty($usuarioLogueado)): ?>
    <div class="mobile-drawer-user">
      <div class="mobile-drawer-avatar"><?php if ($navAvatar): ?><img src="<?= htmlspecialchars($navAvatar) ?>" alt="Avatar"><?php else: ?><?= $navIniciales ?><?php endif; ?></div>
      <div><div class="mobile-drawer-name"><?= htmlspecialchars($usuarioNombre) ?></div><div class="mobile-drawer-role"><?= __('civinsis.roles.' . $usuarioRol) ?></div></div>
    </div>
    <?php endif; ?>
    <div class="mobile-drawer-links">
      <?php foreach ($navLinksMobile as $l): ?>
        <a href="<?= $l['href'] ?>" class="mobile-drawer-link <?= ($activeNav === $l['key']) ? 'active' : '' ?>">
          <span class="mobile-drawer-link-icon"><i class="fas <?= $l['icon'] ?>"></i></span><?= $l['label'] ?>
        </a>
      <?php endforeach; ?>
      <?php if ($esAdminNav): ?><a href="admin.php" class="mobile-drawer-link"><span class="mobile-drawer-link-icon"><i class="fas fa-shield-alt"></i></span><?= __('civinsis.nav.admin') ?></a><?php endif; ?>
    </div>
    <div class="mobile-drawer-idiomas">
      <?php foreach ($idiomasDisponibles as $codigo => $meta): ?>
        <a href="<?= route('idioma.cambiar', $codigo) ?>" class="mobile-drawer-idioma <?= $codigo === $idiomaActual ? 'active' : '' ?>">
          <?= $meta['bandera'] ?> <?= htmlspecialchars($meta['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="mobile-drawer-footer">
      <?php if (!empty($usuarioLogueado)): ?>
        <a href="perfil.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:.5rem"><i class="fas fa-user"></i> <?= __('civinsis.nav.mi_perfil') ?></a>
        <button onclick="logout()" class="btn btn-ghost" style="width:100%;justify-content:center"><i class="fas fa-sign-out-alt"></i> <?= __('civinsis.nav.cerrar_sesion') ?></button>
      <?php else: ?>
        <a href="auth.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:.5rem"><i class="fas fa-sign-in-alt"></i> <?= __('civinsis.nav.ingresar') ?></a>
        <a href="auth.php?tab=registro" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fas fa-user-plus"></i> <?= __('civinsis.nav.registrarse') ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="mobile-drawer-overlay" id="mobileOverlay"></div>
