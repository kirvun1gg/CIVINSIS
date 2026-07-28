<?php
$iniciales = strtoupper(substr($usuarioNombre, 0, 1));
$esAdmin   = ($usuarioRol === 'admin');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrador – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav'=>'admin'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:5rem;min-height:100vh">
  <div class="container">

    <!-- Header admin -->
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap">
      <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ef7e22,#d46a10);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem">
        <i class="fas fa-shield-alt"></i>
      </div>
      <div>
        <h1 style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--text)">
          Panel de Administración
          <span class="admin-badge"><?= ucfirst($usuarioRol) ?></span>
        </h1>
        <p style="font-size:.875rem;color:var(--text-muted)">Gestiona propuestas, comentarios y usuarios de CIVINSIS</p>
      </div>
    </div>

    <!-- KPIs admin -->
    <div class="dash-kpi-grid" id="adminKpis">
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalProp">–</div><div class="kpi-label"><i class="fas fa-file-alt"></i> Propuestas</div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalComent">–</div><div class="kpi-label"><i class="fas fa-comments"></i> Comentarios</div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalUsers">–</div><div class="kpi-label"><i class="fas fa-users"></i> Usuarios</div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalVotos">–</div><div class="kpi-label"><i class="fas fa-arrow-up"></i> Votos totales</div></div>
    </div>

    <!-- Tabs -->
    <div class="profile-tabs" style="margin-bottom:1.5rem">
      <button class="profile-tab active" data-admin-tab="estadisticas">
        <i class="fas fa-chart-line"></i> Estadísticas
      </button>
      <button class="profile-tab" data-admin-tab="propuestas">
        <i class="fas fa-file-alt"></i> Propuestas
      </button>
      <button class="profile-tab" data-admin-tab="comentarios">
        <i class="fas fa-comments"></i> Comentarios
      </button>
      <?php if ($esAdmin): ?>
      <button class="profile-tab" data-admin-tab="usuarios">
        <i class="fas fa-users"></i> Usuarios
      </button>
      <button class="profile-tab" data-admin-tab="contacto">
        <i class="fas fa-envelope"></i> Contacto <span class="msg-badge" id="contactoBadge" style="display:none">0</span>
      </button>
      <button class="profile-tab" data-admin-tab="categorias">
        <i class="fas fa-tags"></i> Categorías
      </button>
      <button class="profile-tab" data-admin-tab="gamificacion">
        <i class="fas fa-trophy"></i> Gamificación
      </button>
      <button class="profile-tab" data-admin-tab="alertas" id="tabAlertas">
        <i class="fas fa-robot"></i> Alertas IA <span class="msg-badge" id="alertasBadge" style="display:none">0</span>
      </button>
      <?php endif; ?>
    </div>

    <!-- Tab: Estadísticas -->
    <div class="admin-section active" id="admin-tab-estadisticas">
      <div id="statsBox">
        <div style="text-align:center;padding:3rem 0;color:var(--text-muted)">
          <i class="fas fa-chart-line fa-bounce"></i> Cargando estadísticas…
        </div>
      </div>
    </div>

    <!-- Tab: Propuestas -->
    <div class="admin-section" id="admin-tab-propuestas">
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th><th>Título</th><th>Autor</th><th>Categoría</th><th>Estado</th><th>Votos</th><th>Fecha</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody id="adminPropTable">
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab: Comentarios -->
    <div class="admin-section" id="admin-tab-comentarios">
      <div class="gam-bar">
        <h3 class="gam-titulo">Comentarios</h3>
        <button class="btn btn-outline btn-sm" onclick="spamAbrir()"><i class="fas fa-broom"></i> Limpiar spam</button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th><th>Contenido</th><th>Autor</th><th>Propuesta</th><th>Fecha</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody id="adminComentTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($esAdmin): ?>
    <!-- Tab: Usuarios -->
    <div class="admin-section" id="admin-tab-usuarios">
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody id="adminUsersTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab: Alertas IA -->
    <!-- Tab: Gamificación -->
    <div class="admin-section" id="admin-tab-gamificacion">
      <div class="gam-chips" id="gamChips"></div>
      <div class="gam-bar">
        <h3 class="gam-titulo" id="gamTitulo">Desafíos</h3>
        <button class="btn btn-primary btn-sm" onclick="gamNuevo()"><i class="fas fa-plus"></i> Nuevo</button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead id="gamHead"></thead>
          <tbody id="gamBody"><tr><td style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando…</td></tr></tbody>
        </table>
      </div>
    </div>

    <div class="admin-section" id="admin-tab-alertas">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700"><i class="fas fa-robot" style="color:var(--verde)"></i> Alertas de Moderación IA</h3>
          <p style="font-size:.8rem;color:var(--text-muted)">Contenido detectado automáticamente por CIVI como inapropiado</p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center">
          <button onclick="loadAlertas(false)" class="btn btn-outline btn-sm" id="filterAlertasTodas">Todas</button>
          <button onclick="loadAlertas(true)"  class="btn btn-ghost  btn-sm" id="filterAlertasPend">Pendientes</button>
        </div>
      </div>
      <div id="alertasContainer">
        <div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
      </div>
    </div>

    <?php if ($esAdmin): ?>
    <!-- Tab: Contacto -->
    <div class="admin-section" id="admin-tab-contacto">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700">Mensajes de Contacto</h3>
          <p style="font-size:.8rem;color:var(--text-muted)">Mensajes enviados desde la página de contacto</p>
        </div>
        <div style="display:flex;gap:.5rem">
          <button onclick="loadContactMessages('all')" class="btn btn-outline btn-sm" id="filterAll">Todos</button>
          <button onclick="loadContactMessages('unread')" class="btn btn-ghost btn-sm" id="filterUnread">Sin leer</button>
        </div>
      </div>
      <div id="contactMessages">
        <div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
      </div>
    </div>

    <!-- Tab: Categorías -->
    <div class="admin-section" id="admin-tab-categorias">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700">Gestión de Categorías</h3>
          <p style="font-size:.8rem;color:var(--text-muted)">Crea, edita y elimina categorías de propuestas</p>
        </div>
        <button onclick="openCatModal()" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva categoría</button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Icono</th><th>Nombre</th><th>Color</th><th>Descripción</th><th>Acciones</th></tr>
          </thead>
          <tbody id="adminCatTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<!-- Modal de confirmación -->
