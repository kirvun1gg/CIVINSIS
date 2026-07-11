/* ============================================================
   CIVINSIS - JavaScript Principal
   ============================================================ */

'use strict';

// ── Tema (modo oscuro) ─────────────────────────────────────
const Theme = {
  key: 'civitas_theme',

  init() {
    const saved = localStorage.getItem(this.key) || 'light';
    this.apply(saved);
    document.querySelectorAll('[data-dark-toggle]').forEach(el => {
      el.addEventListener('click', () => this.toggle());
    });
  },

  apply(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(this.key, theme);
    document.querySelectorAll('[data-dark-toggle]').forEach(el => {
      el.setAttribute('aria-pressed', theme === 'dark');
    });
  },

  toggle() {
    const current = document.documentElement.getAttribute('data-theme');
    this.apply(current === 'dark' ? 'light' : 'dark');
  }
};

// ── Navbar ─────────────────────────────────────────────────
const Nav = {
  init() {
    const navbar = document.querySelector('.navbar');
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (navbar) {
      window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
      }, { passive: true });
    }

    if (hamburger && mobileMenu) {
      hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('open');
        mobileMenu.classList.toggle('open');
        document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
      });

      // Cerrar al hacer clic en un enlace
      mobileMenu.querySelectorAll('a, button').forEach(el => {
        el.addEventListener('click', () => {
          hamburger.classList.remove('open');
          mobileMenu.classList.remove('open');
          document.body.style.overflow = '';
        });
      });
    }

    // Resaltar enlace activo
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link, .sidebar-link').forEach(link => {
      const href = link.getAttribute('href');
      if (href && href.includes(currentPath) && currentPath !== '') {
        link.classList.add('active');
      }
    });
  }
};

