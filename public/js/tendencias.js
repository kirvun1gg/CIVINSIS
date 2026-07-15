/* ============================================================
   CIVINSIS · Tendencias
   Depende de API (app.js). Define esc() de respaldo si falta.
   ============================================================ */

if (typeof esc !== 'function') {
  window.esc = function (str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  };
}

const PROG_LABEL_TEND = {
  idea: 'Idea', discusion: 'Discusión', mejoras: 'Mejoras', votacion: 'Votación', destacada: 'Destacada'
};

const Tendencias = {
  refreshMs: 45000,
  timer: null,

  async init() {
    await this.cargar();
    this.timer = setInterval(() => this.cargar(true), this.refreshMs);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) clearInterval(this.timer);
      else this.timer = setInterval(() => this.cargar(true), this.refreshMs);
    });
  },

  async cargar(silencioso = false) {
    const cont = document.getElementById('tendenciasPanel');
    if (!cont) return;

    const res = await API.get('php/tendencias.php', { accion: 'todo' });
    if (!res.success) {
      if (!silencioso) cont.innerHTML = `<div class="empty-state"><p>No se pudieron cargar las tendencias.</p></div>`;
      return;
    }
    cont.innerHTML = this.render(res);
  },

  render(d) {
    return `
      <div class="tend-grid">
        ${this.debatesHTML(d.debates_activos)}
        ${this.propuestasHTML(d.propuestas_creciendo)}
        ${this.usuariosHTML(d.usuarios_destacados)}
        ${this.comentariosHTML(d.comentarios_valorados)}
        ${this.desafiosHTML(d.desafios_completados)}
      </div>`;
  },

  card(icono, titulo, color, contenido, verTodo) {
    return `
      <div class="tend-card">
        <div class="tend-card-header">
          <h3><span class="tend-emoji">${icono}</span> ${titulo}</h3>
          ${verTodo ? `<a href="${verTodo}" class="inicio-ver-todo">Ver todo <i class="fas fa-arrow-right"></i></a>` : ''}
        </div>
        <div class="tend-card-body">${contenido}</div>
      </div>`;
  },

  rankNum(i) {
    const cls = i === 0 ? 'oro' : i === 1 ? 'plata' : i === 2 ? 'bronce' : '';
    return `<span class="tend-rank ${cls}">${i + 1}</span>`;
  },

  debatesHTML(lista) {
    if (!lista || !lista.length) return this.card('🔥', 'Debates más activos', '', `<p class="inicio-vacio">No hay debates activos aún.</p>`, 'debates.php');
    const items = lista.map((d, i) => `
      <a href="debate.php?id=${d.id}" class="tend-item">
        ${this.rankNum(i)}
        <div class="tend-item-body">
          <div class="tend-item-title">${esc(d.titulo)}</div>
          <div class="tend-item-meta">
            <span class="debate-cat-badge" style="--cat-color:${d.categoria_color}"><i class="${d.categoria_icono}"></i> ${esc(d.categoria)}</span>
            <span><i class="fas fa-reply"></i> ${d.respuestas}</span>
            ${d.recientes > 0 ? `<span class="tend-hot"><i class="fas fa-arrow-trend-up"></i> +${d.recientes} nuevas</span>` : ''}
          </div>
        </div>
      </a>`).join('');
    return this.card('🔥', 'Debates más activos', '', items, 'debates.php');
  },

  propuestasHTML(lista) {
    if (!lista || !lista.length) return this.card('📈', 'Propuestas en crecimiento', '', `<p class="inicio-vacio">Sin propuestas destacadas aún.</p>`, 'dashboard.php');
    const items = lista.map((p, i) => `
      <a href="propuesta.php?id=${p.id}" class="tend-item">
        ${this.rankNum(i)}
        <div class="tend-item-body">
          <div class="tend-item-title">${esc(p.titulo)}</div>
          <div class="tend-item-meta">
            <span class="inicio-fase-badge fase-${p.progreso}">${PROG_LABEL_TEND[p.progreso] || p.progreso}</span>
            <span><i class="fas fa-hand-sparkles"></i> ${p.votos}</span>
            ${p.recientes > 0 ? `<span class="tend-hot"><i class="fas fa-arrow-trend-up"></i> +${p.recientes}</span>` : ''}
          </div>
        </div>
      </a>`).join('');
    return this.card('📈', 'Propuestas en crecimiento', '', items, 'dashboard.php');
  },

  usuariosHTML(lista) {
    if (!lista || !lista.length) return this.card('⭐', 'Usuarios destacados', '', `<p class="inicio-vacio">Sin actividad destacada este mes.</p>`, 'ranking.php');
    const items = lista.map((u, i) => `
      <div class="tend-item">
        ${this.rankNum(i)}
        <div class="tend-user-avatar">${u.avatar && u.avatar.indexOf('data:') === 0 ? `<img src="${u.avatar}">` : esc((u.nombre || '?').charAt(0).toUpperCase())}</div>
        <div class="tend-item-body">
          <div class="tend-item-title">${esc(u.nombre)}
            ${u.titulo ? `<span class="label-titulo ${u.titulo.rareza}" style="color:${u.titulo.color};border-color:${u.titulo.color}">${esc(u.titulo.nombre)}</span>` : ''}
          </div>
          <div class="tend-item-meta">
            <span>Nv. ${u.nivel}</span>
            <span><i class="fas fa-award"></i> ${u.reputacion} rep.</span>
            <span><i class="fas fa-fire"></i> ${u.actividad} acciones</span>
          </div>
        </div>
      </div>`).join('');
    return this.card('⭐', 'Usuarios destacados', '', items, 'ranking.php');
  },

  comentariosHTML(lista) {
    if (!lista || !lista.length) return this.card('💬', 'Comentarios más valorados', '', `<p class="inicio-vacio">Aún no hay respuestas valoradas.</p>`);
    const items = lista.map(c => `
      <a href="debate.php?id=${c.debate_id}" class="tend-coment">
        <div class="tend-coment-top">
          <div class="tend-user-avatar sm">${c.avatar && c.avatar.indexOf('data:') === 0 ? `<img src="${c.avatar}">` : esc((c.autor || '?').charAt(0))}</div>
          <span class="tend-coment-autor">${esc(c.autor)}</span>
          <span class="tend-coment-votos"><i class="fas fa-arrow-up"></i> ${c.votos}</span>
        </div>
        <div class="tend-coment-texto">"${esc(c.texto)}"</div>
        ${c.debate ? `<div class="tend-coment-debate"><i class="fas fa-comments"></i> ${esc(c.debate)}</div>` : ''}
      </a>`).join('');
    return this.card('💬', 'Comentarios más valorados', '', items);
  },

  desafiosHTML(lista) {
    if (!lista || !lista.length) return this.card('🎯', 'Desafíos más completados', '', `<p class="inicio-vacio">Nadie ha completado desafíos aún.</p>`, 'desafios.php');
    const items = lista.map((d, i) => `
      <a href="desafios.php" class="tend-item">
        ${this.rankNum(i)}
        <div class="tend-desafio-icon"><i class="${d.icono}"></i></div>
        <div class="tend-item-body">
          <div class="tend-item-title">${esc(d.titulo)}</div>
          <div class="tend-item-meta"><span><i class="fas fa-check-circle"></i> ${d.total} ${d.total === 1 ? 'persona' : 'personas'}</span></div>
        </div>
      </a>`).join('');
    return this.card('🎯', 'Desafíos más completados', '', items, 'desafios.php');
  }
};
