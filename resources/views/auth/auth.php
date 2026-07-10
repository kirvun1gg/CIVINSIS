<?php
// auth.php - Página de autenticación CIVINSIS (v2 – diseño espectacular)
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

<!-- ══ FONDO ANIMADO ESPECTACULAR ══════════════════════════ -->
<div class="bg-scene">
  <div class="bg-mesh"></div>
  <div class="bg-grid"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div class="orb orb-4"></div>
  <div class="ring ring-1"></div>
  <div class="ring ring-2"></div>
  <div class="ring ring-3"></div>
  <canvas id="bg-canvas"></canvas>
  <svg class="waves" viewBox="0 0 2880 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    <path class="wave-path" d="M0,80 C240,140 480,20 720,80 C960,140 1200,20 1440,80 C1680,140 1920,20 2160,80 C2400,140 2640,20 2880,80 L2880,200 L0,200 Z" fill="rgba(54,192,161,0.28)"/>
    <path class="wave-path" d="M0,100 C300,160 600,40 900,100 C1200,160 1500,40 1800,100 C2100,160 2400,40 2700,100 L2880,100 L2880,200 L0,200 Z" fill="rgba(239,126,34,0.18)"/>
    <path class="wave-path" d="M0,120 C400,60 800,160 1200,120 C1600,80 2000,160 2400,120 L2880,120 L2880,200 L0,200 Z" fill="rgba(54,192,161,0.12)"/>
  </svg>
</div>

<!-- ══ PÁGINA ════════════════════════════════════════════════ -->
<div class="auth-page">

  <!-- Barra superior -->
  <div class="top-bar">
    <a href="index.php" class="brand">
      <div class="brand-icon"><i class="fas fa-city"></i></div>
      <span class="brand-name">CIVINSIS</span>
    </a>
    <div style="display:flex;align-items:center;gap:10px">
      <a href="index.php" class="theme-btn" title="Volver al inicio" style="font-size:.85rem;width:auto;padding:0 14px;border-radius:22px;gap:6px;display:flex;align-items:center">
        <i class="fas fa-arrow-left"></i> Inicio
      </a>
      <button class="theme-btn" id="themeBtn" title="Cambiar modo">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>
    </div>
  </div>

  <!-- ── Card principal ── -->
  <div class="auth-card">

    <div class="card-header">
      <div class="card-logo"><i class="fas fa-city"></i></div>
      <div class="card-title">CIVINSIS</div>
      <div class="card-sub">Participación Social Juvenil</div>
    </div>

    <!-- Tabs -->
    <div class="tabs" id="tabs" data-active="<?= $activeTab === 'registro' ? 'register' : 'login' ?>">
      <div class="tab-pill"></div>
      <button class="tab-btn <?= $activeTab !== 'registro' ? 'active' : '' ?>" data-tab="login" id="tab-login">
        <i class="fas fa-sign-in-alt"></i><span>Iniciar Sesión</span>
      </button>
      <button class="tab-btn <?= $activeTab === 'registro' ? 'active' : '' ?>" data-tab="register" id="tab-register">
        <i class="fas fa-user-plus"></i><span>Registrarse</span>
      </button>
    </div>

    <!-- Escena de formularios -->
    <div class="forms-scene" id="formsScene">

      <!-- ── PANEL LOGIN ── -->
      <div class="form-panel <?= $activeTab !== 'registro' ? 'is-active' : '' ?>" id="panel-login">
        <div class="form-head">
          <h2>¡Bienvenido/a de vuelta!</h2>
          <p>Ingresa a tu cuenta para participar en la comunidad</p>
        </div>
        <form id="loginForm" novalidate>
          <div class="form-group">
            <label class="form-label" for="login-email"><i class="fas fa-envelope"></i> Correo</label>
            <div class="input-wrap">
              <i class="ico fas fa-envelope"></i>
              <input class="field" type="email" id="login-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
              <div class="focus-line"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="login-pass"><i class="fas fa-lock"></i> Contraseña</label>
            <div class="input-wrap">
              <i class="ico fas fa-lock"></i>
              <input class="field" type="password" id="login-pass" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
              <button type="button" class="eye-btn" data-for="login-pass"><i class="fas fa-eye"></i></button>
              <div class="focus-line"></div>
            </div>
          </div>
          <div class="form-extras">
            <label><input type="checkbox" name="remember"> <span>Recuérdame</span></label>
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
          </div>
          <button type="submit" class="submit-btn">
            <span>Iniciar Sesión</span><i class="fas fa-arrow-right arrow"></i>
          </button>
        </form>
        <div class="form-switch">¿No tienes cuenta? <button type="button" class="switch-btn" data-to="register">Regístrate aquí</button></div>
      </div>

      <!-- ── PANEL REGISTRO ── -->
      <div class="form-panel <?= $activeTab === 'registro' ? 'is-active' : '' ?>" id="panel-register">
        <div class="form-head">
          <h2>Únete a la Comunidad</h2>
          <p>Crea tu cuenta y comienza a generar cambio</p>
        </div>
        <form id="registerForm" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nombre</label>
              <div class="input-wrap">
                <i class="ico fas fa-user"></i>
                <input class="field" type="text" id="reg-nombre" name="nombre" placeholder="Tu nombre" required>
                <div class="focus-line"></div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Apellido</label>
              <div class="input-wrap">
                <i class="ico fas fa-user"></i>
                <input class="field" type="text" id="reg-apellido" name="apellido" placeholder="Tu apellido" required>
                <div class="focus-line"></div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-envelope"></i> Correo</label>
            <div class="input-wrap">
              <i class="ico fas fa-envelope"></i>
              <input class="field" type="email" id="reg-email" name="email" placeholder="tu@correo.com" autocomplete="email" required>
              <div class="focus-line"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> Contraseña</label>
            <div class="input-wrap">
              <i class="ico fas fa-lock"></i>
              <input class="field" type="password" id="reg-pass" name="password" placeholder="Mínimo 8 caracteres" minlength="8" required>
              <button type="button" class="eye-btn" data-for="reg-pass"><i class="fas fa-eye"></i></button>
              <div class="focus-line"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> Confirmar contraseña</label>
            <div class="input-wrap">
              <i class="ico fas fa-lock"></i>
              <input class="field" type="password" id="reg-confirm" name="confirm_password" placeholder="Repite tu contraseña" required>
              <button type="button" class="eye-btn" data-for="reg-confirm"><i class="fas fa-eye"></i></button>
              <div class="focus-line"></div>
            </div>
          </div>
          <div class="form-group check-group">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">Acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a></label>
          </div>
          <button type="submit" class="submit-btn">
            <span>Crear Cuenta</span><i class="fas fa-arrow-right arrow"></i>
          </button>
        </form>
        <div class="form-switch">¿Ya tienes cuenta? <button type="button" class="switch-btn" data-to="login">Inicia sesión aquí</button></div>
      </div>

    </div><!-- /forms-scene -->
  </div><!-- /auth-card -->
</div><!-- /auth-page -->

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
    if (!url) return 'dashboard.php';
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