// ── Toast notifications ────────────────────────────────────
const Toast = {
  container: null,

  init() {
    this.container = document.querySelector('.toast-container');
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 3500) {
    if (!this.container) this.init();
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
      <i class="fas ${icons[type] || icons.info} toast-icon ${type}"></i>
      <span class="toast-msg">${message}</span>
    `;
    this.container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('removing');
      toast.addEventListener('animationend', () => toast.remove());
    }, duration);
  }
};

// ── Modal ──────────────────────────────────────────────────
const Modal = {
  open(id) {
    const backdrop = document.getElementById(id);
    if (backdrop) {
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      backdrop.addEventListener('click', e => {
        if (e.target === backdrop) this.close(id);
      });
    }
  },

  close(id) {
    const backdrop = document.getElementById(id);
    if (backdrop) {
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
    }
  }
};

// ── Scroll Reveal ──────────────────────────────────────────
const Reveal = {
  init() {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
  }
};

// ── Partículas en hero ─────────────────────────────────────
const Particles = {
  init() {
    this.initCanvas();
    this.initParallaxOrbs();
    this.initCivitasLetters();
  },

  // Canvas de partículas conectadas
  initCanvas() {
    const canvas = document.getElementById('heroCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W = 0, H = 0;
    const resize = () => {
      W = canvas.width  = canvas.offsetWidth  || window.innerWidth;
      H = canvas.height = canvas.offsetHeight || window.innerHeight;
    };
    resize();
    window.addEventListener('resize', resize, { passive: true });

    const COUNT = 60;
    const pts = Array.from({ length: COUNT }, () => ({
      x: Math.random() * W, y: Math.random() * H,
      vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35,
      r: Math.random() * 2 + .5,
      color: Math.random() > .5
        ? `rgba(54,192,161,${Math.random()*.45+.15})`
        : `rgba(239,126,34,${Math.random()*.35+.12})`
    }));

    const tick = () => {
      ctx.clearRect(0, 0, W, H);
      // Conexiones
      for (let i = 0; i < pts.length; i++) {
        for (let j = i + 1; j < pts.length; j++) {
          const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
          const d  = Math.sqrt(dx*dx + dy*dy);
          if (d < 115) {
            ctx.beginPath();
            ctx.moveTo(pts[i].x, pts[i].y);
            ctx.lineTo(pts[j].x, pts[j].y);
            ctx.strokeStyle = `rgba(54,192,161,${.055*(1-d/115)})`;
            ctx.lineWidth = 1;
            ctx.stroke();
          }
        }
      }
      // Puntos
      pts.forEach(p => {
        p.x += p.vx; p.y += p.vy;
        if (p.x < -10) p.x = W+10; if (p.x > W+10) p.x = -10;
        if (p.y < -10) p.y = H+10; if (p.y > H+10) p.y = -10;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
        ctx.fillStyle = p.color;
        ctx.fill();
      });
      requestAnimationFrame(tick);
    };
    tick();
  },

  // Parallax suave de orbes con el mouse
  initParallaxOrbs() {
    const orbs = document.querySelectorAll('.hero .orb, .hero .pulse-ring');
    if (!orbs.length) return;
    document.addEventListener('mousemove', e => {
      const xP = (e.clientX / window.innerWidth  - .5) * 2;
      const yP = (e.clientY / window.innerHeight - .5) * 2;
      orbs.forEach((orb, i) => {
        const f = (i + 1) * 9;
        orb.style.transform = `translate(${xP*f}px,${yP*f}px)`;
      });
    }, { passive: true });
  },

  // Letras de CIVINSIS — tooltips y click events creativos
  initCivitasLetters() {
    const tooltips = {
      'cl-c': '¡Ciudadanía!',
      'cl-i': '¡Ideas!',
      'cl-v': '¡Voz!',
      'cl-i2':'¡Impacto!',
      'cl-t': '¡Transformación!',
      'cl-a': '¡Acción!',
      'cl-s': '¡Solidaridad!',
    };
    const clickFx = {
      'cl-c': el => { el.style.animation='none'; setTimeout(()=>el.style.animation='',10); confetti(el); },
      'cl-i': el => shakeOnce(el),
      'cl-v': el => { el.style.transform='scaleY(-1) scale(1.3)'; setTimeout(()=>el.style.transform='',600); },
      'cl-i2':el => shakeOnce(el),
      'cl-t': el => { el.style.transform='scaleY(1.6) translateY(-12px)'; setTimeout(()=>el.style.transform='',500); },
      'cl-a': el => { el.style.animation='burst .5s ease both'; setTimeout(()=>el.style.animation='',600); },
      'cl-s': el => { el.style.animation='waveS .4s ease-in-out 3 alternate'; setTimeout(()=>el.style.animation='',1400); },
    };

    function shakeOnce(el) {
      el.style.animation = 'shakeI .5s ease';
      setTimeout(() => el.style.animation = '', 500);
    }

    // Mini confetti al click de C
    function confetti(el) {
      const rect = el.getBoundingClientRect();
      const cx = rect.left + rect.width/2, cy = rect.top + rect.height/2;
      const colors = ['#36c0a1','#ef7e22','#ffe600','#ff5e5e','#00e5ff','#d500f9'];
      for (let i = 0; i < 18; i++) {
        const dot = document.createElement('div');
        dot.style.cssText = `
          position:fixed;left:${cx}px;top:${cy}px;width:8px;height:8px;
          border-radius:50%;background:${colors[i%colors.length]};
          pointer-events:none;z-index:9999;transition:all .8s ease-out;
        `;
        document.body.appendChild(dot);
        const angle = (i / 18) * Math.PI * 2;
        const dist  = 60 + Math.random() * 80;
        setTimeout(() => {
          dot.style.transform = `translate(${Math.cos(angle)*dist}px,${Math.sin(angle)*dist}px)`;
          dot.style.opacity = '0';
        }, 10);
        setTimeout(() => dot.remove(), 820);
      }
    }

    document.querySelectorAll('.cl').forEach(el => {
      // Agregar tooltip si no existe
      const key = [...el.classList].find(c => c.startsWith('cl-'));
      if (key && tooltips[key] && !el.querySelector('.cl-tooltip')) {
        const tip = document.createElement('span');
        tip.className = 'cl-tooltip';
        tip.textContent = tooltips[key];
        el.appendChild(tip);
      }

      // Click event
      el.addEventListener('click', () => {
        const fn = key && clickFx[key];
        if (fn) fn(el);
      });
    });
  }
};

// ── API Helper ─────────────────────────────────────────────
const API = {
  // POST como JSON (soporta base64, arrays, etc.)
  async post(url, data = {}) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      return await res.json();
    } catch(e) {
      return { success: false, message: 'Error de conexión: ' + e.message };
    }
  },

  // POST como FormData (para login/registro con campos de formulario)
  async postForm(url, formEl) {
    try {
      const res = await fetch(url, { method: 'POST', body: new FormData(formEl) });
      return await res.json();
    } catch(e) {
      return { success: false, message: 'Error de conexión: ' + e.message };
    }
  },

  async get(url, params = {}) {
    try {
      const qs = new URLSearchParams(params).toString();
      const res = await fetch(url + (qs ? '?' + qs : ''));
      return await res.json();
    } catch(e) {
      return { success: false, message: 'Error de conexión' };
    }
  }
};

// ── Auth (login / registro) ────────────────────────────────
const Auth = {
  init() {
    const tabs      = document.querySelectorAll('.auth-tab');
    const forms     = document.querySelectorAll('.auth-form');
    const switches  = document.querySelectorAll('[data-auth-switch]');

    const switchTo = (target) => {
      tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === target));
      forms.forEach(f => f.classList.toggle('active', f.id === `form-${target}`));
    };

    tabs.forEach(tab => tab.addEventListener('click', () => switchTo(tab.dataset.tab)));
    switches.forEach(sw => sw.addEventListener('click', e => { e.preventDefault(); switchTo(sw.dataset.authSwitch); }));

    // Formulario de login
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
      loginForm.querySelector('form')?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = loginForm.querySelector('[type=submit]');
        this.setLoading(btn, true);
        const data = {
          accion: 'login',
          email: loginForm.querySelector('[name=email]').value,
          password: loginForm.querySelector('[name=password]').value
        };
        const res = await API.post('php/auth.php', data);
        this.setLoading(btn, false);
        if (res.success) {
          Toast.show(res.message, 'success');
          setTimeout(() => window.location.href = res.redirect, 800);
        } else {
          Toast.show(res.message, 'error');
        }
      });
    }

    // Formulario de registro
    const regForm = document.getElementById('form-registro');
    if (regForm) {
      regForm.querySelector('form')?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = regForm.querySelector('[type=submit]');
        const pass = regForm.querySelector('[name=password]').value;
        const conf = regForm.querySelector('[name=confirm_password]').value;
        if (pass !== conf) { Toast.show('Las contraseñas no coinciden', 'error'); return; }
        this.setLoading(btn, true);
        const data = {
          accion: 'registro',
          nombre: regForm.querySelector('[name=nombre]').value,
          apellido: regForm.querySelector('[name=apellido]').value,
          email: regForm.querySelector('[name=email]').value,
          password: pass,
          confirm_password: conf
        };
        const res = await API.post('php/auth.php', data);
        this.setLoading(btn, false);
        if (res.success) {
          Toast.show(res.message, 'success');
          setTimeout(() => window.location.href = res.redirect, 800);
        } else {
          Toast.show(res.message, 'error');
        }
      });
    }

    // Toggle password visibility
    document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });
  },

  setLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
      btn.dataset.originalText = btn.innerHTML;
      btn.innerHTML = '<span class="spinner"></span> Procesando...';
      btn.disabled = true;
    } else {
      btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
      btn.disabled = false;
    }
  }
};

// ── Propuestas (Dashboard) ─────────────────────────────────
const Proposals = {
  currentPage: 1,
  currentCat: 0,
  currentOrder: 'fecha',
  searchQuery: '',

  init() {
    this.container = document.getElementById('proposalsGrid');
    if (!this.container) return;

    this.load();

    // Búsqueda
    const searchInput = document.getElementById('searchInput');
    let searchTimer;
    searchInput?.addEventListener('input', e => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        this.searchQuery = e.target.value;
        this.currentPage = 1;
        this.load();
      }, 400);
    });

    // Filtros de categoría
    document.querySelectorAll('[data-cat]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('[data-cat]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        this.currentCat = btn.dataset.cat;
        this.currentPage = 1;
        this.load();
      });
    });

    // Ordenamiento
    document.getElementById('ordenSelect')?.addEventListener('change', e => {
      this.currentOrder = e.target.value;
      this.currentPage = 1;
      this.load();
    });
  },

  async load() {
    this.container.innerHTML = this.skeletons();
    const res = await API.get('php/propuestas.php', {
      accion: 'listar',
      categoria: this.currentCat,
      q: this.searchQuery,
      orden: this.currentOrder,
      pagina: this.currentPage
    });

    if (!res.success) { this.container.innerHTML = '<p class="text-muted">Error al cargar propuestas.</p>'; return; }

    if (!res.propuestas.length) {
      this.container.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1">
          <i class="fas fa-inbox"></i>
          <p>No hay propuestas que coincidan con tu búsqueda.</p>
        </div>`;
      document.getElementById('pagination')?.replaceWith(document.createElement('div'));
      return;
    }

    this.container.innerHTML = res.propuestas.map(p => this.cardHTML(p)).join('');
    this.renderPagination(res.pagina_actual, res.paginas);
    this.attachCardEvents();
    Reveal.init();
  },

  cardHTML(p) {
    // Estilos por diseño — solo para diseños no-default
    var diseno = p.diseno || 'default';
    var cardExtraStyle = '';
    var cardExtraClass = '';

    if (diseno === 'dark') {
      cardExtraStyle = 'background:#131f1a;border-color:rgba(54,192,161,.25);';
      cardExtraClass = ' card-design-dark';
    } else if (diseno === 'gradient') {
      cardExtraStyle = 'background:linear-gradient(135deg,#eaf8f3,#fef3e8);border-color:rgba(54,192,161,.2);';
    } else if (diseno === 'minimal') {
      cardExtraStyle = 'background:#fff;border:2px solid #0f1c19;';
    } else if (diseno === 'neon') {
      cardExtraClass = ' card-design-neon';
    } else if (diseno === 'glass') {
      cardExtraClass = ' card-design-glass';
    } else if (diseno === 'sunset') {
      cardExtraClass = ' card-design-sunset';
    } else if (diseno === 'ocean') {
      cardExtraClass = ' card-design-ocean';
    } else if (diseno === 'retro') {
      cardExtraClass = ' card-design-retro';
    } else if (diseno === 'aurora') {
      cardExtraClass = ' card-design-aurora';
    } else if (diseno === 'cyber') {
      cardExtraClass = ' card-design-cyber';
    } else if (diseno === 'pastel') {
      cardExtraClass = ' card-design-pastel';
    }
    if (p.destacada == 1 || p.destacada === true) cardExtraClass += ' is-destacada';

    var imageHtml = (p.imagen && p.imagen.length > 10 && p.imagen.indexOf('data:') === 0)
      ? '<div class="card-image"><img src="' + p.imagen + '" alt="" loading="lazy"></div>'
      : '';

    var estado = (p.estado || 'activa').replace('_', ' ');
    var voteClass = p.ya_vote ? 'vote-btn voted' : 'vote-btn';

    var catFx = (p.efecto_categoria === false || p.efecto_categoria == 0) ? '' : (p.categoria_efecto || 'default');
    if (p.color_acento) { cardExtraStyle += 'border-color:' + p.color_acento + ';box-shadow:inset 4px 0 0 ' + p.color_acento + ';'; }
    var html = '<article class="proposal-card reveal' + cardExtraClass + '" data-id="' + p.id + '" data-cat-effect="' + catFx + '" style="cursor:pointer;' + cardExtraStyle + '">';
    html += imageHtml;
    html += '<div class="card-header">';
    html += '<div class="card-cat"><i class="' + p.categoria_icono + '" style="color:' + p.categoria_color + '"></i>' + p.categoria + '</div>';
    html += '<h3 class="card-title">' + p.titulo + '</h3>';
    html += '<p class="card-desc">' + p.descripcion + '</p>';
    html += '</div>';
    html += '<div class="card-body"><div style="display:flex;gap:.5rem;flex-wrap:wrap">';
    html += '<span class="estado-chip estado-' + (p.estado || 'activa') + '"><i class="fas fa-circle" style="font-size:.45rem"></i> ' + estado + '</span>';
    html += '</div>';
    // Autor con avatar (#3)
    var avaInner = (p.autor_avatar && p.autor_avatar.indexOf('data:') === 0)
      ? '<img class="author-avatar" src="' + p.autor_avatar + '" alt="">'
      : '<span class="author-avatar">' + ((p.autor||'?').charAt(0).toUpperCase()) + '</span>';
    var authorClass = p.autor_marco ? p.autor_marco : '';
    html += '<div class="card-author">' + avaInner + '<span class="author-name">' + (p.autor||'Anónimo') + '</span>' + (p.autor_titulo ? ' <span class="label-titulo ' + p.autor_titulo.rareza + '" style="color:' + p.autor_titulo.color + ';border-color:' + p.autor_titulo.color + '">' + p.autor_titulo.nombre + '</span>' : '') + '</div>';
    html += '</div>';
    html += '<div class="card-footer">';
    html += '<div class="card-meta"><span><i class="fas fa-eye"></i>' + p.vistas + '</span><span><i class="fas fa-calendar"></i>' + p.fecha_formateada + '</span></div>';
    html += '<button class="' + voteClass + '" data-pid="' + p.id + '"><i class="fas fa-arrow-up"></i><span>' + p.votos + '</span></button>';
    html += '</div></article>';
    return html;
  },

  attachCardEvents() {
    var container = this.container;
    if (!container) return;
    // Click en card -> navegar
    container.querySelectorAll('.proposal-card[data-id]').forEach(function(card) {
      card.addEventListener('click', function(e) {
        if (e.target.closest('.vote-btn')) return;
        window.location.href = 'propuesta.php?id=' + card.dataset.id;
      });
    });
    // Click en vote btn
    container.querySelectorAll('.vote-btn[data-pid]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        Proposals.vote(btn.dataset.pid, btn);
      });
    });
  },

    async vote(propuestaId, btn) {
    const res = await API.post('php/propuestas.php', { accion: 'votar', propuesta_id: propuestaId });
    if (res.success) {
      btn.querySelector('span').textContent = res.votos;
      btn.classList.toggle('voted', res.accion === 'agregado');
      Toast.show(res.accion === 'agregado' ? '¡Voto registrado!' : 'Voto removido', 'success');
    } else {
      Toast.show(res.message, 'error');
    }
  },

  renderPagination(current, total) {
    const container = document.getElementById('pagination');
    if (!container || total <= 1) { if (container) container.innerHTML = ''; return; }

    let html = `<button class="page-btn" onclick="Proposals.goto(${current - 1})" ${current===1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= total; i++) {
      if (i === 1 || i === total || Math.abs(i - current) <= 1) {
        html += `<button class="page-btn ${i===current?'active':''}" onclick="Proposals.goto(${i})">${i}</button>`;
      } else if (Math.abs(i - current) === 2) {
        html += `<span style="color:var(--text-muted);padding:0 .25rem">…</span>`;
      }
    }
    html += `<button class="page-btn" onclick="Proposals.goto(${current + 1})" ${current===total?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    container.innerHTML = html;
  },

  goto(page) { this.currentPage = page; this.load(); window.scrollTo({ top: 0, behavior: 'smooth' }); },

  skeletons() {
    return Array(6).fill(0).map(() => `
      <div class="proposal-card" style="pointer-events:none">
        <div class="card-header">
          <div class="skeleton" style="height:14px;width:30%;margin-bottom:12px"></div>
          <div class="skeleton" style="height:20px;margin-bottom:8px"></div>
          <div class="skeleton" style="height:14px;margin-bottom:4px"></div>
          <div class="skeleton" style="height:14px;width:80%"></div>
        </div>
        <div class="card-body"><div class="skeleton" style="height:24px;width:40%"></div></div>
        <div class="card-footer" style="justify-content:space-between">
          <div class="skeleton" style="height:14px;width:40%"></div>
          <div class="skeleton" style="height:28px;width:70px;border-radius:100px"></div>
        </div>
      </div>`).join('');
  }
};