<!-- Modal de confirmación ambientado (moderación IA y borrados) -->
<div class="modal-backdrop" id="askModal">
  <div class="modal ask-modal">
    <div class="ask-icon" id="askIcon"><i class="fas fa-question"></i></div>
    <h3 class="ask-title" id="askTitle">Confirmar</h3>
    <p class="ask-msg" id="askMsg"></p>
    <div class="ask-detail" id="askDetail" hidden>
      <span class="ask-detail-lbl" id="askDetailLbl">Contenido</span>
      <p id="askDetailTxt"></p>
    </div>
    <div class="ask-input" id="askInputWrap" hidden>
      <label class="ask-input-lbl" id="askInputLbl">Motivo</label>
      <input type="text" class="form-control" id="askInput" autocomplete="off">
    </div>
    <div class="ask-actions">
      <button class="btn btn-ghost btn-sm" id="askCancel">Cancelar</button>
      <button class="btn btn-sm" id="askOk">Confirmar</button>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="confirmModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="confirmTitle">Confirmar acción</h3>
      <button class="modal-close" onclick="closeConfirm()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p id="confirmMsg" style="color:var(--text-muted)"></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeConfirm()">Cancelar</button>
      <button class="btn btn-danger" id="confirmBtn">Confirmar</button>
    </div>
  </div>
</div>

<!-- Modal editar propuesta -->
<div class="modal-backdrop" id="editPropModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Editar propuesta</h3>
      <button class="modal-close" onclick="closeEditProp()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editPropId">
      <div class="form-group">
        <label class="form-label">Título</label>
        <input type="text" class="form-control" id="editPropTitulo">
      </div>
      <div class="form-group">
        <label class="form-label">Estado</label>
        <select class="form-control" id="editPropEstado">
          <option value="activa">Activa</option>
          <option value="en_revision">En revisión</option>
          <option value="aprobada">Aprobada</option>
          <option value="rechazada">Rechazada</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Fase del ciclo de vida</label>
        <select class="form-control" id="editPropProgreso">
          <option value="idea">💡 Idea</option>
          <option value="discusion">💬 Discusión</option>
          <option value="mejoras">✏️ Mejoras</option>
          <option value="votacion">🗳️ Votación</option>
          <option value="destacada">⭐ Destacada</option>
        </select>
        <div class="form-hint">Al mover una propuesta a "Destacada" se activa automáticamente su efecto visual y se le avisa al autor.</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeEditProp()">Cancelar</button>
      <button class="btn btn-primary" onclick="saveEditProp()"><i class="fas fa-save"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Modal responder contacto -->
<div class="modal-backdrop" id="contactReplyModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-reply" style="color:var(--verde)"></i> Responder mensaje</h3>
      <button class="modal-close" onclick="closeContactReply()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="contactMsgDetail" style="background:var(--surface);border-radius:var(--radius);padding:1rem;margin-bottom:1rem;font-size:.875rem">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem">
          <div><strong>De:</strong> <span id="cMsgNombre"></span></div>
          <div><strong>Email:</strong> <span id="cMsgEmail"></span></div>
          <div style="grid-column:1/-1"><strong>Asunto:</strong> <span id="cMsgAsunto"></span></div>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:.75rem;color:var(--text-2)" id="cMsgTexto"></div>
      </div>
      <div class="form-group">
        <label class="form-label"><i class="fas fa-pen" style="color:var(--verde)"></i> Tu respuesta</label>
        <textarea id="contactReplyText" class="form-control" rows="5" placeholder="Escribe tu respuesta al usuario..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeContactReply()">Cancelar</button>
      <button class="btn btn-primary" onclick="sendContactReply()"><i class="fas fa-paper-plane"></i> Guardar respuesta</button>
    </div>
  </div>
</div>

<!-- Modal CRUD Categoría -->
<div class="modal-backdrop" id="spamModal">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-broom"></i> Posible spam</h3>
      <button class="modal-close" onclick="spamCerrar()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="spamBody"></div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="spamCerrar()">Cerrar</button>
      <button class="btn btn-primary btn-sm" style="background:#e74c3c;border-color:#e74c3c" onclick="spamEliminar()">
        <i class="fas fa-trash"></i> Eliminar seleccionados
      </button>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="gamModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="gamModalTitulo">Nuevo</h3>
      <button class="modal-close" onclick="gamCerrar()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="gamForm"></div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="gamCerrar()">Cancelar</button>
      <button class="btn btn-primary btn-sm" onclick="gamGuardar()"><i class="fas fa-save"></i> Guardar</button>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="catModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="catModalTitle"><i class="fas fa-tag" style="color:var(--verde)"></i> Nueva categoría</h3>
      <button class="modal-close" onclick="closeCatModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="catId">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" id="catNombre" class="form-control" placeholder="Ej: Infraestructura">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Icono (Font Awesome)</label>
          <input type="text" id="catIcono" class="form-control" placeholder="fas fa-road">
          <div class="form-hint">Preview: <i id="catIconoPreview" class="fas fa-tag"></i></div>
        </div>
        <div class="form-group">
          <label class="form-label">Color</label>
          <input type="color" id="catColor" class="form-control" value="#36c0a1" style="height:44px;padding:.25rem">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción</label>
        <textarea id="catDesc" class="form-control" rows="2" placeholder="Descripción breve de la categoría"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeCatModal()">Cancelar</button>
      <button class="btn btn-primary" onclick="saveCat()"><i class="fas fa-save"></i> Guardar</button>
    </div>
  </div>
