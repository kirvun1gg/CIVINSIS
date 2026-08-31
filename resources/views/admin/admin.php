<?php
// $usuarioNombre y $usuarioRol los inyecta el View Composer global en TODAS
// las vistas (app/Providers/AppServiceProvider.php::boot()). El valor por
// defecto de abajo nunca se usa en producción - solo evita que el IDE marque
// la variable como indefinida y sirve de red de seguridad.
$usuarioNombre = $usuarioNombre ?? '';
$usuarioRol    = $usuarioRol ?? 'invitado';
$iniciales = civinsis_iniciales($usuarioNombre);
$esAdmin   = ($usuarioRol === 'admin');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.admin.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/admin.css">
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
          <?= __('civinsis.admin.panel_titulo') ?>
          <span class="admin-badge"><?= ucfirst($usuarioRol) ?></span>
        </h1>
        <p style="font-size:.875rem;color:var(--text-muted)"><?= __('civinsis.admin.panel_desc') ?></p>
      </div>
    </div>

    <!-- KPIs admin -->
    <div class="dash-kpi-grid" id="adminKpis">
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalProp">–</div><div class="kpi-label"><i class="fas fa-file-alt"></i> <?= __('civinsis.admin.kpi_propuestas') ?></div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalComent">–</div><div class="kpi-label"><i class="fas fa-comments"></i> <?= __('civinsis.admin.kpi_comentarios') ?></div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalUsers">–</div><div class="kpi-label"><i class="fas fa-users"></i> <?= __('civinsis.admin.kpi_usuarios') ?></div></div>
      <div class="kpi-card"><div class="kpi-num" id="kpiTotalVotos">–</div><div class="kpi-label"><i class="fas fa-arrow-up"></i> <?= __('civinsis.admin.kpi_votos_totales') ?></div></div>
    </div>

    <!-- Tabs -->
    <div class="profile-tabs" style="margin-bottom:1.5rem">
      <button class="profile-tab active" data-admin-tab="estadisticas">
        <i class="fas fa-chart-line"></i> <?= __('civinsis.admin.tab_estadisticas') ?>
      </button>
      <button class="profile-tab" data-admin-tab="propuestas">
        <i class="fas fa-file-alt"></i> <?= __('civinsis.admin.tab_propuestas') ?>
      </button>
      <button class="profile-tab" data-admin-tab="comentarios">
        <i class="fas fa-comments"></i> <?= __('civinsis.admin.tab_comentarios') ?>
      </button>
      <?php if ($esAdmin): ?>
      <button class="profile-tab" data-admin-tab="usuarios">
        <i class="fas fa-users"></i> <?= __('civinsis.admin.tab_usuarios') ?>
      </button>
      <button class="profile-tab" data-admin-tab="contacto">
        <i class="fas fa-envelope"></i> <?= __('civinsis.admin.tab_contacto') ?> <span class="msg-badge" id="contactoBadge" style="display:none">0</span>
      </button>
      <button class="profile-tab" data-admin-tab="categorias">
        <i class="fas fa-tags"></i> <?= __('civinsis.admin.tab_categorias') ?>
      </button>
      <button class="profile-tab" data-admin-tab="gamificacion">
        <i class="fas fa-trophy"></i> <?= __('civinsis.admin.tab_gamificacion') ?>
      </button>
      <button class="profile-tab" data-admin-tab="alertas" id="tabAlertas">
        <i class="fas fa-robot"></i> <?= __('civinsis.admin.tab_alertas') ?> <span class="msg-badge" id="alertasBadge" style="display:none">0</span>
      </button>
      <?php endif; ?>
    </div>

    <!-- Tab: Estadísticas -->
    <div class="admin-section active" id="admin-tab-estadisticas">
      <div id="statsBox">
        <div style="text-align:center;padding:3rem 0;color:var(--text-muted)">
          <i class="fas fa-chart-line fa-bounce"></i> <?= __('civinsis.admin.cargando_estadisticas') ?>
        </div>
      </div>
    </div>

    <!-- Tab: Propuestas -->
    <div class="admin-section" id="admin-tab-propuestas">
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th><?= __('civinsis.comun.col_id') ?></th><th><?= __('civinsis.comun.col_titulo') ?></th><th><?= __('civinsis.comun.col_autor') ?></th><th><?= __('civinsis.comun.col_categoria') ?></th><th><?= __('civinsis.comun.col_estado') ?></th><th><?= __('civinsis.comun.col_votos') ?></th><th><?= __('civinsis.comun.col_fecha') ?></th><th><?= __('civinsis.comun.col_acciones') ?></th>
            </tr>
          </thead>
          <tbody id="adminPropTable">
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab: Comentarios -->
    <div class="admin-section" id="admin-tab-comentarios">
      <div class="gam-bar">
        <h3 class="gam-titulo"><?= __('civinsis.admin.comentarios_titulo') ?></h3>
        <button class="btn btn-outline btn-sm" onclick="spamAbrir()"><i class="fas fa-broom"></i> <?= __('civinsis.admin.limpiar_spam') ?></button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th><?= __('civinsis.comun.col_id') ?></th><th><?= __('civinsis.admin.col_contenido') ?></th><th><?= __('civinsis.comun.col_autor') ?></th><th><?= __('civinsis.admin.col_propuesta') ?></th><th><?= __('civinsis.comun.col_fecha') ?></th><th><?= __('civinsis.comun.col_acciones') ?></th>
            </tr>
          </thead>
          <tbody id="adminComentTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr>
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
              <th><?= __('civinsis.comun.col_id') ?></th><th><?= __('civinsis.admin.col_nombre') ?></th><th><?= __('civinsis.admin.col_email') ?></th><th><?= __('civinsis.admin.col_rol') ?></th><th><?= __('civinsis.admin.col_registro') ?></th><th><?= __('civinsis.comun.col_acciones') ?></th>
            </tr>
          </thead>
          <tbody id="adminUsersTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr>
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
        <h3 class="gam-titulo" id="gamTitulo"><?= __('civinsis.admin.desafios_titulo') ?></h3>
        <button class="btn btn-primary btn-sm" onclick="gamNuevo()"><i class="fas fa-plus"></i> <?= __('civinsis.admin.nuevo') ?></button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead id="gamHead"></thead>
          <tbody id="gamBody"><tr><td style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr></tbody>
        </table>
      </div>
    </div>

    <div class="admin-section" id="admin-tab-alertas">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700"><i class="fas fa-robot" style="color:var(--verde)"></i> <?= __('civinsis.admin.alertas_titulo') ?></h3>
          <p style="font-size:.8rem;color:var(--text-muted)"><?= __('civinsis.admin.alertas_desc') ?></p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center">
          <button onclick="loadAlertas(false)" class="btn btn-outline btn-sm" id="filterAlertasTodas"><?= __('civinsis.comun.todas') ?></button>
          <button onclick="loadAlertas(true)"  class="btn btn-ghost  btn-sm" id="filterAlertasPend"><?= __('civinsis.admin.filtro_pendientes') ?></button>
        </div>
      </div>
      <div id="alertasContainer">
        <div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> <?= __('civinsis.comun.cargando') ?></div>
      </div>
    </div>

    <?php if ($esAdmin): ?>
    <!-- Tab: Contacto -->
    <div class="admin-section" id="admin-tab-contacto">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700"><?= __('civinsis.admin.contacto_titulo') ?></h3>
          <p style="font-size:.8rem;color:var(--text-muted)"><?= __('civinsis.admin.contacto_desc') ?></p>
        </div>
        <div style="display:flex;gap:.5rem">
          <button onclick="loadContactMessages('all')" class="btn btn-outline btn-sm" id="filterAll"><?= __('civinsis.comun.todos') ?></button>
          <button onclick="loadContactMessages('unread')" class="btn btn-ghost btn-sm" id="filterUnread"><?= __('civinsis.admin.filtro_sin_leer') ?></button>
        </div>
      </div>
      <div id="contactMessages">
        <div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fas fa-spinner fa-spin"></i> <?= __('civinsis.comun.cargando') ?></div>
      </div>
    </div>

    <!-- Tab: Categorías -->
    <div class="admin-section" id="admin-tab-categorias">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
        <div>
          <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700"><?= __('civinsis.admin.categorias_titulo') ?></h3>
          <p style="font-size:.8rem;color:var(--text-muted)"><?= __('civinsis.admin.categorias_desc') ?></p>
        </div>
        <button onclick="openCatModal()" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= __('civinsis.admin.nueva_categoria') ?></button>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th><?= __('civinsis.comun.col_id') ?></th><th><?= __('civinsis.admin.col_icono') ?></th><th><?= __('civinsis.admin.col_nombre') ?></th><th><?= __('civinsis.admin.col_color') ?></th><th><?= __('civinsis.admin.col_descripcion') ?></th><th><?= __('civinsis.comun.col_acciones') ?></th></tr>
          </thead>
          <tbody id="adminCatTable">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)"><?= __('civinsis.comun.cargando') ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<!-- Modal de confirmación -->