// ── Propuesta Detalle ──────────────────────────────────────
const ProposalDetail = {
  async init() {
    const id = new URLSearchParams(window.location.search).get('id');
    if (!id) return;

    const res = await API.get('php/propuestas.php', { accion: 'detalle', id });
    if (!res.success) { document.getElementById('detailContent').innerHTML = '<p>Propuesta no encontrada.</p>'; return; }

    const p = res.propuesta;
    document.title = `${p.titulo} - CIVINSIS`;

    // Cover image
    const coverHtml = (p.imagen && p.imagen.length > 10)
      ? `<img src="${p.imagen}" alt="Portada" class="detail-image">`
      : '';

    // Design badge
    const designLabels = {default:'Clásico',dark:'Oscuro',gradient:'Gradiente',minimal:'Minimalista',neon:'Neón',glass:'Glass',sunset:'Sunset',ocean:'Ocean',retro:'Retro'};
    const designBadge = p.diseno && p.diseno !== 'default'
      ? `<span style="font-size:.7rem;padding:.2rem .55rem;border-radius:100px;background:var(--surface);color:var(--text-muted);font-weight:600;margin-left:.5rem"><i class="fas fa-palette"></i> ${designLabels[p.diseno]||p.diseno}</span>`
      : '';

    document.getElementById('detailContent').innerHTML = `
      ${coverHtml}
      <div class="detail-header animate-fade-up">
        <div class="detail-cat">
          <span class="badge" style="background:${p.categoria_color}20;color:${p.categoria_color}">
            <i class="${p.categoria_icono}"></i>${p.categoria}
          </span>${designBadge}
        </div>
        <h1 class="detail-title">${p.titulo}</h1>
        <div class="detail-meta">
          <a href="${p.autor_id ? 'usuario.php?id='+p.autor_id : '#'}" style="text-decoration:none;color:inherit;display:inline-flex;align-items:center;gap:.35rem"><i class="fas fa-user"></i>${p.autor}${p.autor_titulo ? ` <span class="label-titulo ${p.autor_titulo.rareza}" style="color:${p.autor_titulo.color};border-color:${p.autor_titulo.color}">${p.autor_titulo.nombre}</span>` : ''}</a>
          <span><i class="fas fa-calendar"></i>${p.fecha_formateada}</span>
          <span><i class="fas fa-eye"></i>${p.vistas} vistas</span>
        </div>
        <p class="detail-desc">${p.descripcion}</p>
        <div class="detail-actions">
          <button id="voteBtn" class="btn ${p.ya_vote ? 'btn-primary' : 'btn-outline'} ${p.ya_vote ? 'voted' : ''}"
            onclick="ProposalDetail.vote(${p.id})">
            <i class="fas fa-arrow-up"></i>
            <span id="voteCount">${p.votos}</span> votos
          </button>
          ${p.es_autor ? `
            <button class="btn btn-ghost btn-sm" onclick="ProposalDetail.openEdit()">
              <i class="fas fa-pen"></i> Editar
            </button>
            <button class="btn btn-danger btn-sm" onclick="ProposalDetail.delete(${p.id})">
              <i class="fas fa-trash"></i> Eliminar
            </button>
          ` : ''}
        </div>
      </div>
      <div class="detail-content animate-fade-up">
        <h2 class="content-title"><i class="fas fa-align-left"></i> Descripción completa</h2>
        <div class="content-body">${p.contenido}</div>
      </div>`;

    // Guardar propuesta para edición
    this._propuesta = p;
    this.loadComments(id);
  },

  async vote(id) {
    const res = await API.post('php/propuestas.php', { accion: 'votar', propuesta_id: id });
    if (res.success) {
      document.getElementById('voteCount').textContent = res.votos;
      const btn = document.getElementById('voteBtn');
      btn.classList.toggle('btn-primary', res.accion === 'agregado');
      btn.classList.toggle('btn-outline', res.accion !== 'agregado');
      Toast.show(res.accion === 'agregado' ? '¡Voto registrado!' : 'Voto removido', 'success');
    } else {
      Toast.show(res.message, 'error');
    }
  },

  async loadComments(id) {
    const section = document.getElementById('commentsSection');
    if (!section) return;
    const res = await API.get('php/propuestas.php', { accion: 'comentarios', id });
    const list = section.querySelector('#commentsList');
    if (!res.comentarios.length) {
      list.innerHTML = '<div class="empty-state"><i class="fas fa-comment-slash"></i><p>Sé el primero en comentar.</p></div>';
    } else {
      list.innerHTML = res.comentarios.map(c => this.commentHTML(c)).join('');
    }
    section.querySelector('.comments-count').textContent = res.total;
  },

  commentHTML(c) {
    const initials = c.autor.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
    return `
      <div class="comment">
        <div class="comment-avatar">${initials}</div>
        <div class="comment-content">
          <div>
            <span class="comment-author">${c.autor}</span>
            <span class="comment-date">${c.fecha_formateada}</span>
          </div>
          <p class="comment-text">${c.contenido}</p>
        </div>
      </div>`;
  },

  async submitComment(propuestaId) {
    const textarea = document.getElementById('commentText');
    const contenido = textarea.value.trim();
    if (!contenido) { Toast.show('Escribe un comentario antes de publicar', 'error'); return; }
    const res = await API.post('php/propuestas.php', { accion: 'comentar', propuesta_id: propuestaId, contenido });
    if (res.success) {
      const list = document.getElementById('commentsList');
      const empty = list.querySelector('.empty-state');
      if (empty) empty.remove();
      list.insertAdjacentHTML('afterbegin', this.commentHTML(res.comentario));
      textarea.value = '';
      const cnt = document.querySelector('.comments-count');
      if (cnt) cnt.textContent = parseInt(cnt.textContent || 0) + 1;
      Toast.show('¡Comentario publicado!', 'success');
    } else {
      Toast.show(res.message, 'error');
    }
  },

  openEdit() {
    if (!this._propuesta) return;
    const p = this._propuesta;
    document.getElementById('editTitulo').value = p.titulo;
    document.getElementById('editDescripcion').value = p.descripcion;
    document.getElementById('editContenido').value = p.contenido;
    document.getElementById('editCategoria').value = p.categoria_id;
    Modal.open('modalEdit');
  },

  async saveEdit(id) {
    const data = {
      accion: 'editar', id,
      titulo:      document.getElementById('editTitulo').value,
      descripcion: document.getElementById('editDescripcion').value,
      contenido:   document.getElementById('editContenido').value,
      categoria_id: document.getElementById('editCategoria').value
    };
    const res = await API.post('php/propuestas.php', data);
    if (res.success) {
      Toast.show(res.message, 'success');
      Modal.close('modalEdit');
      setTimeout(() => location.reload(), 800);
    } else {
      Toast.show(res.message, 'error');
    }
  },

  async delete(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta propuesta? Esta acción no se puede deshacer.')) return;
    const res = await API.post('php/propuestas.php', { accion: 'eliminar', id });
    if (res.success) {
      Toast.show(res.message, 'success');
      setTimeout(() => window.location.href = 'dashboard.php', 800);
    } else {
      Toast.show(res.message, 'error');
    }
  }
};

