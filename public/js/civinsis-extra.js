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
  const historial = [];

  function buildWidget() {
    if (document.getElementById('civiFab')) return;

    const fab = document.createElement('button');
    fab.id = 'civiFab';
    fab.className = 'civi-fab';
    fab.setAttribute('aria-label', 'Asistente CIVI');
    fab.innerHTML = '<span class="civi-pulse"></span><i class="fas fa-robot"></i>';

    const panel = document.createElement('div');
    panel.className = 'civi-panel';
    panel.id = 'civiPanel';
    panel.innerHTML = `
      <div class="civi-head">
        <div class="civi-ava"><i class="fas fa-robot"></i></div>
        <div>
          <h4>CIVI</h4>
          <small>Tu asistente cívico</small>
        </div>
        <button class="civi-close" id="civiClose" aria-label="Cerrar"><i class="fas fa-times"></i></button>
      </div>
      <div class="civi-body" id="civiBody"></div>
      <div class="civi-chips" id="civiChips">
        <span class="civi-chip" data-q="Dame ideas de propuestas">💡 Dame ideas</span>
        <span class="civi-chip" data-q="¿Cómo creo una propuesta?">📝 ¿Cómo creo una propuesta?</span>
        <span class="civi-chip" data-q="¿Cómo personalizo mi perfil?">🎨 Personalizar perfil</span>
      </div>
      <div class="civi-input">
        <input type="text" id="civiInput" placeholder="Escribe tu mensaje..." autocomplete="off">
        <button id="civiSend" aria-label="Enviar"><i class="fas fa-paper-plane"></i></button>
      </div>`;

    document.body.appendChild(fab);
    document.body.appendChild(panel);

    const body  = panel.querySelector('#civiBody');
    const input = panel.querySelector('#civiInput');

    function addMsg(text, who) {
      const m = document.createElement('div');
      m.className = 'civi-msg ' + who;
      m.textContent = text;
      body.appendChild(m);
      body.scrollTop = body.scrollHeight;
      return m;
    }

    function openPanel() {
      panel.classList.add('open');
      if (!body.dataset.greeted) {
        addMsg('¡Hola! 👋 Soy CIVI, tu asistente en CIVINSIS. Puedo darte ideas, ayudarte a redactar una propuesta o explicarte cómo usar la plataforma. ¿En qué te ayudo?', 'bot');
        body.dataset.greeted = '1';
      }
      setTimeout(() => input.focus(), 250);
    }

    async function send(texto) {
      texto = (texto || input.value).trim();
      if (!texto) return;
      input.value = '';
      addMsg(texto, 'user');
      historial.push({ role: 'user', content: texto });

      const typing = document.createElement('div');
      typing.className = 'civi-typing';
      typing.textContent = 'CIVI está escribiendo…';
      body.appendChild(typing);
      body.scrollTop = body.scrollHeight;

      try {
        const res = await fetch('php/ia.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ accion: 'chat', mensaje: texto, historial })
        });
        const data = await res.json();
        typing.remove();
        const resp = data.respuesta || data.message || 'No pude responder, intenta de nuevo.';
        addMsg(resp, 'bot');
        historial.push({ role: 'assistant', content: resp });
      } catch (err) {
        typing.remove();
        addMsg('Ups, hubo un problema de conexión. Intenta otra vez. 🙏', 'bot');
      }
    }

    fab.addEventListener('click', () => panel.classList.contains('open') ? panel.classList.remove('open') : openPanel());
    panel.querySelector('#civiClose').addEventListener('click', () => panel.classList.remove('open'));
    panel.querySelector('#civiSend').addEventListener('click', () => send());
    input.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
    panel.querySelectorAll('.civi-chip').forEach(c =>
      c.addEventListener('click', () => send(c.dataset.q)));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', buildWidget);
  } else {
    buildWidget();
  }
})();
