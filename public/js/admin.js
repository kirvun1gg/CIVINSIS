// ── i18n de toasts (evaluado en cada llamada, no al cargar el script) ──
function tAdmin() { return (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.admin) || {}; }
function tComunAdmin() { return (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.comunes) || {}; }
// ── i18n del chrome del panel (títulos, tablas, badges) — distinto de tAdmin(),
// que es solo para los mensajes toast. Ver civinsis.admin/civinsis.comun. ──
function tA() { return (window.CIVI_I18N && CIVI_I18N.admin) || {}; }
function tC() { return (window.CIVI_I18N && CIVI_I18N.comun) || {}; }

// Tabs admin
document.querySelectorAll('[data-admin-tab]').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('[data-admin-tab]').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    tab.classList.add('active');
    const sec = document.getElementById('admin-tab-' + tab.dataset.adminTab);
    if (sec) sec.classList.add('active');
    if (tab.dataset.adminTab === 'propuestas') loadAdminPropuestas();
    else if (tab.dataset.adminTab === 'comentarios') loadAdminComentarios();
    else if (tab.dataset.adminTab === 'usuarios') loadAdminUsuarios();
    else if (tab.dataset.adminTab === 'estadisticas') loadAdminStats();
    else if (tab.dataset.adminTab === 'gamificacion') gamInit();
  });
});

// ── Limpieza de spam ─────────────────────────────────────
async function spamAbrir() {
  const box = document.getElementById('spamBody');
  box.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0">' + escHtml(tA().spam_analizando || 'Analizando comentarios…') + '</p>';
  document.getElementById('spamModal').classList.add('open');
  try {
    const r = await fetch('php/admin.php?accion=spam_listar');
    const d = await r.json();
    if (!d.success || !(d.items || []).length) {
      box.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0"><i class="fas fa-check" style="color:#22c55e"></i> ' + escHtml(tA().spam_limpio || 'No se detectó spam. Todo limpio.') + '</p>';
      return;
    }
    box.innerHTML = `
      <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:.8rem">
        ${escHtml((tA().spam_detectados || 'Se detectaron {n} comentarios sospechosos. Revísalos antes de eliminar.').replace('{n}', d.total))}
      </p>` +
      d.items.map((it, i) => `
        <label class="spam-item">
          <input type="checkbox" class="spam-check" data-ids="${it.ids.join(',')}">
          <div>
            <div class="spam-motivo"><i class="fas fa-triangle-exclamation"></i> ${escHtml(it.motivo)} · ${escHtml(it.usuario)}</div>
            <div class="spam-extracto">${escHtml(it.extracto)}</div>
          </div>
        </label>`).join('');
  } catch (e) {
    box.innerHTML = '<p style="color:#e74c3c;padding:1rem 0">' + escHtml(tA().spam_error_analizar || 'Error al analizar.') + '</p>';
  }
}
function spamCerrar() { document.getElementById('spamModal').classList.remove('open'); }

async function spamEliminar() {
  const ids = [...document.querySelectorAll('.spam-check:checked')]
    .flatMap(c => c.dataset.ids.split(',').map(Number));
  if (!ids.length) { showToast(tAdmin().nada_seleccionado || 'No seleccionaste nada', 'error'); return; }
  if (!confirm((tA().spam_confirmar_eliminar || '¿Eliminar {n} comentarios? Esta acción no se puede deshacer.').replace('{n}', ids.length))) return;
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'spam_eliminar', ids }),
    });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) { spamCerrar(); loadAdminComentarios(); }
  } catch (e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

// ═══════════════════════════════════════════════════════════
//  GESTIÓN DE GAMIFICACIÓN (formularios generados del esquema)
// ═══════════════════════════════════════════════════════════
let _gamEsquema = null;
let _gamEntidad = 'desafio';
let _gamItems   = [];

async function gamInit() {
  if (!_gamEsquema) {
    try {
      const r = await fetch('php/admin.php?accion=gestion_esquema');
      const d = await r.json();
      if (!d.success) { showToast(tAdmin().error_cargar_config || 'No se pudo cargar la configuración', 'error'); return; }
      _gamEsquema = d.entidades;
    } catch (e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); return; }

    document.getElementById('gamChips').innerHTML = Object.entries(_gamEsquema).map(([k, e]) => `
      <button class="gam-chip ${k === _gamEntidad ? 'active' : ''}" data-ent="${k}" onclick="gamCambiar('${k}')">
        <i class="fas ${e.icono}"></i> ${e.label}
      </button>`).join('');
  }
  gamCargar();
}

function gamCambiar(clave) {
  _gamEntidad = clave;
  document.querySelectorAll('.gam-chip').forEach(c => c.classList.toggle('active', c.dataset.ent === clave));
  gamCargar();
}

/** Columnas que se muestran en la tabla (las primeras, para no saturar). */
function gamColumnas(e) {
  return e.campos.filter(c => c.tipo !== 'textarea').slice(0, 5);
}

