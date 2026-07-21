/* ============================================================
   CIVINSIS · Extras JS
   #5 Efectos hover por categoría  ·  #4 Widget de IA "CIVI"
   Autónomo: no depende de app.js y funciona con tarjetas
   renderizadas dinámicamente (delegación de eventos).
   ============================================================ */
(function () {
  'use strict';

  /* ---------------------------------------------------------
     #5 · PARTÍCULAS POR CATEGORÍA
  --------------------------------------------------------- */
  const EFECTOS = {
    'medio-ambiente': ['🍃','🌿','🌸','🍀','🌱'],
    'educacion':      ['📚','✏️','🎓','💡','📝'],
    'salud':          ['❤️','➕','💊','🩺','✚'],
    'tecnologia':     ['⚡','💻','⚙️','🔧','01'],
    'cultura':        ['🎭','🎵','🎨','🎬','🎤'],
    'deporte':        ['⚽','🏀','🏆','🔥','🎽'],
    'seguridad':      ['🛡️','🔒','⭐','🚨','🦺'],
    'infraestructura':['🚧','🧱','🔨','🏗️','🛣️'],
    'default':        ['✨','⭐','💫','🌟']
  };

  function spawnParticles(card) {
    const efecto = card.getAttribute('data-cat-effect') || 'default';
    const set = EFECTOS[efecto] || EFECTOS.default;

    let layer = card.querySelector('.cat-fx-layer');
    if (!layer) {
      layer = document.createElement('div');
      layer.className = 'cat-fx-layer';
      card.appendChild(layer);
    }

    const n = 8 + Math.floor(Math.random() * 4);
    for (let i = 0; i < n; i++) {
      const p = document.createElement('span');
      p.className = 'cat-particle';
      p.textContent = set[Math.floor(Math.random() * set.length)];
      const x     = Math.random() * 100;
      const size  = 12 + Math.random() * 16;
      const rise  = -(120 + Math.random() * 140);
      const drift = (Math.random() - 0.5) * 80;
      const spin  = (Math.random() - 0.5) * 720;
      const dur   = 1.8 + Math.random() * 1.6;
      const delay = Math.random() * 0.5;
      p.style.cssText =
        `--x:${x}%;--size:${size}px;--rise:${rise}px;--drift:${drift}px;` +
        `--spin:${spin}deg;--dur:${dur}s;--delay:${delay}s`;
      layer.appendChild(p);
      setTimeout(() => p.remove(), (dur + delay) * 1000 + 100);
    }
  }

  // Delegación: spawnea una vez por "entrada" del cursor en la tarjeta
  document.addEventListener('mouseover', function (e) {
    const card = e.target.closest('.proposal-card[data-cat-effect]');
    if (!card) return;
    // ¿venimos de fuera de la tarjeta?
    if (card.contains(e.relatedTarget)) return;
    if (card.dataset.fxBusy === '1') return;
    if (card.getAttribute('data-cat-effect') === '') return;
    card.dataset.fxBusy = '1';
    spawnParticles(card);
    // pequeña ráfaga adicional para sensación viva
    setTimeout(() => { if (card.matches(':hover')) spawnParticles(card); }, 700);
    setTimeout(() => { card.dataset.fxBusy = '0'; }, 900);
  });

  /* ---------------------------------------------------------
     #4 · WIDGET DE IA "CIVI"
  --------------------------------------------------------- */

  function buildWidget() {
    if (document.getElementById('civiFab')) return;

    const fab = document.createElement('button');
    fab.id = 'civiFab';
    fab.className = 'civi-fab';
    fab.setAttribute('aria-label', 'Abrir CIVI, tu entrenador cívico');
    fab.innerHTML = '<span class="civi-pulse"></span><i class="fas fa-robot"></i>';

    const panel = document.createElement('div');
    panel.id = 'civiPanel';
    panel.className = 'civi-panel';
    panel.innerHTML = `
      <div class="civi-head">
        <div class="civi-ava"><i class="fas fa-robot"></i></div>
        <div>
          <h4>CIVI</h4>
          <small>Tu entrenador cívico</small>
        </div>
        <button class="civi-close" id="civiClose" aria-label="Cerrar"><i class="fas fa-times"></i></button>
      </div>
      <div class="civi-body" id="civiBody"></div>
      <div class="civi-chips" id="civiChips">
        <button class="civi-chip" data-msg="Dame una idea para una propuesta">💡 Dame una idea</button>
        <button class="civi-chip" data-msg="¿Cómo hago una buena propuesta?">📝 Mejorar propuesta</button>
        <button class="civi-chip" data-msg="¿Qué es una propuesta ciudadana?">🏛️ Explícame un concepto</button>
      </div>
      <div class="civi-input">
        <input id="civiInput" type="text" placeholder="Pregúntale a CIVI..." autocomplete="off">
        <button id="civiSend" aria-label="Enviar"><i class="fas fa-paper-plane"></i></button>
      </div>`;

    document.body.appendChild(fab);
    document.body.appendChild(panel);

    const body  = panel.querySelector('#civiBody');
    const input = panel.querySelector('#civiInput');
    const historial = [];
    let saludado = false;

    function addMsg(texto, who) {
      const m = document.createElement('div');
      m.className = 'civi-msg ' + (who === 'user' ? 'user' : 'bot');
      m.textContent = texto;
      body.appendChild(m);
      body.scrollTop = body.scrollHeight;
      return m;
    }

    function openPanel() {
      if (window.CIVI) window.CIVI.stash(); // guarda la burbuja pendiente para no perderla
      panel.classList.add('open');
      if (!saludado) {
        saludado = true;
        addMsg('¡Hola! Soy CIVI, tu entrenador cívico. Puedo darte ideas, ayudarte a mejorar una propuesta o explicarte conceptos de participación. ¿En qué te ayudo?', 'bot');
      }
      setTimeout(() => input.focus(), 100);
    }
    function closePanel() { panel.classList.remove('open'); if (window.CIVI) window.CIVI.unstash(); }

    async function send(texto) {
      const msg = (texto || input.value || '').trim();
      if (!msg) return;
      input.value = '';
      addMsg(msg, 'user');
      historial.push({ role: 'user', content: msg });

      const typing = document.createElement('div');
      typing.className = 'civi-typing';
      typing.innerHTML = '<i class="fas fa-ellipsis fa-fade"></i> CIVI está escribiendo…';
      body.appendChild(typing);
      body.scrollTop = body.scrollHeight;

      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'chat', mensaje: msg, historial }),
        });
        const d = await res.json();
        typing.remove();
        const resp = (d && d.success && d.respuesta)
          ? d.respuesta
          : 'Ahora mismo no puedo responder. Inténtalo de nuevo en un momento.';
        addMsg(resp, 'bot');
        historial.push({ role: 'assistant', content: resp });
      } catch (e) {
        typing.remove();
        addMsg('Hubo un problema de conexión. Inténtalo de nuevo.', 'bot');
      }
    }

    fab.addEventListener('click', () => {
      panel.classList.contains('open') ? closePanel() : openPanel();
    });
    panel.querySelector('#civiClose').addEventListener('click', closePanel);
    panel.querySelector('#civiSend').addEventListener('click', () => send());
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') send(); });
    panel.querySelectorAll('.civi-chip').forEach((c) => {
      c.addEventListener('click', () => send(c.dataset.msg));
    });
  }

  /* ---------------------------------------------------------
     #6 · CIVI PERCEPCIÓN — burbujas contextuales
     CIVI observa y aparece SOLO cuando detecta algo útil.
  --------------------------------------------------------- */
  const CIVI = {
    bubble: null,
    hideTimer: null,

    // Contexto de la página actual (para el nudge del servidor)
    contexto() {
      const stem = (location.pathname.split('/').pop() || '').toLowerCase().replace('.php', '');
      const map = {
        crear: 'crear', debates: 'debates', debate: 'debate', dashboard: 'dashboard',
        propuestas: 'propuestas', tendencias: 'propuestas', inicio: 'inicio', index: 'inicio',
        perfil: 'perfil', progreso: 'perfil', desafios: 'dashboard', ranking: 'dashboard',
      };
      return map[stem] || stem || 'inicio';
    },

    ensure() {
      if (this.bubble) return this.bubble;
      const b = document.createElement('div');
      b.className = 'civi-bubble';
      b.id = 'civiBubble';
      document.body.appendChild(b);
      this.bubble = b;
      return b;
    },

    seen(id) { return id && sessionStorage.getItem('civi_seen_' + id); },
    markSeen(id) { if (id) sessionStorage.setItem('civi_seen_' + id, '1'); },

    glow(on) {
      const fab = document.getElementById('civiFab');
      if (fab) fab.classList.toggle('civi-attn', !!on);
    },

    hide(clear = true) {
      if (this.bubble) this.bubble.classList.remove('show');
      this.glow(false);
      clearTimeout(this.hideTimer);
      if (clear) this._current = null;
    },

    // Al abrir el chat: guarda la burbuja pendiente y la oculta (no la pierde).
    stash() {
      if (this._current) this._stashed = this._current;
      this.hide(false);
    },
    // Al cerrar el chat: vuelve a mostrar lo que quedó pendiente.
    unstash() {
      if (this._stashed) {
        const s = this._stashed; this._stashed = null;
        s._force = true;
        this.suggest(s);
      }
    },

    /**
     * Muestra una burbuja de CIVI.
     * opts: { id, texto, cta_texto, cta_url, cta_fn, once, sticky }
     */
    suggest(opts) {
      if (!opts || !opts.texto) return;
      if (opts.once && !opts._force && this.seen(opts.id)) return; // no repetir lo ya visto
      // si el chat está abierto, guardamos la sugerencia para mostrarla al cerrarlo
      const chat = document.getElementById('civiPanel');
      if (chat && chat.classList.contains('open')) { this._stashed = opts; return; }

      const b = this.ensure();
      const esc = (s) => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
      let ctaHtml = '';
      if (opts.cta_texto && (opts.cta_url || opts.cta_fn)) {
        ctaHtml = opts.cta_url
          ? `<a class="civi-b-cta" href="${esc(opts.cta_url)}"><i class="fas fa-arrow-right"></i> ${esc(opts.cta_texto)}</a>`
          : `<button type="button" class="civi-b-cta" id="civiBcta"><i class="fas fa-wand-magic-sparkles"></i> ${esc(opts.cta_texto)}</button>`;
      }
      b.innerHTML = `
        <div class="civi-b-ava"><i class="fas fa-robot"></i></div>
        <div class="civi-b-body">
          <div class="civi-b-name">CIVI</div>
          <div class="civi-b-text">${esc(opts.texto)}</div>
          ${ctaHtml}
        </div>
        <button type="button" class="civi-b-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>`;

      b.querySelector('.civi-b-close').onclick = () => { this.markSeen(opts.id); this.hide(); };
      if (opts.cta_fn) {
        const btn = b.querySelector('#civiBcta');
        if (btn) btn.onclick = () => { this.markSeen(opts.id); this.hide(); opts.cta_fn(); };
      }

      requestAnimationFrame(() => b.classList.add('show'));
      this.glow(true);
      this.markSeen(opts.id);
      this._current = opts;

      clearTimeout(this.hideTimer);
      if (!opts.sticky) {
        this.hideTimer = setTimeout(() => this.hide(), 15000);
        b.onmouseenter = () => clearTimeout(this.hideTimer);
        b.onmouseleave = () => { this.hideTimer = setTimeout(() => this.hide(), 5000); };
      }
    },

    // Al pulsar el FAB: CIVI te observa AHORA y da su mejor consejo del momento.
    async askNow() {
      // si ya hay una burbuja visible, es un toggle → cerrar
      if (this.bubble && this.bubble.classList.contains('show')) { this.hide(); return; }
      const ctx = this.contexto();
      // 1) ¿hay una oportunidad contextual? (nudge)
      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'nudge', contexto: ctx }),
        });
        const d = await res.json();
        if (d && d.mostrar) {
          this.suggest({ id: 'ask_' + Date.now(), sticky: true, texto: d.texto, cta_texto: d.cta_texto, cta_url: d.cta_url });
          return;
        }
      } catch (e) { /* seguimos */ }
      // 2) si no, muestra tu objetivo actual (coach)
      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'coach' }),
        });
        const d = await res.json();
        if (d && d.success && d.objetivo) {
          const o = d.objetivo;
          this.suggest({ id: 'ask_obj_' + Date.now(), sticky: true,
            texto: o.titulo + (o.descripcion ? '. ' + o.descripcion : ''),
            cta_texto: o.cta_texto, cta_url: o.cta_url });
          return;
        }
      } catch (e) { /* seguimos */ }
      // 3) respaldo: aliento breve
      this.suggest({ id: 'ask_ok_' + Date.now(), sticky: true,
        texto: 'Todo en orden por ahora. Sigue participando y estaré atento a cómo ayudarte. 👀' });
    },

    // Percepción a nivel de página: pregunta al cerebro si hay algo que decir
    async perceive() {
      // Bienvenida (una sola vez en el dispositivo)
      if (!localStorage.getItem('civi_welcomed')) {
        localStorage.setItem('civi_welcomed', '1');
        this.suggest({
          id: 'welcome', sticky: true,
          texto: '¡Hola! Soy CIVI, tu entrenador cívico. Estaré atento y apareceré cuando detecte algo en lo que pueda ayudarte. 😊',
        });
        return; // no encimar con otro nudge en la primera visita
      }

      const ctx = this.contexto();
      const key = 'civi_nudge_' + ctx;
      if (sessionStorage.getItem(key)) return; // ya sugerimos algo en esta página esta sesión
      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'nudge', contexto: ctx }),
        });
        const d = await res.json();
        if (d && d.mostrar) {
          sessionStorage.setItem(key, '1');
          this.suggest({ id: 'nudge_' + ctx, texto: d.texto, cta_texto: d.cta_texto, cta_url: d.cta_url });
        }
      } catch (e) { /* silencioso: CIVI nunca molesta si algo falla */ }
    },

    init() {
      // percepción con un pequeño retraso: se siente "observado", no de golpe
      setTimeout(() => this.perceive(), 2600);
      this.watchComentarios();
      this.watchAcciones();
      this.seedGrowth();
    },

    // Guarda el nivel/logros actuales como base (para detectar cambios luego)
    async seedGrowth() {
      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'crecimiento' }),
        });
        const d = await res.json();
        if (d && d.disponible) {
          sessionStorage.setItem('civi_nivel', d.nivel);
          sessionStorage.setItem('civi_logros', d.logros);
        }
      } catch (e) { /* silencioso */ }
    },

    // Entrenador de crecimiento: CIVI observa tus acciones y celebra el momento justo
    watchAcciones() {
      const obs = new MutationObserver((muts) => {
        for (const m of muts) {
          for (const node of m.addedNodes) {
            if (node.nodeType === 1 && node.classList && node.classList.contains('toast')
                && node.querySelector && node.querySelector('.toast-icon.success')) {
              clearTimeout(this._growthT);
              this._growthT = setTimeout(() => this.checkGrowth(), 950);
              return;
            }
          }
        }
      });
      obs.observe(document.body, { subtree: true, childList: true });
    },

    async checkGrowth() {
      try {
        const res = await fetch('php/ia.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'crecimiento' }),
        });
        const d = await res.json();
        if (!d || !d.disponible) return;

        const prevNivel  = parseInt(sessionStorage.getItem('civi_nivel')  || '0', 10);
        const prevLogros = parseInt(sessionStorage.getItem('civi_logros') || '-1', 10);
        sessionStorage.setItem('civi_nivel', d.nivel);
        sessionStorage.setItem('civi_logros', d.logros);

        // 1) ¡Subió de nivel!
        if (prevNivel && d.nivel > prevNivel) {
          this.suggest({ id: 'lvl_' + d.nivel, once: true, sticky: true,
            texto: `¡Felicidades! 🎉 Acabas de subir al nivel ${d.nivel}. Estás creciendo como ciudadano.` });
          return;
        }
        // 2) ¡Nuevo logro!
        if (prevLogros >= 0 && d.logros > prevLogros) {
          this.suggest({ id: 'logro_' + d.logros, once: true, sticky: true,
            texto: `¡Desbloqueaste un nuevo logro! 🏅 Ya llevas ${d.logros}. Sigue así.` });
          return;
        }
        // 3) A un paso de subir de nivel
        if (d.xp_faltante > 0 && d.xp_faltante <= 25) {
          this.suggest({ id: 'casi_lvl_' + d.nivel, once: true,
            texto: `¡Estás a solo ${d.xp_faltante} XP del nivel ${d.nivel + 1}! Un aporte más y lo logras.`,
            cta_texto: 'Participar', cta_url: 'dashboard.php' });
          return;
        }
        // 4) Misión casi completa
        if (d.mision_cerca && d.mision_cerca.cantidad > 0) {
          const falta = d.mision_cerca.cantidad - d.mision_cerca.progreso;
          if (falta > 0 && falta <= 1) {
            this.suggest({ id: 'mis_' + d.mision_cerca.nombre, once: true,
              texto: `Te falta muy poco para completar «${d.mision_cerca.nombre}». ¡Ya casi!`,
              cta_texto: 'Ver misiones', cta_url: 'progreso.php' });
          }
        }
      } catch (e) { /* silencioso */ }
    },

    // Entrenador de comentarios: percibe el tono mientras escribes (educar, no censurar)
    watchComentarios() {
      const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

      const revisar = debounce(async (el) => {
        const txt = (el.value || '').trim();
        if (txt.length < 4) { el.dataset.civiTono = '0'; return; }
        if (el.dataset.civiTonoTxt === txt) return; // ya evaluamos este texto exacto
        el.dataset.civiTonoTxt = txt;

        // CIVI le pide a la IA que juzgue el tono real (contexto, no lista de palabras)
        let agresivo = false;
        try {
          const res = await fetch('php/ia.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'revisar_tono', texto: txt }),
          });
          const d = await res.json();
          agresivo = !!(d && d.agresivo);
        } catch (e) { return; }

        if (agresivo) {
          if (el.dataset.civiTono === '1') return; // ya avisamos para este episodio
          el.dataset.civiTono = '1';
          this.suggest({
            id: 'tono_' + Date.now(), sticky: true,
            texto: 'Este comentario podría sonar ofensivo. Puedes reformularlo, o publicarlo así — pero un moderador lo revisará antes de darlo por válido.',
            cta_texto: 'Reformular con CIVI',
            cta_fn: async () => {
              const original = el.value;
              try {
                const res = await fetch('php/ia.php', {
                  method: 'POST', headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ accion: 'tono', texto: original }),
                });
                const d = await res.json();
                if (d && d.success && d.respuesta) {
                  el.value = d.respuesta.trim();
                  el.dataset.civiTono = '0';
                  el.dataset.civiTonoTxt = '';
                  el.focus();
                  // dejar el cursor al final para que el usuario siga editando
                  const n = el.value.length;
                  try { el.setSelectionRange(n, n); } catch (e) {}
                  if (window.Toast) Toast.show('CIVI reformuló tu comentario — revísalo y edítalo antes de publicar', 'success');
                }
              } catch (e) { /* silencioso */ }
            },
          });
        } else {
          el.dataset.civiTono = '0'; // texto limpio → puede volver a avisar si reaparece
        }
      }, 900);

      // Delegación: el input del comentario/respuesta aparece dinámicamente en modales
      document.addEventListener('input', (e) => {
        const el = e.target;
        if (el && (el.id === 'commentText' || el.id === 'respuestaText')) revisar(el);
      });
    },
  };
  window.CIVI = CIVI;

  function initCivi() {
    buildWidget();
    CIVI.init();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCivi);
  } else {
    initCivi();
  }
})();
