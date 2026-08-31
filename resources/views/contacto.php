<?php
// $usuarioLogueado y $usuarioNombre los inyecta el View Composer global en
// TODAS las vistas (app/Providers/AppServiceProvider.php::boot()). El valor
// por defecto de abajo nunca se usa en producción - solo evita que el IDE
// marque la variable como indefinida y sirve de red de seguridad.
$usuarioLogueado = $usuarioLogueado ?? false;
$usuarioNombre   = $usuarioNombre ?? '';
$activeNav = 'contacto';
$asunto_prefill = htmlspecialchars($_GET['asunto'] ?? '');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.nav.contacto') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php echo view('layouts.navbar')->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 3rem);padding-bottom:5rem;min-height:100vh">
  <div class="container" style="max-width:960px">

    <div class="text-center reveal" style="margin-bottom:3.5rem">
      <span class="section-label"><?= __('civinsis.contacto.section_label') ?></span>
      <h1 class="section-title"><?= __('civinsis.contacto.titulo') ?></h1>
      <p style="color:var(--text-muted);max-width:520px;margin:0 auto;font-size:1rem">
        <?= __('civinsis.contacto.subtitulo') ?>
      </p>
    </div>

    <div class="contacto-layout">

      <!-- Formulario -->
      <div class="contacto-form-wrap reveal">
        <div class="contacto-card">
          <h2 class="contacto-card-title"><i class="fas fa-paper-plane"></i> <?= __('civinsis.contacto.card_titulo') ?></h2>

          <div id="contactoSuccess" class="contacto-success" style="display:none">
            <div class="contacto-success-icon">🎉</div>
            <h3><?= __('civinsis.contacto.exito_titulo') ?></h3>
            <p><?= __('civinsis.contacto.exito_desc') ?></p>
            <button class="btn btn-outline" onclick="resetForm()" style="margin-top:1rem"><i class="fas fa-redo"></i> <?= __('civinsis.contacto.enviar_otro') ?></button>
          </div>

          <form id="contactoForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem" class="contact-name-grid">
              <div class="form-group">
                <label class="form-label"><i class="fas fa-user" style="color:var(--verde)"></i> <?= __('civinsis.contacto.nombre') ?></label>
                <input type="text" id="cNombre" class="form-control" placeholder="<?= __('civinsis.contacto.nombre_placeholder') ?>"
                  value="<?= $usuarioLogueado ? htmlspecialchars($usuarioNombre) : '' ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope" style="color:var(--naranja)"></i> <?= __('civinsis.contacto.correo') ?></label>
                <input type="email" id="cEmail" class="form-control" placeholder="tu@correo.com" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label"><i class="fas fa-tag" style="color:var(--verde)"></i> <?= __('civinsis.contacto.asunto') ?></label>
              <select id="cAsunto" class="form-control" required>
                <option value=""><?= __('civinsis.contacto.asunto_placeholder') ?></option>
                <option value="Consulta general" <?= $asunto_prefill==='Consulta general'?'selected':'' ?>><?= __('civinsis.contacto.asunto_consulta_general') ?></option>
                <option value="Reporte de contenido" <?= $asunto_prefill==='Reporte de contenido'?'selected':'' ?>><?= __('civinsis.contacto.asunto_reporte') ?></option>
                <option value="Problema técnico" <?= $asunto_prefill==='Problema técnico'?'selected':'' ?>><?= __('civinsis.contacto.asunto_problema_tecnico') ?></option>
                <option value="Sugerencia de mejora"><?= __('civinsis.contacto.asunto_sugerencia') ?></option>
                <option value="Cuenta suspendida"><?= __('civinsis.contacto.asunto_cuenta_suspendida') ?></option>
                <option value="Colaboración"><?= __('civinsis.contacto.asunto_colaboracion') ?></option>
                <option value="Prensa"><?= __('civinsis.contacto.asunto_prensa') ?></option>
                <option value="Otro"><?= __('civinsis.contacto.asunto_otro') ?></option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label"><i class="fas fa-comment-alt" style="color:var(--naranja)"></i> <?= __('civinsis.contacto.mensaje') ?></label>
              <textarea id="cMensaje" class="form-control" rows="6"
                placeholder="<?= __('civinsis.contacto.mensaje_placeholder') ?>"
                maxlength="2000" required oninput="updateMsgCount()"></textarea>
              <div class="form-hint"><span id="msgCount">0</span>/2000 <?= __('civinsis.contacto.caracteres') ?></div>
            </div>

            <div class="contacto-privacy">
              <i class="fas fa-shield-alt"></i>
              <span><?= __('civinsis.contacto.privacidad_nota') ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center" id="cSubmitBtn">
              <i class="fas fa-paper-plane"></i> <?= __('civinsis.contacto.enviar_mensaje') ?>
            </button>
          </form>
        </div>
      </div>

      <!-- Sidebar de info -->
      <aside class="contacto-sidebar">
        <div class="contacto-info-card reveal">
          <h3><i class="fas fa-clock"></i> <?= __('civinsis.contacto.tiempo_respuesta') ?></h3>
          <p><?= __('civinsis.contacto.tiempo_respuesta_desc') ?></p>
        </div>

<div class="contacto-info-card reveal">
          <h3><i class="fas fa-question-circle"></i> <?= __('civinsis.contacto.antes_de_escribir') ?></h3>
          <p><?= __('civinsis.contacto.antes_de_escribir_desc') ?></p>
          <a href="faq.php" class="btn btn-ghost btn-sm" style="margin-top:.75rem;width:100%;justify-content:center">
            <i class="fas fa-book"></i> <?= __('civinsis.contacto.ver_faq') ?>
          </a>
        </div>

        <div class="contacto-info-card contacto-info-social reveal">
          <h3><i class="fas fa-share-alt"></i> <?= __('civinsis.contacto.siguenos') ?></h3>
          <div class="social-links" style="justify-content:flex-start;margin-top:.75rem">
            <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          </div>
        </div>
      </aside>

    </div>
  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/contacto.js"></script>
</body>
</html>
