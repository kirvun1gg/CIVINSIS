<?php
// $usuarioLogueado, $usuarioId, $usuarioNombre, $usuarioRol y $usuarioAvatar
// los inyecta el View Composer global en TODAS las vistas
// (app/Providers/AppServiceProvider.php::boot()). El valor por defecto de
// abajo nunca se usa en producción - solo evita que el IDE marque la
// variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
$usuarioId       = $usuarioId ?? null;
$usuarioNombre   = $usuarioNombre ?? '';
$usuarioRol      = $usuarioRol ?? 'invitado';
$usuarioAvatar   = $usuarioAvatar ?? null;
$id = (int) ($debateId ?? ($_GET['id'] ?? 0));
if (!$id) { header('Location: debates.php'); exit; }
$iniciales = civinsis_iniciales($usuarioNombre, $usuarioLogueado);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.debate.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
</head>
<body data-debate-id="<?= $id ?>" data-usuario-id="<?= $usuarioLogueado ? (int)$usuarioId : '' ?>" data-es-mod="<?= in_array($usuarioRol ?? '', ['admin','moderador']) ? 'true' : 'false' ?>">

<?php echo view('layouts.navbar', ['activeNav' => 'debates'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 1.5rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:860px">

    <div style="margin-bottom:1.5rem;font-size:.85rem;color:var(--text-muted)">
      <a href="debates.php" style="color:var(--verde-500)"><i class="fas fa-arrow-left"></i> <?= __('civinsis.debate.volver') ?></a>
    </div>

    <div id="debateHeader">
      <div style="display:flex;flex-direction:column;gap:1rem">
        <div class="skeleton" style="height:220px;border-radius:20px"></div>
      </div>
    </div>

    <!-- Resumen IA -->
    <div id="resumenIABox" class="resumen-ia-box" style="display:none"></div>

    <!-- Sección de respuestas -->
    <div class="comments-section" id="respuestasSection" style="display:none">
      <div class="respuestas-toolbar">
        <h3 class="comments-title">
          <i class="fas fa-comments" style="color:var(--verde-500)"></i>
          <?= __('civinsis.debate.respuestas') ?> (<span id="respuestasCount">0</span>)
        </h3>
        <div class="respuestas-orden">
          <button class="orden-btn active" data-orden="relevantes" onclick="DebateDetail.cambiarOrden('relevantes', this)"><?= __('civinsis.debate.orden_relevantes') ?></button>
          <button class="orden-btn" data-orden="recientes" onclick="DebateDetail.cambiarOrden('recientes', this)"><?= __('civinsis.debate.orden_recientes') ?></button>
          <button class="orden-btn" data-orden="votadas" onclick="DebateDetail.cambiarOrden('votadas', this)"><?= __('civinsis.debate.orden_votadas') ?></button>
        </div>
      </div>

      <?php if ($usuarioLogueado): ?>
      <div class="comment-form" id="respuestaForm">
        <div id="citaPreview" class="cita-preview" style="display:none"></div>
        <div style="display:flex;gap:1rem;align-items:flex-start">
          <div class="comment-avatar" style="flex-shrink:0">
            <?php if (!empty($usuarioAvatar)): ?>
              <img src="<?= htmlspecialchars($usuarioAvatar) ?>" alt="Avatar">
            <?php else: ?>
              <?= $iniciales ?>
            <?php endif; ?>
          </div>
          <div style="flex:1">
            <textarea id="respuestaText" placeholder="<?= __('civinsis.debate.respuesta_placeholder') ?>" rows="3"></textarea>
            <div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center">
              <span id="replyingToLabel" style="font-size:.8rem;color:var(--text-muted)"></span>
              <button class="btn btn-primary btn-sm" onclick="DebateDetail.enviarRespuesta(<?= $id ?>)">
                <i class="fas fa-paper-plane"></i> <?= __('civinsis.debate.publicar_respuesta') ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div style="background:var(--verde-alpha);border:1px solid var(--verde-200);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;text-align:center">
        <p style="font-size:.875rem;color:var(--verde-700);margin-bottom:.5rem">
          <i class="fas fa-info-circle"></i> <?= __('civinsis.debate.inicia_sesion_participar') ?>
        </p>
        <a href="auth.php" class="btn btn-primary btn-sm"><?= __('civinsis.comun.iniciar_sesion') ?></a>
      </div>
      <?php endif; ?>

      <div id="respuestasList"></div>
    </div>

  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/utils.js"></script>
<script src="js/debates.js"></script>
</body>
</html>
