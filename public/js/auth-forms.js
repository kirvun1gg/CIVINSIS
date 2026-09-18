// ── Conectar frontend con backend PHP ──────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const loginForm    = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const i18n = (window.CIVI_I18N && CIVI_I18N.toast) || {};
  const tc = i18n.comunes || {};
  const ta = i18n.auth || {};

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
      return { success: false, message: ta.error_interno_servidor || 'Error interno del servidor. Revisa la consola.' };
    }
  }

  // ── LOGIN ──
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email    = loginForm.querySelector('[name="email"]').value.trim();
    const password = loginForm.querySelector('[name="password"]').value;

    if (!email || !password) { toast(ta.completa_campos || 'Completa todos los campos', 'error'); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { toast(ta.correo_no_valido || 'Correo no válido', 'error'); return; }

    const btn  = loginForm.querySelector('.submit-btn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${ta.ingresando || 'Ingresando...'}`;

    const fd = new FormData();
    fd.append('accion',   'login');   // ← el backend usa 'accion'
    fd.append('email',    email);
    fd.append('password', password);

    const d = await callBackend(fd);

    if (d.success) {
      toast(ta.bienvenido_redirigiendo || '¡Bienvenido/a! Redirigiendo...', 'success');
      setTimeout(() => window.location.href = resolveRedirect(d.redirect), 900);
    } else {
      toast(d.message || ta.credenciales_incorrectas || 'Credenciales incorrectas', 'error');
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
      toast(ta.completa_campos || 'Completa todos los campos', 'error'); return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      toast(ta.correo_no_valido_registro || 'Correo electrónico no válido', 'error'); return;
    }
    if (password.length < 8) {
      toast(ta.password_min_caracteres || 'La contraseña necesita al menos 8 caracteres', 'error'); return;
    }
    if (password !== confirm) {
      toast(tc.contrasenas_no_coinciden || 'Las contraseñas no coinciden', 'error'); return;
    }
    if (!terms) {
      toast(ta.aceptar_terminos || 'Debes aceptar los términos de uso', 'error'); return;
    }

    const btn  = registerForm.querySelector('.submit-btn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${ta.creando_cuenta || 'Creando cuenta...'}`;

    const fd = new FormData();
    fd.append('accion',           'registro');  // ← el backend usa 'registro'
    fd.append('nombre',           nombre);
    fd.append('apellido',         apellido);
    fd.append('email',            email);
    fd.append('password',         password);
    fd.append('confirm_password', confirm);

    const d = await callBackend(fd);

    if (d.success) {
      toast(ta.cuenta_creada_redirigiendo || '¡Cuenta creada! Redirigiendo...', 'success');
      setTimeout(() => window.location.href = resolveRedirect(d.redirect), 900);
    } else {
      toast(d.message || ta.error_crear_cuenta || 'Error al crear cuenta', 'error');
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  }, true);
});
