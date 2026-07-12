<?php
// propuesta.php - Vista de detalle de una propuesta
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: dashboard.php'); exit; }

$iniciales = $usuarioLogueado ? strtoupper(substr($usuarioNombre, 0, 1)) : 'U';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propuesta – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'propuestas'])->render(); ?>

<!-- Contenido -->
<main style="padding-top:calc(var(--nav-height) + 1.5rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:860px">

    <!-- Breadcrumb -->
    <div style="margin-bottom:1.5rem;font-size:.85rem;color:var(--text-muted);display:flex;align-items:center;gap:.5rem">
      <a href="dashboard.php" style="color:var(--verde-500)"><i class="fas fa-arrow-left"></i> Volver a propuestas</a>
    </div>

    <!-- Detalle (se carga con JS) -->
    <div id="detailContent">
      <div style="display:flex;flex-direction:column;gap:1rem">
        <div class="skeleton" style="height:240px;border-radius:20px"></div>
        <div class="skeleton" style="height:180px;border-radius:20px"></div>
      </div>
    </div>

    <!-- Sección de comentarios -->
    <div class="comments-section" id="commentsSection" style="display:none">
      <h3 class="comments-title">
        <i class="fas fa-comments" style="color:var(--verde-500)"></i>
        Comentarios (<span class="comments-count">0</span>)
      </h3>

      <?php if ($usuarioLogueado): ?>
      <div class="comment-form">
        <div style="display:flex;gap:1rem;align-items:flex-start">
          <div class="comment-avatar" style="flex-shrink:0">
            <?php if (!empty($usuarioAvatar)): ?>
              <img src="<?= htmlspecialchars($usuarioAvatar) ?>" alt="Avatar">
            <?php else: ?>
              <?= $iniciales ?>
            <?php endif; ?>
          </div>
          <div style="flex:1">
            <textarea id="commentText" placeholder="Comparte tu opinión sobre esta propuesta..." rows="3"></textarea>
            <div style="margin-top:.75rem;display:flex;justify-content:flex-end">
              <button class="btn btn-primary btn-sm" onclick="ProposalDetail.submitComment(<?= $id ?>)">
                <i class="fas fa-paper-plane"></i> Publicar comentario
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div style="background:var(--verde-alpha);border:1px solid var(--verde-200);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;text-align:center">
        <p style="font-size:.875rem;color:var(--verde-700);margin-bottom:.5rem">
          <i class="fas fa-info-circle"></i> Inicia sesión para comentar
        </p>
        <a href="auth.php" class="btn btn-primary btn-sm">Iniciar sesión</a>
      </div>
      <?php endif; ?>

      <div id="commentsList">
        <!-- Comentarios se cargan con JS -->
      </div>
    </div>

  </div>
</main>

<!-- Modal de edición -->
<div class="modal-backdrop" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-pen" style="color:var(--verde)"></i> Editar propuesta</h3>
      <button class="modal-close" onclick="Modal.close('modalEdit')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Título</label>
        <input type="text" id="editTitulo" class="form-control" placeholder="Título de la propuesta">
      </div>
      <div class="form-group">
        <label class="form-label">Descripción corta</label>
        <textarea id="editDescripcion" class="form-control" rows="2" placeholder="Resumen breve"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Contenido completo</label>
        <textarea id="editContenido" class="form-control" rows="6" placeholder="Describe tu propuesta en detalle"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Categoría</label>
        <select id="editCategoria" class="form-control">
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modalEdit')">Cancelar</button>
      <button class="btn btn-primary" onclick="ProposalDetail.saveEdit(<?= $id ?>)">
        <i class="fas fa-save"></i> Guardar cambios
      </button>
    </div>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
// Mostrar sección de comentarios una vez cargue el detalle
const origInit = ProposalDetail.init.bind(ProposalDetail);
ProposalDetail.init = async function() {
  await origInit();
  document.getElementById('commentsSection').style.display = 'block';
};
</script>
</body>
</html>
