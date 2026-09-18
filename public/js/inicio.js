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

const DIF_LABEL_INI = {
  get facil()   { return CIVI_I18N.desafios.dificultad_facil; },
  get medio()   { return CIVI_I18N.desafios.dificultad_medio; },
  get dificil() { return CIVI_I18N.desafios.dificultad_dificil; },
};
const PROGRESO_LABEL_INI = {
  get idea()      { return CIVI_I18N.progreso.idea_label; },
  get discusion() { return CIVI_I18N.progreso.discusion_label; },
  get mejoras()   { return CIVI_I18N.progreso.mejoras_label; },
  get votacion()  { return CIVI_I18N.progreso.votacion_label; },
  get destacada() { return CIVI_I18N.progreso.destacada_label; },
};

const CentroActividad = {
  async init() {
    const cont = document.getElementById('actividadPanel');
    if (!cont) return;

    const res = await API.get('php/actividad.php', { accion: 'panel' });
    if (!res.success) {
      cont.innerHTML = `<div class="empty-state"><p>${esc(CIVI_I18N.inicio.error_carga)}</p></div>`;
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

  /** El servidor no conoce la zona horaria real del visitante (corre en
   * UTC), así que el saludo se calcula con la hora LOCAL del navegador
   * en vez de usar saludo.texto (que venía calculado en el servidor). */
  saludoLocal() {
    const h = new Date().getHours();
    if (h < 12) return CIVI_I18N.saludo_manana;
    if (h < 19) return CIVI_I18N.saludo_tarde;
    return CIVI_I18N.saludo_noche;
  },

  heroHTML(saludo, s) {
    return `
      <div class="inicio-hero">
        <div class="inicio-hero-top">
          <div>
            <h1 class="inicio-hero-saludo">${esc(this.saludoLocal())}, <span>${esc(saludo.nombre)}</span> 👋</h1>
            <p class="inicio-hero-sub">${esc(CIVI_I18N.inicio.hero_subtitulo)}</p>
          </div>
          <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus"></i> ${esc(CIVI_I18N.nav.nueva_propuesta)}</a>
        </div>
        <div class="inicio-stats">
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(54,192,161,.14);color:var(--verde-500)"><i class="fas fa-star"></i></div>
            <div>
              <div class="inicio-stat-num">${esc(CIVI_I18N.inicio.nivel_label)} ${s.nivel}</div>
              <div class="inicio-stat-label">${s.xp} XP</div>
              <div class="inicio-nivel-bar"><span style="width:${s.porcentaje_nivel}%"></span></div>
            </div>
          </div>
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(155,89,182,.14);color:#9b59b6"><i class="fas fa-award"></i></div>
            <div>
              <div class="inicio-stat-num">${s.reputacion}</div>
              <div class="inicio-stat-label">${esc(CIVI_I18N.inicio.reputacion)}</div>
            </div>
          </div>
          <div class="inicio-stat">
            <div class="inicio-stat-icon" style="background:rgba(239,126,34,.14);color:var(--naranja-500)"><i class="fas fa-fire"></i></div>
            <div>
              <div class="inicio-stat-num">${s.racha_dias} ${esc(s.racha_dias === 1 ? CIVI_I18N.inicio.dia_singular : CIVI_I18N.inicio.dia_plural)}</div>
              <div class="inicio-stat-label">${esc(CIVI_I18N.inicio.racha_activa)}</div>
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
          ${enlaceVerTodo ? `<a href="${enlaceVerTodo}" class="inicio-ver-todo">${esc(CIVI_I18N.inicio.ver_todo)} <i class="fas fa-arrow-right"></i></a>` : ''}
        </div>
        ${contenido}
      </div>`;
  },

  misionHTML(m) {
    if (!m) {
      return this.card(CIVI_I18N.inicio.mision_activa, 'fas fa-bullseye',
        `<p class="inicio-vacio">${esc(CIVI_I18N.inicio.mision_vacia)}</p>`);
    }
    const pct = m.cantidad ? Math.min(100, Math.round((m.progreso / m.cantidad) * 100)) : 0;
    return this.card(CIVI_I18N.inicio.mision_activa, 'fas fa-bullseye', `
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
    return this.card(CIVI_I18N.inicio.desafio_recomendado, 'fas fa-flag-checkered', `
      <div class="inicio-desafio" onclick="Desafios&&Desafios.aceptar?Desafios.aceptar(${d.id}):location.href='desafios.php'">
        <div class="inicio-desafio-icon"><i class="${d.icono}"></i></div>
        <div class="inicio-desafio-body">
          <div class="inicio-desafio-titulo">${esc(d.titulo)}</div>
          <div class="inicio-desafio-meta">
            <span class="inicio-dif dif-${d.dificultad}">${DIF_LABEL_INI[d.dificultad] || d.dificultad}</span>
            <span><i class="fas fa-bolt"></i> ${d.xp} XP</span>
          </div>
        </div>
        <a href="crear.php?desafio_id=${d.id}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">${esc(CIVI_I18N.desafios.aceptar)}</a>
      </div>`, 'desafios.php');
  },

  logroHTML(l) {
    if (!l) return '';
    return this.card(CIVI_I18N.inicio.ultimo_logro, 'fas fa-trophy', `
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
      return this.card(CIVI_I18N.inicio.respuestas_recibidas, 'fas fa-reply',
        `<p class="inicio-vacio">${esc(CIVI_I18N.inicio.respuestas_vacio)}</p>`);
    }
    const items = lista.map(r => `
      <a href="${r.propuesta_id ? 'propuesta.php?id=' + r.propuesta_id : '#'}" class="inicio-respuesta">
        <div class="inicio-resp-avatar">${r.avatar && r.avatar.indexOf('data:') === 0 ? `<img src="${r.avatar}">` : esc((r.autor || '?').charAt(0))}</div>
        <div class="inicio-resp-body">
          <div class="inicio-resp-autor">${esc(r.autor)} <span>· ${esc(r.fecha)}</span></div>
          <div class="inicio-resp-texto">${esc(r.texto)}</div>
        </div>
      </a>`).join('');
    return this.card(CIVI_I18N.inicio.respuestas_recibidas, 'fas fa-reply', items);
  },

  propuestasHTML(lista) {
    if (!lista || !lista.length) {
      return this.card(CIVI_I18N.inicio.propuestas_para_ti, 'fas fa-layer-group',
        `<p class="inicio-vacio">${esc(CIVI_I18N.inicio.propuestas_vacio)}</p>`, 'dashboard.php');
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
    return this.card(CIVI_I18N.inicio.propuestas_para_ti, 'fas fa-layer-group', items, 'dashboard.php');
  },

  debatesHTML(lista) {
    if (!lista || !lista.length) {
      return this.card(CIVI_I18N.inicio.debates_recomendados, 'fas fa-comments',
        `<p class="inicio-vacio">${esc(CIVI_I18N.inicio.debates_vacio)}</p>`, 'debates.php');
    }
    const items = lista.map(d => `
      <a href="debate.php?id=${d.id}" class="inicio-lista-item">
        <span class="inicio-cat-dot" style="background:${d.categoria_color}"><i class="${d.categoria_icono}"></i></span>
        <div class="inicio-lista-body">
          <div class="inicio-lista-titulo">${esc(d.titulo)}</div>
          <div class="inicio-lista-meta"><span><i class="fas fa-reply"></i> ${d.respuestas} ${esc(CIVI_I18N.inicio.respuestas_sufijo)}</span></div>
        </div>
      </a>`).join('');
    return this.card(CIVI_I18N.inicio.debates_recomendados, 'fas fa-comments', items, 'debates.php');
  },

  actividadHTML(lista) {
    if (!lista || !lista.length) {
      return this.card(CIVI_I18N.inicio.actividad_reciente, 'fas fa-clock-rotate-left',
        `<p class="inicio-vacio">${esc(CIVI_I18N.inicio.actividad_vacio)}</p>`);
    }
    const items = lista.map(a => `
      <a href="${a.enlace}" class="inicio-actividad-item">
        <span class="inicio-act-icon" style="color:${a.color};background:${a.color}22"><i class="${a.icono}"></i></span>
        <div class="inicio-act-body">
          <div class="inicio-act-texto">${esc(a.texto)}</div>
          <div class="inicio-act-fecha">${esc(a.fecha_humana)}</div>
        </div>
      </a>`).join('');
    return this.card(CIVI_I18N.inicio.actividad_reciente, 'fas fa-clock-rotate-left', items);
  }
};

CentroActividad.init();
