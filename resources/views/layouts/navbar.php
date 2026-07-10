<?php
// layouts/navbar.php  (renderizado por Laravel; las variables vienen del View Composer)
$activeNav    = $activeNav ?? '';
$navLinks = [
  ['href'=>'index.php','icon'=>'fa-home','label'=>'Inicio','key'=>'inicio'],
  ['href'=>'dashboard.php','icon'=>'fa-layer-group','label'=>'Propuestas','key'=>'propuestas'],
];
if (!empty($usuarioLogueado)) {
  $navLinks[] = ['href'=>'crear.php','icon'=>'fa-plus-circle','label'=>'Crear','key'=>'crear'];
}
$navLinks[] = ['href'=>'faq.php','icon'=>'fa-question-circle','label'=>'FAQ','key'=>'faq'];
$navLinks[] = ['href'=>'contacto.php','icon'=>'fa-envelope','label'=>'Contacto','key'=>'contacto'];
$navAvatar    = $usuarioAvatar ?? null;
$navIniciales = !empty($usuarioLogueado) ? strtoupper(mb_substr($usuarioNombre, 0, 1)) : '';
$esAdminNav   = in_array($usuarioRol ?? '', ['admin','moderador']);
?>
<nav class="navbar" id="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="nav-logo">
      <div class="nav-logo-box"><img src="/media/logo.png" alt="">></div>
      <span class="nav-logo-text">CIVINSIS</span>
    </a>
    <div class="nav-links">
      <?php foreach ($navLinks as $l): ?>
        <a href="<?= $l['href'] ?>" class="nav-link <?= ($activeNav === $l['key']) ? 'active' : '' ?>">
          <i class="fas <?= $l['icon'] ?>"></i> <?= $l['label'] ?>
        </a>
      <?php endforeach; ?>
      <?php if ($esAdminNav): ?>
        <a href="admin.php" class="nav-link nav-link-admin <?= ($activeNav === 'admin') ? 'active' : '' ?>">
          <i class="fas fa-shield-alt"></i> Admin
        </a>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <div class="dark-toggle-wrap">
        <i class="fas fa-sun"></i>
        <button class="dark-toggle" data-dark-toggle aria-label="Cambiar tema"></button>
        <i class="fas fa-moon"></i>
      </div>
      <?php if (!empty($usuarioLogueado)): ?>
        <a href="perfil.php" class="nav-user-pill">
          <div class="nav-user-avatar" id="navUserAvatar">
            <?php if ($navAvatar): ?><img src="<?= htmlspecialchars($navAvatar) ?>" alt="Avatar"><?php else: ?><?= $navIniciales ?><?php endif; ?>
          </div>
          <span class="nav-user-name"><?= htmlspecialchars($usuarioNombre) ?></span>
        </a>
        <button class="btn btn-outline btn-sm" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Salir</button>
      <?php else: ?>
        <a href="auth.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
        <a href="auth.php?tab=registro" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Registrarse</a>
      <?php endif; ?>
      <button class="hamburger" aria-label="Menú" id="hamburger">
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
      <div><div class="mobile-drawer-name"><?= htmlspecialchars($usuarioNombre) ?></div><div class="mobile-drawer-role"><?= ucfirst($usuarioRol) ?></div></div>
    </div>
    <?php endif; ?>
    <div class="mobile-drawer-links">
      <?php foreach ($navLinks as $l): ?>
        <a href="<?= $l['href'] ?>" class="mobile-drawer-link <?= ($activeNav === $l['key']) ? 'active' : '' ?>">
          <span class="mobile-drawer-link-icon"><i class="fas <?= $l['icon'] ?>"></i></span><?= $l['label'] ?>
        </a>
      <?php endforeach; ?>
      <?php if ($esAdminNav): ?><a href="admin.php" class="mobile-drawer-link"><span class="mobile-drawer-link-icon"><i class="fas fa-shield-alt"></i></span>Admin</a><?php endif; ?>
    </div>
    <div class="mobile-drawer-footer">
      <?php if (!empty($usuarioLogueado)): ?>
        <a href="perfil.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:.5rem"><i class="fas fa-user"></i> Mi Perfil</a>
        <button onclick="logout()" class="btn btn-ghost" style="width:100%;justify-content:center"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
      <?php else: ?>
        <a href="auth.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:.5rem"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
        <a href="auth.php?tab=registro" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fas fa-user-plus"></i> Registrarse</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="mobile-drawer-overlay" id="mobileOverlay"></div>
