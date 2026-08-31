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