</div>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
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
  box.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0">Analizando comentarios…</p>';
  document.getElementById('spamModal').classList.add('open');
  try {
    const r = await fetch('php/admin.php?accion=spam_listar');
    const d = await r.json();
    if (!d.success || !(d.items || []).length) {
      box.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0"><i class="fas fa-check" style="color:#22c55e"></i> No se detectó spam. Todo limpio.</p>';
      return;
    }
    box.innerHTML = `
      <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:.8rem">
        Se detectaron ${d.total} comentarios sospechosos. Revísalos antes de eliminar.
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
    box.innerHTML = '<p style="color:#e74c3c;padding:1rem 0">Error al analizar.</p>';
  }
}
function spamCerrar() { document.getElementById('spamModal').classList.remove('open'); }

async function spamEliminar() {
  const ids = [...document.querySelectorAll('.spam-check:checked')]
    .flatMap(c => c.dataset.ids.split(',').map(Number));
  if (!ids.length) { showToast('No seleccionaste nada', 'error'); return; }
  const okSpam = await askConfirm({
    variante: 'danger', icono: 'fa-broom', titulo: 'Eliminar spam',
    mensaje: `Se eliminarán ${ids.length} comentarios marcados como spam.`,
    detalle: 'Esta acción es permanente y no se puede deshacer.', detalleLabel: 'Atención',
    confirmar: `Sí, eliminar ${ids.length}`,
  });
  if (!okSpam) return;
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'spam_eliminar', ids }),
    });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) { spamCerrar(); loadAdminComentarios(); }
  } catch (e) { showToast('Error de conexión', 'error'); }
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
      if (!d.success) { showToast('No se pudo cargar la configuración', 'error'); return; }
      _gamEsquema = d.entidades;
    } catch (e) { showToast('Error de conexión', 'error'); return; }

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

  head.innerHTML = `<tr><th>ID</th>${cols.map(c => `<th>${c.label}</th>`).join('')}<th>Acciones</th></tr>`;
  body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando…</td></tr>`;

  try {
    const r = await fetch(`php/admin.php?accion=gestion_listar&entidad=${_gamEntidad}`);
    const d = await r.json();
    if (!d.success) { body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;color:#e74c3c">${d.message}</td></tr>`; return; }
    _gamItems = d.items || [];
    if (!_gamItems.length) {
      body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;padding:2rem;color:var(--text-muted)">Todavía no hay ${e.label.toLowerCase()}</td></tr>`;
      return;
    }
    body.innerHTML = _gamItems.map(it => `
      <tr>
        <td><span style="color:var(--text-muted)">#${it.id}</span></td>
        ${cols.map(c => `<td>${gamCelda(c, it[c.name])}</td>`).join('')}
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn edit" onclick="gamEditar(${it.id})" title="Editar"><i class="fas fa-edit"></i></button>
            <button class="admin-action-btn delete" onclick="gamEliminar(${it.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>`).join('');
  } catch (err) {
    body.innerHTML = `<tr><td colspan="${cols.length + 2}" style="text-align:center;color:#e74c3c">Error al cargar</td></tr>`;
  }
}

function gamCelda(campo, valor) {
  if (campo.tipo === 'bool') {
    return valor ? '<span class="gam-badge on">Sí</span>' : '<span class="gam-badge off">No</span>';
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
    input = `<select class="form-control" id="${id}"><option value="true" ${v ? 'selected' : ''}>Sí</option><option value="false" ${!v ? 'selected' : ''}>No</option></select>`;
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
    (item ? 'Editar ' : 'Crear ') + (e.singular || e.label.toLowerCase());
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
  } catch (err) { showToast('Error de conexión', 'error'); }
}

async function gamEliminar(id) {
  const okGam = await askConfirm({
    variante: 'danger', icono: 'fa-trash', titulo: 'Eliminar elemento',
    mensaje: '¿Seguro que quieres eliminarlo?',
    detalle: 'Si algún usuario ya lo tiene, se desactivará en vez de borrarse para no romper su perfil.',
    detalleLabel: 'Ten en cuenta', confirmar: 'Sí, eliminar',
  });
  if (!okGam) return;
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'gestion_eliminar', entidad: _gamEntidad, id }),
    });
    const d = await r.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) gamCargar();
  } catch (err) { showToast('Error de conexión', 'error'); }
}

// ═══════════════════════════════════════════════════════════
//  ESTADÍSTICAS COMPLETAS
// ═══════════════════════════════════════════════════════════
const _n = (v) => (v ?? 0).toLocaleString('es');

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
    if (!d.success) { box.innerHTML = '<p style="color:var(--text-muted)">No se pudieron cargar las estadísticas.</p>'; return; }

    const u = d.usuarios, c = d.contenido, g = d.gamificacion, m = d.moderacion;

    const barras = (d.por_categoria || []).length
      ? (() => {
          const max = Math.max(...d.por_categoria.map(x => x.total));
          return d.por_categoria.map(x => `
            <div class="stat-bar-row">
              <span class="stat-bar-lbl">${x.nombre}</span>
              <div class="stat-bar-track">
                <div class="stat-bar-fill" style="width:${Math.round(x.total / max * 100)}%;background:${x.color || '#36c0a1'}"></div>
              </div>
              <span class="stat-bar-num">${_n(x.total)}</span>
            </div>`).join('');
        })()
      : '<p style="color:var(--text-muted);font-size:.85rem">Todavía no hay propuestas por categoría.</p>';

    box.innerHTML = `
      <h3 class="stat-title"><i class="fas fa-users"></i> Comunidad</h3>
      <div class="stat-grid">
        ${statCard('fa-user-group', '#36c0a1', _n(u.total), 'Usuarios registrados', `+${_n(u.nuevos_30)} en 30 días`)}
        ${statCard('fa-user-check', '#22c55e', _n(u.activos), 'Usuarios activos', `${_n(u.recientes)} entraron en 30 días`)}
        ${statCard('fa-user-slash', '#ef4444', _n(u.suspendidos), 'Suspendidos', '')}
        ${statCard('fa-triangle-exclamation', '#f59e0b', _n(m.alertas_pendientes), 'Alertas por revisar', '')}
      </div>

      <h3 class="stat-title"><i class="fas fa-layer-group"></i> Contenido</h3>
      <div class="stat-grid">
        ${statCard('fa-file-lines', '#4a9eff', _n(c.propuestas), 'Propuestas creadas', `+${_n(c.propuestas_30)} en 30 días`)}
        ${statCard('fa-star', '#f59e0b', _n(c.propuestas_destacadas), 'Propuestas destacadas', `${_n(c.propuestas_votacion)} en votación`)}
        ${statCard('fa-comments', '#8b5cf6', _n(c.debates_activos), 'Debates activos', `${_n(c.debates)} en total`)}
        ${statCard('fa-comment-dots', '#06b6d4', _n(c.comentarios), 'Comentarios', `+${_n(c.comentarios_30)} en 30 días`)}
        ${statCard('fa-reply-all', '#14b8a6', _n(c.respuestas_debate), 'Respuestas en debates', '')}
        ${statCard('fa-eye-slash', '#ef4444', _n(c.propuestas_censuradas + c.comentarios_censurados), 'Contenido oculto', '')}
      </div>

      <h3 class="stat-title"><i class="fas fa-trophy"></i> Gamificación</h3>
      <div class="stat-grid">
        ${statCard('fa-bolt', '#f59e0b', _n(g.xp_total), 'XP total repartido', '')}
        ${statCard('fa-star-half-stroke', '#36c0a1', _n(g.reputacion_media), 'Reputación promedio', `nivel medio ${g.nivel_medio}`)}
        ${statCard('fa-award', '#8b5cf6', _n(g.logros_desbloqueados), 'Logros desbloqueados', '')}
        ${statCard('fa-certificate', '#ef7e22', _n(g.insignias_desbloqueadas), 'Insignias otorgadas', `${_n(g.desafios_completados)} desafíos completados`)}
      </div>

      <h3 class="stat-title"><i class="fas fa-chart-simple"></i> Propuestas por categoría</h3>
      <div class="stat-bars">${barras}</div>`;
  } catch (e) {
    box.innerHTML = '<p style="color:var(--text-muted)">Error de conexión al cargar estadísticas.</p>';
  }
}