<div class="modal-backdrop" id="confirmModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="confirmTitle"><?= __('civinsis.admin.confirmar_accion') ?></h3>
      <button class="modal-close" onclick="closeConfirm()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p id="confirmMsg" style="color:var(--text-muted)"></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeConfirm()"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-danger" id="confirmBtn"><?= __('civinsis.comun.confirmar') ?></button>
    </div>
  </div>
</div>

<!-- Modal editar propuesta -->
<div class="modal-backdrop" id="editPropModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><?= __('civinsis.admin.editar_propuesta') ?></h3>
      <button class="modal-close" onclick="closeEditProp()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editPropId">
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.admin.campo_titulo') ?></label>
        <input type="text" class="form-control" id="editPropTitulo">
      </div>
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.admin.campo_estado') ?></label>
        <select class="form-control" id="editPropEstado">
          <option value="activa"><?= __('civinsis.admin.estado_activa') ?></option>
          <option value="en_revision"><?= __('civinsis.admin.estado_en_revision') ?></option>
          <option value="aprobada"><?= __('civinsis.admin.estado_aprobada') ?></option>
          <option value="rechazada"><?= __('civinsis.admin.estado_rechazada') ?></option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.admin.campo_fase') ?></label>
        <select class="form-control" id="editPropProgreso">
          <option value="idea">💡 <?= __('civinsis.admin.fase_idea') ?></option>
          <option value="discusion">💬 <?= __('civinsis.admin.fase_discusion') ?></option>
          <option value="mejoras">✏️ <?= __('civinsis.admin.fase_mejoras') ?></option>
          <option value="votacion">🗳️ <?= __('civinsis.admin.fase_votacion') ?></option>
          <option value="destacada">⭐ <?= __('civinsis.admin.fase_destacada') ?></option>
        </select>
        <div class="form-hint"><?= __('civinsis.admin.fase_hint') ?></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeEditProp()"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-primary" onclick="saveEditProp()"><i class="fas fa-save"></i> <?= __('civinsis.comun.guardar') ?></button>
    </div>
  </div>
