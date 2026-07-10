<?php
$iniciales  = strtoupper(substr($usuarioNombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear propuesta – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'crear'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:5rem;min-height:100vh">
  <div class="container" style="max-width:820px">

    <div style="margin-bottom:2.5rem" class="animate-fade-up">
      <a href="dashboard.php" style="color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;margin-bottom:1rem">
        <i class="fas fa-arrow-left"></i> Volver
      </a>
      <span class="section-label">Nueva propuesta</span>
      <h1 class="section-title">Comparte tu <span>idea</span></h1>
      <p style="color:var(--text-muted);font-size:.95rem">
        Completa el formulario y publica tu propuesta para que la comunidad la descubra y apoye.
      </p>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;align-items:start" class="create-layout">

      <!-- Formulario principal -->
      <div>
        <form id="createForm" class="animate-fade-up">

          <!-- Título -->
          <div class="form-group">
            <label class="form-label" for="titulo">
              <i class="fas fa-heading" style="color:var(--verde)"></i> Título de la propuesta *
            </label>
            <input type="text" id="titulo" name="titulo" class="form-control"
              placeholder="Ej: Programa de reciclaje en parques públicos"
              maxlength="200" required oninput="updatePreview()">
            <div class="form-hint">Sé claro y conciso. Máximo 200 caracteres.</div>
          </div>

          <!-- Categoría -->
          <div class="form-group">
            <label class="form-label" for="categoria_id">
              <i class="fas fa-tag" style="color:var(--naranja)"></i> Categoría *
            </label>
            <select id="categoria_id" name="categoria_id" class="form-control" required>
              <option value="">Selecciona una categoría...</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" data-icon="<?= $cat['icono'] ?>" data-color="<?= $cat['color'] ?>">
                  <?= htmlspecialchars($cat['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Descripción corta -->
          <div class="form-group">
            <label class="form-label" for="descripcion">
              <i class="fas fa-align-left" style="color:var(--verde)"></i> Descripción breve *
            </label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
              placeholder="Resume tu propuesta en 2-3 oraciones. Esta aparece en las tarjetas del listado."
              maxlength="500" required oninput="updatePreview()"></textarea>
            <div class="form-hint"><span id="descCount">0</span>/500 caracteres</div>
          </div>

          <!-- Contenido enriquecido -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-file-alt" style="color:var(--naranja)"></i> Contenido completo *
            </label>

            <!-- Toolbar mejorado -->
            <div class="rich-editor-toolbar" id="editorToolbar">
              <button type="button" data-cmd="bold" title="Negrita"><i class="fas fa-bold"></i></button>
              <button type="button" data-cmd="italic" title="Cursiva"><i class="fas fa-italic"></i></button>
              <button type="button" data-cmd="underline" title="Subrayado"><i class="fas fa-underline"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="h2" title="Título 2"><i class="fas fa-heading"></i></button>
              <button type="button" data-cmd="h3" title="Título 3"><b style="font-size:.7rem">H3</b></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="insertUnorderedList" title="Lista"><i class="fas fa-list-ul"></i></button>
              <button type="button" data-cmd="insertOrderedList" title="Lista numerada"><i class="fas fa-list-ol"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="blockquote" title="Cita"><i class="fas fa-quote-left"></i></button>
              <button type="button" data-cmd="createLink" title="Enlace"><i class="fas fa-link"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="strikeThrough" title="Tachado"><i class="fas fa-strikethrough"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="h1" title="H1"><b style="font-size:.65rem">H1</b></button>
              <button type="button" data-cmd="codeBlock" title="Código"><i class="fas fa-code"></i></button>
              <button type="button" data-cmd="infoBox" title="Caja info"><i class="fas fa-info-circle"></i></button>
              <button type="button" data-cmd="warningBox" title="Advertencia"><i class="fas fa-exclamation-triangle"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="insertImage" title="Imagen en texto"><i class="fas fa-image"></i></button>
              <button type="button" data-cmd="insertTable" title="Tabla"><i class="fas fa-table"></i></button>
              <button type="button" data-cmd="insertHR" title="Separador"><i class="fas fa-minus"></i></button>
              <button type="button" data-cmd="foreColor" title="Color texto"><i class="fas fa-palette"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="justifyCenter" title="Centrar"><i class="fas fa-align-center"></i></button>
              <div class="toolbar-sep"></div>
              <button type="button" data-cmd="removeFormat" title="Limpiar formato"><i class="fas fa-eraser"></i></button>
            </div>

            <!-- Área editable -->
            <div class="rich-editor-content" id="richEditor" contenteditable="true"
              data-placeholder="Explica en detalle:&#10;• ¿Cuál es el problema que buscas resolver?&#10;• ¿Cuál es tu solución concreta?&#10;• ¿Cuál sería el impacto esperado?&#10;• ¿Qué recursos serían necesarios?">
            </div>
            <input type="hidden" id="contenido" name="contenido">
            <div class="form-hint">Cuanto más detallada sea tu propuesta, más credibilidad tendrá.</div>
          </div>

          <!-- Imagen -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-image" style="color:var(--verde)"></i> Imagen de portada <span style="color:var(--text-muted);font-weight:400">(opcional)</span>
            </label>
            <div class="image-upload-area" id="imageUploadArea">
              <input type="file" id="imagenFile" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImage(this)">
              <div id="imageUploadContent">
                <div class="image-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="image-upload-text">
                  <strong>Haz clic o arrastra una imagen aquí</strong>
                  JPG, PNG, WebP o GIF • Máx. 5MB
                </div>
              </div>
            </div>
            <div id="imagePreviewWrap" style="display:none;margin-top:.75rem;position:relative">
              <img id="imagePreview" src="" alt="Vista previa" class="image-preview">
              <button type="button" onclick="removeImage()" style="position:absolute;top:.5rem;right:.5rem;width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Diseño predeterminado -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-palette" style="color:var(--naranja)"></i> Diseño de la tarjeta <span style="color:var(--text-muted);font-weight:400">(opcional)</span>
            </label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.75rem" id="designPicker">
              <label class="design-option" data-design="default">
                <input type="radio" name="diseno" value="default" checked style="display:none">
                <div class="design-preview design-default">
                  <div style="height:6px;background:var(--grad-primary);border-radius:3px 3px 0 0"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:var(--surface);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:var(--surface);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Clásico</span>
              </label>
              <label class="design-option" data-design="dark">
                <input type="radio" name="diseno" value="dark" style="display:none">
                <div class="design-preview" style="background:#0c1612;border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:linear-gradient(90deg,#36c0a1,#00e5ff);border-radius:3px 3px 0 0"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:#1a2922;border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:#1a2922;border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Oscuro</span>
              </label>
              <label class="design-option" data-design="gradient">
                <input type="radio" name="diseno" value="gradient" style="display:none">
                <div class="design-preview" style="background:linear-gradient(135deg,#eaf8f3,#fef3e8);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:var(--grad-primary);border-radius:3px 3px 0 0"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(54,192,161,.2);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(239,126,34,.2);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Gradiente</span>
              </label>
              <label class="design-option" data-design="minimal">
                <input type="radio" name="diseno" value="minimal" style="display:none">
                <div class="design-preview" style="background:#fff;border:2px solid #0f1c19;border-radius:8px;overflow:hidden">
                  <div style="height:4px;background:#0f1c19"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:#eee;border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:#eee;border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Minimalista</span>
              </label>
              <label class="design-option" data-design="neon">
                <input type="radio" name="diseno" value="neon" style="display:none">
                <div class="design-preview" style="background:#050e0b;border-radius:8px;overflow:hidden;box-shadow:0 0 8px rgba(54,192,161,.4)">
                  <div style="height:6px;background:linear-gradient(90deg,#36c0a1,#00e5ff)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(54,192,161,.2);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(54,192,161,.15);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Neón</span>
              </label>
              <label class="design-option" data-design="glass">
                <input type="radio" name="diseno" value="glass" style="display:none">
                <div class="design-preview" style="background:linear-gradient(135deg,rgba(54,192,161,.15),rgba(239,126,34,.1));border:1px solid rgba(255,255,255,.3);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:rgba(255,255,255,.5)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(255,255,255,.25);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(255,255,255,.2);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Glass</span>
              </label>
              <label class="design-option" data-design="sunset">
                <input type="radio" name="diseno" value="sunset" style="display:none">
                <div class="design-preview" style="background:linear-gradient(160deg,#1a0a00,#2d0f1e);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:linear-gradient(90deg,#ef7e22,#e74c3c)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(239,126,34,.25);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(231,76,60,.2);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Sunset</span>
              </label>
              <label class="design-option" data-design="ocean">
                <input type="radio" name="diseno" value="ocean" style="display:none">
                <div class="design-preview" style="background:linear-gradient(160deg,#001a2c,#002a40);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:linear-gradient(90deg,#0ea5e9,#06b6d4)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(14,165,233,.25);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(6,182,212,.2);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Ocean</span>
              </label>
              <label class="design-option" data-design="retro">
                <input type="radio" name="diseno" value="retro" style="display:none">
                <div class="design-preview" style="background:#fdfaf0;border:2px solid #2c1a0e;border-radius:4px;overflow:hidden;box-shadow:3px 3px 0 #2c1a0e">
                  <div style="height:5px;background:#2c1a0e"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:#d4c9a8;border-radius:2px;width:80%"></div>
                    <div style="height:4px;background:#d4c9a8;border-radius:2px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Retro</span>
              </label>
              <label class="design-option" data-design="aurora">
                <input type="radio" name="diseno" value="aurora" style="display:none">
                <div class="design-preview" style="background:linear-gradient(135deg,#1a2980,#26d0ce);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:linear-gradient(90deg,#ff6ec4,#7873f5,#4ade80)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(255,255,255,.45);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(255,255,255,.3);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Aurora</span>
              </label>
              <label class="design-option" data-design="cyber">
                <input type="radio" name="diseno" value="cyber" style="display:none">
                <div class="design-preview" style="background:#0a0e27;border:1px solid #00f0ff;border-radius:8px;overflow:hidden;box-shadow:0 0 8px rgba(0,240,255,.4)">
                  <div style="height:6px;background:#00f0ff"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(0,240,255,.4);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(0,240,255,.2);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Cyber</span>
              </label>
              <label class="design-option" data-design="pastel">
                <input type="radio" name="diseno" value="pastel" style="display:none">
                <div class="design-preview" style="background:linear-gradient(135deg,#ffe5ec,#e0f7fa);border-radius:8px;overflow:hidden">
                  <div style="height:6px;background:linear-gradient(90deg,#f9a8d4,#a5f3fc)"></div>
                  <div style="padding:.5rem;display:flex;flex-direction:column;gap:.3rem">
                    <div style="height:6px;background:rgba(0,0,0,.12);border-radius:3px;width:80%"></div>
                    <div style="height:4px;background:rgba(0,0,0,.08);border-radius:3px;width:60%"></div>
                  </div>
                </div>
                <span class="design-label">Pastel</span>
              </label>
            </div>
          </div>

          <!-- Opciones extra de tarjeta (#2 #5) -->
          <div class="form-group" style="margin-top:1.25rem">
            <label class="form-label">Opciones de la tarjeta</label>
            <div style="display:flex;flex-direction:column;gap:.7rem">
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="checkbox" id="efectoCategoria" checked>
                <span><i class="fas fa-wand-magic-sparkles" style="color:var(--verde)"></i>
                  Efecto temático al pasar el cursor (según la categoría)</span>
              </label>
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="checkbox" id="propDestacada">
                <span><i class="fas fa-star" style="color:var(--naranja)"></i>
                  Marcar como destacada (borde brillante)</span>
              </label>
              <div style="display:flex;align-items:center;gap:.6rem">
                <i class="fas fa-palette" style="color:var(--text-muted)"></i>
                <span style="font-size:.85rem">Color de acento personalizado:</span>
                <input type="color" id="colorAcento" value="#36c0a1" style="width:46px;height:34px;padding:.15rem;border:1px solid var(--border);border-radius:8px;cursor:pointer">
                <button type="button" id="limpiarAcento" class="btn btn-ghost btn-sm" style="font-size:.72rem">Sin acento</button>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="fas fa-rocket"></i> Publicar propuesta
            </button>
            <a href="dashboard.php" class="btn btn-ghost btn-lg">
              <i class="fas fa-times"></i> Cancelar
            </a>
          </div>
        </form>
      </div>

      <!-- Sidebar -->
      <div style="display:flex;flex-direction:column;gap:1.25rem;position:sticky;top:calc(var(--nav-height) + 1rem)">
        <!-- Preview -->
        <div class="proposal-card" id="previewCard" style="pointer-events:none;opacity:.85">
          <div class="card-header">
            <div class="card-cat" id="previewCat"><i class="fas fa-tag"></i> Categoría</div>
            <h3 class="card-title" id="previewTitle" style="color:var(--text-muted);font-style:italic;font-weight:400">Tu título aparecerá aquí...</h3>
            <p class="card-desc" id="previewDesc" style="color:var(--text-muted);font-style:italic">Tu descripción aparecerá aquí...</p>
          </div>
          <div class="card-footer">
            <div class="card-meta">
              <span><i class="fas fa-user"></i><?= htmlspecialchars($usuarioNombre) ?></span>
            </div>
            <span class="vote-btn"><i class="fas fa-arrow-up"></i> 0</span>
          </div>
        </div>

        <!-- Tips -->
        <div style="background:var(--naranja-alpha);border:1px solid var(--naranja-200);border-radius:var(--radius-lg);padding:1.25rem">
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--naranja-700);margin-bottom:.75rem">
            <i class="fas fa-lightbulb"></i> Tips para una buena propuesta
          </h4>
          <ul style="display:flex;flex-direction:column;gap:.5rem">
            <?php foreach(['Define claramente el problema','Propón soluciones concretas','Incluye el impacto esperado','Usa lenguaje claro y accesible','Elige la categoría correcta'] as $tip): ?>
            <li style="font-size:.8rem;color:var(--text-muted);display:flex;gap:.5rem;align-items:flex-start">
              <i class="fas fa-check-circle" style="color:var(--verde);margin-top:2px;flex-shrink:0"></i>
              <?= $tip ?>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- AURIS -->
        <div style="background:var(--verde-alpha);border:1px solid var(--verde-200);border-radius:var(--radius-lg);padding:1.25rem">
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--verde-700);margin-bottom:.5rem">
            <i class="fas fa-robot"></i> ¿Necesitas ayuda?
          </h4>
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">AURIS puede ayudarte a estructurar y mejorar tu propuesta.</p>
          <button class="btn btn-outline btn-sm" style="width:100%;justify-content:center" onclick="Auris.togglePanel()">
            <i class="fas fa-comments"></i> Hablar con AURIS
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<style>
@media(max-width:640px){.create-layout{grid-template-columns:1fr!important}}

/* Diseño selector */
.design-option { cursor:pointer; text-align:center; }
.design-preview {
  border:2px solid var(--border); border-radius:8px; overflow:hidden;
  margin-bottom:.4rem; transition:var(--trans); height:54px;
}
.design-option input:checked + .design-preview { border-color:var(--verde); box-shadow:0 0 0 2px var(--verde-alpha2); }
.design-label { font-size:.72rem; color:var(--text-muted); font-weight:600; }

/* Placeholder en contenteditable */
.rich-editor-content:empty::before {
  content: attr(data-placeholder);
  color: var(--text-muted);
  pointer-events: none;
  white-space: pre-line;
}
</style>
<script>
// Preview en tiempo real
function updatePreview() {
  const titulo = document.getElementById('titulo').value;
  const desc   = document.getElementById('descripcion').value;
  document.getElementById('previewTitle').textContent = titulo || 'Tu título aparecerá aquí...';
  document.getElementById('previewTitle').style.fontStyle = titulo ? 'normal' : 'italic';
  document.getElementById('previewTitle').style.color = titulo ? 'var(--text)' : 'var(--text-muted)';
  document.getElementById('previewDesc').textContent = desc || 'Tu descripción aparecerá aquí...';
  document.getElementById('previewDesc').style.fontStyle = desc ? 'normal' : 'italic';
  document.getElementById('descCount').textContent = desc.length;
}

// Categoría preview
document.getElementById('categoria_id')?.addEventListener('change', function() {
  const opt   = this.options[this.selectedIndex];
  const icon  = opt.dataset.icon || 'fas fa-tag';
  const color = opt.dataset.color || 'var(--verde)';
  document.getElementById('previewCat').innerHTML = `<i class="${icon}" style="color:${color}"></i> ${opt.text}`;
});

// Editor enriquecido
const toolbar = document.getElementById('editorToolbar');
const editor  = document.getElementById('richEditor');

toolbar.addEventListener('click', e => {
  const btn = e.target.closest('[data-cmd]');
  if (!btn) return;
  e.preventDefault();
  const cmd = btn.dataset.cmd;
  editor.focus();
  if (cmd === 'h1') {
    document.execCommand('formatBlock', false, '<h1>');
  } else if (cmd === 'h2') {
    document.execCommand('formatBlock', false, '<h2>');
  } else if (cmd === 'h3') {
    document.execCommand('formatBlock', false, '<h3>');
  } else if (cmd === 'blockquote') {
    document.execCommand('formatBlock', false, '<blockquote>');
  } else if (cmd === 'codeBlock') {
    const sel = window.getSelection(); const text = sel.toString() || 'código';
    document.execCommand('insertHTML', false, `<pre><code>${text}</code></pre><p><br></p>`);
  } else if (cmd === 'infoBox') {
    document.execCommand('insertHTML', false, '<div class="info-box"><strong>ℹ️ Info:</strong> Escribe aquí.</div><p><br></p>');
  } else if (cmd === 'warningBox') {
    document.execCommand('insertHTML', false, '<div class="warning-box"><strong>⚠️ Importante:</strong> Escribe aquí.</div><p><br></p>');
  } else if (cmd === 'insertImage') {
    const url = prompt('URL de la imagen:');
    if (url) document.execCommand('insertHTML', false, `<img src="${url}" class="img-center" alt=""><p><br></p>`);
  } else if (cmd === 'insertTable') {
    const r = parseInt(prompt('Filas:','3'))||3, cl = parseInt(prompt('Columnas:','3'))||3;
    let t = '<table><thead><tr>' + Array(cl).fill(0).map((_,i)=>`<th>Col ${i+1}</th>`).join('') + '</tr></thead><tbody>';
    for(let i=0;i<r;i++){ t+='<tr>'+Array(cl).fill('<td>Dato</td>').join('')+'</tr>'; }
    document.execCommand('insertHTML', false, t+'</tbody></table><p><br></p>');
  } else if (cmd === 'insertHR') {
    document.execCommand('insertHTML', false, '<hr><p><br></p>');
  } else if (cmd === 'createLink') {
    const url = prompt('Ingresa la URL:');
    if (url) document.execCommand('createLink', false, url);
  } else if (cmd === 'foreColor') {
    const colors = ['#36c0a1','#ef7e22','#e74c3c','#3498db','#9b59b6','#f39c12','#27ae60','#000'];
    const pick = document.createElement('div');
    pick.style.cssText = 'position:fixed;z-index:9999;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:.75rem;display:flex;gap:.4rem;box-shadow:var(--shadow-lg)';
    const rect = btn.getBoundingClientRect();
    pick.style.left = rect.left+'px'; pick.style.top = (rect.bottom+8)+'px';
    pick.innerHTML = colors.map(c=>`<button onclick="document.execCommand('foreColor',false,'${c}');this.parentElement.remove()" style="width:26px;height:26px;border-radius:50%;background:${c};border:2px solid rgba(255,255,255,.2);cursor:pointer"></button>`).join('');
    document.body.appendChild(pick);
    setTimeout(()=>document.addEventListener('click',()=>pick.remove(),{once:true}),100);
    return;
  } else {
    document.execCommand(cmd, false, null);
  }
  // Toggle active state
  document.querySelectorAll('#editorToolbar [data-cmd]').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
});

// Imagen
function previewImage(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 5 * 1024 * 1024) { alert('La imagen no puede superar 5MB'); return; }
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('imagePreview').src = e.target.result;
    document.getElementById('imagePreviewWrap').style.display = 'block';
    document.getElementById('imageUploadContent').style.display = 'none';
    document.getElementById('imageUploadArea').classList.add('has-image');
  };
  reader.readAsDataURL(file);
}
function removeImage() {
  document.getElementById('imagenFile').value = '';
  document.getElementById('imagePreviewWrap').style.display = 'none';
  document.getElementById('imageUploadContent').style.display = 'block';
  document.getElementById('imageUploadArea').classList.remove('has-image');
}

// Drag and drop en upload area
const uploadArea = document.getElementById('imageUploadArea');
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.borderColor = 'var(--verde)'; });
uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = ''; });
uploadArea.addEventListener('drop', e => {
  e.preventDefault();
  uploadArea.style.borderColor = '';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    document.getElementById('imagenFile').files = e.dataTransfer.files;
    previewImage(document.getElementById('imagenFile'));
  }
});

// Submit: sincronizar contenido del editor
document.getElementById('createForm').addEventListener('submit', function(e) {
  document.getElementById('contenido').value = editor.innerHTML;
  if (!editor.textContent.trim()) {
    e.preventDefault();
    editor.style.borderColor = '#e74c3c';
    editor.focus();
    return;
  }
});

// Color de acento opcional para la tarjeta (#2)
(function(){
  var acento = document.getElementById('colorAcento');
  var limpiar = document.getElementById('limpiarAcento');
  if (acento) {
    acento.dataset.activo = '0';
    acento.addEventListener('input', function(){ acento.dataset.activo = '1'; acento.style.outline = '2px solid var(--verde)'; });
  }
  if (limpiar) {
    limpiar.addEventListener('click', function(){
      if (acento) { acento.dataset.activo = '0'; acento.style.outline = 'none'; }
    });
  }
})();
</script>
</body>
</html>
