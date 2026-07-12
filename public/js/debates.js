/* ============================================================
   CIVINSIS · Módulo de Debates
   Depende de API, Toast y Modal definidos en app.js
   ============================================================ */

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

function timeAgoDebate(fecha) {
  if (!fecha) return '';
  const diff = (Date.now() - new Date(fecha.replace(' ', 'T'))) / 1000;
  if (diff < 60) return 'hace un momento';
  if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`;
  if (diff < 2592000) return `hace ${Math.floor(diff / 86400)} d`;
  return new Date(fecha.replace(' ', 'T')).toLocaleDateString('es');
}

// ── LISTADO ───────────────────────────────────────────────
const Debates = {
  currentCat: 0,
  currentPage: 1,
  currentOrden: 'recientes',
  searchTimeout: null,

  init() {
    this.load();
    const search = document.getElementById('debateSearchInput');
    if (search) {
      search.addEventListener('input', () => {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => { this.currentPage = 1; this.load(); }, 400);
      });
    }
    const orden = document.getElementById('debateOrdenSelect');
    if (orden) orden.addEventListener('change', () => { this.currentOrden = orden.value; this.currentPage = 1; this.load(); });
  },

  filterCat(cat, btn) {
    document.querySelectorAll('#debatesGrid ~ .filters-bar [data-cat]').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.filters-bar [data-cat]').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.currentCat = cat;
    this.currentPage = 1;
    this.load();
  },

  async load() {
    const grid = document.getElementById('debatesGrid');
    if (!grid) return;
    grid.innerHTML = this.skeletons();

    const buscar = document.getElementById('debateSearchInput')?.value || '';
    const res = await API.get('php/debates.php', {
      accion: 'listar',
      pagina: this.currentPage,
      categoria_id: this.currentCat,
      orden: this.currentOrden,
      buscar,
    });

    if (!res.success) { grid.innerHTML = `<p style="color:var(--text-muted)">No se pudieron cargar los debates.</p>`; return; }

    if (!res.debates.length) {
      grid.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-comments" style="font-size:2.5rem;color:var(--text-muted);margin-bottom:1rem"></i>
          <p>Todavía no hay debates en esta categoría. ¡Sé el primero en iniciar uno!</p>
        </div>`;
      document.getElementById('debatesPagination').innerHTML = '';
      return;
    }

    grid.innerHTML = res.debates.map(d => this.card(d)).join('');
    this.renderPagination(res.pagina, res.total_paginas);
  },

  card(d) {
    const cerrado = d.estado === 'cerrado';
    return `
      <div class="debate-card" onclick="location.href='debate.php?id=${d.id}'">
        <div class="debate-card-top">
          <span class="debate-cat-badge" style="--cat-color:${d.categoria_color}">
            <i class="${d.categoria_icono}"></i> ${esc(d.categoria)}
          </span>
          <span class="debate-estado-badge ${cerrado ? 'cerrado' : 'activo'}">
            <i class="fas ${cerrado ? 'fa-lock' : 'fa-circle'}"></i> ${cerrado ? 'Cerrado' : 'Activo'}
          </span>
        </div>
        <h3 class="debate-card-title">${esc(d.titulo)}</h3>
        <p class="debate-card-desc">${esc(d.descripcion)}</p>
        <div class="debate-card-footer">
          ${this.authorHtml(d)}
          <div class="debate-card-stats">
            <span><i class="fas fa-users"></i> ${d.participantes}</span>
            <span><i class="fas fa-reply"></i> ${d.respuestas_count}</span>
          </div>
        </div>
      </div>`;
  },

  /** Reutiliza el mismo markup/CSS que las tarjetas de propuestas (.card-author, .author-avatar, .label-nivel, .label-titulo). */
  authorHtml(d) {
    const avaInner = (d.autor_avatar && d.autor_avatar.indexOf('data:') === 0)
      ? `<img class="author-avatar" src="${d.autor_avatar}" alt="">`
      : `<span class="author-avatar">${esc((d.autor || '?').charAt(0).toUpperCase())}</span>`;
    const nivelHtml = d.autor_nivel ? ` <span class="label-nivel">Nv.${d.autor_nivel}</span>` : '';
    const tituloHtml = d.autor_titulo
      ? `<div class="autor-titulo-row"><span class="label-titulo ${d.autor_titulo.rareza}" style="color:${d.autor_titulo.color};border-color:${d.autor_titulo.color}">${esc(d.autor_titulo.nombre)}</span></div>`
      : '';
    return `<div class="card-author">
        <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
          ${avaInner}<span class="author-name">${esc(d.autor)}</span>${nivelHtml}
        </div>
        ${tituloHtml}
      </div>`;
  },

  renderPagination(current, total) {
    const container = document.getElementById('debatesPagination');
    if (!container || total <= 1) { if (container) container.innerHTML = ''; return; }
    let html = `<button class="page-btn" onclick="Debates.goto(${current - 1})" ${current === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= total; i++) {
      if (i === 1 || i === total || Math.abs(i - current) <= 1) {
        html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="Debates.goto(${i})">${i}</button>`;
      } else if (Math.abs(i - current) === 2) {
        html += `<span style="color:var(--text-muted);padding:0 .25rem">…</span>`;
      }
    }
    html += `<button class="page-btn" onclick="Debates.goto(${current + 1})" ${current === total ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
    container.innerHTML = html;
  },

  goto(page) { this.currentPage = page; this.load(); window.scrollTo({ top: 0, behavior: 'smooth' }); },

  skeletons() {
    return Array(6).fill(0).map(() => `
      <div class="debate-card" style="pointer-events:none">
        <div class="skeleton" style="height:14px;width:30%;margin-bottom:12px"></div>
        <div class="skeleton" style="height:20px;margin-bottom:8px"></div>
        <div class="skeleton" style="height:14px;margin-bottom:4px"></div>
        <div class="skeleton" style="height:14px;width:80%"></div>
      </div>`).join('');
  },

  async crear() {
    const titulo = document.getElementById('debateTitulo').value.trim();
    const descripcion = document.getElementById('debateDescripcion').value.trim();
    const categoria_id = document.getElementById('debateCategoria').value;

    if (!titulo || !descripcion) { Toast.show('Completa la pregunta y la descripción', 'error'); return; }

    const res = await API.post('php/debates.php', { accion: 'crear', titulo, descripcion, categoria_id });
    if (res.success) {
      Toast.show(res.message, 'success');
      Modal.close('modalNuevoDebate');
      setTimeout(() => { location.href = `debate.php?id=${res.id}`; }, 600);
    } else {
      Toast.show(res.message, 'error');
    }
  }
};

// ── DETALLE ───────────────────────────────────────────────
const DebateDetail = {
  id: null,
  usuarioId: null,
  esAdmin: false,
  orden: 'relevantes',
  replyTo: null, // { parentId, citaId, autor }

  async init(id, usuarioId, esAdmin) {
    this.id = id;
    this.usuarioId = usuarioId;
    this.esAdmin = !!esAdmin;
    await this.cargarDebate();
    await this.cargarRespuestas();
  },

  async cargarDebate() {
    const res = await API.get('php/debates.php', { accion: 'detalle', id: this.id });
    const header = document.getElementById('debateHeader');
    if (!res.success) {
      header.innerHTML = `<p style="color:var(--text-muted)">Este debate no existe o fue removido.</p>`;
      return;
    }
    this.debate = res.debate;
    const d = res.debate;
    const cerrado = d.estado === 'cerrado';

    header.innerHTML = `
      <div class="debate-header-card">
        <div class="debate-card-top">
          <span class="debate-cat-badge" style="--cat-color:${d.categoria_color}">
            <i class="${d.categoria_icono}"></i> ${esc(d.categoria)}
          </span>
          <span class="debate-estado-badge ${cerrado ? 'cerrado' : 'activo'}">
            <i class="fas ${cerrado ? 'fa-lock' : 'fa-circle'}"></i> ${cerrado ? 'Cerrado' : 'Activo'}
          </span>
        </div>
        <h1 class="debate-header-title">${esc(d.titulo)}</h1>
        <p class="debate-header-desc">${esc(d.descripcion)}</p>
        <div class="debate-header-meta">
          ${Debates.authorHtml(d)}
          <span><i class="fas fa-calendar"></i> ${esc(d.fecha_formateada)}</span>
          <span><i class="fas fa-users"></i> ${d.participantes} participantes</span>
          <span><i class="fas fa-reply"></i> ${d.respuestas_count} respuestas</span>
        </div>
        ${this.esAdmin ? `
        <div style="margin-top:1rem">
          <button class="btn btn-sm btn-ghost" onclick="DebateDetail.toggleCerrar()">
            <i class="fas ${cerrado ? 'fa-lock-open' : 'fa-lock'}"></i> ${cerrado ? 'Reabrir debate' : 'Cerrar debate'}
          </button>
        </div>` : ''}
      </div>`;

    document.getElementById('respuestasSection').style.display = 'block';

    if (d.respuestas_count >= 5) this.cargarResumen();
  },

  async cargarResumen() {
    const box = document.getElementById('resumenIABox');
    box.style.display = 'block';
    box.innerHTML = `
      <div class="resumen-ia-header"><i class="fas fa-sparkles"></i> Resumen del debate (IA)</div>
      <button class="btn btn-sm btn-outline" onclick="DebateDetail.generarResumen()">
        <i class="fas fa-wand-magic-sparkles"></i> Generar resumen con IA
      </button>`;
  },

  async generarResumen() {
    const box = document.getElementById('resumenIABox');
    box.innerHTML = `<div class="resumen-ia-header"><i class="fas fa-sparkles"></i> Generando resumen...</div>
      <div class="skeleton" style="height:60px;border-radius:12px"></div>`;

    const res = await API.get('php/debates.php', { accion: 'resumen_ia', debate_id: this.id });
    if (!res.success) {
      box.innerHTML = `<div class="resumen-ia-header"><i class="fas fa-sparkles"></i> Resumen del debate (IA)</div>
        <p style="color:var(--text-muted);font-size:.85rem">${esc(res.message)}</p>`;
      return;
    }
    box.innerHTML = `
      <div class="resumen-ia-header"><i class="fas fa-sparkles"></i> Resumen del debate (IA)</div>
      <p class="resumen-ia-text">${esc(res.resumen)}</p>`;
  },

  async toggleCerrar() {
    const res = await API.post('php/debates.php', { accion: 'cerrar', id: this.id });
    if (res.success) { Toast.show(res.message, 'success'); this.cargarDebate(); }
    else Toast.show(res.message, 'error');
  },

  cambiarOrden(orden, btn) {
    document.querySelectorAll('.orden-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.orden = orden;
    this.cargarRespuestas();
  },

  async cargarRespuestas() {
    const list = document.getElementById('respuestasList');
    list.innerHTML = `<div class="skeleton" style="height:80px;border-radius:14px;margin-bottom:1rem"></div>`;

    const res = await API.get('php/debates.php', { accion: 'respuestas', debate_id: this.id, orden: this.orden });
    if (!res.success || !res.respuestas.length) {
      list.innerHTML = `<div class="empty-state"><p>Aún no hay respuestas. ¡Comparte tu opinión primero!</p></div>`;
      document.getElementById('respuestasCount').textContent = '0';
      return;
    }

    document.getElementById('respuestasCount').textContent = res.total;
    list.innerHTML = res.respuestas.map(r => this.renderRespuesta(r)).join('');
  },

  renderRespuesta(r, nivel = 0) {
    const citaHtml = r.cita ? `
      <div class="respuesta-cita">
        <i class="fas fa-quote-left"></i> <strong>${esc(r.cita.autor)}:</strong> ${esc(r.cita.contenido)}
      </div>` : '';

    const hijosHtml = (r.respuestas || []).map(h => this.renderRespuesta(h, nivel + 1)).join('');

    return `
      <div class="respuesta-item ${r.destacada ? 'destacada' : ''}" style="margin-left:${Math.min(nivel, 3) * 28}px" data-id="${r.id}">
        ${r.destacada ? '<div class="respuesta-destacada-tag"><i class="fas fa-star"></i> Destacada por moderación</div>' : ''}
        <div style="display:flex;gap:.85rem">
          <div class="comment-avatar" style="flex-shrink:0">${r.avatar ? `<img src="${esc(r.avatar)}">` : esc((r.autor || 'A').charAt(0))}</div>
          <div style="flex:1;min-width:0">
            <div class="respuesta-autor-row">
              <span class="respuesta-autor">${esc(r.autor)}</span>
              ${r.autor_titulo ? `<span class="respuesta-titulo" style="color:${r.autor_titulo.color}">${esc(r.autor_titulo.nombre)}</span>` : ''}
              <span class="respuesta-fecha">${timeAgoDebate(r.fecha_creacion)}</span>
            </div>
            ${citaHtml}
            <p class="respuesta-contenido">${esc(r.contenido)}</p>
            <div class="respuesta-acciones">
              <button class="resp-action ${r.votada ? 'active' : ''}" onclick="DebateDetail.votar(${r.id}, this)">
                <i class="fas fa-arrow-up"></i> <span class="resp-votos">${r.votos}</span>
              </button>
              <button class="resp-action" onclick="DebateDetail.responderA(${r.id}, null, '${esc(r.autor).replace(/'/g, "\\'")}')">
                <i class="fas fa-reply"></i> Responder
              </button>
              <button class="resp-action" onclick="DebateDetail.citar(${r.id}, '${esc(r.autor).replace(/'/g, "\\'")}')">
                <i class="fas fa-quote-right"></i> Citar
              </button>
              ${this.esAdmin ? `
              <button class="resp-action" onclick="DebateDetail.destacar(${r.id})">
                <i class="fas fa-star"></i> ${r.destacada ? 'Quitar destacado' : 'Destacar'}
              </button>` : ''}
            </div>
          </div>
        </div>
      </div>
      ${hijosHtml}`;
  },

  responderA(id, citaId, autor) {
    this.replyTo = { parentId: id, citaId: null, autor };
    document.getElementById('replyingToLabel').innerHTML = `Respondiendo a <strong>${esc(autor)}</strong> <a href="#" onclick="DebateDetail.cancelarReply();return false;">(cancelar)</a>`;
    document.getElementById('citaPreview').style.display = 'none';
    document.getElementById('respuestaText').focus();
  },

  citar(id, autor) {
    const item = document.querySelector(`.respuesta-item[data-id="${id}"] .respuesta-contenido`);
    const texto = item ? item.textContent : '';
    this.replyTo = { parentId: null, citaId: id, autor };
    const preview = document.getElementById('citaPreview');
    preview.style.display = 'block';
    preview.innerHTML = `<i class="fas fa-quote-left"></i> Citando a <strong>${esc(autor)}</strong>: “${esc(texto.slice(0, 140))}” <a href="#" onclick="DebateDetail.cancelarReply();return false;">✕</a>`;
    document.getElementById('replyingToLabel').textContent = '';
    document.getElementById('respuestaText').focus();
  },

  cancelarReply() {
    this.replyTo = null;
    document.getElementById('replyingToLabel').textContent = '';
    document.getElementById('citaPreview').style.display = 'none';
  },

  async enviarRespuesta(debateId) {
    const contenido = document.getElementById('respuestaText').value.trim();
    if (!contenido) { Toast.show('Escribe algo antes de publicar', 'error'); return; }

    const payload = { accion: 'responder', debate_id: debateId, contenido };
    if (this.replyTo?.parentId) payload.parent_id = this.replyTo.parentId;
    if (this.replyTo?.citaId) payload.cita_id = this.replyTo.citaId;

    const res = await API.post('php/debates.php', payload);
    if (res.success) {
      Toast.show('¡Respuesta publicada!', 'success');
      document.getElementById('respuestaText').value = '';
      this.cancelarReply();
      this.cargarRespuestas();
      this.cargarDebate();
    } else {
      Toast.show(res.message, 'error');
    }
  },

  async votar(id, btn) {
    if (!this.usuarioId) { Toast.show('Inicia sesión para votar', 'error'); return; }
    const res = await API.post('php/debates.php', { accion: 'votar_respuesta', respuesta_id: id });
    if (res.success) {
      btn.classList.toggle('active', res.votado);
      btn.querySelector('.resp-votos').textContent = res.votos;
    } else {
      Toast.show(res.message, 'error');
    }
  },

  async destacar(id) {
    const res = await API.post('php/debates.php', { accion: 'destacar', respuesta_id: id });
    if (res.success) { Toast.show(res.message, 'success'); this.cargarRespuestas(); }
    else Toast.show(res.message, 'error');
  }
};