async function gamCargar() {
  const e = _gamEsquema[_gamEntidad];
  document.getElementById('gamTitulo').textContent = e.label;
  const head = document.getElementById('gamHead');
  const body = document.getElementById('gamBody');
  const cols = gamColumnas(e);

  head.innerHTML = `<tr><th>${escHtml(tC().col_id || 'ID')}</th>${cols.map(c => `<th>${c.label}</th>`).join('')}<th>${escHtml(tC().col_acciones || 'Acciones')}</th></tr>`;
  body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;padding:2rem;color:var(--text-muted)">${escHtml(tC().cargando || 'Cargando…')}</td></tr>`;

  try {
    const r = await fetch(`php/admin.php?accion=gestion_listar&entidad=${_gamEntidad}`);
    const d = await r.json();
    if (!d.success) { body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;color:#e74c3c">${d.message}</td></tr>`; return; }
    _gamItems = d.items || [];
    if (!_gamItems.length) {
      body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;padding:2rem;color:var(--text-muted)">${escHtml((tA().gam_todavia_no_hay || 'Todavía no hay {label}').replace('{label}', e.label.toLowerCase()))}</td></tr>`;
      return;
    }
    body.innerHTML = _gamItems.map(it => `
      <tr>
        <td><span style="color:var(--text-muted)">#${it.id}</span></td>
        ${cols.map(c => {
          // 'nombre'/'descripcion' del catálogo (títulos, misiones, insignias,
          // cosméticos) llegan también traducidos en '<campo>_traducido' —
          // se muestran esos en la tabla, pero el valor crudo (it[c.name])
          // sigue siendo el que se usa para editar (ver gamAbrir/gamCampoHtml).
          const traducido = it[c.name + '_traducido'];
          return `<td>${gamCelda(c, traducido !== undefined ? traducido : it[c.name])}</td>`;
        }).join('')}
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn edit" onclick="gamEditar(${it.id})" title="${escHtml(tC().editar || 'Editar')}"><i class="fas fa-edit"></i></button>
            <button class="admin-action-btn delete" onclick="gamEliminar(${it.id})" title="${escHtml(tC().eliminar || 'Eliminar')}"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>`).join('');
  } catch (err) {
    body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;color:#e74c3c">${escHtml(tA().error_al_cargar || 'Error al cargar')}</td></tr>`;
  }
}

function gamCelda(campo, valor) {
  if (campo.tipo === 'bool') {
    return valor ? `<span class="gam-badge on">${escHtml(tC().si || 'Sí')}</span>` : `<span class="gam-badge off">${escHtml(tC().no || 'No')}</span>`;
  }
  if (campo.tipo === 'color') {
    return `<span class="gam-color" style="background:${escHtml(valor || '#ccc')}"></span> <span style="color:var(--text-muted);font-size:.8rem">${escHtml(valor || '')}</span>`;
  }
  if (campo.tipo === 'select' && campo.opciones) {
    return escHtml(campo.opciones[valor] ?? (valor ?? '–'));
  }
  if (valor === null || valor === '' || valor === undefined) return '<span style="color:var(--text-muted)">–</span>';
  return escHtml(String(valor).slice(0, 60));
}

function gamCampoHtml(c, valor) {
  const id = 'gam_' + c.name;
  const v  = valor ?? '';
  let input;
  if (c.tipo === 'textarea') {
    input = `<textarea class="form-control" id="${id}" rows="3" placeholder="${c.ph || ''}">${escHtml(v)}</textarea>`;
  } else if (c.tipo === 'bool') {
    input = `<select class="form-control" id="${id}"><option value="true" ${v ? 'selected' : ''}>${escHtml(tC().si || 'Sí')}</option><option value="false" ${!v ? 'selected' : ''}>${escHtml(tC().no || 'No')}</option></select>`;
  } else if (c.tipo === 'select') {
    const vacio = c.vacio ? `<option value="">${c.vacio}</option>` : '';
    const ops = Object.entries(c.opciones || {}).map(([k, l]) =>
      `<option value="${escHtml(k)}" ${String(v) === String(k) ? 'selected' : ''}>${escHtml(l)}</option>`).join('');
    input = `<select class="form-control" id="${id}">${vacio}${ops}</select>`;
  } else if (c.tipo === 'color') {
    input = `<input type="color" class="form-control" id="${id}" value="${escHtml(v || '#36c0a1')}" style="height:42px;padding:.25rem">`;
  } else if (c.tipo === 'number') {
    input = `<input type="number" class="form-control" id="${id}" value="${v === '' ? 0 : v}">`;
  } else {
    input = `<input type="text" class="form-control" id="${id}" value="${escHtml(v)}" placeholder="${c.ph || ''}">`;
  }
  return `<div class="form-group"><label class="form-label">${escHtml(c.label)}${c.req ? ' *' : ''}</label>${input}</div>`;
}

function gamAbrir(item) {
  const e = _gamEsquema[_gamEntidad];
  document.getElementById('gamModalTitulo').textContent =
    (item ? (tC().editar || 'Editar') : (tC().crear || 'Crear')) + ' ' + (e.singular || e.label.toLowerCase());
  document.getElementById('gamForm').innerHTML =
    `<input type="hidden" id="gam_id" value="${item ? item.id : ''}">` +
    e.campos.map(c => gamCampoHtml(c, item ? item[c.name] : (c.tipo === 'bool' ? true : ''))).join('');
  document.getElementById('gamModal').classList.add('open');
}

function gamNuevo()  { gamAbrir(null); }
function gamEditar(id) { const it = _gamItems.find(x => x.id === id); if (it) gamAbrir(it); }
function gamCerrar() { document.getElementById('gamModal').classList.remove('open'); }

async function gamGuardar() {
  const e = _gamEsquema[_gamEntidad];
  const id = document.getElementById('gam_id').value;
  const datos = {};
  e.campos.forEach(c => {
    const el = document.getElementById('gam_' + c.name);
    if (el) datos[c.name] = el.value;
  });
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'gestion_guardar', entidad: _gamEntidad, id: id || 0, datos }),
    });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) { gamCerrar(); gamCargar(); }
  } catch (err) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

async function gamEliminar(id) {
  if (!confirm(tA().gam_confirmar_eliminar || '¿Eliminar este elemento? Si ya lo tienen usuarios, se desactivará en vez de borrarse.')) return;
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'gestion_eliminar', entidad: _gamEntidad, id }),
    });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) gamCargar();
  } catch (err) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

// ═══════════════════════════════════════════════════════════
//  ESTADÍSTICAS COMPLETAS
// ═══════════════════════════════════════════════════════════
const _n = (v) => (v ?? 0).toLocaleString((window.CIVI_I18N && CIVI_I18N._locale) || 'es');

function statCard(icono, color, valor, label, extra) {
  return `
    <div class="stat-card">
      <div class="stat-ico" style="background:${color}1f;color:${color}"><i class="fas ${icono}"></i></div>
      <div class="stat-body">
        <div class="stat-val">${valor}</div>
        <div class="stat-lbl">${label}</div>
        ${extra ? `<div class="stat-extra">${extra}</div>` : ''}
      </div>
    </div>`;
}

