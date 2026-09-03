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
        <span class="auth-brand-icon"><img src="/media/logo.png" alt=""></span>
        <span class="auth-brand-name">CIVINSIS</span>
      </a>
      <div class="auth-animation-slot" id="authAnimationSlot">

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
        <span class="auth-brand-icon"><img src="/media/logo.png" alt=""></span>
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
            
            <div style="text-align: center; margin: 20px 0; color: #aaa; font-size: 14px;">O</div>
            
            <a href="/auth/google" class="submit-btn" style="background-color: #fff; color: #444; border: 1px solid #ccc; text-align: center; display: flex; align-items: center; justify-content: center; text-decoration: none;">
              <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" style="width: 20px; height: 20px; margin-right: 10px;">
              <span>Iniciar sesión con Google</span>
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
            
            <div style="text-align: center; margin: 20px 0; color: #aaa; font-size: 14px;">O</div>
            
            <a href="/auth/google" class="submit-btn" style="background-color: #fff; color: #444; border: 1px solid #ccc; text-align: center; display: flex; align-items: center; justify-content: center; text-decoration: none;">
              <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" style="width: 20px; height: 20px; margin-right: 10px;">
              <span>Registrarse con Google</span>
            </a>
          </form>
          <p class="form-switch"><?= __('civinsis.auth.ya_tienes_cuenta') ?> <button type="button" class="switch-btn" data-to="login"><?= __('civinsis.auth.inicia_sesion_aqui') ?></button></p>
        </div>

      </div><!-- /forms-scene -->
    </div><!-- /auth-form-wrap -->
  </main>
</div><!-- /auth-split -->

<div class="toast-container" id="toastContainer"></div>
<script src="js/auth.js?v=2"></script>
<script src="js/auth-forms.js"></script>
</body>
</html>
