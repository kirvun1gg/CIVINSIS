<?php
// auth.php - Página de autenticación CIVINSIS (v3 · split screen)
$activeTab = $_GET['tab'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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

      <!-- ╔══════════════════════════════════════════════════════╗
           ║  ▼▼▼  ZONA PARA TU ANIMACIÓN  ▼▼▼                   ║
           ║  Reemplaza el contenido de este div por tu          ║
           ║  animación (Lottie, <video>, SVG, canvas, iframe…). ║
           ║  Ocupa todo el espacio disponible automáticamente.  ║
           ╚══════════════════════════════════════════════════════╝ -->
      <div class="auth-animation-slot" id="authAnimationSlot">
        <div class="auth-anim-placeholder">
          <i class="fas fa-people-group"></i>
          <p>Tu animación va aquí</p>
          <span>Sustituye el contenido de <code>#authAnimationSlot</code></span>
        </div>
      </div>
      <!-- ▲▲▲  FIN ZONA ANIMACIÓN  ▲▲▲ -->

      <div class="auth-visual-caption">
        <h2>Tu voz construye comunidad</h2>
        <p>Propón ideas, debate con otros jóvenes y transforma tu entorno.</p>
      </div>
    </div>
  </aside>

  <!-- ══════════ MITAD DERECHA · Formulario ══════════ -->
  <main class="auth-form-side">

    <div class="auth-topbar">
      <a href="index.php" class="auth-back"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
      <button class="theme-btn" id="themeBtn" title="Cambiar tema" aria-label="Cambiar tema">
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
          <i class="fas fa-right-to-bracket"></i><span>Iniciar sesión</span>
        </button>
        <button class="tab-btn <?= $activeTab === 'registro' ? 'active' : '' ?>" data-tab="register" id="tab-register">
          <i class="fas fa-user-plus"></i><span>Registrarse</span>
        </button>
      </div>

      <div class="forms-scene" id="formsScene">

        <!-- ── LOGIN ── -->
        <div class="form-panel <?= $activeTab !== 'registro' ? 'is-active' : '' ?>" id="panel-login">
          <div class="form-head">
            <h1>¡Bienvenido/a de vuelta!</h1>
            <p>Ingresa para seguir participando en tu comunidad</p>
          </div>
          <form id="loginForm" novalidate>
            <div class="form-group">
              <label class="form-label" for="login-email">Correo electrónico</label>
              <div class="input-wrap">
                <i class="ico fas fa-envelope"></i>
                <input class="field" type="email" id="login-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="login-pass">Contraseña</label>
              <div class="input-wrap">
                <i class="ico fas fa-lock"></i>
                <input class="field" type="password" id="login-pass" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                <button type="button" class="eye-btn" data-for="login-pass" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-extras">
              <label class="remember"><input type="checkbox" name="remember"> <span>Recuérdame</span></label>
              <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="submit-btn">
              <span>Iniciar sesión</span> <i class="fas fa-arrow-right arrow"></i>
            </button>
          </form>
          <p class="form-switch">¿No tienes cuenta? <button type="button" class="switch-btn" data-to="register">Regístrate aquí</button></p>
        </div>

        <!-- ── REGISTRO ── -->
        <div class="form-panel <?= $activeTab === 'registro' ? 'is-active' : '' ?>" id="panel-register">
          <div class="form-head">
            <h1>Únete a la comunidad</h1>
            <p>Crea tu cuenta y empieza a generar cambio</p>
          </div>
          <form id="registerForm" novalidate>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="reg-nombre">Nombre</label>
                <div class="input-wrap">
                  <i class="ico fas fa-user"></i>
                  <input class="field" type="text" id="reg-nombre" name="nombre" placeholder="Tu nombre" required>
                  <div class="focus-line"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="reg-apellido">Apellido</label>
                <div class="input-wrap">
                  <i class="ico fas fa-user"></i>
                  <input class="field" type="text" id="reg-apellido" name="apellido" placeholder="Tu apellido" required>
                  <div class="focus-line"></div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="reg-email">Correo electrónico</label>
              <div class="input-wrap">
                <i class="ico fas fa-envelope"></i>
                <input class="field" type="email" id="reg-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="reg-pass">Contraseña</label>
                <div class="input-wrap">
                  <i class="ico fas fa-lock"></i>
                  <input class="field" type="password" id="reg-pass" name="password" placeholder="Mín. 8 caracteres" minlength="8" required>
                  <button type="button" class="eye-btn" data-for="reg-pass" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                  <div class="focus-line"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="reg-confirm">Confirmar</label>
                <div class="input-wrap">
                  <i class="ico fas fa-lock"></i>
                  <input class="field" type="password" id="reg-confirm" name="confirm_password" placeholder="Repite la contraseña" required>
                  <button type="button" class="eye-btn" data-for="reg-confirm" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                  <div class="focus-line"></div>
                </div>
              </div>
            </div>
            <div class="check-group">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms">Acepto los <a href="terminos.php">Términos y Condiciones</a> y la <a href="privacidad.php">Política de Privacidad</a></label>
            </div>
            <button type="submit" class="submit-btn">
              <span>Crear cuenta</span> <i class="fas fa-arrow-right arrow"></i>
            </button>
          </form>
          <p class="form-switch">¿Ya tienes cuenta? <button type="button" class="switch-btn" data-to="login">Inicia sesión aquí</button></p>
        </div>

      </div><!-- /forms-scene -->
    </div><!-- /auth-form-wrap -->
  </main>
</div><!-- /auth-split -->

<div class="toast-container" id="toastContainer"></div>
<script src="js/auth.js"></script>
<script>
// ── Conectar frontend con backend PHP ──────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const loginForm    = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  // ── Utilidad: resolver redirect ──
  // El backend devuelve '../dashboard.php' (relativo a /php/).
  // Desde /auth.php lo convertimos a 'dashboard.php'
  function resolveRedirect(url) {
    if (!url) return 'inicio.php';
    return url.replace(/^\.\.\//, ''); // quita '../'
  }

  // ── Utilidad: toast ──
  function toast(msg, type) {
    const c = document.getElementById('toastContainer');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    const icons = { success:'fa-check-circle', error:'fa-times-circle', info:'fa-info-circle' };
    t.innerHTML = `<i class="fas ${icons[type]||icons.info} toast-icon ${type}"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.classList.add('fade-out'); setTimeout(() => t.remove(), 350); }, 3500);
  }

  // ── Utilidad: llamada segura al backend ──
  async function callBackend(fd) {
    const r = await fetch('php/auth.php', { method: 'POST', body: fd });
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch {
      console.error('Respuesta no-JSON del servidor:', text);
      return { success: false, message: 'Error interno del servidor. Revisa la consola.' };
    }
  }

  // ── LOGIN ──
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email    = loginForm.querySelector('[name="email"]').value.trim();
    const password = loginForm.querySelector('[name="password"]').value;

    if (!email || !password) { toast('Completa todos los campos', 'error'); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { toast('Correo no válido', 'error'); return; }

    const btn  = loginForm.querySelector('.submit-btn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';

    const fd = new FormData();
    fd.append('accion',   'login');   // ← el backend usa 'accion'
    fd.append('email',    email);
    fd.append('password', password);

    const d = await callBackend(fd);

    if (d.success) {
      toast('¡Bienvenido/a! Redirigiendo...', 'success');
      setTimeout(() => window.location.href = resolveRedirect(d.redirect), 900);
    } else {
      toast(d.message || 'Credenciales incorrectas', 'error');
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  }, true);

  // ── REGISTRO ──
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre   = registerForm.querySelector('[name="nombre"]').value.trim();
    const apellido = registerForm.querySelector('[name="apellido"]').value.trim();
    const email    = registerForm.querySelector('[name="email"]').value.trim();
    const password = registerForm.querySelector('[name="password"]').value;
    const confirm  = registerForm.querySelector('[name="confirm_password"]').value;
    const terms    = registerForm.querySelector('[name="terms"]').checked;

    if (!nombre || !apellido || !email || !password || !confirm) {
      toast('Completa todos los campos', 'error'); return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      toast('Correo electrónico no válido', 'error'); return;
    }
    if (password.length < 8) {
      toast('La contraseña necesita al menos 8 caracteres', 'error'); return;
    }
    if (password !== confirm) {
      toast('Las contraseñas no coinciden', 'error'); return;
    }
    if (!terms) {
      toast('Debes aceptar los términos de uso', 'error'); return;
    }

    const btn  = registerForm.querySelector('.submit-btn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...';

    const fd = new FormData();
    fd.append('accion',           'registro');  // ← el backend usa 'registro'
    fd.append('nombre',           nombre);
    fd.append('apellido',         apellido);
    fd.append('email',            email);
    fd.append('password',         password);
    fd.append('confirm_password', confirm);

    const d = await callBackend(fd);

    if (d.success) {
      toast('¡Cuenta creada! Redirigiendo...', 'success');
      setTimeout(() => window.location.href = resolveRedirect(d.redirect), 900);
    } else {
      toast(d.message || 'Error al crear cuenta', 'error');
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  }, true);
});
</script>
</body>
</html>