async function loadAdminStats() {
  const box = document.getElementById('statsBox');
  try {
    const r = await fetch('php/admin.php?accion=estadisticas');
    const d = await r.json();
    if (!d.success) { box.innerHTML = '<p style="color:var(--text-muted)">' + escHtml(tA().stat_error_no_cargaron || 'No se pudieron cargar las estadísticas.') + '</p>'; return; }

    const u = d.usuarios, c = d.contenido, g = d.gamificacion, m = d.moderacion;
    const ta = tA();
    const ex = (key, fallback, rep) => escHtml((ta[key] || fallback).replace('{n}', rep));

    const barras = (d.por_categoria || []).length
      ? (() => {
          const max = Math.max(...d.por_categoria.map(x => x.total));
          return d.por_categoria.map(x => `
            <div class="stat-bar-row">
              <span class="stat-bar-lbl">${escHtml(x.nombre)}</span>
              <div class="stat-bar-track">
                <div class="stat-bar-fill" style="width:${Math.round(x.total / max * 100)}%;background:${x.color || '#36c0a1'}"></div>
              </div>
              <span class="stat-bar-num">${_n(x.total)}</span>
            </div>`).join('');
        })()
      : '<p style="color:var(--text-muted);font-size:.85rem">' + escHtml(ta.stat_sin_datos_categoria || 'Todavía no hay propuestas por categoría.') + '</p>';

    box.innerHTML = `
      <h3 class="stat-title"><i class="fas fa-users"></i> ${escHtml(ta.stat_seccion_comunidad || 'Comunidad')}</h3>
      <div class="stat-grid">
        ${statCard('fa-user-group', '#36c0a1', _n(u.total), escHtml(ta.stat_usuarios_registrados || 'Usuarios registrados'), ex('stat_extra_mas_en_30_dias', '+{n} en 30 días', _n(u.nuevos_30)))}
        ${statCard('fa-user-check', '#22c55e', _n(u.activos), escHtml(ta.stat_usuarios_activos || 'Usuarios activos'), ex('stat_extra_entraron_30_dias', '{n} entraron en 30 días', _n(u.recientes)))}
        ${statCard('fa-user-slash', '#ef4444', _n(u.suspendidos), escHtml(ta.stat_suspendidos || 'Suspendidos'), '')}
        ${statCard('fa-triangle-exclamation', '#f59e0b', _n(m.alertas_pendientes), escHtml(ta.stat_alertas_por_revisar || 'Alertas por revisar'), '')}
      </div>

      <h3 class="stat-title"><i class="fas fa-layer-group"></i> ${escHtml(ta.stat_seccion_contenido || 'Contenido')}</h3>
      <div class="stat-grid">
        ${statCard('fa-file-lines', '#4a9eff', _n(c.propuestas), escHtml(ta.stat_propuestas_creadas || 'Propuestas creadas'), ex('stat_extra_mas_en_30_dias', '+{n} en 30 días', _n(c.propuestas_30)))}
        ${statCard('fa-star', '#f59e0b', _n(c.propuestas_destacadas), escHtml(ta.stat_propuestas_destacadas || 'Propuestas destacadas'), ex('stat_extra_en_votacion', '{n} en votación', _n(c.propuestas_votacion)))}
        ${statCard('fa-comments', '#8b5cf6', _n(c.debates_activos), escHtml(ta.stat_debates_activos || 'Debates activos'), ex('stat_extra_en_total', '{n} en total', _n(c.debates)))}
        ${statCard('fa-comment-dots', '#06b6d4', _n(c.comentarios), escHtml(ta.stat_comentarios || 'Comentarios'), ex('stat_extra_mas_en_30_dias', '+{n} en 30 días', _n(c.comentarios_30)))}
        ${statCard('fa-reply-all', '#14b8a6', _n(c.respuestas_debate), escHtml(ta.stat_respuestas_debates || 'Respuestas en debates'), '')}
        ${statCard('fa-eye-slash', '#ef4444', _n(c.propuestas_censuradas + c.comentarios_censurados), escHtml(ta.stat_contenido_oculto || 'Contenido oculto'), '')}
      </div>

      <h3 class="stat-title"><i class="fas fa-trophy"></i> ${escHtml(ta.stat_seccion_gamificacion || 'Gamificación')}</h3>
      <div class="stat-grid">
        ${statCard('fa-bolt', '#f59e0b', _n(g.xp_total), escHtml(ta.stat_xp_total_repartido || 'XP total repartido'), '')}
        ${statCard('fa-star-half-stroke', '#36c0a1', _n(g.reputacion_media), escHtml(ta.stat_reputacion_promedio || 'Reputación promedio'), ex('stat_extra_nivel_medio', 'nivel medio {n}', g.nivel_medio))}
        ${statCard('fa-award', '#8b5cf6', _n(g.logros_desbloqueados), escHtml(ta.stat_logros_desbloqueados || 'Logros desbloqueados'), '')}
        ${statCard('fa-certificate', '#ef7e22', _n(g.insignias_desbloqueadas), escHtml(ta.stat_insignias_otorgadas || 'Insignias otorgadas'), ex('stat_extra_desafios_completados', '{n} desafíos completados', _n(g.desafios_completados)))}
      </div>

      <h3 class="stat-title"><i class="fas fa-chart-simple"></i> ${escHtml(ta.stat_titulo_por_categoria || 'Propuestas por categoría')}</h3>
      <div class="stat-bars">${barras}</div>`;
  } catch (e) {
    box.innerHTML = '<p style="color:var(--text-muted)">' + escHtml(tA().stat_error_conexion || 'Error de conexión al cargar estadísticas.') + '</p>';
  }
}

// ── Acciones de administración (destacar / ocultar / suspender) ──
async function adminAccion(payload, confirmMsg) {
  if (confirmMsg && !confirm(confirmMsg)) return false;
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const d = await r.json();
    showToast(d.message || (d.success ? tAdmin().listo || 'Listo' : tAdmin().error_generico || 'Error'), d.success ? 'success' : 'error');
    return d.success;
  } catch (e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); return false; }
}

async function destacarContenido(tipo, id) {
  if (await adminAccion({ accion: 'destacar', tipo, id })) {
    if (tipo === 'propuesta') loadAdminPropuestas();
    else if (tipo === 'comentario') loadAdminComentarios();
  }
}

async function ocultarContenido(tipo, id) {
  if (await adminAccion({ accion: 'ocultar', tipo, id })) {
    if (tipo === 'propuesta') loadAdminPropuestas();
    else if (tipo === 'comentario') loadAdminComentarios();
  }
}

async function suspenderUsuario(id) {
  const razon = prompt(tA().suspender_motivo_prompt || 'Motivo de la suspensión (lo verá el equipo de moderación):', tA().suspender_motivo_default || 'Incumplimiento de las normas de la comunidad');
  if (razon === null) return;
  if (await adminAccion({ accion: 'suspender', id, razon })) loadAdminUsuarios();
}

async function reactivarUsuario(id) {
  if (await adminAccion({ accion: 'reactivar', id }, tA().confirmar_reactivar_usuario || '¿Reactivar a este usuario?')) loadAdminUsuarios();
}

// ── KPIs ─────────────────────────────────────────────────
async function loadAdminKpis() {
  try {
    const r  = await fetch('php/propuestas.php?accion=listar&pagina=1&limit=100');
    const d  = await r.json();
    if (d.success) {
      document.getElementById('kpiTotalProp').textContent = d.total || 0;
      const r2 = await fetch('php/propuestas.php?accion=top&limit=100');
      const d2 = await r2.json();
      if (d2.success) {
        const votos = d2.propuestas.reduce((s,p) => s + parseInt(p.votos||0), 0);
        document.getElementById('kpiTotalVotos').textContent = votos.toLocaleString((window.CIVI_I18N && CIVI_I18N._locale) || 'es');
      }
    }
    document.getElementById('kpiTotalUsers').textContent = '3'; // demo
    document.getElementById('kpiTotalComent').textContent = '5'; // demo
  } catch(e) {}
}
loadAdminKpis();