</div>

<!-- Modal responder contacto -->
<div class="modal-backdrop" id="contactReplyModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-reply" style="color:var(--verde)"></i> <?= __('civinsis.admin.responder_mensaje') ?></h3>
      <button class="modal-close" onclick="closeContactReply()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="contactMsgDetail" style="background:var(--surface);border-radius:var(--radius);padding:1rem;margin-bottom:1rem;font-size:.875rem">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem">
          <div><strong><?= __('civinsis.admin.de') ?></strong> <span id="cMsgNombre"></span></div>
          <div><strong><?= __('civinsis.admin.email') ?></strong> <span id="cMsgEmail"></span></div>
          <div style="grid-column:1/-1"><strong><?= __('civinsis.admin.asunto') ?></strong> <span id="cMsgAsunto"></span></div>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:.75rem;color:var(--text-2)" id="cMsgTexto"></div>
      </div>
      <div class="form-group">
        <label class="form-label"><i class="fas fa-pen" style="color:var(--verde)"></i> <?= __('civinsis.admin.tu_respuesta') ?></label>
        <textarea id="contactReplyText" class="form-control" rows="5" placeholder="<?= __('civinsis.admin.respuesta_placeholder') ?>"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeContactReply()"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-primary" onclick="sendContactReply()"><i class="fas fa-paper-plane"></i> <?= __('civinsis.admin.guardar_respuesta') ?></button>
    </div>
  </div>
</div>

<!-- Modal CRUD Categoría -->
<div class="modal-backdrop" id="spamModal">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-broom"></i> <?= __('civinsis.admin.posible_spam') ?></h3>
      <button class="modal-close" onclick="spamCerrar()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="spamBody"></div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="spamCerrar()"><?= __('civinsis.comun.cerrar') ?></button>
      <button class="btn btn-primary btn-sm" style="background:#e74c3c;border-color:#e74c3c" onclick="spamEliminar()">
        <i class="fas fa-trash"></i> <?= __('civinsis.admin.eliminar_seleccionados') ?>
      </button>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="gamModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="gamModalTitulo"><?= __('civinsis.comun.nuevo') ?></h3>
      <button class="modal-close" onclick="gamCerrar()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="gamForm"></div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="gamCerrar()"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-primary btn-sm" onclick="gamGuardar()"><i class="fas fa-save"></i> <?= __('civinsis.comun.guardar') ?></button>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="catModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="catModalTitle"><i class="fas fa-tag" style="color:var(--verde)"></i> <?= __('civinsis.admin.nueva_categoria') ?></h3>
      <button class="modal-close" onclick="closeCatModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="catId">
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.admin.campo_nombre_req') ?></label>
        <input type="text" id="catNombre" class="form-control" placeholder="<?= __('civinsis.admin.categoria_nombre_placeholder') ?>">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label"><?= __('civinsis.admin.campo_icono') ?></label>
          <input type="text" id="catIcono" class="form-control" placeholder="fas fa-road">
          <div class="form-hint">Preview: <i id="catIconoPreview" class="fas fa-tag"></i></div>
        </div>
        <div class="form-group">
          <label class="form-label"><?= __('civinsis.admin.campo_color') ?></label>
          <input type="color" id="catColor" class="form-control" value="#36c0a1" style="height:44px;padding:.25rem">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label"><?= __('civinsis.admin.campo_descripcion_breve') ?></label>
        <textarea id="catDesc" class="form-control" rows="2" placeholder="<?= __('civinsis.admin.categoria_desc_placeholder') ?>"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeCatModal()"><?= __('civinsis.comun.cancelar') ?></button>
      <button class="btn btn-primary" onclick="saveCat()"><i class="fas fa-save"></i> <?= __('civinsis.comun.guardar') ?></button>
    </div>
  </div>
</div>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/admin.js"></script>
<?php echo view('layouts.footer')->render(); ?>
</body>
</html>
