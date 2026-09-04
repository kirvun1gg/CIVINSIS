<?php
// verify-google.php - Verificación del código enviado al iniciar sesión con
// Google (GoogleAuthController::showVerifyView/verify). Mismo layout
// split-screen que auth.php para mantener consistencia visual.
//
// $errors lo inyecta Illuminate\View\Middleware\ShareErrorsFromSession en
// TODAS las vistas del grupo "web"; $email lo pasa explícitamente
// GoogleAuthController::showVerifyView(). Los valores de abajo nunca se usan
// en producción - solo evitan que el IDE marque las variables como
// indefinidas y sirven de red de seguridad.
$errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
$email  = $email ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.auth.verify_titulo') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/auth-styles.css') ?>">
</head>
<body>

<div class="auth-split">

  <!-- ══════════ MITAD IZQUIERDA · Panel visual ══════════ -->
  <aside class="auth-visual">
    <div class="auth-visual-orb orb-a"></div>
    <div class="auth-visual-orb orb-b"></div>

    <div class="auth-visual-inner">
      <a href="<?= url('/') ?>" class="auth-brand">
        <span class="auth-brand-icon"><img src="<?= asset('media/logo.png') ?>" alt=""></span>
        <span class="auth-brand-name">CIVINSIS</span>
      </a>

      <div class="auth-visual-caption">
        <h2><?= __('civinsis.auth.visual_titulo') ?></h2>
        <p><?= __('civinsis.auth.visual_desc') ?></p>
      </div>
    </div>
  </aside>

  <!-- ══════════ MITAD DERECHA · Formulario ══════════ -->
  <main class="auth-form-side">

    <div class="auth-topbar">
      <a href="<?= url('/') ?>" class="auth-back"><i class="fas fa-arrow-left"></i> <?= __('civinsis.auth.volver_inicio') ?></a>
      <button class="theme-btn" id="themeBtn" title="<?= __('civinsis.auth.cambiar_tema') ?>" aria-label="<?= __('civinsis.auth.cambiar_tema') ?>">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>
    </div>

    <div class="auth-form-wrap">

      <a href="<?= url('/') ?>" class="auth-brand auth-brand-mobile">
        <span class="auth-brand-icon"><img src="<?= asset('media/logo.png') ?>" alt=""></span>
        <span class="auth-brand-name">CIVINSIS</span>
      </a>

      <div class="form-panel is-active">
        <div class="form-head">
          <h1><i class="fas fa-shield-halved" style="color:var(--c-teal);margin-right:.4rem"></i><?= __('civinsis.auth.verify_titulo') ?></h1>
          <p><?= __('civinsis.auth.verify_subtitulo') ?></p>
        </div>

        <?php if ($errors->any()): ?>
          <div class="auth-error-box">
            <?php foreach ($errors->all() as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= route('google.verify') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

          <div class="form-group">
            <label class="form-label" for="verify-code"><?= __('civinsis.auth.verify_campo_codigo') ?></label>
            <div class="input-wrap">
              <input class="field verify-code-field" type="text" id="verify-code" name="code"
                placeholder="<?= __('civinsis.auth.verify_campo_codigo_placeholder') ?>"
                maxlength="6" autocomplete="one-time-code" autofocus required>
              <div class="focus-line"></div>
            </div>
          </div>

          <button type="submit" class="submit-btn">
            <span><?= __('civinsis.auth.verify_boton') ?></span> <i class="fas fa-arrow-right arrow"></i>
          </button>
        </form>

        <p class="form-switch">
          <a href="<?= url('/auth.php') ?>" class="switch-btn" style="text-decoration:none"><?= __('civinsis.auth.verify_volver') ?></a>
        </p>
      </div>

    </div>
  </main>
</div>

<script src="<?= asset('js/auth-verify.js') ?>"></script>
</body>
</html>