// ── Propuestas admin ─────────────────────────────────────
async function loadAdminPropuestas() {
  const tbody = document.getElementById('adminPropTable');
  try {
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1&limite=50');
    const d = await r.json();
    if (!d.success || !d.propuestas.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">' + escHtml(tA().propuestas_vacio || 'No hay propuestas') + '</td></tr>'; return; }
    const _locale = (window.CIVI_I18N && CIVI_I18N._locale) || 'es';
    const _estadoLbl = (e) => tA()['estado_' + e] || e;
    tbody.innerHTML = d.propuestas.map(p => `
      <tr>
        <td><span style="color:var(--text-muted)">#${p.id}</span></td>
        <td><a href="propuesta.php?id=${p.id}" style="color:var(--verde);font-weight:600">${p.titulo}</a></td>
        <td>${p.autor || '–'}</td>
        <td><span class="badge badge-verde">${p.categoria || '–'}</span></td>
        <td><span class="estado-chip estado-${p.estado}">${escHtml(_estadoLbl(p.estado))}</span></td>
        <td><span class="progreso-chip progreso-${p.progreso || 'idea'}">${escHtml(p.progreso_label || p.progreso || 'idea')}</span></td>
        <td><strong style="color:var(--naranja)">${p.votos}</strong></td>
        <td style="color:var(--text-muted)">${new Date(p.fecha_creacion).toLocaleDateString(_locale)}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn edit" onclick="openEditProp(${p.id},'${escHtml(p.titulo)}','${p.estado}','${p.progreso || 'idea'}')" title="${escHtml(tC().editar || 'Editar')}"><i class="fas fa-edit"></i></button>
            <button class="admin-action-btn" onclick="destacarContenido('propuesta',${p.id})" title="${escHtml(p.destacada ? (tA().quitar_destacado || 'Quitar destacado') : (tA().destacar_propuesta || 'Destacar propuesta'))}" style="color:${p.destacada ? '#f59e0b' : 'var(--naranja-500)'}"><i class="fa${p.destacada ? 's' : 'r'} fa-star"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('propuesta','${p.id}','${escHtml((tA().eliminar_propuesta_confirm || 'Eliminar propuesta «{titulo}»').replace('{titulo}', p.titulo))}')" title="${escHtml(tC().eliminar || 'Eliminar')}"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
    addMobileLabels(tbody);
  } catch(e) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#e74c3c">' + escHtml(tA().error_al_cargar || 'Error al cargar') + '</td></tr>'; }
}
loadAdminStats();
loadAdminPropuestas();

// ── Comentarios admin ────────────────────────────────────
async function loadAdminComentarios() {
  const tbody = document.getElementById('adminComentTable');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">' + escHtml(tC().cargando || 'Cargando...') + '</td></tr>';
  try {
    const r = await fetch('php/propuestas.php?accion=admin_comentarios');
    const d = await r.json();
    if (!d.success || !d.comentarios || !d.comentarios.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">' + escHtml(tA().comentarios_vacio || 'No hay comentarios') + '</td></tr>'; return;
    }
    const _locale2 = (window.CIVI_I18N && CIVI_I18N._locale) || 'es';
    tbody.innerHTML = d.comentarios.map(c => `
      <tr>
        <td><span style="color:var(--text-muted)">#${c.id}</span></td>
        <td style="max-width:260px"><span style="color:var(--text-2)">${escHtml(c.contenido).substring(0,80)}${c.contenido.length>80?'…':''}</span></td>
        <td>${c.autor || '–'}</td>
        <td><a href="propuesta.php?id=${c.propuesta_id}" style="color:var(--verde)">#${c.propuesta_id}</a></td>
        <td style="color:var(--text-muted)">${new Date(c.fecha_creacion).toLocaleDateString(_locale2)}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn" onclick="destacarContenido('comentario',${c.id})" title="${escHtml(c.destacado ? (tA().quitar_destacado || 'Quitar destacado') : (tA().destacar_comentario || 'Destacar comentario'))}" style="color:${c.destacado ? '#f59e0b' : 'var(--naranja-500)'}"><i class="fa${c.destacado ? 's' : 'r'} fa-star"></i></button>
            <button class="admin-action-btn" onclick="ocultarContenido('comentario',${c.id})" title="${escHtml(tA().ocultar_mostrar_comentario || 'Ocultar/mostrar comentario')}" style="color:#8b5cf6"><i class="fas fa-eye-slash"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('comentario','${c.id}','${escHtml(tA().eliminar_comentario_confirm || 'Eliminar este comentario')}')" title="${escHtml(tC().eliminar || 'Eliminar')}"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
    addMobileLabels(tbody);
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#e74c3c">' + escHtml(tA().error_al_cargar || 'Error al cargar') + '</td></tr>'; }
}

// ── Usuarios admin ────────────────────────────────────────
async function loadAdminUsuarios() {
  const tbody = document.getElementById('adminUsersTable');
  if (!tbody) return;
  try {
    const r = await fetch('php/auth.php?accion=admin_usuarios');
    const d = await r.json();
    if (!d.success || !d.usuarios || !d.usuarios.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">' + escHtml(tA().usuarios_vacio || 'No hay usuarios') + '</td></tr>'; return;
    }
    const _locale3 = (window.CIVI_I18N && CIVI_I18N._locale) || 'es';
    tbody.innerHTML = d.usuarios.map(u => `
      <tr>
        <td><span style="color:var(--text-muted)">#${u.id}</span></td>
        <td><strong>${escHtml(u.nombre)} ${escHtml(u.apellido)}</strong></td>
        <td style="color:var(--text-muted)">${u.email}</td>
        <td>
          <select class="form-control" style="padding:.3rem .6rem;font-size:.8rem" onchange="changeUserRole(${u.id},this.value)">
            <option value="usuario" ${u.rol==='usuario'?'selected':''}>${CIVI_I18N.roles.usuario}</option>
            <option value="moderador" ${u.rol==='moderador'?'selected':''}>${CIVI_I18N.roles.moderador}</option>
            <option value="admin" ${u.rol==='admin'?'selected':''}>${CIVI_I18N.roles.admin}</option>
          </select>
        </td>
        <td style="color:var(--text-muted)">${new Date(u.fecha_registro).toLocaleDateString(_locale3)}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn" onclick="${u.activo === false ? `reactivarUsuario(${u.id})` : `suspenderUsuario(${u.id})`}" title="${escHtml(u.activo === false ? (tA().accion_reactivar_usuario || 'Reactivar usuario') : (tA().accion_suspender_usuario || 'Suspender usuario'))}" style="color:${u.activo === false ? '#22c55e' : '#ef4444'}"><i class="fas fa-user-${u.activo === false ? 'check' : 'slash'}"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('usuario','${u.id}','${escHtml((tA().eliminar_usuario_confirm || 'Eliminar usuario {nombre}').replace('{nombre}', u.nombre))}')" title="${escHtml(tC().eliminar || 'Eliminar')}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
    addMobileLabels(tbody);
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#e74c3c">' + escHtml(tA().error_al_cargar || 'Error al cargar') + '</td></tr>'; }
}

// ── Cambiar rol ───────────────────────────────────────────
async function changeUserRole(userId, nuevoRol) {
  try {
    const r = await fetch('php/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ accion: 'cambiar_rol', usuario_id: userId, rol: nuevoRol })
    });
    const d = await r.json();
    if (d.success) showToast(tAdmin().rol_actualizado || 'Rol actualizado', 'success');
    else showToast(d.mensaje || tAdmin().error_cambiar_rol || 'Error al cambiar rol', 'error');
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

// ── Confirm + Delete ──────────────────────────────────────
let pendingDelete = null;
let _confirmCallback = null; // usado por openConfirm() para confirmaciones genéricas (no-delete)
function confirmDelete(tipo, id, msg) {
  pendingDelete = { tipo, id };
  document.getElementById('confirmTitle').textContent = tA().confirmar_eliminacion || 'Confirmar eliminación';
  document.getElementById('confirmMsg').textContent = (tA().confirmar_eliminar_msg || '¿Estás seguro de que deseas eliminar esto? {msg}. Esta acción no se puede deshacer.').replace('{msg}', msg);
  document.getElementById('confirmModal').classList.add('open');
}
/** Confirmación genérica reutilizando el mismo modal (título + mensaje + callback). */
function openConfirm(title, msg, onConfirm) {
  pendingDelete = null;
  _confirmCallback = onConfirm;
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMsg').textContent = msg;
  document.getElementById('confirmModal').classList.add('open');
}
function closeConfirm() {
  document.getElementById('confirmModal').classList.remove('open');
  pendingDelete = null;
  _confirmCallback = null;
}
document.getElementById('confirmBtn').addEventListener('click', async () => {
  if (_confirmCallback) { const cb = _confirmCallback; closeConfirm(); await cb(); return; }
  if (!pendingDelete) return;
  const { tipo, id } = pendingDelete;
  closeConfirm();
  try {
    let url = '';
    let body = {};
    if (tipo === 'propuesta') { url = 'php/propuestas.php'; body = { accion: 'eliminar', id }; }
    else if (tipo === 'comentario') { url = 'php/propuestas.php'; body = { accion: 'eliminar_comentario', id }; }
    else if (tipo === 'usuario') { url = 'php/auth.php'; body = { accion: 'eliminar_usuario', id }; }
    const r = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
    const d = await r.json();
    if (d.success) {
      showToast(tAdmin().eliminado_correctamente || 'Eliminado correctamente', 'success');
      if (tipo === 'propuesta') loadAdminPropuestas();
      else if (tipo === 'comentario') loadAdminComentarios();
      else if (tipo === 'usuario') loadAdminUsuarios();
      loadAdminKpis();
    } else showToast(d.mensaje || tAdmin().error_eliminar || 'Error al eliminar', 'error');
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
});

// ── Editar propuesta ─────────────────────────────────────
function openEditProp(id, titulo, estado) {
  document.getElementById('editPropId').value = id;
  document.getElementById('editPropTitulo').value = titulo;
  document.getElementById('editPropEstado').value = estado;
  document.getElementById('editPropModal').classList.add('open');
}
function closeEditProp() { document.getElementById('editPropModal').classList.remove('open'); }
async function saveEditProp() {
  const id     = document.getElementById('editPropId').value;
  const titulo = document.getElementById('editPropTitulo').value;
  const estado = document.getElementById('editPropEstado').value;
  try {
    const r = await fetch('php/propuestas.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ accion: 'admin_editar', id, titulo, estado })
    });
    const d = await r.json();
    if (d.success) { showToast(tAdmin().propuesta_actualizada || 'Propuesta actualizada', 'success'); closeEditProp(); loadAdminPropuestas(); }
    else showToast(d.mensaje || tAdmin().error_actualizar || 'Error al actualizar', 'error');
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/** En pantallas angostas .admin-table pasa de filas a "tarjetas" (ver CSS);
 * cada celda necesita un data-label con el texto de su columna para mostrar
 * "Nombre: X" en vez de una tabla ilegible que solo se puede leer haciendo
 * scroll horizontal. Se toman los encabezados directo del <thead>, así que
 * no hay que repetir el texto de las columnas en JS. */
function addMobileLabels(tbody) {
  if (!tbody) return;
  const table = tbody.closest('table');
  if (!table) return;
  const headers = [...table.querySelectorAll('thead th')].map(th => th.textContent.trim());
  tbody.querySelectorAll('tr').forEach(tr => {
    [...tr.children].forEach((td, i) => { if (headers[i]) td.setAttribute('data-label', headers[i]); });
  });
}

function showToast(msg, type='info') {
  if (window.Toast) { Toast.show(msg, type); return; }
  const d = document.createElement('div');
  d.className = 'toast';
  d.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'} toast-icon ${type}"></i><span class="toast-msg">${msg}</span>`;
  document.querySelector('.toast-container').appendChild(d);
  setTimeout(() => { d.classList.add('removing'); setTimeout(() => d.remove(), 300); }, 3500);
}

// ── CONTACT MESSAGES ────────────────────────────────────────
let currentContactId = null;

async function loadContactMessages(filter='all') {
  const el = document.getElementById('contactMessages');
  el.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> ' + escHtml(tC().cargando || 'Cargando...') + '</div>';
  document.querySelectorAll('#filterAll,#filterUnread').forEach(b => b.classList.remove('btn-primary','btn-outline','btn-ghost'));
  document.getElementById(filter==='unread'?'filterUnread':'filterAll').classList.add('btn-primary');
  document.getElementById(filter==='unread'?'filterAll':'filterUnread').classList.add('btn-ghost');

  const qs = filter==='unread' ? '?leido=0' : '';
  try {
    const r = await fetch('php/contacto.php?accion=listar'+qs);
    const d = await r.json();
    if (!d.success) { el.innerHTML = '<div class="empty-state"><i class="fas fa-envelope-open"></i><p>' + escHtml(tA().contacto_vacio || 'No hay mensajes.') + '</p></div>'; return; }

    // Update badge
    const unread = d.mensajes.filter(m => !m.leido).length;
    const badge = document.getElementById('contactoBadge');
    if (badge) { badge.textContent = unread; badge.style.display = unread>0?'inline-flex':'none'; }

    if (!d.mensajes.length) {
      el.innerHTML = '<div class="empty-state"><i class="fas fa-envelope-open"></i><p>' + escHtml(filter==='unread' ? (tA().contacto_vacio_sin_leer || 'No hay mensajes sin leer.') : (tA().contacto_vacio || 'No hay mensajes.')) + '</p></div>';
      return;
    }

    el.innerHTML = d.mensajes.map(m => `
      <div class="contact-msg-card ${m.leido?'':'msg-unread'}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:.75rem;transition:var(--trans)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
          <div>
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
              ${!m.leido ? '<span style="width:8px;height:8px;border-radius:50%;background:var(--verde);display:inline-block;flex-shrink:0"></span>' : ''}
              <strong style="font-size:.95rem;color:var(--text)">${escHtml(m.nombre)}</strong>
              <span style="font-size:.78rem;color:var(--text-muted)">&lt;${escHtml(m.email)}&gt;</span>
            </div>
            <div style="font-size:.85rem;font-weight:600;color:var(--text-2);margin-bottom:.35rem">${escHtml(m.asunto)}</div>
            <p style="font-size:.83rem;color:var(--text-muted);line-height:1.5;max-width:600px">${escHtml(m.mensaje).substring(0,200)}${m.mensaje.length>200?'…':''}</p>
            ${m.respuesta ? `<div style="margin-top:.6rem;padding:.5rem .75rem;background:var(--verde-alpha);border-left:3px solid var(--verde);border-radius:4px;font-size:.8rem;color:var(--verde-700)"><strong>${escHtml(tA().contacto_respondido_label || '✓ Respondido:')}</strong> ${escHtml(m.respuesta).substring(0,120)}…</div>` : ''}
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0">
            <span style="font-size:.75rem;color:var(--text-muted)">${m.fecha_formateada}</span>
            <div style="display:flex;gap:.4rem">
              <button onclick="openContactReply(${m.id},'${escHtml(m.nombre)}','${escHtml(m.email)}','${escHtml(m.asunto)}',\`${escHtml(m.mensaje)}\`)" class="admin-action-btn edit" title="${escHtml(tA().responder_ver || 'Responder/Ver')}"><i class="fas fa-reply"></i></button>
              ${!m.leido ? `<button onclick="markMsgRead(${m.id})" class="admin-action-btn" style="background:var(--verde-alpha);color:var(--verde-600)" title="${escHtml(tA().marcar_leido || 'Marcar leído')}"><i class="fas fa-check"></i></button>` : ''}
              <button onclick="deleteMsgConfirm(${m.id})" class="admin-action-btn delete" title="${escHtml(tC().eliminar || 'Eliminar')}"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>`).join('');
  } catch(e) { el.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>' + escHtml(tA().contacto_error_cargar || 'Error al cargar mensajes.') + '</p></div>'; }
}

function openContactReply(id, nombre, email, asunto, mensaje) {
  currentContactId = id;
  document.getElementById('cMsgNombre').textContent = nombre;
  document.getElementById('cMsgEmail').textContent = email;
  document.getElementById('cMsgAsunto').textContent = asunto;
  document.getElementById('cMsgTexto').textContent = mensaje;
  document.getElementById('contactReplyText').value = '';
  document.getElementById('contactReplyModal').classList.add('open');
  markMsgRead(id, true); // mark silently
}
function closeContactReply() { document.getElementById('contactReplyModal').classList.remove('open'); }

async function sendContactReply() {
  const txt = document.getElementById('contactReplyText').value.trim();
  if (!txt) { showToast(tAdmin().escribe_respuesta || 'Escribe una respuesta antes de guardar', 'error'); return; }
  try {
    const r = await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'responder', id: currentContactId, respuesta: txt }) });
    const d = await r.json();
    if (d.success) { showToast(tAdmin().respuesta_guardada || 'Respuesta guardada', 'success'); closeContactReply(); loadContactMessages(); }
    else showToast(d.message || tAdmin().error_generico || 'Error', 'error');
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

async function markMsgRead(id, silent=false) {
  try {
    await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'marcar_leido', id }) });
    if (!silent) { showToast(tAdmin().marcado_leido || 'Marcado como leído', 'success'); loadContactMessages(); }
  } catch(e) {}
}

