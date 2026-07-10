/**
 * CIVINSIS — Auth v2
 * Transición flip 3D morph · Partículas · Dark/Light toggle
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

  // ── Elementos ──────────────────────────────────
  const html         = document.documentElement;
  const themeBtn     = document.getElementById('themeBtn');
  const themeIcon    = document.getElementById('themeIcon');
  const tabs         = document.getElementById('tabs');
  const tabBtns      = document.querySelectorAll('.tab-btn');
  const switchBtns   = document.querySelectorAll('.switch-btn');
  const eyeBtns      = document.querySelectorAll('.eye-btn');
  const loginForm    = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const panelLogin   = document.getElementById('panel-login');
  const panelReg     = document.getElementById('panel-register');
  const scene        = document.getElementById('formsScene');

  let currentPanel = 'login';
  let isTransitioning = false;

  // ── Sincronizar altura del scene con el panel activo ──────────
  // Necesario porque los paneles inactivos tienen position:absolute
  // y no contribuyen al alto del contenedor.
  function syncHeight(panel) {
    // Medir la altura real del panel (position:relative = is-active)
    scene.style.height = panel.offsetHeight + 'px';
  }

  // Sincronizar en resize
  window.addEventListener('resize', () => {
    if (!isTransitioning) {
      syncHeight(currentPanel === 'login' ? panelLogin : panelReg);
    }
  }, { passive: true });

  // ════════════════════════════════════════════════
  // TEMA DARK / LIGHT
  // ════════════════════════════════════════════════
  function applyTheme(mode) {
    if (mode === 'light') {
      html.classList.add('light-mode');
      themeIcon.className = 'fas fa-sun';
    } else {
      html.classList.remove('light-mode');
      themeIcon.className = 'fas fa-moon';
    }
  }

  function initTheme() {
    const saved = localStorage.getItem('civitas-theme');
    const sys   = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    applyTheme(saved || sys);
  }

  themeBtn.addEventListener('click', () => {
    const isLight = html.classList.toggle('light-mode');
    themeIcon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('civitas-theme', isLight ? 'light' : 'dark');

    // Micro animación del botón
    themeBtn.style.transform = 'rotate(360deg) scale(1.15)';
    setTimeout(() => { themeBtn.style.transform = ''; }, 450);
  });

  initTheme();

  // Sincronizar altura inicial del scene (login panel visible por defecto)
  // Con pequeño delay para que el DOM esté completamente renderizado
  requestAnimationFrame(() => syncHeight(panelLogin));

  // ════════════════════════════════════════════════
  // TRANSICIÓN FLIP 3D MORPH
  // ════════════════════════════════════════════════
  /**
   * Algoritmo de la transición:
   * 1. Fijar la altura del scene al panel activo actual (evita salto de layout)
   * 2. El panel activo recibe exit-left o exit-right (según dirección)
   * 3. El nuevo panel entra desde enter-right o enter-left
   * 4. Tras 30ms (para que el browser pinte el estado inicial),
   *    el nuevo panel recibe is-active (dispara la transición CSS)
   * 5. Cuando la transición termina (~550ms) limpiamos las clases
   *    y soltamos la altura fija del scene
   */
  function switchTo(target) {
    if (target === currentPanel || isTransitioning) return;
    isTransitioning = true;

    const fromPanel = currentPanel === 'login' ? panelLogin : panelReg;
    const toPanel   = target === 'login' ? panelLogin : panelReg;
    const goingRight = (target === 'register'); // login→register

    // 1. Fijar altura con el panel que sale (evita colapso)
    syncHeight(fromPanel);

    // 2. Sacar el panel activo del flujo con su clase de salida
    fromPanel.classList.remove('is-active');
    fromPanel.classList.add(goingRight ? 'exit-left' : 'exit-right');

    // 3. Colocar el panel entrante en su posición inicial (invisible)
    toPanel.classList.remove('is-active', 'exit-left', 'exit-right', 'enter-left', 'enter-right');
    toPanel.classList.add(goingRight ? 'enter-right' : 'enter-left');

    // 4. Doble rAF: garantiza que el browser pintó el estado inicial
    //    antes de activar la transición
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        // Activar el panel entrante → dispara transición CSS
        toPanel.classList.remove('enter-right', 'enter-left');
        toPanel.classList.add('is-active');

        // Actualizar tabs
        tabs.setAttribute('data-active', target);
        tabBtns.forEach(b => {
          b.classList.toggle('active', b.getAttribute('data-tab') === target);
        });

        currentPanel = target;

        // Actualizar la altura del scene al nuevo panel
        // (con pequeño delay para que haya terminado de renderizar)
        setTimeout(() => syncHeight(toPanel), 60);
      });
    });

    // 5. Limpieza tras la transición
    const DURATION = 580;
    setTimeout(() => {
      fromPanel.classList.remove('exit-left', 'exit-right');
      isTransitioning = false;
    }, DURATION);
  }

  // Clicks en los tabs
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => switchTo(btn.getAttribute('data-tab')));
  });

  // Clicks en "¿No tienes cuenta? / ¿Ya tienes cuenta?"
  switchBtns.forEach(btn => {
    btn.addEventListener('click', () => switchTo(btn.getAttribute('data-to')));
  });

  // ════════════════════════════════════════════════
  // MOSTRAR / OCULTAR CONTRASEÑA
  // ════════════════════════════════════════════════
  eyeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const inputId = btn.getAttribute('data-for');
      const input   = document.getElementById(inputId);
      const icon    = btn.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
      }

      btn.style.transform = 'scale(1.2)';
      setTimeout(() => { btn.style.transform = ''; }, 200);
    });
  });

  // ════════════════════════════════════════════════
  // TOASTS
  // ════════════════════════════════════════════════
  function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <i class="fas ${icons[type] || icons.info} toast-icon ${type}"></i>
      <span>${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('fade-out');
      setTimeout(() => toast.remove(), 350);
    }, 3200);
  }

  // ════════════════════════════════════════════════
  // VALIDACIONES
  // ════════════════════════════════════════════════
  function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  // ════════════════════════════════════════════════
  // FORM — LOGIN
  // ════════════════════════════════════════════════
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email    = loginForm.querySelector('[name="email"]').value.trim();
    const password = loginForm.querySelector('[name="password"]').value;
    const remember = loginForm.querySelector('[name="remember"]').checked;

    if (!email || !password) {
      showToast('Por favor completa todos los campos', 'error');
      return;
    }
    if (!isEmail(email)) {
      showToast('Ingresa un correo electrónico válido', 'error');
      return;
    }

    const btn = loginForm.querySelector('.submit-btn');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';

    try {
      const fd = new FormData();
      fd.append('action', 'login');
      fd.append('email', email);
      fd.append('password', password);
      fd.append('remember', remember ? 'on' : '');

      const res  = await fetch('auth-handler.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast('¡Bienvenido! Redirigiendo...', 'success');
        setTimeout(() => { window.location.href = data.redirect || 'dashboard.php'; }, 900);
      } else {
        showToast(data.errors?.[0] || data.message || 'Credenciales incorrectas', 'error');
        btn.disabled = false;
        btn.innerHTML = origHTML;
      }
    } catch {
      showToast('Error de conexión con el servidor', 'error');
      btn.disabled = false;
      btn.innerHTML = origHTML;
    }
  });

  // ════════════════════════════════════════════════
  // FORM — REGISTRO
  // ════════════════════════════════════════════════
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const get = (n) => registerForm.querySelector(`[name="${n}"]`);

    const nombre   = get('nombre').value.trim();
    const usuario  = get('usuario').value.trim();
    const email    = get('email').value.trim();
    const genero   = get('genero').value;
    const password = get('password').value;
    const confirm  = get('confirm_password').value;
    const terms    = get('terms').checked;
    const privacy  = get('privacy').checked;

    if (!nombre || !usuario || !email || !genero || !password || !confirm) {
      showToast('Por favor completa todos los campos', 'error');
      return;
    }
    if (!isEmail(email)) {
      showToast('Ingresa un correo electrónico válido', 'error');
      return;
    }
    if (password.length < 8) {
      showToast('La contraseña debe tener al menos 8 caracteres', 'error');
      return;
    }
    if (password !== confirm) {
      showToast('Las contraseñas no coinciden', 'error');
      return;
    }
    if (!/^[a-zA-Z0-9_-]{3,}$/.test(usuario)) {
      showToast('El usuario debe tener al menos 3 caracteres (letras, números, _ -)', 'error');
      return;
    }
    if (!terms || !privacy) {
      showToast('Debes aceptar los términos y la política de privacidad', 'error');
      return;
    }

    const btn = registerForm.querySelector('.submit-btn');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...';

    try {
      const fd = new FormData();
      fd.append('action', 'register');
      fd.append('nombre', nombre);
      fd.append('usuario', usuario);
      fd.append('email', email);
      fd.append('genero', genero);
      fd.append('password', password);
      fd.append('confirm_password', confirm);
      fd.append('terms', 'on');
      fd.append('privacy', 'on');

      const res  = await fetch('auth-handler.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast('¡Cuenta creada! Redirigiendo...', 'success');
        setTimeout(() => { window.location.href = data.redirect || 'dashboard.php'; }, 900);
      } else {
        showToast(data.errors?.[0] || data.message || 'Error al crear la cuenta', 'error');
        btn.disabled = false;
        btn.innerHTML = origHTML;
      }
    } catch {
      showToast('Error de conexión con el servidor', 'error');
      btn.disabled = false;
      btn.innerHTML = origHTML;
    }
  });

  // ════════════════════════════════════════════════
  // PARTÍCULAS EN CANVAS
  // ════════════════════════════════════════════════
  function initParticles() {
    const canvas = document.getElementById('bg-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let W = 0, H = 0;
    const resize = () => {
      W = canvas.width  = window.innerWidth;
      H = canvas.height = window.innerHeight;
    };
    resize();
    window.addEventListener('resize', resize, { passive: true });

    const COUNT = 70;
    const particles = Array.from({ length: COUNT }, () => makeParticle(W, H));

    function makeParticle(w, h) {
      const teal   = Math.random() > 0.5;
      const alpha  = Math.random() * 0.5 + 0.15;
      return {
        x:     Math.random() * w,
        y:     Math.random() * h,
        r:     Math.random() * 2.2 + 0.5,
        vx:    (Math.random() - 0.5) * 0.35,
        vy:    (Math.random() - 0.5) * 0.35,
        color: teal
          ? `rgba(54,192,161,${alpha})`
          : `rgba(239,126,34,${alpha})`,
      };
    }

    function drawConnections() {
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const d  = Math.sqrt(dx * dx + dy * dy);
          if (d < 110) {
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.strokeStyle = `rgba(54,192,161,${0.06 * (1 - d / 110)})`;
            ctx.lineWidth = 1;
            ctx.stroke();
          }
        }
      }
    }

    function tick() {
      ctx.clearRect(0, 0, W, H);
      drawConnections();
      particles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < -10) p.x = W + 10;
        if (p.x > W + 10) p.x = -10;
        if (p.y < -10) p.y = H + 10;
        if (p.y > H + 10) p.y = -10;

        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.color;
        ctx.fill();
      });
      requestAnimationFrame(tick);
    }
    tick();
  }

  initParticles();

  // ════════════════════════════════════════════════
  // EFECTO DE PARALLAX SUAVE EN ORBES
  // ════════════════════════════════════════════════
  document.addEventListener('mousemove', (e) => {
    const xPct = (e.clientX / window.innerWidth  - 0.5) * 2;  // -1 a 1
    const yPct = (e.clientY / window.innerHeight - 0.5) * 2;

    document.querySelectorAll('.orb').forEach((orb, i) => {
      const factor = (i + 1) * 10;
      orb.style.transform =
        `translate(${xPct * factor}px, ${yPct * factor}px)`;
    });
  }, { passive: true });

  console.log('[CIVINSIS] Auth v2 iniciado correctamente ✓');
});
