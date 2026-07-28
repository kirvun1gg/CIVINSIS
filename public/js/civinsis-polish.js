/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Capa de pulido
   Celebraciones, sonidos opcionales, skeletons, microinteracciones
   y mejoras de accesibilidad. No depende de ninguna librería.
   Se expone como window.CV
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const menosMovimiento = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const CV = {

    /* ═════════════════════════════════════════════════════
       SONIDOS OPCIONALES
       Se generan con WebAudio (sin archivos que descargar) y
       están APAGADOS por defecto: el usuario los activa.
       ═════════════════════════════════════════════════════ */
    sonido: {
      get activo() { return localStorage.getItem('cv_sonido') === '1'; },
      set activo(v) { localStorage.setItem('cv_sonido', v ? '1' : '0'); },
      _ctx: null,
      _ac() {
        if (!this._ctx) {
          const AC = window.AudioContext || window.webkitAudioContext;
          if (!AC) return null;
          this._ctx = new AC();
        }
        if (this._ctx.state === 'suspended') this._ctx.resume();
        return this._ctx;
      },
      /** Reproduce una secuencia de notas suaves. */
      tocar(notas, volumen = 0.06) {
        if (!this.activo) return;
        const ctx = this._ac();
        if (!ctx) return;
        notas.forEach((n, i) => {
          const t = ctx.currentTime + i * 0.09;
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();
          osc.type = 'sine';
          osc.frequency.setValueAtTime(n, t);
          gain.gain.setValueAtTime(0, t);
          gain.gain.linearRampToValueAtTime(volumen, t + 0.02);
          gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.32);
          osc.connect(gain); gain.connect(ctx.destination);
          osc.start(t); osc.stop(t + 0.34);
        });
      },
      nivel()   { this.tocar([523.25, 659.25, 783.99, 1046.5]); },  // do-mi-sol-do
      logro()   { this.tocar([659.25, 830.61, 987.77]); },          // mi-sol#-si
      exito()   { this.tocar([587.33, 880], 0.05); },
      aviso()   { this.tocar([440, 349.23], 0.05); },
      click()   { this.tocar([880], 0.025); },
    },

    /* ═════════════════════════════════════════════════════
       CELEBRACIONES (subir de nivel / logro)
       ═════════════════════════════════════════════════════ */
    _capa: null,

    _crearCapa() {
      if (this._capa) return this._capa;
      const c = document.createElement('div');
      c.className = 'cv-celebra';
      c.setAttribute('role', 'dialog');
      c.setAttribute('aria-modal', 'true');
      c.innerHTML = `
        <div class="cv-celebra-card">
          <div class="cv-medalla"><i class="fas fa-star" id="cvMedallaIco"></i></div>
          <div class="cv-celebra-kicker" id="cvKicker"></div>
          <h2 class="cv-celebra-titulo" id="cvTitulo"></h2>
          <p class="cv-celebra-texto" id="cvTexto"></p>
          <button type="button" class="cv-celebra-btn" id="cvCerrar">
            <i class="fas fa-check"></i> <span>¡Seguir participando!</span>
          </button>
        </div>`;
      document.body.appendChild(c);
      this._capa = c;

      const cerrar = () => this.cerrarCelebracion();
      c.querySelector('#cvCerrar').addEventListener('click', cerrar);
      c.addEventListener('click', (e) => { if (e.target === c) cerrar(); });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && c.classList.contains('show')) cerrar();
      });
      return c;
    },

    _confeti(card) {
      if (menosMovimiento()) return;
      const colores = ['#36c0a1', '#ef7e22', '#f59e0b', '#4a9eff', '#22c55e'];
      for (let i = 0; i < 26; i++) {
        const p = document.createElement('span');
        p.className = 'cv-confeti';
        p.style.left = Math.random() * 100 + '%';
        p.style.background = colores[i % colores.length];
        p.style.setProperty('--dur', (1.8 + Math.random() * 1.4) + 's');
        p.style.setProperty('--delay', (Math.random() * 0.5) + 's');
        p.style.setProperty('--dist', (220 + Math.random() * 220) + 'px');
        p.style.setProperty('--giro', (Math.random() * 900 - 450) + 'deg');
        card.appendChild(p);
        setTimeout(() => p.remove(), 3600);
      }
    },

    /* Una celebración se guarda como PENDIENTE mientras está abierta.
       Si la página se recarga (varias acciones hacen location.reload),
       se vuelve a mostrar al cargar en vez de perderse. */
    _guardarPendiente(d) { try { localStorage.setItem('cv_celebrar', JSON.stringify(d)); } catch (e) {} },
    _limpiarPendiente()  { try { localStorage.removeItem('cv_celebrar'); } catch (e) {} },

    _mostrarPendiente() {
      let d = null;
      try { d = JSON.parse(localStorage.getItem('cv_celebrar') || 'null'); } catch (e) {}
      if (!d) return;
      setTimeout(() => {
        if (d.tipo === 'nivel') this.celebrarNivel(d.valor, true);
        else if (d.tipo === 'logro') this.celebrarLogro(d.nombre, d.valor, true);
      }, 600);
    },

    /** Muestra la tarjeta de celebración. */
    celebrar({ kicker, titulo, texto, icono = 'fa-star', sonido = 'nivel' }) {
      const c = this._crearCapa();
      c.querySelector('#cvMedallaIco').className = 'fas ' + icono;
      c.querySelector('#cvKicker').textContent = kicker || '';
      c.querySelector('#cvTitulo').textContent = titulo || '';
      c.querySelector('#cvTexto').textContent = texto || '';

      requestAnimationFrame(() => {
        c.classList.add('show');
        this._confeti(c.querySelector('.cv-celebra-card'));
        c.querySelector('#cvCerrar').focus();
      });
      if (sonido && this.sonido[sonido]) this.sonido[sonido]();

      // se cierra sola por si el usuario se distrae
      clearTimeout(this._tCelebra);
      this._tCelebra = setTimeout(() => this.cerrarCelebracion(), 9000);
    },

    cerrarCelebracion() {
      this._limpiarPendiente();
      if (!this._capa) return;
      this._capa.classList.remove('show');
      clearTimeout(this._tCelebra);
      this._capa.querySelectorAll('.cv-confeti').forEach((p) => p.remove());
    },

    celebrarNivel(nivel, yaPendiente) {
      if (!yaPendiente) this._guardarPendiente({ tipo: 'nivel', valor: nivel });
      this.celebrar({
        kicker: 'Has subido de nivel',
        titulo: `¡Nivel ${nivel}!`,
        texto: 'Tu participación está construyendo comunidad. Sigue así: cada aporte cuenta.',
        icono: 'fa-arrow-trend-up', sonido: 'nivel',
      });
    },

    celebrarLogro(nombre, total, yaPendiente) {
      if (!yaPendiente) this._guardarPendiente({ tipo: 'logro', nombre, valor: total });
      this.celebrar({
        kicker: 'Logro desbloqueado',
        titulo: nombre || '¡Nuevo logro!',
        texto: total ? `Ya llevas ${total} logros. Tu constancia se nota.` : 'Tu constancia se nota.',
        icono: 'fa-award', sonido: 'logro',
      });
    },

    /* ═════════════════════════════════════════════════════
       SKELETON LOADING
       ═════════════════════════════════════════════════════ */
    /** Devuelve el HTML de N tarjetas fantasma. */
    skeleton(n = 3, tipo = 'card') {
      const uno = tipo === 'fila'
        ? `<div class="cv-skel-card"><div class="cv-skel-fila">
             <div class="cv-skel cv-skel-avatar"></div>
             <div><div class="cv-skel cv-skel-linea media"></div>
                  <div class="cv-skel cv-skel-linea corta"></div></div>
           </div></div>`
        : `<div class="cv-skel-card">
             <div class="cv-skel cv-skel-titulo"></div>
             <div class="cv-skel cv-skel-linea"></div>
             <div class="cv-skel cv-skel-linea media"></div>
             <div class="cv-skel cv-skel-linea corta"></div>
           </div>`;
      return `<div aria-busy="true" aria-live="polite">${uno.repeat(n)}</div>`;
    },

    /** Pinta skeletons dentro de un contenedor mientras carga. */
    cargando(sel, n = 3, tipo = 'card') {
      const el = typeof sel === 'string' ? document.querySelector(sel) : sel;
      if (el) el.innerHTML = this.skeleton(n, tipo);
    },

    /* ═════════════════════════════════════════════════════
       MICROINTERACCIONES
       ═════════════════════════════════════════════════════ */
    _ondas() {
      document.addEventListener('pointerdown', (e) => {
        if (menosMovimiento()) return;
        const btn = e.target.closest('.btn, .civi-b-cta, .cv-celebra-btn');
        if (!btn || btn.disabled) return;
        const r = btn.getBoundingClientRect();
        const d = Math.max(r.width, r.height);
        const o = document.createElement('span');
        o.className = 'cv-onda';
        o.style.width = o.style.height = d + 'px';
        o.style.left = (e.clientX - r.left - d / 2) + 'px';
        o.style.top = (e.clientY - r.top - d / 2) + 'px';
        if (getComputedStyle(btn).position === 'static') btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.appendChild(o);
        setTimeout(() => o.remove(), 600);
      }, { passive: true });
    },

    /** Late un elemento (p. ej. al votar). */
    latir(el) {
      if (!el || menosMovimiento()) return;
      el.classList.remove('cv-latido');
      void el.offsetWidth;
      el.classList.add('cv-latido');
      setTimeout(() => el.classList.remove('cv-latido'), 500);
    },

    /** Anima un número de un valor a otro. */
    contar(el, desde, hasta, ms = 700) {
      if (!el) return;
      if (menosMovimiento()) { el.textContent = hasta; return; }
      const ini = performance.now();
      const paso = (t) => {
        const p = Math.min(1, (t - ini) / ms);
        const suave = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(desde + (hasta - desde) * suave);
        if (p < 1) requestAnimationFrame(paso);
      };
      requestAnimationFrame(paso);
    },

    /** Aparición progresiva de tarjetas al hacer scroll. */
    _revelar() {
      if (menosMovimiento() || !('IntersectionObserver' in window)) return;
      const obs = new IntersectionObserver((entradas) => {
        entradas.forEach((e) => {
          if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
      }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

      const marcar = () => {
        document.querySelectorAll('.card, .propuesta-card, .debate-card')
          .forEach((el) => {
            if (!el.classList.contains('cv-reveal') && !el.classList.contains('visible')) {
              el.classList.add('cv-reveal'); obs.observe(el);
            }
          });
      };
      marcar();
      // el contenido llega por AJAX: volver a marcar cuando cambie el DOM
      new MutationObserver(() => { clearTimeout(this._tRev); this._tRev = setTimeout(marcar, 120); })
        .observe(document.body, { childList: true, subtree: true });
    },

    /* ═════════════════════════════════════════════════════
       ACCESIBILIDAD
       ═════════════════════════════════════════════════════ */
    _accesibilidad() {
      // 1) Enlace "saltar al contenido" para quien navega con teclado
      if (!document.querySelector('.cv-saltar')) {
        const main = document.querySelector('main') || document.querySelector('.container');
        if (main) {
          if (!main.id) main.id = 'contenido';
          const a = document.createElement('a');
          a.className = 'cv-saltar';
          a.href = '#' + main.id;
          a.textContent = 'Saltar al contenido';
          document.body.insertBefore(a, document.body.firstChild);
        }
      }

      // 2) Botones que solo tienen icono: nombrarlos para lectores de pantalla
      const nombrar = () => {
        document.querySelectorAll('button:not([aria-label]):not([data-cv-aria])').forEach((b) => {
          const texto = b.textContent.trim();
          if (texto) return;                       // ya tiene texto visible
          const i = b.querySelector('i[class*="fa-"]');
          if (!i) return;
          const tip = b.getAttribute('title') || b.dataset.tip;
          if (tip) b.setAttribute('aria-label', tip);
          b.dataset.cvAria = '1';
        });
      };
      nombrar();
      new MutationObserver(() => { clearTimeout(this._tAria); this._tAria = setTimeout(nombrar, 200); })
        .observe(document.body, { childList: true, subtree: true });

      // 3) Convertir title="" en tooltip propio (los nativos son lentos y feos)
      document.querySelectorAll('[title]:not([data-tip])').forEach((el) => {
        const t = el.getAttribute('title');
        if (!t) return;
        el.dataset.tip = t;
        el.removeAttribute('title');
      });
    },

    /* ═════════════════════════════════════════════════════
       RENDIMIENTO
       ═════════════════════════════════════════════════════ */
    _rendimiento() {
      // Carga diferida de imágenes que no la declaren
      const lazy = () => {
        document.querySelectorAll('img:not([loading])').forEach((img) => {
          img.loading = 'lazy';
          img.decoding = 'async';
        });
      };
      lazy();
      new MutationObserver(() => { clearTimeout(this._tLazy); this._tLazy = setTimeout(lazy, 250); })
        .observe(document.body, { childList: true, subtree: true });
    },

    /* ═════════════════════════════════════════════════════
       BOTÓN DE SONIDO (se inserta junto al de tema si existe)
       ═════════════════════════════════════════════════════ */
    _botonSonido() {
      if (document.getElementById('cvSonidoBtn')) return;
      // se coloca justo antes del interruptor de tema
      const ancla = document.querySelector('.dark-toggle-wrap')
        || document.getElementById('themeBtn')
        || document.querySelector('.theme-toggle, .nav-actions');
      if (!ancla) return;

      const b = document.createElement('button');
      b.id = 'cvSonidoBtn';
      b.type = 'button';
      b.className = 'cv-sonido-btn' + (this.sonido.activo ? ' activo' : '');
      b.dataset.tip = this.sonido.activo ? 'Sonidos activados' : 'Sonidos desactivados';
      b.setAttribute('aria-label', b.dataset.tip);
      b.setAttribute('aria-pressed', String(this.sonido.activo));
      b.innerHTML = `<i class="fas ${this.sonido.activo ? 'fa-volume-high' : 'fa-volume-xmark'}"></i>`;

      b.addEventListener('click', () => {
        this.sonido.activo = !this.sonido.activo;
        const on = this.sonido.activo;
        b.classList.toggle('activo', on);
        b.dataset.tip = on ? 'Sonidos activados' : 'Sonidos desactivados';
        b.setAttribute('aria-label', b.dataset.tip);
        b.setAttribute('aria-pressed', String(on));
        b.querySelector('i').className = 'fas ' + (on ? 'fa-volume-high' : 'fa-volume-xmark');
        if (on) this.sonido.exito();
      });

      (ancla.parentNode || document.body).insertBefore(b, ancla);
    },

    /* ═════════════════════════════════════════════════════
       ARRANQUE
       ═════════════════════════════════════════════════════ */
    init() {
      this._ondas();
      this._revelar();
      this._accesibilidad();
      this._rendimiento();
      this._botonSonido();
      this._mostrarPendiente();

      // Sonido sutil al confirmarse una acción (los toasts de éxito)
      new MutationObserver((muts) => {
        for (const m of muts) {
          for (const n of m.addedNodes) {
            if (n.nodeType === 1 && n.classList?.contains('toast')) {
              if (n.querySelector?.('.toast-icon.success')) this.sonido.exito();
              else if (n.querySelector?.('.toast-icon.error')) this.sonido.aviso();
            }
          }
        }
      }).observe(document.body, { childList: true, subtree: true });
    },
  };

  window.CV = CV;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CV.init());
  } else {
    CV.init();
  }
})();
