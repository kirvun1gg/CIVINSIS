<?php
// auth.php - Página de autenticación CIVINSIS (v3 · split screen)
$activeTab = $_GET['tab'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.auth.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="icon" type="image/png" href="<?= asset('media/logo.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/auth-styles.css">
</head>
<body>

<div class="auth-split">

  <!-- ══════════ MITAD IZQUIERDA · Panel visual ══════════ -->
  <aside class="auth-visual">
    <div class="auth-visual-orb orb-a"></div>
    <div class="auth-visual-orb orb-b"></div>

    <div class="auth-visual-inner">
      <a href="index.php" class="auth-brand">
        <span class="auth-brand-icon"><img src="<?= asset('media/logo.png') ?>" alt=""></span>
        <span class="auth-brand-name">CIVINSIS</span>
      </a>
      <div class="auth-animation-slot" id="authAnimationSlot">
<svg viewBox="0 0 500 500" class="civi-anim-svg" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="gradTeal" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="var(--c-teal)"/>
      <stop offset="100%" stop-color="var(--c-teal-dim)"/>
    </linearGradient>
    <linearGradient id="gradOrange" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="var(--c-orange)"/>
      <stop offset="100%" stop-color="var(--c-orange-dim)"/>
    </linearGradient>
    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="8" result="blur" />
      <feComposite in="SourceGraphic" in2="blur" operator="over" />
    </filter>
  </defs>

  <style>
    .civi-anim-svg {
      width: 100%;
      height: 100%;
      overflow: visible;
      font-family: "Font Awesome 6 Free", sans-serif;
    }
    .c-ring {
      fill: none;
      stroke: var(--c-teal);
      stroke-width: 1.5;
      stroke-dasharray: 4 6;
      opacity: 0.3;
      transform-origin: 250px 250px;
      animation: spinRing 25s linear infinite;
    }
    .c-ring-reverse {
      fill: none;
      stroke: var(--c-orange);
      stroke-width: 1.5;
      stroke-dasharray: 8 8;
      opacity: 0.2;
      transform-origin: 250px 250px;
      animation: spinRingReverse 35s linear infinite;
    }
    .c-core {
      fill: url(#gradTeal);
      filter: url(#glow);
      transform-origin: 250px 250px;
      animation: pulseCore 4s ease-in-out infinite alternate;
    }
    .c-node {
      fill: var(--card-bg);
      stroke: var(--c-teal);
      stroke-width: 2;
    }
    .c-node-icon {
      fill: var(--txt-h);
      font-weight: 900;
      font-size: 20px;
      text-anchor: middle;
      dominant-baseline: central;
    }
    .orbit-group-1 {
      transform-origin: 250px 250px;
      animation: spinGroup 24s linear infinite;
    }
    .orbit-group-2 {
      transform-origin: 250px 250px;
      animation: spinGroupReverse 32s linear infinite;
    }
    .anti-spin-1 {
      transform-origin: 0px 0px;
      animation: antiSpin 24s linear infinite;
    }
    .anti-spin-2 {
      transform-origin: 0px 0px;
      animation: antiSpinReverse 32s linear infinite;
    }
    .c-connection {
      stroke: var(--c-teal);
      stroke-width: 1;
      opacity: 0.4;
      stroke-dasharray: 3 3;
    }

    @keyframes spinRing { 100% { transform: rotate(360deg); } }
    @keyframes spinRingReverse { 100% { transform: rotate(-360deg); } }
    @keyframes pulseCore {
      0% { transform: scale(0.95); opacity: 0.8; }
      100% { transform: scale(1.05); opacity: 1; }
    }
    @keyframes spinGroup { 100% { transform: rotate(360deg); } }
    @keyframes spinGroupReverse { 100% { transform: rotate(-360deg); } }
    @keyframes antiSpin { 100% { transform: rotate(-360deg); } }
    @keyframes antiSpinReverse { 100% { transform: rotate(360deg); } }
    
    .floating-element {
      animation: floatUpDown 6s ease-in-out infinite alternate;
      transform-origin: 250px 250px;
    }
    @keyframes floatUpDown {
      0% { transform: translateY(-8px); }
      100% { transform: translateY(8px); }
    }
    .c-particle {
      fill: var(--c-orange);
      opacity: 0.6;
      animation: floatUpDown 4s ease-in-out infinite alternate;
    }
    .c-particle:nth-child(even) {
      fill: var(--c-teal);
      animation-duration: 5s;
      animation-direction: alternate-reverse;
    }
  </style>

  <circle cx="120" cy="120" r="3" class="c-particle" style="animation-delay: 0s;"/>
  <circle cx="380" cy="150" r="4" class="c-particle" style="animation-delay: 1s;"/>
  <circle cx="400" cy="380" r="2.5" class="c-particle" style="animation-delay: 2s;"/>
  <circle cx="150" cy="400" r="3.5" class="c-particle" style="animation-delay: 3s;"/>
  <circle cx="250" cy="80" r="2" class="c-particle" style="animation-delay: 4s;"/>

  <circle cx="250" cy="250" r="140" class="c-ring" />
  <circle cx="250" cy="250" r="190" class="c-ring-reverse" />
  <circle cx="250" cy="250" r="90" class="c-ring" style="animation-duration: 15s; opacity: 0.15; stroke-dasharray: 2 4;" />

  <g class="orbit-group-1">
    <line x1="250" y1="250" x2="250" y2="110" class="c-connection" />
    <line x1="250" y1="250" x2="371" y2="320" class="c-connection" />
    <line x1="250" y1="250" x2="129" y2="320" class="c-connection" />
    
    <g transform="translate(250, 110)">
      <g class="anti-spin-1">
        <circle cx="0" cy="0" r="24" class="c-node" />
        <text x="0" y="2" class="c-node-icon">&#xf007;</text>
      </g>
    </g>
    <g transform="translate(371, 320)">
      <g class="anti-spin-1">
        <circle cx="0" cy="0" r="24" class="c-node" />
        <text x="0" y="2" class="c-node-icon">&#xf15c;</text>
      </g>
    </g>
    <g transform="translate(129, 320)">
      <g class="anti-spin-1">
        <circle cx="0" cy="0" r="24" class="c-node" />
        <text x="0" y="2" class="c-node-icon">&#xf086;</text>
      </g>
    </g>
  </g>

  <g class="orbit-group-2">
    <line x1="250" y1="250" x2="250" y2="60" class="c-connection" style="stroke:var(--c-orange); opacity:0.3;" />
    <line x1="250" y1="250" x2="85" y2="345" class="c-connection" style="stroke:var(--c-orange); opacity:0.3;" />
    <line x1="250" y1="250" x2="415" y2="345" class="c-connection" style="stroke:var(--c-orange); opacity:0.3;" />

    <g transform="translate(250, 60)">
      <g class="anti-spin-2">
        <circle cx="0" cy="0" r="20" class="c-node" style="stroke:var(--c-orange);" />
        <text x="0" y="1" class="c-node-icon" style="font-size:16px;">&#xf132;</text>
      </g>
    </g>
    <g transform="translate(85, 345)">
      <g class="anti-spin-2">
        <circle cx="0" cy="0" r="20" class="c-node" style="stroke:var(--c-orange);" />
        <text x="0" y="1" class="c-node-icon" style="font-size:16px;">&#xf0eb;</text>
      </g>
    </g>
    <g transform="translate(415, 345)">
      <g class="anti-spin-2">
        <circle cx="0" cy="0" r="20" class="c-node" style="stroke:var(--c-orange);" />
        <text x="0" y="1" class="c-node-icon" style="font-size:16px;">&#xf0ac;</text>
      </g>
    </g>
  </g>

  <g class="floating-element">
    <circle cx="250" cy="250" r="50" class="c-core" />
    <text x="250" y="252" fill="var(--body-bg)" font-family="'Font Awesome 6 Free'" font-weight="900" font-size="44" text-anchor="middle" dominant-baseline="central">&#xf0c0;</text>
    <circle cx="250" cy="250" r="62" fill="none" stroke="var(--c-teal)" stroke-width="1.5" stroke-dasharray="6 6" style="transform-origin:250px 250px; animation: spinRingReverse 15s linear infinite; opacity:0.6;"/>
  </g>
</svg>
      </div>

      <div class="auth-visual-caption">
        <h2><?= __('civinsis.auth.visual_titulo') ?></h2>
        <p><?= __('civinsis.auth.visual_desc') ?></p>
      </div>
    </div>
  </aside>

  <!-- ══════════ MITAD DERECHA · Formulario ══════════ -->
  <main class="auth-form-side">

    <div class="auth-topbar">
      <a href="index.php" class="auth-back"><i class="fas fa-arrow-left"></i> <?= __('civinsis.auth.volver_inicio') ?></a>
      <button class="theme-btn" id="themeBtn" title="<?= __('civinsis.auth.cambiar_tema') ?>" aria-label="<?= __('civinsis.auth.cambiar_tema') ?>">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>
    </div>

    <div class="auth-form-wrap">

      <a href="index.php" class="auth-brand auth-brand-mobile">
        <span class="auth-brand-icon"><img src="<?= asset('media/logo.png') ?>" alt=""></span>
        <span class="auth-brand-name">CIVINSIS</span>
      </a>

      <div class="tabs" id="tabs" data-active="<?= $activeTab === 'registro' ? 'register' : 'login' ?>">
        <div class="tab-pill"></div>
        <button class="tab-btn <?= $activeTab !== 'registro' ? 'active' : '' ?>" data-tab="login" id="tab-login">
          <i class="fas fa-right-to-bracket"></i><span><?= __('civinsis.auth.tab_login') ?></span>
        </button>
        <button class="tab-btn <?= $activeTab === 'registro' ? 'active' : '' ?>" data-tab="register" id="tab-register">
          <i class="fas fa-user-plus"></i><span><?= __('civinsis.auth.tab_registro') ?></span>
        </button>
      </div>

      <div class="forms-scene" id="formsScene">

        <!-- ── LOGIN ── -->
        <div class="form-panel <?= $activeTab !== 'registro' ? 'is-active' : '' ?>" id="panel-login">
          <div class="form-head">
            <h1><?= __('civinsis.auth.login_titulo') ?></h1>
            <p><?= __('civinsis.auth.login_subtitulo') ?></p>
          </div>
          <form id="loginForm" novalidate>
            <div class="form-group">
              <label class="form-label" for="login-email"><?= __('civinsis.auth.correo_electronico') ?></label>
              <div class="input-wrap">
                <i class="ico fas fa-envelope"></i>
                <input class="field" type="email" id="login-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="login-pass"><?= __('civinsis.auth.contrasena') ?></label>
              <div class="input-wrap">
                <i class="ico fas fa-lock"></i>
                <input class="field" type="password" id="login-pass" name="password" placeholder="<?= __('civinsis.auth.contrasena_placeholder') ?>" autocomplete="current-password" required>
                <button type="button" class="eye-btn" data-for="login-pass" aria-label="<?= __('civinsis.auth.mostrar_contrasena') ?>"><i class="fas fa-eye"></i></button>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-extras">
              <label class="remember"><input type="checkbox" name="remember"> <span><?= __('civinsis.auth.recuerdame') ?></span></label>
              <a href="<?= route('custom.password.request') ?>" class="forgot-link"><?= __('civinsis.auth.olvidaste_contrasena') ?></a>
            </div>
            <button type="submit" class="submit-btn">
              <span><?= __('civinsis.auth.boton_iniciar_sesion') ?></span> <i class="fas fa-arrow-right arrow"></i>
            </button>
            
            <div class="auth-divider"><?= __('civinsis.auth.o_continua_con') ?></div>

            <a href="<?= route('google.login') ?>" class="btn-google">
              <i class="fab fa-google"></i>
              <span><?= __('civinsis.auth.boton_google_login') ?></span>
            </a>
          </form>
          <p class="form-switch"><?= __('civinsis.auth.no_tienes_cuenta') ?> <button type="button" class="switch-btn" data-to="register"><?= __('civinsis.auth.registrate_aqui') ?></button></p>
        </div>

        <!-- ── REGISTRO ── -->
        <div class="form-panel <?= $activeTab === 'registro' ? 'is-active' : '' ?>" id="panel-register">
          <div class="form-head">
            <h1><?= __('civinsis.auth.registro_titulo') ?></h1>
            <p><?= __('civinsis.auth.registro_subtitulo') ?></p>
          </div>
          <form id="registerForm" novalidate>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="reg-nombre"><?= __('civinsis.auth.nombre') ?></label>
                <div class="input-wrap">
                  <i class="ico fas fa-user"></i>
                  <input class="field" type="text" id="reg-nombre" name="nombre" placeholder="<?= __('civinsis.auth.nombre_placeholder') ?>" required>
                  <div class="focus-line"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="reg-apellido"><?= __('civinsis.auth.apellido') ?></label>
                <div class="input-wrap">
                  <i class="ico fas fa-user"></i>
                  <input class="field" type="text" id="reg-apellido" name="apellido" placeholder="<?= __('civinsis.auth.apellido_placeholder') ?>" required>
                  <div class="focus-line"></div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="reg-email"><?= __('civinsis.auth.correo_electronico') ?></label>
              <div class="input-wrap">
                <i class="ico fas fa-envelope"></i>
                <input class="field" type="email" id="reg-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="reg-pass"><?= __('civinsis.auth.contrasena') ?></label>
                <div class="input-wrap">
                  <i class="ico fas fa-lock"></i>
                  <input class="field" type="password" id="reg-pass" name="password" placeholder="<?= __('civinsis.auth.contrasena_min') ?>" minlength="8" required>
                  <button type="button" class="eye-btn" data-for="reg-pass" aria-label="<?= __('civinsis.auth.mostrar_contrasena') ?>"><i class="fas fa-eye"></i></button>
                  <div class="focus-line"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="reg-confirm"><?= __('civinsis.auth.confirmar') ?></label>
                <div class="input-wrap">
                  <i class="ico fas fa-lock"></i>
                  <input class="field" type="password" id="reg-confirm" name="confirm_password" placeholder="<?= __('civinsis.auth.confirmar_placeholder') ?>" required>
                  <button type="button" class="eye-btn" data-for="reg-confirm" aria-label="<?= __('civinsis.auth.mostrar_contrasena') ?>"><i class="fas fa-eye"></i></button>
                  <div class="focus-line"></div>
                </div>
              </div>
            </div>
            <div class="check-group">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms"><?= __('civinsis.auth.acepto_pre') ?> <a href="terminos.php"><?= __('civinsis.auth.terminos_condiciones') ?></a> <?= __('civinsis.auth.acepto_y') ?> <a href="privacidad.php"><?= __('civinsis.auth.politica_privacidad') ?></a></label>
            </div>
            <button type="submit" class="submit-btn">
              <span><?= __('civinsis.auth.boton_crear_cuenta') ?></span> <i class="fas fa-arrow-right arrow"></i>
            </button>
            
            <div class="auth-divider"><?= __('civinsis.auth.o_continua_con') ?></div>

            <a href="<?= route('google.login') ?>" class="btn-google">
              <i class="fab fa-google"></i>
              <span><?= __('civinsis.auth.boton_google_registro') ?></span>
            </a>
          </form>
          <p class="form-switch"><?= __('civinsis.auth.ya_tienes_cuenta') ?> <button type="button" class="switch-btn" data-to="login"><?= __('civinsis.auth.inicia_sesion_aqui') ?></button></p>
        </div>

      </div><!-- /forms-scene -->
    </div><!-- /auth-form-wrap -->
  </main>
</div><!-- /auth-split -->

<div class="toast-container" id="toastContainer"></div>
<?php
// auth.php es una página standalone (no incluye layouts/footer.php), así que
// expone aquí solo lo que auth-forms.js necesita para traducir sus toasts.
$authI18n = ['toast' => __('civinsis.toast')];
?>
<script>
window.CIVI_I18N = <?= str_replace('</', '<\/', json_encode($authI18n, JSON_UNESCAPED_UNICODE)) ?>;
</script>
<script src="js/auth.js?v=2"></script>
<script src="js/auth-forms.js"></script>
<?php if (session('status')): ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (window.showAuthToast) showAuthToast(<?= json_encode(session('status'), JSON_UNESCAPED_UNICODE) ?>, 'success');
  });
</script>
<?php endif; ?>
</body>
</html>