function deleteMsgConfirm(id) {
  openConfirm(tA().eliminar_mensaje_confirm_titulo || '¿Eliminar mensaje?', tA().accion_no_deshacer || 'Esta acción no se puede deshacer.', async () => {
    const r = await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'eliminar', id }) });
    const d = await r.json();
    if (d.success) { showToast(tAdmin().mensaje_eliminado || 'Mensaje eliminado', 'success'); closeConfirm(); loadContactMessages(); }
    else showToast(tAdmin().error_eliminar || 'Error al eliminar', 'error');
  });
}

// ── CATEGORIES CRUD ──────────────────────────────────────────
let currentCatId = null;

async function loadAdminCategorias() {
  try {
    const r = await fetch('php/admin_categorias.php?accion=listar');
    const d = await r.json();
    const tbody = document.getElementById('adminCatTable');
    if (!d.success || !d.categorias.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">' + escHtml(tA().categorias_vacio || 'No hay categorías') + '</td></tr>';
      return;
    }
    tbody.innerHTML = d.categorias.map(cat => `
      <tr>
        <td>${cat.id}</td>
        <td><i class="${escHtml(cat.icono)}" style="color:${escHtml(cat.color)};font-size:1.2rem"></i></td>
        <td><strong>${escHtml(cat.nombre_traducido || cat.nombre)}</strong></td>
        <td><span style="display:inline-flex;align-items:center;gap:.4rem"><span style="width:16px;height:16px;border-radius:50%;background:${escHtml(cat.color)};display:inline-block"></span>${escHtml(cat.color)}</span></td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(cat.descripcion_traducido || cat.descripcion || '—')}</td>
        <td><div class="admin-actions">
          <button onclick="openCatModal(${cat.id},'${escHtml(cat.nombre)}','${escHtml(cat.icono)}','${escHtml(cat.color)}','${escHtml(cat.descripcion||'')}')" class="admin-action-btn edit" title="${escHtml(tC().editar || 'Editar')}"><i class="fas fa-pen"></i></button>
          <button onclick="deleteCat(${cat.id})" class="admin-action-btn delete" title="${escHtml(tC().eliminar || 'Eliminar')}"><i class="fas fa-trash"></i></button>
        </div></td>
      </tr>`).join('');
    addMobileLabels(tbody);
  } catch(e) { showToast(tAdmin().error_cargar_categorias || 'Error cargando categorías', 'error'); }
}

