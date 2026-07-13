/* ============================================================
   CIVINSIS · Módulo de Ranking
   Depende de API (app.js). Usa esc() si está disponible
   (debates.js/desafios.js), si no define un respaldo propio.
   ============================================================ */

if (typeof esc !== 'function') {
  window.esc = function (str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  };
}

const RANKING_AUTOREFRESH_MS = 30000;

const Ranking = {
  usuarioId: null,
  categorias: [],
  actual: 'xp',
  timer: null,

  async init(usuarioId) {
    this.usuarioId = usuarioId;

    const res = await API.get('php/ranking.php', { accion: 'categorias' });
    if (res.success) this.categorias = res.categorias;
    this.renderTabs();

    await this.cargar(this.actual);

    // "En tiempo real": refresco periódico silencioso mientras la pestaña esté abierta
    this.timer = setInterval(() => this.cargar(this.actual, true), RANKING_AUTOREFRESH_MS);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) clearInterval(this.timer);
      else this.timer = setInterval(() => this.cargar(this.actual, true), RANKING_AUTOREFRESH_MS);
    });
  },

  renderTabs() {
    const wrap = document.getElementById('rankingTabs');
    if (!wrap) return;
    wrap.innerHTML = this.categorias.map(c => `
      <button class="ranking-tab ${c.clave === this.actual ? 'active' : ''}" data-cat="${c.clave}" onclick="Ranking.cambiar('${c.clave}')">
        <i class="${c.icono}"></i> ${esc(c.label)}
      </button>`).join('');
  },

  cambiar(clave) {
    this.actual = clave;
    document.querySelectorAll('.ranking-tab').forEach(b => b.classList.toggle('active', b.dataset.cat === clave));
    this.cargar(clave);
  },

  async cargar(categoria, silencioso = false) {
    const list = document.getElementById('rankingList');
    if (!silencioso && list) list.innerHTML = this.skeletons();

    const res = await API.get('php/ranking.php', { accion: 'listar', categoria, limit: 20 });
    if (!res.success) {
      if (list) list.innerHTML = `<div class="empty-state"><p>No se pudo cargar el ranking.</p></div>`;
      return;
    }

    const header = document.getElementById('rankingPanelHeader');
    if (header) {
      header.innerHTML = `<i class="${res.meta.icono}"></i> <span>${esc(res.meta.label)}</span>`;
    }

    if (!res.ranking.length) {
      if (list) list.innerHTML = `<div class="empty-state"><p>Todavía no hay datos suficientes en esta categoría.</p></div>`;
      document.getElementById('rankingMiPosicion').innerHTML = '';
      return;
    }

    const esPropuestas = categoria === 'propuestas';
    if (list) list.innerHTML = res.ranking.map((r, i) => esPropuestas ? this.filaPropuesta(r, i) : this.filaUsuario(r, i, res.meta.unidad)).join('');

    const miBox = document.getElementById('rankingMiPosicion');
    if (miBox) {
      miBox.innerHTML = (res.mi_posicion && res.mi_posicion > 20)
        ? `<div class="ranking-mi-posicion"><i class="fas fa-user"></i> Tu posición en esta categoría: <strong>#${res.mi_posicion}</strong></div>`
        : '';
    }
  },

  medalla(i) {
    return i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : (i + 1);
  },

  filaUsuario(u, i, unidad) {
    const soyYo = this.usuarioId && u.id === this.usuarioId;
    const avaInner = (u.avatar && u.avatar.indexOf('data:') === 0)
      ? `<img class="ranking-avatar-img" src="${u.avatar}" alt="">`
      : `<span class="ranking-avatar-img">${esc((u.nombre || '?').charAt(0).toUpperCase())}</span>`;
    return `
      <div class="ranking-row ${i < 3 ? 'top3' : ''} ${soyYo ? 'soy-yo' : ''}">
        <div class="ranking-pos-badge pos-${i}">${this.medalla(i)}</div>
        <div class="ranking-avatar-wrap">${avaInner}</div>
        <div class="ranking-user-info">
          <div class="ranking-user-name">${esc(u.nombre)} ${soyYo ? '<span class="ranking-tu-tag">(tú)</span>' : ''}</div>
          <div class="ranking-user-meta">
            Nv. ${u.nivel}
            ${u.titulo ? `<span class="label-titulo ${u.titulo.rareza}" style="color:${u.titulo.color};border-color:${u.titulo.color}">${esc(u.titulo.nombre)}</span>` : ''}
          </div>
        </div>
        <div class="ranking-valor">${(u.valor || 0).toLocaleString('es')} <span class="ranking-unidad">${esc(unidad)}</span></div>
      </div>`;
  },

  filaPropuesta(p, i) {
    return `
      <a href="${p.enlace}" class="ranking-row top-propuesta ${i < 3 ? 'top3' : ''}">
        <div class="ranking-pos-badge pos-${i}">${this.medalla(i)}</div>
        <div class="ranking-user-info">
          <div class="ranking-user-name">${esc(p.titulo)}</div>
          <div class="ranking-user-meta">
            <span class="debate-cat-badge" style="--cat-color:${p.categoria_color}"><i class="${p.categoria_icono}"></i> ${esc(p.categoria)}</span>
            <span style="margin-left:.5rem">por ${esc(p.autor)}</span>
          </div>
        </div>
        <div class="ranking-valor"><i class="fas fa-heart" style="color:#e74c3c;font-size:.8rem;margin-right:.25rem"></i>${(p.valor || 0).toLocaleString('es')}</div>
      </a>`;
  },

  skeletons() {
    return Array(6).fill(0).map(() => `
      <div class="ranking-row" style="pointer-events:none">
        <div class="skeleton" style="width:32px;height:32px;border-radius:8px"></div>
        <div class="skeleton" style="width:40px;height:40px;border-radius:50%"></div>
        <div style="flex:1">
          <div class="skeleton" style="height:14px;width:50%;margin-bottom:6px"></div>
          <div class="skeleton" style="height:10px;width:30%"></div>
        </div>
      </div>`).join('');
  }
};
