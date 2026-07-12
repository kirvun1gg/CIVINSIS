<?php
$id = (int) ($debateId ?? ($_GET['id'] ?? 0));
if (!$id) { header('Location: debates.php'); exit; }
$iniciales = $usuarioLogueado ? strtoupper(substr($usuarioNombre, 0, 1)) : 'U';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Debate – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'debates'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 1.5rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:860px">

    <div style="margin-bottom:1.5rem;font-size:.85rem;color:var(--text-muted)">
      <a href="debates.php" style="color:var(--verde-500)"><i class="fas fa-arrow-left"></i> Volver a debates</a>
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
          Respuestas (<span id="respuestasCount">0</span>)
        </h3>
        <div class="respuestas-orden">
          <button class="orden-btn active" data-orden="relevantes" onclick="DebateDetail.cambiarOrden('relevantes', this)">Relevantes</button>
          <button class="orden-btn" data-orden="recientes" onclick="DebateDetail.cambiarOrden('recientes', this)">Recientes</button>
          <button class="orden-btn" data-orden="votadas" onclick="DebateDetail.cambiarOrden('votadas', this)">Más votadas</button>
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
            <textarea id="respuestaText" placeholder="Comparte tu punto de vista sobre este debate..." rows="3"></textarea>
            <div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center">
              <span id="replyingToLabel" style="font-size:.8rem;color:var(--text-muted)"></span>
              <button class="btn btn-primary btn-sm" onclick="DebateDetail.enviarRespuesta(<?= $id ?>)">
                <i class="fas fa-paper-plane"></i> Publicar respuesta
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div style="background:var(--verde-alpha);border:1px solid var(--verde-200);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;text-align:center">
        <p style="font-size:.875rem;color:var(--verde-700);margin-bottom:.5rem">
          <i class="fas fa-info-circle"></i> Inicia sesión para participar en el debate
        </p>
        <a href="auth.php" class="btn btn-primary btn-sm">Iniciar sesión</a>
      </div>
      <?php endif; ?>

      <div id="respuestasList"></div>
    </div>

  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/debates.js"></script>
<script>DebateDetail.init(<?= $id ?>, <?= $usuarioLogueado ? (int)$usuarioId : 'null' ?>, <?= json_encode(in_array($usuarioRol ?? '', ['admin','moderador'])) ?>);</script>
</body>
</html>
