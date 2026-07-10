<?php
$activeNav = 'contacto';
$asunto_prefill = htmlspecialchars($_GET['asunto'] ?? '');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php echo view('layouts.navbar')->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 3rem);padding-bottom:5rem;min-height:100vh">
  <div class="container" style="max-width:960px">

    <div class="text-center reveal" style="margin-bottom:3.5rem">
      <span class="section-label">Comunícate con nosotros</span>
      <h1 class="section-title">Estamos <span>aquí para ayudarte</span></h1>
      <p style="color:var(--text-muted);max-width:520px;margin:0 auto;font-size:1rem">
        ¿Tienes dudas, sugerencias o quieres reportar algo? Escríbenos y te responderemos lo antes posible.
      </p>
    </div>

    <div class="contacto-layout">

      <!-- Formulario -->
      <div class="contacto-form-wrap reveal">
        <div class="contacto-card">
          <h2 class="contacto-card-title"><i class="fas fa-paper-plane"></i> Enviar mensaje</h2>

          <div id="contactoSuccess" class="contacto-success" style="display:none">
            <div class="contacto-success-icon">🎉</div>
            <h3>¡Mensaje enviado!</h3>
            <p>Recibimos tu mensaje y te responderemos pronto. Gracias por comunicarte con CIVINSIS.</p>
            <button class="btn btn-outline" onclick="resetForm()" style="margin-top:1rem"><i class="fas fa-redo"></i> Enviar otro</button>
          </div>

          <form id="contactoForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem" class="contact-name-grid">
              <div class="form-group">
                <label class="form-label"><i class="fas fa-user" style="color:var(--verde)"></i> Nombre *</label>
                <input type="text" id="cNombre" class="form-control" placeholder="Tu nombre"
                  value="<?= $usuarioLogueado ? htmlspecialchars($usuarioNombre) : '' ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope" style="color:var(--naranja)"></i> Correo *</label>
                <input type="email" id="cEmail" class="form-control" placeholder="tu@correo.com" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label"><i class="fas fa-tag" style="color:var(--verde)"></i> Asunto *</label>
              <select id="cAsunto" class="form-control" required>
                <option value="">Selecciona el motivo...</option>
                <option value="Consulta general" <?= $asunto_prefill==='Consulta general'?'selected':'' ?>>Consulta general</option>
                <option value="Reporte de contenido" <?= $asunto_prefill==='Reporte de contenido'?'selected':'' ?>>Reporte de contenido</option>
                <option value="Problema técnico" <?= $asunto_prefill==='Problema técnico'?'selected':'' ?>>Problema técnico</option>
                <option value="Sugerencia de mejora">Sugerencia de mejora</option>
                <option value="Cuenta suspendida">Cuenta suspendida / Apelación</option>
                <option value="Colaboración">Propuesta de colaboración</option>
                <option value="Prensa">Prensa y medios</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label"><i class="fas fa-comment-alt" style="color:var(--naranja)"></i> Mensaje *</label>
              <textarea id="cMensaje" class="form-control" rows="6"
                placeholder="Cuéntanos en detalle tu consulta, problema o sugerencia. Mientras más detalle nos des, mejor podremos ayudarte."
                maxlength="2000" required oninput="updateMsgCount()"></textarea>
              <div class="form-hint"><span id="msgCount">0</span>/2000 caracteres</div>
            </div>

            <div class="contacto-privacy">
              <i class="fas fa-shield-alt"></i>
              <span>Tu información es privada y solo será usada para responderte. No hacemos spam.</span>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center" id="cSubmitBtn">
              <i class="fas fa-paper-plane"></i> Enviar mensaje
            </button>
          </form>
        </div>
      </div>

      <!-- Sidebar de info -->
      <aside class="contacto-sidebar">
        <div class="contacto-info-card reveal">
          <h3><i class="fas fa-clock"></i> Tiempo de respuesta</h3>
          <p>Respondemos en un plazo de <strong>24–48 horas</strong> en días hábiles.</p>
        </div>

<div class="contacto-info-card reveal">
          <h3><i class="fas fa-question-circle"></i> Antes de escribir</h3>
          <p>Revisa nuestras preguntas frecuentes, puede que ya tengamos la respuesta.</p>
          <a href="faq.php" class="btn btn-ghost btn-sm" style="margin-top:.75rem;width:100%;justify-content:center">
            <i class="fas fa-book"></i> Ver FAQ
          </a>
        </div>

        <div class="contacto-info-card contacto-info-social reveal">
          <h3><i class="fas fa-share-alt"></i> Síguenos</h3>
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
<script>
function updateMsgCount() {
  document.getElementById('msgCount').textContent = document.getElementById('cMensaje').value.length;
}
function resetForm() {
  document.getElementById('contactoForm').reset();
  document.getElementById('contactoForm').style.display = 'block';
  document.getElementById('contactoSuccess').style.display = 'none';
  document.getElementById('msgCount').textContent = '0';
}
document.getElementById('contactoForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('cSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
  const data = {
    accion: 'enviar',
    nombre: document.getElementById('cNombre').value,
    email: document.getElementById('cEmail').value,
    asunto: document.getElementById('cAsunto').value,
    mensaje: document.getElementById('cMensaje').value
  };
  try {
    const r = await fetch('php/contacto.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const d = await r.json();
    if (d.success) {
      this.style.display = 'none';
      document.getElementById('contactoSuccess').style.display = 'block';
    } else {
      Toast.show(d.message || 'Error al enviar', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
    }
  } catch(err) {
    Toast.show('Error de conexión', 'error');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
  }
});
</script>
</body>
</html>