function openCatModal(id=null, nombre='', icono='fas fa-tag', color='#36c0a1', desc='') {
  currentCatId = id;
  document.getElementById('catModalTitle').innerHTML = id
    ? '<i class="fas fa-pen" style="color:var(--verde)"></i> ' + escHtml(tA().editar_categoria || 'Editar categoría')
    : '<i class="fas fa-plus" style="color:var(--verde)"></i> ' + escHtml(tA().nueva_categoria || 'Nueva categoría');
  document.getElementById('catId').value = id||'';
  document.getElementById('catNombre').value = nombre;
  document.getElementById('catIcono').value = icono;
  document.getElementById('catColor').value = color;
  document.getElementById('catDesc').value = desc;
  document.getElementById('catIconoPreview').className = icono;
  document.getElementById('catModal').classList.add('open');
}
function closeCatModal() { document.getElementById('catModal').classList.remove('open'); }

document.getElementById('catIcono')?.addEventListener('input', function() {
  document.getElementById('catIconoPreview').className = this.value;
});

async function saveCat() {
  const nombre = document.getElementById('catNombre').value.trim();
  if (!nombre) { showToast(tAdmin().nombre_obligatorio || 'El nombre es obligatorio', 'error'); return; }
  const data = {
    accion: currentCatId ? 'editar' : 'crear',
    id: currentCatId,
    nombre,
    icono: document.getElementById('catIcono').value.trim() || 'fas fa-tag',
    color: document.getElementById('catColor').value,
    descripcion: document.getElementById('catDesc').value.trim()
  };
  try {
    const r = await fetch('php/admin_categorias.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const d = await r.json();
    if (d.success) { showToast(currentCatId ? (tAdmin().categoria_actualizada || 'Categoría actualizada') : (tAdmin().categoria_creada || 'Categoría creada'), 'success'); closeCatModal(); loadAdminCategorias(); }
    else showToast(d.message || tAdmin().error_generico || 'Error', 'error');
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

function deleteCat(id) {
  openConfirm(tA().eliminar_categoria_confirm_titulo || '¿Eliminar categoría?', tA().eliminar_categoria_confirm_msg || 'Las propuestas en esta categoría podrían verse afectadas.', async () => {
    const r = await fetch('php/admin_categorias.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({accion:'eliminar', id}) });
    const d = await r.json();
    if (d.success) { showToast(tAdmin().categoria_eliminada || 'Categoría eliminada', 'success'); closeConfirm(); loadAdminCategorias(); }
    else showToast(d.message || tAdmin().error_eliminar || 'Error al eliminar', 'error');
  });
}

// ── ALERTAS IA ───────────────────────────────────────────────
async function loadAlertas(soloPendientes = false) {
  const el = document.getElementById('alertasContainer');
  el.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> ' + escHtml(tC().cargando || 'Cargando...') + '</div>';

  document.getElementById('filterAlertasTodas').className = 'btn btn-sm ' + (soloPendientes ? 'btn-ghost' : 'btn-outline');
  document.getElementById('filterAlertasPend').className  = 'btn btn-sm ' + (soloPendientes ? 'btn-outline' : 'btn-ghost');

  try {
    const url = 'php/ia.php?accion=alertas' + (soloPendientes ? '&sin_revisar=1' : '');
    const r = await fetch(url);
    const d = await r.json();

    if (!d.success) { el.innerHTML = '<div class="empty-state"><i class="fas fa-shield-alt"></i><p>' + escHtml(tA().alertas_sin_permisos || 'Sin permisos para ver alertas.') + '</p></div>'; return; }

    // Actualizar badge
    const badge = document.getElementById('alertasBadge');
    if (badge) { badge.textContent = d.pendientes; badge.style.display = d.pendientes > 0 ? 'inline-flex' : 'none'; }

    if (!d.alertas || !d.alertas.length) {
      const vacioMsg = soloPendientes ? (tA().alertas_vacio_pendientes || 'No hay alertas pendientes. ¡Todo limpio! 🎉') : (tA().alertas_vacio_todas || 'No hay alertas. ¡Todo limpio! 🎉');
      el.innerHTML = '<div class="empty-state" style="text-align:center;padding:3rem;color:var(--text-muted)"><i class="fas fa-check-circle" style="font-size:3rem;color:var(--verde);margin-bottom:1rem;display:block"></i><p>' + escHtml(vacioMsg) + '</p></div>';
      return;
    }

    const severidadColor = { alta: '#e74c3c', media: '#ef7e22', baja: '#36c0a1' };
    const severidadIcon  = { alta: 'fa-exclamation-circle', media: 'fa-exclamation-triangle', baja: 'fa-info-circle' };
    const severidadLbl   = { alta: tA().alerta_severidad_alta || 'Alta', media: tA().alerta_severidad_media || 'Media', baja: tA().alerta_severidad_baja || 'Baja' };
    const tipoLbl        = { comentario: tA().alerta_tipo_comentario || 'Comentario', propuesta: tA().alerta_tipo_propuesta || 'Propuesta' };

    el.innerHTML = d.alertas.map(a => `
      <div class="contact-msg-card ${a.revisado ? '' : 'msg-unread'}"
           style="background:var(--bg-card);border:1px solid var(--border);border-left:4px solid ${severidadColor[a.severidad]||'#ef7e22'};border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:.75rem;transition:var(--trans)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;flex-wrap:wrap">
              <span style="background:${severidadColor[a.severidad]||'#ef7e22'}22;color:${severidadColor[a.severidad]||'#ef7e22'};padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase">
                <i class="fas ${severidadIcon[a.severidad]||'fa-exclamation-triangle'}"></i> ${escHtml(severidadLbl[a.severidad] || a.severidad)}
              </span>
              <span style="background:var(--surface);padding:.2rem .6rem;border-radius:20px;font-size:.75rem;color:var(--text-muted)">
                <i class="fas fa-${a.tipo==='comentario'?'comment':'file-alt'}"></i> ${escHtml(tipoLbl[a.tipo] || a.tipo)} #${a.referencia_id}
              </span>
              ${a.revisado ? `<span style="background:#36c0a122;color:var(--verde);padding:.2rem .6rem;border-radius:20px;font-size:.75rem"><i class="fas fa-check"></i> ${escHtml(tA().alerta_estado_revisado || 'Revisado')}</span>` : `<span style="background:#ef7e2222;color:#ef7e22;padding:.2rem .6rem;border-radius:20px;font-size:.75rem"><i class="fas fa-clock"></i> ${escHtml(tA().alerta_estado_pendiente || 'Pendiente')}</span>`}
            </div>
            <div style="margin-bottom:.4rem">
              <strong style="font-size:.82rem;color:var(--text-muted)">${escHtml(tA().alertas_razon_detectada || 'Razón detectada:')}</strong>
              <span style="font-size:.85rem;color:var(--text)">${escHtml(a.razon)}</span>
            </div>
            <div style="background:var(--surface);border-radius:8px;padding:.6rem .9rem;font-size:.82rem;color:var(--text-2);line-height:1.5;max-height:80px;overflow:hidden;text-overflow:ellipsis">
              ${escHtml(a.contenido_original).substring(0, 200)}${a.contenido_original.length > 200 ? '…' : ''}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0">
            <span style="font-size:.75rem;color:var(--text-muted)">${a.fecha}</span>
            <div style="display:flex;gap:.4rem">
              ${a.existe && a.link
                ? `<a href="${a.link}" target="_blank" class="admin-action-btn edit" title="${escHtml(tA().ver_contenido || 'Ver contenido')}"><i class="fas fa-eye"></i></a>`
                : `<span class="admin-action-btn" style="opacity:.35;cursor:not-allowed" title="${escHtml(tA().contenido_eliminado || 'El contenido ya no existe')}"><i class="fas fa-eye-slash"></i></span>`}
              ${!a.revisado ? `<button onclick="marcarAlertaRevisada(${a.id})" class="admin-action-btn" style="background:#94a3b822;color:#64748b" title="${escHtml(tA().descartar_alerta_title || 'Descartar alerta (dejar como está, sin restaurar ni penalizar)')}"><i class="fas fa-check"></i></button>` : ''}
              ${!a.revisado ? `<button onclick="aprobarAlerta(${a.id}, '${a.tipo}')" class="admin-action-btn" style="background:#4a9eff22;color:#4a9eff" title="${escHtml(tA().aprobar_restaurar_title || 'Aprobar / restaurar contenido (la IA se equivocó)')}"><i class="fas fa-unlock"></i></button>` : ''}
              ${!a.revisado ? `<button onclick="censurarAlerta(${a.id}, '${a.tipo}')" class="admin-action-btn" style="background:#e74c3c22;color:#e74c3c" title="${escHtml(tA().censurar_title || 'Confirmar censura y penalizar reputación del autor')}"><i class="fas fa-ban"></i></button>` : ''}
            </div>
          </div>
        </div>
      </div>
    `).join('');
  } catch(e) {
    el.innerHTML = '<div style="text-align:center;padding:2rem;color:#e74c3c"><i class="fas fa-exclamation-triangle"></i> ' + escHtml(tA().alertas_error_cargar || 'Error al cargar alertas.') + '</div>';
  }
}

async function marcarAlertaRevisada(id) {
  try {
    const r = await fetch('php/ia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'marcar_revisado', id })
    });
    const d = await r.json();
    if (d.success) {
      showToast(tAdmin().alerta_revisada || 'Alerta marcada como revisada', 'success');
      loadAlertas();
    } else {
      showToast(d.mensaje || tAdmin().error_generico || 'Error', 'error');
    }
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

// ── Modal de confirmación genérico (estilo CIVINSIS, reemplaza confirm()) ──
function closeConfirmModal() { document.getElementById('confirmModal').classList.remove('open'); }

function showConfirmModal({ icon, confirmColor, title, message, confirmText, onConfirm }) {
  document.querySelector('#confirmModal .confirm-modal').style.setProperty('--confirm-color', confirmColor);
  document.getElementById('confirmModalIcon').className = 'fas ' + icon;
  document.getElementById('confirmModalTitleText').textContent = title;
  document.getElementById('confirmModalMessage').textContent = message;

  const btn = document.getElementById('confirmModalBtn');
  btn.textContent = confirmText;
  btn.onclick = () => { closeConfirmModal(); onConfirm(); };

  document.getElementById('confirmModal').classList.add('open');
}

// Texto del tipo de contenido con su artículo ("este comentario", "esta propuesta"...)
// para que el mensaje del modal diga exactamente qué se va a moderar.
function etiquetaTipoAlerta(tipo) {
  const mapa = {
    comentario:       tA().tipo_este_comentario || 'este comentario',
    propuesta:        tA().tipo_esta_propuesta  || 'esta propuesta',
    debate:           tA().tipo_este_debate     || 'este debate',
    debate_respuesta: tA().tipo_esta_respuesta  || 'esta respuesta',
  };
  return mapa[tipo] || (tA().tipo_este_contenido || 'este contenido');
}

async function aprobarAlerta(id, tipo) {
  const item = etiquetaTipoAlerta(tipo);
  showConfirmModal({
    icon: 'fa-unlock',
    title: tA().aprobar_titulo_modal || 'Restaurar contenido',
    message: (tA().aprobar_confirm_din || '¿Estás seguro de que quieres restaurar y publicar {item}? Volverá a verse pese a la alerta de la IA.').replace('{item}', item),
    confirmText: tA().aprobar_boton || 'Sí, restaurar',
    confirmColor: '#4a9eff',
    onConfirm: () => ejecutarAprobarAlerta(id),
  });
}

async function ejecutarAprobarAlerta(id) {
  try {
    const r = await fetch('php/ia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'aprobar', id })
    });
    const d = await r.json();
    if (d.success) {
      showToast(d.message || tAdmin().contenido_publicado || 'Contenido publicado', 'success');
      loadAlertas();
    } else {
      showToast(d.message || tAdmin().error_generico || 'Error', 'error');
    }
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

async function censurarAlerta(id, tipo) {
  const item = etiquetaTipoAlerta(tipo);
  showConfirmModal({
    icon: 'fa-lock',
    title: tA().censurar_titulo_modal || 'Confirmar censura',
    message: (tA().censurar_confirm_din || '¿Estás seguro de que quieres censurar {item}? Se ocultará a los usuarios y se penalizará la reputación de quien lo publicó.').replace('{item}', item),
    confirmText: tA().censurar_boton || 'Sí, censurar',
    confirmColor: '#e74c3c',
    onConfirm: () => ejecutarCensurarAlerta(id),
  });
}

async function ejecutarCensurarAlerta(id) {
  try {
    const r = await fetch('php/ia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'censurar', id })
    });
    const d = await r.json();
    if (d.success) {
      showToast(d.message || tAdmin().contenido_censurado || 'Contenido censurado', 'success');
      loadAlertas();
    } else {
      showToast(d.message || tAdmin().error_generico || 'Error', 'error');
    }
  } catch(e) { showToast(tComunAdmin().error_conexion || 'Error de conexión', 'error'); }
}

// Load contact messages badge on init
(async () => {
  try {
    const r = await fetch('php/contacto.php?accion=listar&leido=0');
    const d = await r.json();
    if (d.success) {
      const badge = document.getElementById('contactoBadge');
      if (badge && d.total > 0) { badge.textContent = d.total; badge.style.display = 'inline-flex'; }
    }
  } catch(e) {}
})();

// Badge de alertas pendientes
(async () => {
  try {
    const r = await fetch('php/ia.php?accion=alertas&sin_revisar=1');
    const d = await r.json();
    if (d.success && d.pendientes > 0) {
      const badge = document.getElementById('alertasBadge');
      if (badge) { badge.textContent = d.pendientes; badge.style.display = 'inline-flex'; }
    }
  } catch(e) {}
})();

// Extend tab loader to include new tabs
document.querySelectorAll('[data-admin-tab]').forEach(tab => {
  tab.addEventListener('click', () => {
    const t = tab.dataset.adminTab;
    if (t === 'contacto') loadContactMessages();
    if (t === 'categorias') loadAdminCategorias();
    if (t === 'alertas') loadAlertas();
  });
});