// ── Crear propuesta ────────────────────────────────────────
const CreateProposal = {
  init() {
    const form = document.getElementById('createForm');
    if (!form) return;
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = form.querySelector('[type=submit]');
      Auth.setLoading(btn, true);

      // Sincronizar contenido del editor enriquecido
      const editorEl = document.getElementById('richEditor');
      if (editorEl) {
        const hiddenContent = document.getElementById('contenido');
        if (hiddenContent) hiddenContent.value = editorEl.innerHTML;
      }

      const disenoInput = form.querySelector('[name=diseno]:checked');
      const diseno = disenoInput ? disenoInput.value : 'default';

      const imgPreview = document.getElementById('imagePreview');
      const imagenBase64 = (imgPreview && imgPreview.src && imgPreview.src.startsWith('data:')) ? imgPreview.src : '';

      const contenidoEl = document.getElementById('contenido');
      const richEl = document.getElementById('richEditor');
      const contenidoVal = (contenidoEl && contenidoEl.value) ? contenidoEl.value : (richEl ? richEl.innerHTML : '');

      var acentoEl   = document.getElementById('colorAcento');
      var destacEl   = document.getElementById('propDestacada');
      var efectoEl   = document.getElementById('efectoCategoria');

      const data = {
        accion:        'crear',
        titulo:        (form.querySelector('[name=titulo]') || {}).value || '',
        descripcion:   (form.querySelector('[name=descripcion]') || {}).value || '',
        contenido:     contenidoVal,
        categoria_id:  (form.querySelector('[name=categoria_id]') || {}).value || '',
        diseno:        diseno,
        imagen_base64: imagenBase64,
        color_acento:     (acentoEl && acentoEl.dataset.activo === '1') ? acentoEl.value : '',
        destacada:        (destacEl && destacEl.checked) ? 1 : 0,
        efecto_categoria: (efectoEl && !efectoEl.checked) ? 0 : 1
      };

      if (!data.titulo.trim() || !data.categoria_id) {
        Toast.show('Completa el título y la categoría', 'error');
        Auth.setLoading(btn, false);
        return;
      }

      const res = await API.post('php/propuestas.php', data);
      Auth.setLoading(btn, false);
      if (res.success) {
        Toast.show(res.message, 'success');
        setTimeout(function() { window.location.href = 'propuesta.php?id=' + res.id; }, 900);
      } else {
        Toast.show(res.message || 'Error al publicar', 'error');
      }
    });
  }
};