// ── Acciones de administración (destacar / ocultar / suspender) ──
async function adminAccion(payload, confirmMsg) {
  if (confirmMsg) {
    const okAcc = await askConfirm({
      variante: 'warning', icono: 'fa-circle-question',
      titulo: 'Confirmar acción', mensaje: confirmMsg, confirmar: 'Sí, continuar',
    });
    if (!okAcc) return false;
  }
  try {
    const r = await fetch('php/admin.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const d = await r.json();
    showToast(d.message || (d.success ? 'Listo' : 'Error'), d.success ? 'success' : 'error');
    return d.success;
  } catch (e) { showToast('Error de conexión', 'error'); return false; }
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
  const razon = await askConfirm({
    variante: 'danger', icono: 'fa-user-slash', titulo: 'Suspender usuario',
    mensaje: 'El usuario no podrá acceder hasta que lo reactives.',
    input: { label: 'Motivo de la suspensión', valor: 'Incumplimiento de las normas de la comunidad',
             placeholder: 'Lo verá el equipo de moderación' },
    confirmar: 'Suspender',
  });
  if (razon === false) return;
  if (await adminAccion({ accion: 'suspender', id, razon })) loadAdminUsuarios();
}

async function reactivarUsuario(id) {
  if (await adminAccion({ accion: 'reactivar', id }, '¿Reactivar a este usuario?')) loadAdminUsuarios();
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
        document.getElementById('kpiTotalVotos').textContent = votos.toLocaleString('es');
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
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1&limit=50');
    const d = await r.json();
    if (!d.success || !d.propuestas.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">No hay propuestas</td></tr>'; return; }
    tbody.innerHTML = d.propuestas.map(p => `
      <tr>
        <td><span style="color:var(--text-muted)">#${p.id}</span></td>
        <td><a href="propuesta.php?id=${p.id}" style="color:var(--verde);font-weight:600">${p.titulo}</a></td>
        <td>${p.autor || '–'}</td>
        <td><span class="badge badge-verde">${p.categoria || '–'}</span></td>
        <td><span class="estado-chip estado-${p.estado}">${p.estado}</span></td>
        <td><span class="progreso-chip progreso-${p.progreso || 'idea'}">${PROGRESO_LABELS[p.progreso || 'idea']}</span></td>
        <td><strong style="color:var(--naranja)">${p.votos}</strong></td>
        <td style="color:var(--text-muted)">${new Date(p.fecha_creacion).toLocaleDateString('es')}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn edit" onclick="openEditProp(${p.id},'${escHtml(p.titulo)}','${p.estado}','${p.progreso || 'idea'}')" title="Editar"><i class="fas fa-edit"></i></button>
            <button class="admin-action-btn" onclick="destacarContenido('propuesta',${p.id})" title="${p.destacada ? 'Quitar destacado' : 'Destacar propuesta'}" style="color:${p.destacada ? '#f59e0b' : 'var(--naranja-500)'}"><i class="fa${p.destacada ? 's' : 'r'} fa-star"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('propuesta','${p.id}','Eliminar propuesta «${escHtml(p.titulo)}»')" title="Eliminar"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#e74c3c">Error al cargar</td></tr>'; }
}
loadAdminStats();
loadAdminPropuestas();

// ── Comentarios admin ────────────────────────────────────
async function loadAdminComentarios() {
  const tbody = document.getElementById('adminComentTable');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr>';
  try {
    const r = await fetch('php/propuestas.php?accion=admin_comentarios');
    const d = await r.json();
    if (!d.success || !d.comentarios || !d.comentarios.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No hay comentarios</td></tr>'; return;
    }
    tbody.innerHTML = d.comentarios.map(c => `
      <tr>
        <td><span style="color:var(--text-muted)">#${c.id}</span></td>
        <td style="max-width:260px"><span style="color:var(--text-2)">${escHtml(c.contenido).substring(0,80)}${c.contenido.length>80?'…':''}</span></td>
        <td>${c.autor || '–'}</td>
        <td><a href="propuesta.php?id=${c.propuesta_id}" style="color:var(--verde)">#${c.propuesta_id}</a></td>
        <td style="color:var(--text-muted)">${new Date(c.fecha_creacion).toLocaleDateString('es')}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn" onclick="destacarContenido('comentario',${c.id})" title="${c.destacado ? 'Quitar destacado' : 'Destacar comentario'}" style="color:${c.destacado ? '#f59e0b' : 'var(--naranja-500)'}"><i class="fa${c.destacado ? 's' : 'r'} fa-star"></i></button>
            <button class="admin-action-btn" onclick="ocultarContenido('comentario',${c.id})" title="Ocultar/mostrar comentario" style="color:#8b5cf6"><i class="fas fa-eye-slash"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('comentario','${c.id}','Eliminar este comentario')" title="Eliminar"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#e74c3c">Error al cargar</td></tr>'; }
}

// ── Usuarios admin ────────────────────────────────────────
async function loadAdminUsuarios() {
  const tbody = document.getElementById('adminUsersTable');
  if (!tbody) return;
  try {
    const r = await fetch('php/auth.php?accion=admin_usuarios');
    const d = await r.json();
    if (!d.success || !d.usuarios || !d.usuarios.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No hay usuarios</td></tr>'; return;
    }
    tbody.innerHTML = d.usuarios.map(u => `
      <tr>
        <td><span style="color:var(--text-muted)">#${u.id}</span></td>
        <td><strong>${escHtml(u.nombre)} ${escHtml(u.apellido)}</strong></td>
        <td style="color:var(--text-muted)">${u.email}</td>
        <td>
          <select class="form-control" style="padding:.3rem .6rem;font-size:.8rem" onchange="changeUserRole(${u.id},this.value)">
            <option value="usuario" ${u.rol==='usuario'?'selected':''}>Usuario</option>
            <option value="moderador" ${u.rol==='moderador'?'selected':''}>Moderador</option>
            <option value="admin" ${u.rol==='admin'?'selected':''}>Admin</option>
          </select>
        </td>
        <td style="color:var(--text-muted)">${new Date(u.fecha_registro).toLocaleDateString('es')}</td>
        <td>
          <div class="admin-actions">
            <button class="admin-action-btn" onclick="${u.activo === false ? `reactivarUsuario(${u.id})` : `suspenderUsuario(${u.id})`}" title="${u.activo === false ? 'Reactivar usuario' : 'Suspender usuario'}" style="color:${u.activo === false ? '#22c55e' : '#ef4444'}"><i class="fas fa-user-${u.activo === false ? 'check' : 'slash'}"></i></button>
            <button class="admin-action-btn delete" onclick="confirmDelete('usuario','${u.id}','Eliminar usuario ${escHtml(u.nombre)}')" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#e74c3c">Error al cargar</td></tr>'; }
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
    if (d.success) showToast('Rol actualizado', 'success');
    else showToast(d.mensaje || 'Error al cambiar rol', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
}

let _alertasCache = [];

const ASK_VARIANTES = {
  danger:  { color: '#e74c3c', icono: 'fa-triangle-exclamation', btn: 'btn-danger' },
  success: { color: '#22c55e', icono: 'fa-circle-check',         btn: 'btn-primary' },
  warning: { color: '#f59e0b', icono: 'fa-eye-slash',            btn: 'btn-primary' },
  info:    { color: '#4a9eff', icono: 'fa-circle-info',          btn: 'btn-primary' },
};
let _askResolver = null;
let _askConInput = false;

function askConfirm(opts = {}) {
  const v = ASK_VARIANTES[opts.variante] || ASK_VARIANTES.danger;
  const modal = document.getElementById('askModal');

  const ico = document.getElementById('askIcon');
  ico.style.background = v.color + '1f';
  ico.style.color = v.color;
  ico.innerHTML = `<i class="fas ${opts.icono || v.icono}"></i>`;

  document.getElementById('askTitle').textContent = opts.titulo || '¿Confirmar acción?';
  document.getElementById('askMsg').textContent   = opts.mensaje || '';

  const det = document.getElementById('askDetail');
  if (opts.detalle) {
    document.getElementById('askDetailLbl').textContent = opts.detalleLabel || 'Contenido afectado';
    document.getElementById('askDetailTxt').textContent = opts.detalle;
    det.style.borderLeftColor = v.color;
    det.hidden = false;
  } else {
    det.hidden = true;
  }

  const inp = document.getElementById('askInput');
  const inpWrap = document.getElementById('askInputWrap');
  if (opts.input) {
    document.getElementById('askInputLbl').textContent = opts.input.label || 'Motivo';
    inp.value = opts.input.valor || '';
    inp.placeholder = opts.input.placeholder || '';
    inpWrap.hidden = false;
  } else {
    inpWrap.hidden = true;
  }

  const ok = document.getElementById('askOk');
  ok.className = `btn btn-sm ${v.btn}`;
  ok.style.background = v.color;
  ok.style.borderColor = v.color;
  ok.innerHTML = `<i class="fas ${opts.icono || v.icono}"></i> ${opts.confirmar || 'Confirmar'}`;
  document.getElementById('askCancel').textContent = opts.cancelar || 'Cancelar';

  modal.classList.add('open');
  _askConInput = !!opts.input;
  setTimeout(() => (opts.input ? inp.focus() : ok.focus()), 60);

  return new Promise((resolve) => { _askResolver = resolve; });
}

function askCerrar(valor) {
  // con campo de texto, confirmar devuelve el texto; cancelar devuelve false
  let salida = valor;
  if (valor === true && _askConInput) salida = document.getElementById('askInput').value.trim();
  document.getElementById('askModal').classList.remove('open');
  _askConInput = false;
  if (_askResolver) { _askResolver(salida); _askResolver = null; }
}

document.getElementById('askOk').addEventListener('click', () => askCerrar(true));
document.getElementById('askCancel').addEventListener('click', () => askCerrar(false));
document.getElementById('askModal').addEventListener('click', (e) => {
  if (e.target.id === 'askModal') askCerrar(false);   // clic fuera = cancelar
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && document.getElementById('askModal').classList.contains('open')) askCerrar(false);
});


document.getElementById('askInput').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') { e.preventDefault(); askCerrar(true); }
});

// ── Confirm + Delete ──────────────────────────────────────
let pendingDelete = null;
const _TIPO_LABEL = { propuesta: 'la propuesta', comentario: 'el comentario', usuario: 'el usuario' };
async function confirmDelete(tipo, id, msg) {
  const ok = await askConfirm({
    variante: 'danger', icono: 'fa-trash',
    titulo: `Eliminar ${_TIPO_LABEL[tipo] || 'este elemento'}`,
    mensaje: 'Esta acción es permanente y no se puede deshacer.',
    detalle: msg || null, detalleLabel: 'Se eliminará',
    confirmar: 'Sí, eliminar',
  });
  if (!ok) return;
  ejecutarBorrado(tipo, id);
}
function closeConfirm() {
  document.getElementById('confirmModal').classList.remove('open');
  pendingDelete = null;
}
async function ejecutarBorrado(tipo, id) {
  try {
    let url = '';
    let body = {};
    if (tipo === 'propuesta') { url = 'php/propuestas.php'; body = { accion: 'eliminar', id }; }
    else if (tipo === 'comentario') { url = 'php/propuestas.php'; body = { accion: 'eliminar_comentario', id }; }
    else if (tipo === 'usuario') { url = 'php/auth.php'; body = { accion: 'eliminar_usuario', id }; }
    const r = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
    const d = await r.json();
    if (d.success) {
      showToast('Eliminado correctamente', 'success');
      if (tipo === 'propuesta') loadAdminPropuestas();
      else if (tipo === 'comentario') loadAdminComentarios();
      else if (tipo === 'usuario') loadAdminUsuarios();
      loadAdminKpis();
    } else showToast(d.mensaje || 'Error al eliminar', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
}

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
    if (d.success) { showToast('Propuesta actualizada', 'success'); closeEditProp(); loadAdminPropuestas(); }
    else showToast(d.mensaje || 'Error al actualizar', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
  el.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
  document.querySelectorAll('#filterAll,#filterUnread').forEach(b => b.classList.remove('btn-primary','btn-outline','btn-ghost'));
  document.getElementById(filter==='unread'?'filterUnread':'filterAll').classList.add('btn-primary');
  document.getElementById(filter==='unread'?'filterAll':'filterUnread').classList.add('btn-ghost');

  const qs = filter==='unread' ? '?leido=0' : '';
  try {
    const r = await fetch('php/contacto.php?accion=listar'+qs);
    const d = await r.json();
    if (!d.success) { el.innerHTML = '<div class="empty-state"><i class="fas fa-envelope-open"></i><p>No hay mensajes.</p></div>'; return; }

    // Update badge
    const unread = d.mensajes.filter(m => !m.leido).length;
    const badge = document.getElementById('contactoBadge');
    if (badge) { badge.textContent = unread; badge.style.display = unread>0?'inline-flex':'none'; }

    if (!d.mensajes.length) {
      el.innerHTML = '<div class="empty-state"><i class="fas fa-envelope-open"></i><p>No hay mensajes'+(filter==='unread'?' sin leer':'')+'.</p></div>';
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
            ${m.respuesta ? `<div style="margin-top:.6rem;padding:.5rem .75rem;background:var(--verde-alpha);border-left:3px solid var(--verde);border-radius:4px;font-size:.8rem;color:var(--verde-700)"><strong>✓ Respondido:</strong> ${escHtml(m.respuesta).substring(0,120)}…</div>` : ''}
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0">
            <span style="font-size:.75rem;color:var(--text-muted)">${m.fecha_formateada}</span>
            <div style="display:flex;gap:.4rem">
              <button onclick="openContactReply(${m.id},'${escHtml(m.nombre)}','${escHtml(m.email)}','${escHtml(m.asunto)}',\`${escHtml(m.mensaje)}\`)" class="admin-action-btn edit" title="Responder/Ver"><i class="fas fa-reply"></i></button>
              ${!m.leido ? `<button onclick="markMsgRead(${m.id})" class="admin-action-btn" style="background:var(--verde-alpha);color:var(--verde-600)" title="Marcar leído"><i class="fas fa-check"></i></button>` : ''}
              <button onclick="deleteMsgConfirm(${m.id})" class="admin-action-btn delete" title="Eliminar"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>`).join('');
  } catch(e) { el.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar mensajes.</p></div>'; }
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
  if (!txt) { showToast('Escribe una respuesta antes de guardar', 'error'); return; }
  try {
    const r = await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'responder', id: currentContactId, respuesta: txt }) });
    const d = await r.json();
    if (d.success) { showToast('Respuesta guardada', 'success'); closeContactReply(); loadContactMessages(); }
    else showToast(d.message||'Error', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
}

async function markMsgRead(id, silent=false) {
  try {
    await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'marcar_leido', id }) });
    if (!silent) { showToast('Marcado como leído', 'success'); loadContactMessages(); }
  } catch(e) {}
}

