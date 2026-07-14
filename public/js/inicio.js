/* ============================================================
   CIVINSIS · Centro de Actividad (panel personalizado)
   Depende de API (app.js). Define esc() de respaldo si falta.
   ============================================================ */

if (typeof esc !== 'function') {
  window.esc = function (str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  };
}

const DIF_LABEL_INI = { facil: 'Fácil', medio: 'Medio', dificil: 'Difícil' };
const PROGRESO_LABEL_INI = {
  idea: 'Idea', discusion: 'Discusión', mejoras: 'Mejoras', votacion: 'Votación', destacada: 'Destacada'
};

const CentroActividad = {
  async init() {
    const cont = document.getElementById('actividadPanel');
    if (!cont) return;

    const res = await API.get('php/actividad.php', { accion: 'panel' });
    if (!res.success) {
      cont.innerHTML = `<div class="empty-state"><p>No se pudo cargar tu panel. Intenta recargar.</p></div>`;
      return;
    }
    cont.innerHTML = this.render(res);
  },

  render(d) {
    return `
      ${this.heroHTML(d.saludo, d.stats)}
      <div class="inicio-grid">
        <div class="inicio-col">
          ${this.misionHTML(d.mision_activa)}
          ${this.desafioHTML(d.desafio_recomendado)}
          ${this.logroHTML(d.ultimo_logro)}
          ${this.respuestasHTML(d.respuestas_recibidas)}
        </div>
        <div class="inicio-col">
          ${this.propuestasHTML(d.propuestas_recomendadas)}
          ${this.debatesHTML(d.debates_recomendados)}
          ${this.actividadHTML(d.actividad_reciente)}
        </div>
      </div>`;
  },

  heroHTML(saludo, s) {
    return `
      <div class="inicio-hero">
        <div class="inicio-hero-top">
          <div>
            <h1 class="inicio-hero-saludo">${esc(saludo.texto)}, <span>${esc(saludo.nombre)}</span> 👋</h1>
            <p class="inicio-hero-sub">Esto es lo que está pasando en tu comunidad hoy.</p>
          </div>
          <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva propuesta</a>
        </div>
        <div class="inicio-stats">
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(54,192,161,.14);color:var(--verde-500)"><i class="fas fa-star"></i></div>
            <div>
              <div class="inicio-stat-num">Nivel ${s.nivel}</div>
              <div class="inicio-stat-label">${s.xp} XP</div>
              <div class="inicio-nivel-bar"><span style="width:${s.porcentaje_nivel}%"></span></div>
            </div>
          </div>
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(155,89,182,.14);color:#9b59b6"><i class="fas fa-award"></i></div>
            <div>
              <div class="inicio-stat-num">${s.reputacion}</div>
              <div class="inicio-stat-label">Reputación</div>
            </div>
          </div>
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(239,126,34,.14);color:var(--naranja-500)"><i class="fas fa-fire"></i></div>
            <div>
              <div class="inicio-stat-num">${s.racha_dias} ${s.racha_dias === 1 ? 'día' : 'días'}</div>
              <div class="inicio-stat-label">Racha activa</div>
            </div>
          </div>
        </div>
      </div>`;
  },

  card(titulo, icono, contenido, enlaceVerTodo) {
    return `
      <div class="inicio-card">
        <div class="inicio-card-header">
          <h3><i class="${icono}"></i> ${titulo}</h3>
          ${enlaceVerTodo ? `<a href="${enlaceVerTodo}" class="inicio-ver-todo">Ver todo <i class="fas fa-arrow-right"></i></a>` : ''}
        </div>
        ${contenido}
      </div>`;
  },

  misionHTML(m) {
    if (!m) {
      return this.card('Misión activa', 'fas fa-bullseye',
        `<p class="inicio-vacio">¡Completaste todas tus misiones! 🎉</p>`);
    }
    const pct = m.cantidad ? Math.min(100, Math.round((m.progreso / m.cantidad) * 100)) : 0;
    return this.card('Misión activa', 'fas fa-bullseye', `
      <div class="inicio-mision">
        <div class="inicio-mision-top">
          <span class="inicio-mision-nombre">${esc(m.nombre)}</span>
          <span class="inicio-mision-xp">+${m.xp} XP</span>
        </div>
        <p class="inicio-mision-desc">${esc(m.descripcion)}</p>
        <div class="inicio-nivel-bar"><span style="width:${pct}%"></span></div>
        <div class="inicio-mision-prog">${m.progreso} / ${m.cantidad}</div>
      </div>`);
  },

  desafioHTML(d) {
    if (!d) return '';
    return this.card('Desafío recomendado', 'fas fa-flag-checkered', `
      <div class="inicio-desafio" onclick="Desafios&&Desafios.aceptar?Desafios.aceptar(${d.id}):location.href='desafios.php'">
        <div class="inicio-desafio-icon"><i class="${d.icono}"></i></div>
        <div class="inicio-desafio-body">
          <div class="inicio-desafio-titulo">${esc(d.titulo)}</div>
          <div class="inicio-desafio-meta">
            <span class="inicio-dif dif-${d.dificultad}">${DIF_LABEL_INI[d.dificultad] || d.dificultad}</span>
            <span><i class="fas fa-bolt"></i> ${d.xp} XP</span>
          </div>
        </div>
        <a href="crear.php?desafio_id=${d.id}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">Aceptar</a>
      </div>`, 'desafios.php');
  },

  logroHTML(l) {
    if (!l) return '';
    return this.card('Último logro', 'fas fa-trophy', `
      <div class="inicio-logro">
        <div class="inicio-logro-icon" style="color:${l.color};background:${l.color}22">${l.icono || '<i class="fas fa-medal"></i>'}</div>
        <div>
          <div class="inicio-logro-nombre">${esc(l.nombre)}</div>
          <div class="inicio-logro-desc">${esc(l.descripcion)}</div>
        </div>
      </div>`, 'perfil.php');
  },

  respuestasHTML(lista) {
    if (!lista || !lista.length) {
      return this.card('Respuestas recibidas', 'fas fa-reply',
        `<p class="inicio-vacio">Aún no has recibido comentarios en tus propuestas.</p>`);
    }
    const items = lista.map(r => `
      <a href="${r.propuesta_id ? 'propuesta.php?id=' + r.propuesta_id : '#'}" class="inicio-respuesta">
        <div class="inicio-resp-avatar">${r.avatar && r.avatar.indexOf('data:') === 0 ? `<img src="${r.avatar}">` : esc((r.autor || '?').charAt(0))}</div>
        <div class="inicio-resp-body">
          <div class="inicio-resp-autor">${esc(r.autor)} <span>· ${esc(r.fecha)}</span></div>
          <div class="inicio-resp-texto">${esc(r.texto)}</div>
        </div>
      </a>`).join('');
    return this.card('Respuestas recibidas', 'fas fa-reply', items);
  },

  propuestasHTML(lista) {
    if (!lista || !lista.length) {
      return this.card('Propuestas para ti', 'fas fa-layer-group',
        `<p class="inicio-vacio">No hay propuestas nuevas por ahora.</p>`, 'dashboard.php');
    }
    const items = lista.map(p => `
      <a href="propuesta.php?id=${p.id}" class="inicio-lista-item">
        <span class="inicio-cat-dot" style="background:${p.categoria_color}"><i class="${p.categoria_icono}"></i></span>
        <div class="inicio-lista-body">
          <div class="inicio-lista-titulo">${esc(p.titulo)}</div>
          <div class="inicio-lista-meta">
            <span class="inicio-fase-badge fase-${p.progreso}">${PROGRESO_LABEL_INI[p.progreso] || p.progreso}</span>
            <span><i class="fas fa-hand-sparkles"></i> ${p.votos}</span>
          </div>
        </div>
      </a>`).join('');
    return this.card('Propuestas para ti', 'fas fa-layer-group', items, 'dashboard.php');
  },

  debatesHTML(lista) {
    if (!lista || !lista.length) {
      return this.card('Debates recomendados', 'fas fa-comments',
        `<p class="inicio-vacio">No hay debates activos por ahora.</p>`, 'debates.php');
    }
    const items = lista.map(d => `
      <a href="debate.php?id=${d.id}" class="inicio-lista-item">
        <span class="inicio-cat-dot" style="background:${d.categoria_color}"><i class="${d.categoria_icono}"></i></span>
        <div class="inicio-lista-body">
          <div class="inicio-lista-titulo">${esc(d.titulo)}</div>
          <div class="inicio-lista-meta"><span><i class="fas fa-reply"></i> ${d.respuestas} respuestas</span></div>
        </div>
      </a>`).join('');
    return this.card('Debates recomendados', 'fas fa-comments', items, 'debates.php');
  },

  actividadHTML(lista) {
    if (!lista || !lista.length) {
      return this.card('Tu actividad reciente', 'fas fa-clock-rotate-left',
        `<p class="inicio-vacio">Todavía no tienes actividad. ¡Empieza creando una propuesta!</p>`);
    }
    const items = lista.map(a => `
      <a href="${a.enlace}" class="inicio-actividad-item">
        <span class="inicio-act-icon" style="color:${a.color};background:${a.color}22"><i class="${a.icono}"></i></span>
        <div class="inicio-act-body">
          <div class="inicio-act-texto">${esc(a.texto)}</div>
          <div class="inicio-act-fecha">${esc(a.fecha_humana)}</div>
        </div>
      </a>`).join('');
    return this.card('Tu actividad reciente', 'fas fa-clock-rotate-left', items);
  }
};