// ── Top Propuestas (index) ─────────────────────────────────
const TopProposals = {
  async init() {
    const container = document.getElementById('topProposals');
    if (!container) return;

    const res = await API.get('php/propuestas.php', { accion: 'top', limit: 5 });
    if (!res.success || !res.propuestas.length) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-trophy"></i>
          <p>No hay foros con votos actualmente.</p>
        </div>`;
      return;
    }

    container.innerHTML = res.propuestas.map((p, i) => `
      <div class="top-card" onclick="window.location.href='propuesta.php?id=${p.id}'">
        <div class="top-rank">#${i + 1}</div>
        <div class="top-info">
          <div class="top-title">${p.titulo}</div>
          <div class="top-cat"><i class="${p.categoria_icono}"></i> ${p.categoria}</div>
        </div>
        <div class="top-votes">
          <span class="top-votes-num">${p.votos}</span>
          <span class="top-votes-label">votos</span>
        </div>
      </div>`).join('');
  }
};




// ── FAQ accordion ──────────────────────────────────────────
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  // Cerrar todos
  document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
  // Abrir el clickeado (si estaba cerrado)
  if (!isOpen) item.classList.add('open');
}

// ── Logout con modal de despedida ─────────────────────────
async function logout() {
  // Mostrar modal de despedida
  showLogoutModal();
}

function showLogoutModal() {
  // Crear overlay de despedida
  const overlay = document.createElement('div');
  overlay.id = 'logoutOverlay';
  overlay.style.cssText = `
    position:fixed;inset:0;z-index:9999;
    display:flex;align-items:center;justify-content:center;
    background:rgba(10,20,15,0);
    transition:background .4s;
    backdrop-filter:blur(0px);
    -webkit-backdrop-filter:blur(0px);
  `;

  const card = document.createElement('div');
  card.style.cssText = `
    background:var(--bg-card);
    border:1px solid var(--border);
    border-radius:28px;
    padding:2.5rem 3rem;
    text-align:center;
    max-width:380px;width:90%;
    transform:scale(.7) translateY(40px);
    opacity:0;
    transition:transform .5s cubic-bezier(.34,1.56,.64,1), opacity .4s;
    box-shadow:0 32px 80px rgba(0,0,0,.3);
    position:relative;overflow:hidden;
  `;

  // Fondo decorativo de la card
  const cardBg = document.createElement('div');
  cardBg.style.cssText = `
    position:absolute;inset:0;
    background:radial-gradient(ellipse 200px 150px at 20% 80%, rgba(54,192,161,.08) 0%, transparent 70%),
               radial-gradient(ellipse 150px 120px at 80% 20%, rgba(239,126,34,.08) 0%, transparent 70%);
    pointer-events:none;
  `;
  card.appendChild(cardBg);

  // Emoji animado
  const emoji = document.createElement('div');
  emoji.style.cssText = `font-size:3.5rem;margin-bottom:1rem;animation:waveHand 1s ease-in-out 3;display:inline-block;`;
  emoji.textContent = '👋';
  card.appendChild(emoji);

  // Título
  const title = document.createElement('h2');
  title.style.cssText = `font-family:var(--font-display);font-size:1.6rem;font-weight:800;color:var(--text);margin-bottom:.5rem;`;
  title.textContent = '¡Hasta pronto!';
  card.appendChild(title);

  // Subtítulo con nombre
  const nombre = document.querySelector('.nav-user-name')?.textContent?.trim() || '';
  const sub = document.createElement('p');
  sub.style.cssText = `color:var(--text-muted);font-size:.95rem;margin-bottom:1.5rem;line-height:1.6;`;
  sub.innerHTML = nombre
    ? `Nos vemos pronto, <strong style="color:var(--verde)">${nombre}</strong>.<br>Tu voz sigue marcando la diferencia. 🌿`
    : `Tu voz sigue marcando la diferencia. 🌿<br>¡Gracias por ser parte de CIVINSIS!`;
  card.appendChild(sub);

  // Barra de progreso
  const barWrap = document.createElement('div');
  barWrap.style.cssText = `height:4px;border-radius:2px;background:var(--surface);overflow:hidden;margin-bottom:1rem;`;
  const bar = document.createElement('div');
  bar.style.cssText = `height:100%;width:0%;border-radius:2px;background:var(--grad-primary);transition:width 2.2s linear;`;
  barWrap.appendChild(bar); card.appendChild(barWrap);

  const barLabel = document.createElement('p');
  barLabel.style.cssText = `font-size:.75rem;color:var(--text-muted);`;
  barLabel.textContent = 'Cerrando sesión...';
  card.appendChild(barLabel);

  overlay.appendChild(card);
  document.body.appendChild(overlay);

  // Keyframe para la mano
  if (!document.getElementById('logoutKeyframes')) {
    const st = document.createElement('style');
    st.id = 'logoutKeyframes';
    st.textContent = `
      @keyframes waveHand {
        0%,100%{transform:rotate(0deg);}
        20%{transform:rotate(-20deg);}
        40%{transform:rotate(20deg);}
        60%{transform:rotate(-15deg);}
        80%{transform:rotate(15deg);}
      }
      @keyframes particleFloat {
        0%{transform:translateY(0) scale(1);opacity:1;}
        100%{transform:translateY(-80px) scale(0);opacity:0;}
      }
    `;
    document.head.appendChild(st);
  }

  // Animar entrada
  requestAnimationFrame(() => {
    overlay.style.background = 'rgba(10,20,15,.6)';
    overlay.style.backdropFilter = 'blur(8px)';
    overlay.style.webkitBackdropFilter = 'blur(8px)';
    requestAnimationFrame(() => {
      card.style.transform = 'scale(1) translateY(0)';
      card.style.opacity = '1';
      setTimeout(() => { bar.style.width = '100%'; }, 200);
    });
  });

  // Partículas decorativas
  const colors = ['#36c0a1','#ef7e22','#47d3b3','#f7af4f'];
  for (let i = 0; i < 12; i++) {
    setTimeout(() => {
      const p = document.createElement('div');
      p.style.cssText = `
        position:absolute;
        width:${4+Math.random()*6}px;height:${4+Math.random()*6}px;
        border-radius:50%;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${10+Math.random()*80}%;
        bottom:${10+Math.random()*30}%;
        animation:particleFloat ${1+Math.random()}s ease forwards;
        pointer-events:none;
      `;
      card.appendChild(p);
      setTimeout(() => p.remove(), 2000);
    }, i * 150);
  }

  // Cerrar sesión después de animación
  setTimeout(async () => {
    try {
      const res = await API.post('php/auth.php', { accion: 'logout' });
      // Reemplazar historial para que "atrás" no regrese a páginas protegidas
      window.location.replace(res.redirect || 'index.php');
    } catch(e) {
      window.location.replace('index.php');
    }
  }, 2500);
}

// ── Init global ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Theme.init();
  Nav.init();
  Toast.init();
  Reveal.init();
  Particles.init();
  
  Auth.init();
  Proposals.init();
  CreateProposal.init();
  TopProposals.init();

  // Init detalle si aplica
  if (document.getElementById('detailContent')) ProposalDetail.init();

  // ESC para cerrar modales y chatbot
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
      document.body.style.overflow = '';
    }
  });
});

/* ═══════════════════════════════════════════════════════════
   CIVINSIS v3 — JavaScript Adicional
   ═══════════════════════════════════════════════════════════ */

// ── Avatar en comentarios ────────────────────────────────────
// Patch ProposalDetail.commentHTML para incluir avatar, marco, título y enlace al perfil
(function() {
  const origCommentHTML = ProposalDetail.commentHTML.bind(ProposalDetail);
  ProposalDetail.commentHTML = function(c) {
    const initials = c.autor.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase();
    const avatarContent = c.avatar
      ? `<img src="${c.avatar}" alt="${c.autor}">`
      : initials;
    const hasPhoto = c.avatar ? 'comment-avatar-has-photo' : '';
    const marcoClass = c.autor_marco ? c.autor_marco : '';
    const perfilUrl = c.autor_id ? `usuario.php?id=${c.autor_id}` : '#';

    // Título en pequeño
    let tituloChip = '';
    if (c.autor_titulo) {
      tituloChip = `<span class="label-titulo ${c.autor_titulo.rareza}" style="color:${c.autor_titulo.color};border-color:${c.autor_titulo.color}">${c.autor_titulo.nombre}</span>`;
    }
    // Nivel badge
    const nivelBadge = c.autor_nivel ? `<span class="label-nivel">Nv.${c.autor_nivel}</span>` : '';

    return `
      <div class="comment">
        <a href="${perfilUrl}" class="comment-avatar ${hasPhoto} ${marcoClass}" style="text-decoration:none;flex-shrink:0" title="Ver perfil de ${c.autor}">${avatarContent}</a>
        <div class="comment-content">
          <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
            <a href="${perfilUrl}" class="comment-author" style="text-decoration:none;color:inherit">${c.autor}</a>
            ${nivelBadge}
            ${tituloChip}
            <span class="comment-date">${c.fecha_formateada}</span>
          </div>
          <p class="comment-text">${c.contenido}</p>
        </div>
      </div>`;
  };
})();

// ── Nav Avatar actualización dinámica ───────────────────────
function updateNavAvatar(avatarSrc) {
  const navAvEl = document.getElementById('navUserAvatar');
  if (!navAvEl) return;
  if (avatarSrc) {
    navAvEl.innerHTML = `<img src="${avatarSrc}" alt="Avatar">`;
  }
}

// ── Mobile Drawer ───────────────────────────────────────────
(function initDrawer() {
  const hamburger = document.getElementById('hamburger');
  const drawer    = document.getElementById('mobileMenu');
  const overlay   = document.getElementById('mobileOverlay');
  const closeBtn  = document.getElementById('mobileMenuClose');
  if (!hamburger || !drawer) return;

  function openDrawer() {
    drawer.classList.add('open');
    overlay.classList.add('open');
    hamburger.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    hamburger.classList.remove('open');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', () => {
    drawer.classList.contains('open') ? closeDrawer() : openDrawer();
  });
  if (overlay) overlay.addEventListener('click', closeDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);

  // Cerrar en resize
  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeDrawer();
  });
})();

// ── CIVINSIS Letters — Click burst effect ───────────────────
document.querySelectorAll('.cl:not(.cl-c)').forEach(letter => {
  letter.addEventListener('click', function(e) {
    // Clase temporal de click
    this.classList.add('clicked');
    setTimeout(() => this.classList.remove('clicked'), 600);

    // Partículas de burst
    const colors = ['#36c0a1','#ef7e22','#00e5ff','#ffe600','#ff6b6b','#a855f7'];
    const rect = this.getBoundingClientRect();
    const cx = rect.left + rect.width/2;
    const cy = rect.top  + rect.height/2;
    for (let i = 0; i < 10; i++) {
      const p = document.createElement('div');
      p.className = 'cl-burst-particle';
      const size = 4 + Math.random() * 6;
      const angle = (Math.PI * 2 * i) / 10 + Math.random() * .5;
      const dist  = 40 + Math.random() * 60;
      p.style.cssText = `
        left:${cx}px; top:${cy}px;
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        --tx:${Math.cos(angle)*dist}px;
        --ty:${Math.sin(angle)*dist}px;
      `;
      document.body.appendChild(p);
      setTimeout(() => p.remove(), 900);
    }
  });
});

// ── Easter Eggs ─────────────────────────────────────────────

// 1. Konami Code → arcoíris flash + mensaje
(function() {
  const seq = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','b','a'];
  let pos = 0;
  document.addEventListener('keydown', e => {
    if (e.key === seq[pos]) {
      pos++;
      if (pos === seq.length) {
        pos = 0;
        const flash = document.createElement('div');
        flash.className = 'konami-flash';
        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 900);
        if (typeof Toast !== 'undefined') {
          Toast.show('🎮 ¡Código Konami activado! ¡Eres un pro!', 'success');
        }
        // Confetti de letras CIVINSIS
        const letters = ['C','I','V','I','T','A','S'];
        for (let i = 0; i < 20; i++) {
          setTimeout(() => {
            const s = document.createElement('div');
            s.className = 'sparkle';
            s.textContent = letters[Math.floor(Math.random()*letters.length)];
            s.style.left  = Math.random()*100 + 'vw';
            s.style.top   = Math.random()*100 + 'vh';
            s.style.color = ['#36c0a1','#ef7e22','#ffe600'][Math.floor(Math.random()*3)];
            s.style.fontSize = (1 + Math.random()*2) + 'rem';
            document.body.appendChild(s);
            setTimeout(() => s.remove(), 800);
          }, i * 60);
        }
      }
    } else { pos = 0; }
  });
})();

// 2. Triple-click en el nombre CIVINSIS del footer → mensaje secreto
(function() {
  const brand = document.querySelector('.footer-brand-name');
  if (!brand) return;
  let clicks = 0, timer;
  brand.addEventListener('click', () => {
    clicks++;
    clearTimeout(timer);
    timer = setTimeout(() => { clicks = 0; }, 600);
    if (clicks >= 3) {
      clicks = 0;
      brand.classList.toggle('secret');
      if (brand.classList.contains('secret') && typeof Toast !== 'undefined') {
        Toast.show('🌿 "La participación es el primer paso del cambio." — CIVINSIS', 'info');
      }
    }
  });
})();

// 3. Cursor sparkles en la sección hero (solo letras CIVINSIS)
(function() {
  const civWord = document.getElementById('civitasWord');
  if (!civWord) return;
  const emojis = ['✨','🌿','⚡','🔥','💫','🌟','🎯','💡'];
  civWord.addEventListener('mousemove', e => {
    if (Math.random() > .15) return;
    const s = document.createElement('div');
    s.className = 'sparkle';
    s.textContent = emojis[Math.floor(Math.random()*emojis.length)];
    s.style.left = e.clientX + 'px';
    s.style.top  = e.clientY + 'px';
    s.style.fontSize = (.7 + Math.random()*.6) + 'rem';
    document.body.appendChild(s);
    setTimeout(() => s.remove(), 800);
  });
})();

// 4. Logo click secreto (5 clicks)
(function() {
  const logo = document.querySelector('.nav-logo-box');
  if (!logo) return;
  let lc = 0, lt;
  logo.addEventListener('click', () => {
    lc++;
    clearTimeout(lt);
    lt = setTimeout(() => lc=0, 1500);
    if (lc >= 5) {
      lc = 0;
      if (typeof Toast !== 'undefined') {
        const msgs = [
          '🚀 "El cambio empieza con una idea." — CIVINSIS',
          '🌱 Gracias por creer en la participación ciudadana.',
          '⚡ ¡Sigues explorando! La curiosidad es poder.',
        ];
        Toast.show(msgs[Math.floor(Math.random()*msgs.length)], 'info');
      }
    }
  });
})();

// ── Logout mejorado — partículas + redirige a index ─────────
const _origLogout = window.logout;
window.logout = async function() {
  // Usamos la función existente pero asegurándonos que redirija a index.php
  showLogoutModal();
};

// Patch showLogoutModal para mejorar animación
(function() {
  const origShowLogout = window.showLogoutModal;
  if (!origShowLogout) return;
  window.showLogoutModal = function() {
    // Llamar original pero luego asegurar redirect a index.php
    origShowLogout();
    // Los anillos adicionales
    setTimeout(() => {
      const overlay = document.getElementById('logoutOverlay');
      if (!overlay) return;
      const card = overlay.querySelector('div > div:not([style*="radial"])');
      if (!card) return;
      // Añadir anillos de logout
      for (let i = 0; i < 3; i++) {
        const ring = document.createElement('div');
        ring.className = 'logout-ring';
        ring.style.cssText = `width:${60+i*40}px;height:${60+i*40}px;top:50%;left:50%;margin-top:-${30+i*20}px;margin-left:-${30+i*20}px;animation-delay:${i*0.4}s`;
        overlay.appendChild(ring);
      }
    }, 400);
  };
})();

// ── Prevenir regreso post-logout ────────────────────────────
// (El session_helper.php ya envía Cache-Control, pero hacemos doble check)
(function() {
  // Si llegamos aquí después de un logout, la página no estará autenticada
  // y el historial se habrá reemplazado con location.replace
})();

// ── Decoración dinámica en secciones del index ──────────────
(function addSectionDecor() {
  // Burbujas en sección propuestas
  const propsBg = document.querySelector('.section-proposals-bg');
  if (propsBg) {
    const bubblesWrap = document.createElement('div');
    bubblesWrap.className = 'floating-bubbles';
    const sizes  = [80,120,160,200,100,140];
    const colors = ['rgba(54,192,161,1)','rgba(239,126,34,1)'];
    sizes.forEach((sz, i) => {
      const b = document.createElement('div');
      b.className = 'float-bubble';
      b.style.cssText = `
        width:${sz}px;height:${sz}px;
        background:${colors[i%2]};
        left:${5+i*15}%;top:${10+Math.sin(i)*40}%;
        --dur:${10+i*2}s;--del:-${i*2}s;
      `;
      bubblesWrap.appendChild(b);
    });
    propsBg.insertBefore(bubblesWrap, propsBg.firstChild);
  }

  // Puntos animados en "Cómo funciona"
  const comoBg = document.querySelector('.section-como-bg');
  if (comoBg) {
    const dotsWrap = document.createElement('div');
    dotsWrap.className = 'como-dots';
    for (let i = 0; i < 18; i++) {
      const d = document.createElement('div');
      d.className = 'como-dot';
      d.style.cssText = `left:${Math.random()*95}%;top:${Math.random()*90}%;--dur:${2+Math.random()*3}s;--del:-${Math.random()*3}s`;
      dotsWrap.appendChild(d);
    }
    comoBg.insertBefore(dotsWrap, comoBg.firstChild);
  }

  // Hexágonos en top-votadas
  const topBg = document.querySelector('.top-votadas-bg');
  if (topBg) {
    ['5%','75%'].forEach((left, i) => {
      const h = document.createElement('div');
      h.className = 'hex-decor';
      h.innerHTML = '⬡';
      h.style.cssText = `left:${left};top:${i===0?'10%':'40%'};animation-duration:${20+i*8}s;animation-direction:${i===0?'normal':'reverse'}`;
      topBg.appendChild(h);
    });
  }
})();

// ── CTA Section con animación de oleaje ─────────────────────
(function upgradeCTASection() {
  const ctaSection = document.querySelector('section[style*="grad-primary"]');
  if (!ctaSection) return;
  ctaSection.className += ' cta-wave-section';
  ctaSection.style.background = '';

  // Fondo decorativo
  const bg = document.createElement('div');
  bg.className = 'cta-wave-bg';
  ctaSection.insertBefore(bg, ctaSection.firstChild);

  // Partículas flotantes
  const particles = document.createElement('div');
  particles.className = 'cta-wave-particles';
  const dotSizes = [6,8,5,10,7,9,5,6];
  dotSizes.forEach((sz, i) => {
    const d = document.createElement('div');
    d.className = 'cta-dot';
    d.style.cssText = `width:${sz}px;height:${sz}px;left:${8+i*11}%;top:${20+Math.sin(i)*35}%;--dur:${4+i}s;--del:-${i*1.2}s`;
    particles.appendChild(d);
  });
  bg.appendChild(particles);

  // Oleaje SVG animado
  const waves = document.createElement('div');
  waves.className = 'cta-waves';
  waves.innerHTML = `
    <svg class="cta-wave-svg" viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,30 C180,60 360,0 540,30 C720,60 900,0 1080,30 C1260,60 1380,20 1440,30 L1440,60 L0,60 Z" fill="rgba(255,255,255,0.12)"/>
      <path d="M720,30 C900,60 1080,0 1260,30 C1440,60 1620,0 1800,30 C1980,60 2100,20 2160,30 L2160,60 L720,60 Z" fill="rgba(255,255,255,0.12)"/>
    </svg>
    <svg class="cta-wave-svg2" viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,20 C240,50 480,10 720,30 C960,50 1200,10 1440,25 L1440,60 L0,60 Z" fill="rgba(255,255,255,0.08)"/>
      <path d="M720,20 C960,50 1200,10 1440,30 C1680,50 1920,10 2160,25 L2160,60 L720,60 Z" fill="rgba(255,255,255,0.08)"/>
    </svg>
  `;
  ctaSection.appendChild(waves);
})();

// ── Actualizar avatar en navbar tras cambio en perfil ───────
// Se llama desde perfil.php después de changeAvatar exitoso
window.refreshNavAvatar = function(src) {
  updateNavAvatar(src);
};