function deleteMsgConfirm(id) {
  openConfirm('¿Eliminar mensaje?', 'Esta acción no se puede deshacer.', async () => {
    const r = await fetch('php/contacto.php', { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'eliminar', id }) });
    const d = await r.json();
    if (d.success) { showToast('Mensaje eliminado', 'success'); closeConfirm(); loadContactMessages(); }
    else showToast('Error al eliminar', 'error');
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
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No hay categorías</td></tr>';
      return;
    }
    tbody.innerHTML = d.categorias.map(cat => `
      <tr>
        <td>${cat.id}</td>
        <td><i class="${escHtml(cat.icono)}" style="color:${escHtml(cat.color)};font-size:1.2rem"></i></td>
        <td><strong>${escHtml(cat.nombre)}</strong></td>
        <td><span style="display:inline-flex;align-items:center;gap:.4rem"><span style="width:16px;height:16px;border-radius:50%;background:${escHtml(cat.color)};display:inline-block"></span>${escHtml(cat.color)}</span></td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(cat.descripcion||'—')}</td>
        <td><div class="admin-actions">
          <button onclick="openCatModal(${cat.id},'${escHtml(cat.nombre)}','${escHtml(cat.icono)}','${escHtml(cat.color)}','${escHtml(cat.descripcion||'')}')" class="admin-action-btn edit"><i class="fas fa-pen"></i></button>
          <button onclick="deleteCat(${cat.id})" class="admin-action-btn delete"><i class="fas fa-trash"></i></button>
        </div></td>
      </tr>`).join('');
  } catch(e) { showToast('Error cargando categorías', 'error'); }
}

