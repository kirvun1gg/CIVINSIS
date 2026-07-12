/* ============================================================
   CIVINSIS · Módulo de Desafíos
   Depende de API, Toast (app.js) y de la función esc() (debates.js).
   Si debates.js no está cargado en esta página, definimos un
   esc() propio como respaldo.
   ============================================================ */

if (typeof esc !== 'function') {
  window.esc = function (str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  };
}

const DIFICULTAD_LABEL = { facil: 'Fácil', medio: 'Medio', dificil: 'Difícil' };

const Desafios = {
  logueado: false,
  currentCat: 0,
  currentDificultad: '',

  init(logueado) {
    this.logueado = !!logueado;
    this.load();
  },

  filterCat(cat, btn) {
    document.querySelectorAll('.filters-bar [data-cat]').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.currentCat = cat;
    this.load();
  },

  filterDificultad(dif, btn) {
    document.querySelectorAll('.filters-bar [data-dif]').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.currentDificultad = dif;
    this.load();
  },

  async load() {
    const grid = document.getElementById('desafiosGrid');
    if (!grid) return;
    grid.innerHTML = this.skeletons();

    const res = await API.get('php/desafios.php', {
      accion: 'listar',
      categoria_id: this.currentCat,
      dificultad: this.currentDificultad,
    });

    if (!res.success || !res.desafios.length) {
      grid.innerHTML = `<div class="empty-state"><p>No hay desafíos disponibles con estos filtros.</p></div>`;
      return;
    }

    grid.innerHTML = res.desafios.map(d => this.card(d)).join('');
  },

  card(d) {
    const estados = {
      completado:   { label: 'Completado', icon: 'fa-circle-check', cls: 'completado' },
      en_progreso:  { label: 'En progreso', icon: 'fa-hourglass-half', cls: 'en-progreso' },
      no_iniciado:  { label: '', icon: '', cls: '' },
    };
    const est = estados[d.estado] || estados.no_iniciado;

    const insigniaHtml = d.insignia ? `
      <div class="desafio-insignia" title="${esc(d.insignia.nombre)}">
        <span style="color:${d.insignia.color}">${d.insignia.icono}</span> ${esc(d.insignia.nombre)}
      </div>` : '';

    return `
      <div class="desafio-card">
        ${est.label ? `<div class="desafio-estado-tag ${est.cls}"><i class="fas ${est.icon}"></i> ${est.label}</div>` : ''}
        <div class="desafio-icon-wrap"><i class="${d.icono}"></i></div>
        <div class="desafio-card-top">
          <span class="desafio-dificultad dif-${d.dificultad}">${DIFICULTAD_LABEL[d.dificultad] || d.dificultad}</span>
          ${d.categoria ? `<span class="debate-cat-badge" style="--cat-color:${d.categoria_color}"><i class="${d.categoria_icono}"></i> ${esc(d.categoria)}</span>` : ''}
        </div>
        <h3 class="desafio-card-title">${esc(d.titulo)}</h3>
        <div class="desafio-recompensas">
          <span><i class="fas fa-bolt"></i> ${d.xp_recompensa} XP</span>
          <span><i class="fas fa-star"></i> ${d.reputacion_recompensa} rep.</span>
        </div>
        ${insigniaHtml}
        <button class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:1rem" onclick="Desafios.aceptar(${d.id})">
          <i class="fas fa-arrow-right"></i> ${d.estado === 'completado' ? 'Crear otra propuesta' : 'Aceptar desafío'}
        </button>
      </div>`;
  },

  skeletons() {
    return Array(6).fill(0).map(() => `
      <div class="desafio-card" style="pointer-events:none">
        <div class="skeleton" style="height:44px;width:44px;border-radius:12px;margin-bottom:1rem"></div>
        <div class="skeleton" style="height:14px;width:40%;margin-bottom:10px"></div>
        <div class="skeleton" style="height:20px;margin-bottom:8px"></div>
        <div class="skeleton" style="height:14px;width:70%"></div>
      </div>`).join('');
  },

  async aceptar(id) {
    const res = await API.post('php/desafios.php', { accion: 'aceptar', desafio_id: id });
    if (res.success) {
      Toast.show(res.message, 'success');
      setTimeout(() => { location.href = `crear.php?desafio_id=${id}`; }, 500);
    } else {
      Toast.show(res.message, 'error');
    }
  }
};

/* ── Widget "¿Sin ideas?" para crear.php ─────────────────── */
const DesafioWidget = {
  async init() {
    const box = document.getElementById('desafioSugeridoBox');
    if (!box) return;

    const params = new URLSearchParams(location.search);
    const desafioId = params.get('desafio_id');

    if (desafioId) {
      await this.mostrarVinculado(parseInt(desafioId, 10));
    } else {
      await this.mostrarSugerido();
    }
  },

  async mostrarVinculado(id) {
    const res = await API.get('php/desafios.php', { accion: 'detalle', id });
    if (!res.success) return;
    const d = res.desafio;

    document.getElementById('desafioIdInput').value = id;

    // Autocompletar título y categoría (sin pisar lo que el usuario ya haya escrito)
    const tituloInput = document.getElementById('titulo');
    if (tituloInput && !tituloInput.value.trim()) {
      tituloInput.value = d.titulo;
      tituloInput.dispatchEvent(new Event('input'));
    }
    const categoriaSelect = document.getElementById('categoria_id');
    if (categoriaSelect && d.categoria_id && !categoriaSelect.value) {
      categoriaSelect.value = d.categoria_id;
      categoriaSelect.dispatchEvent(new Event('change'));
    }

    const box = document.getElementById('desafioSugeridoBox');
    box.innerHTML = `
      <h4 style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--verde-700);margin-bottom:.5rem">
        <i class="${d.icono}"></i> Propuesta vinculada a un desafío
      </h4>
      <p style="font-size:.85rem;color:var(--text-2);margin-bottom:.6rem">${esc(d.titulo)}</p>
      <div class="desafio-recompensas" style="margin-bottom:.25rem">
        <span><i class="fas fa-bolt"></i> ${d.xp_recompensa} XP</span>
        <span><i class="fas fa-star"></i> ${d.reputacion_recompensa} rep.</span>
      </div>
      <a href="crear.php" style="font-size:.75rem;color:var(--text-muted)">Quitar vínculo</a>
    `;
  },

  async mostrarSugerido() {
    const res = await API.get('php/desafios.php', { accion: 'sugerido' });
    const box = document.getElementById('desafioSugeridoBox');
    if (!res.success) { box.style.display = 'none'; return; }
    const d = res.desafio;

    box.innerHTML = `
      <h4 style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--naranja-700);margin-bottom:.5rem">
        <i class="fas fa-lightbulb"></i> ¿Sin ideas?
      </h4>
      <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.6rem">Prueba uno de nuestros desafíos:</p>
      <p style="font-size:.85rem;color:var(--text-2);font-weight:600;margin-bottom:.75rem">${esc(d.titulo)}</p>
      <div style="display:flex;gap:.5rem">
        <a href="desafios.php" class="btn btn-sm btn-outline" style="flex:1;justify-content:center">Ver todos</a>
        <button class="btn btn-sm btn-primary" style="flex:1;justify-content:center" onclick="Desafios.aceptar(${d.id})">Aceptar</button>
      </div>
    `;
  }
};

document.addEventListener('DOMContentLoaded', () => DesafioWidget.init());