function openCatModal(id=null, nombre='', icono='fas fa-tag', color='#36c0a1', desc='') {
  currentCatId = id;
  document.getElementById('catModalTitle').innerHTML = id
    ? '<i class="fas fa-pen" style="color:var(--verde)"></i> Editar categoría'
    : '<i class="fas fa-plus" style="color:var(--verde)"></i> Nueva categoría';
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
  if (!nombre) { showToast('El nombre es obligatorio', 'error'); return; }
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
    if (d.success) { showToast(currentCatId?'Categoría actualizada':'Categoría creada', 'success'); closeCatModal(); loadAdminCategorias(); }
    else showToast(d.message||'Error', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
}

function deleteCat(id) {
  openConfirm('¿Eliminar categoría?', 'Las propuestas en esta categoría podrían verse afectadas.', async () => {
    const r = await fetch('php/admin_categorias.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({accion:'eliminar', id}) });
    const d = await r.json();
    if (d.success) { showToast('Categoría eliminada', 'success'); closeConfirm(); loadAdminCategorias(); }
    else showToast(d.message||'Error al eliminar', 'error');
  });
}

// ── ALERTAS IA ───────────────────────────────────────────────
async function loadAlertas(soloPendientes = false) {
  const el = document.getElementById('alertasContainer');
  el.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

  document.getElementById('filterAlertasTodas').className = 'btn btn-sm ' + (soloPendientes ? 'btn-ghost' : 'btn-outline');
  document.getElementById('filterAlertasPend').className  = 'btn btn-sm ' + (soloPendientes ? 'btn-outline' : 'btn-ghost');

  try {
    const url = 'php/ia.php?accion=alertas' + (soloPendientes ? '&sin_revisar=1' : '');
    const r = await fetch(url);
    const d = await r.json();

    if (!d.success) { el.innerHTML = '<div class="empty-state"><i class="fas fa-shield-alt"></i><p>Sin permisos para ver alertas.</p></div>'; return; }

    // Actualizar badge
    const badge = document.getElementById('alertasBadge');
    if (badge) { badge.textContent = d.pendientes; badge.style.display = d.pendientes > 0 ? 'inline-flex' : 'none'; }

    if (!d.alertas || !d.alertas.length) {
      el.innerHTML = '<div class="empty-state" style="text-align:center;padding:3rem;color:var(--text-muted)"><i class="fas fa-check-circle" style="font-size:3rem;color:var(--verde);margin-bottom:1rem;display:block"></i><p>No hay alertas' + (soloPendientes ? ' pendientes' : '') + '. ¡Todo limpio! 🎉</p></div>';
      return;
    }

    const severidadColor = { alta: '#e74c3c', media: '#ef7e22', baja: '#36c0a1' };
    const severidadIcon  = { alta: 'fa-exclamation-circle', media: 'fa-exclamation-triangle', baja: 'fa-info-circle' };

    _alertasCache = d.alertas || [];
    el.innerHTML = d.alertas.map(a => `
      <div class="contact-msg-card ${a.revisado ? '' : 'msg-unread'}"
           style="background:var(--bg-card);border:1px solid var(--border);border-left:4px solid ${severidadColor[a.severidad]||'#ef7e22'};border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:.75rem;transition:var(--trans)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;flex-wrap:wrap">
              <span style="background:${severidadColor[a.severidad]||'#ef7e22'}22;color:${severidadColor[a.severidad]||'#ef7e22'};padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase">
                <i class="fas ${severidadIcon[a.severidad]||'fa-exclamation-triangle'}"></i> ${a.severidad}
              </span>
              <span style="background:var(--surface);padding:.2rem .6rem;border-radius:20px;font-size:.75rem;color:var(--text-muted)">
                <i class="fas fa-${a.tipo==='comentario'?'comment':'file-alt'}"></i> ${a.tipo} #${a.referencia_id}
              </span>
              ${a.revisado ? '<span style="background:#36c0a122;color:var(--verde);padding:.2rem .6rem;border-radius:20px;font-size:.75rem"><i class="fas fa-check"></i> Revisado</span>' : '<span style="background:#ef7e2222;color:#ef7e22;padding:.2rem .6rem;border-radius:20px;font-size:.75rem"><i class="fas fa-clock"></i> Pendiente</span>'}
            </div>
            <div style="margin-bottom:.4rem">
              <strong style="font-size:.82rem;color:var(--text-muted)">Razón detectada:</strong>
              <span style="font-size:.85rem;color:var(--text)">${escHtml(a.razon)}</span>
            </div>
            <div style="background:var(--surface);border-radius:8px;padding:.6rem .9rem;font-size:.82rem;color:var(--text-2);line-height:1.5;max-height:80px;overflow:hidden;text-overflow:ellipsis">
              ${escHtml(a.contenido_original).substring(0, 200)}${a.contenido_original.length > 200 ? '…' : ''}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0">
            <span style="font-size:.75rem;color:var(--text-muted)">${a.fecha}</span>
            <div style="display:flex;gap:.4rem">
              <a href="${a.tipo==='comentario'?'propuesta.php?id='+a.referencia_id:'propuesta.php?id='+a.referencia_id}"
                 target="_blank" class="admin-action-btn edit" title="Ver contenido">
                <i class="fas fa-eye"></i>
              </a>
              ${!a.revisado ? `<button onclick="marcarAlertaRevisada(${a.id})" class="admin-action-btn" style="background:#36c0a122;color:var(--verde)" title="Marcar como revisada (dejar publicado)"><i class="fas fa-check"></i></button>` : ''}
              ${!a.revisado ? `<button onclick="aprobarAlerta(${a.id})" class="admin-action-btn" style="background:#4a9eff22;color:#4a9eff" title="Aprobar / restaurar contenido"><i class="fas fa-unlock"></i></button>` : ''}
              ${!a.revisado ? `<button onclick="censurarAlerta(${a.id})" class="admin-action-btn" style="background:#e74c3c22;color:#e74c3c" title="Censurar (ocultar contenido)"><i class="fas fa-ban"></i></button>` : ''}
            </div>
          </div>
        </div>
      </div>
    `).join('');
  } catch(e) {
    el.innerHTML = '<div style="text-align:center;padding:2rem;color:#e74c3c"><i class="fas fa-exclamation-triangle"></i> Error al cargar alertas.</div>';
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
      showToast('Alerta marcada como revisada', 'success');
      loadAlertas();
    } else {
      showToast(d.mensaje || 'Error', 'error');
    }
  } catch(e) { showToast('Error de conexión', 'error'); }
}

async function aprobarAlerta(id) {
  const _a = _alertasCache.find(x => x.id === id);
  const okAp = await askConfirm({
    variante: 'success', icono: 'fa-unlock', titulo: 'Aprobar contenido',
    mensaje: 'Se publicará o restaurará pese a la alerta de la IA, y la alerta quedará cerrada.',
    detalle: _a ? _a.contenido_original : null,
    detalleLabel: _a ? `Contenido marcado · ${_a.razon || 'sin motivo'}` : null,
    confirmar: 'Sí, aprobar',
  });
  if (!okAp) return;
  try {
    const r = await fetch('php/ia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'aprobar', id })
    });
    const d = await r.json();
    if (d.success) {
      showToast(d.message || 'Contenido publicado', 'success');
      loadAlertas();
    } else {
      showToast(d.message || 'Error', 'error');
    }
  } catch(e) { showToast('Error de conexión', 'error'); }
}

async function censurarAlerta(id) {
  const _c = _alertasCache.find(x => x.id === id);
  const okCe = await askConfirm({
    variante: 'warning', icono: 'fa-ban', titulo: 'Censurar contenido',
    mensaje: 'Se ocultará a los usuarios y quedará marcado como retirado por moderación.',
    detalle: _c ? _c.contenido_original : null,
    detalleLabel: _c ? `Contenido marcado · ${_c.razon || 'sin motivo'}` : null,
    confirmar: 'Sí, censurar',
  });
  if (!okCe) return;
  try {
    const r = await fetch('php/ia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'censurar', id })
    });
    const d = await r.json();
    if (d.success) {
      showToast(d.message || 'Contenido censurado', 'success');
      loadAlertas();
    } else {
      showToast(d.message || 'Error', 'error');
    }
  } catch(e) { showToast('Error de conexión', 'error'); }
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
</script>
<style>
.admin-section { display: none; }

/* ── Modal de confirmación (moderación IA / borrados) ── */
.ask-modal { max-width: 420px; text-align: center; padding: 1.9rem 1.6rem 1.5rem; }
.ask-icon {
  width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 1.1rem;
  display: flex; align-items: center; justify-content: center; font-size: 1.6rem;
  animation: askPop .32s cubic-bezier(.34,1.5,.5,1);
}
@keyframes askPop { from { transform: scale(.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.ask-title { font-family: var(--font-display); font-weight: 800; font-size: 1.15rem; color: var(--text); margin: 0 0 .5rem; }
.ask-msg { font-size: .88rem; line-height: 1.55; color: var(--text-muted); margin: 0; }
.ask-detail {
  margin-top: 1rem; text-align: left; background: var(--surface);
  border: 1px solid var(--border); border-left: 3px solid var(--verde);
  border-radius: 10px; padding: .7rem .85rem;
}
.ask-detail-lbl { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .04em;
  text-transform: uppercase; color: var(--text-muted); margin-bottom: .3rem; }
.ask-detail p { margin: 0; font-size: .83rem; line-height: 1.5; color: var(--text);
  max-height: 120px; overflow: auto; word-break: break-word; }
.ask-actions { display: flex; gap: .6rem; justify-content: center; margin-top: 1.4rem; }
.ask-actions .btn { min-width: 120px; justify-content: center; }
#askModal .modal { animation: askIn .28s cubic-bezier(.34,1.4,.5,1); }
@keyframes askIn { from { transform: translateY(16px) scale(.96); opacity: 0; } to { transform: none; opacity: 1; } }
.admin-section.active { display: block; }

.ask-input { margin-top: 1rem; text-align: left; }
.ask-input-lbl { display: block; font-size: .72rem; font-weight: 700; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: .03em; margin-bottom: .35rem; }

.admin-section.active { display: block; }

/* ── Estadísticas del panel ───────────────────────────── */
.stat-title { font-family: var(--font-display); font-weight: 800; font-size: .95rem; color: var(--text);
  margin: 1.75rem 0 .85rem; display: flex; align-items: center; gap: .5rem; }
.stat-title:first-child { margin-top: 0; }
.stat-title i { color: var(--verde); }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .9rem; }
.stat-card { display: flex; align-items: center; gap: .85rem; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: 14px; padding: 1rem 1.1rem; transition: var(--trans); }
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-ico { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center;
  justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
.stat-body { min-width: 0; }
.stat-val { font-family: var(--font-display); font-weight: 800; font-size: 1.4rem; color: var(--text); line-height: 1.1; }
.stat-lbl { font-size: .78rem; color: var(--text-muted); margin-top: .15rem; }
.stat-extra { font-size: .72rem; color: var(--verde-700); margin-top: .2rem; }

.stat-bars { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 1.1rem 1.2rem; }
.stat-bar-row { display: flex; align-items: center; gap: .75rem; margin-bottom: .6rem; }
.stat-bar-row:last-child { margin-bottom: 0; }
.stat-bar-lbl { width: 130px; font-size: .8rem; color: var(--text); flex-shrink: 0;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-bar-track { flex: 1; height: 9px; background: var(--surface); border-radius: 999px; overflow: hidden; }
.stat-bar-fill { height: 100%; border-radius: 999px; transition: width .5s ease; }
.stat-bar-num { width: 44px; text-align: right; font-size: .78rem; font-weight: 700; color: var(--text-muted); }

/* Marca de contenido destacado en las tablas */
.admin-destacado { color: #f59e0b; }

/* ── Gestión de gamificación ──────────────────────────── */
.gam-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.1rem; }
.gam-chip { display: inline-flex; align-items: center; gap: .45rem; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: 999px; padding: .45rem .95rem; font-size: .82rem;
  font-weight: 600; color: var(--text-muted); cursor: pointer; transition: var(--trans); }
.gam-chip:hover { border-color: var(--verde-200); color: var(--text); }
.gam-chip.active { background: var(--grad-primary); border-color: transparent; color: #fff; }
.gam-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .9rem; }
.gam-titulo { font-family: var(--font-display); font-weight: 800; font-size: 1rem; color: var(--text); margin: 0; }
.gam-badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
.gam-badge.on  { background: #22c55e22; color: #22c55e; }
.gam-badge.off { background: #ef444422; color: #ef4444; }
.spam-item { display: flex; gap: .7rem; align-items: flex-start; padding: .7rem .8rem; border: 1px solid var(--border);
  border-radius: 10px; margin-bottom: .55rem; cursor: pointer; transition: var(--trans); }
.spam-item:hover { border-color: var(--verde-200); background: var(--surface); }
.spam-motivo { font-size: .78rem; font-weight: 700; color: #f59e0b; margin-bottom: .2rem; }
.spam-extracto { font-size: .82rem; color: var(--text-muted); line-height: 1.4; }
.gam-color { display: inline-block; width: 14px; height: 14px; border-radius: 4px;
  border: 1px solid var(--border); vertical-align: middle; }
</style>
<?php echo view('layouts.footer')->render(); ?>
</body>
</html>
