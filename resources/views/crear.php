<?php
// $usuarioNombre y $categorias los inyecta el View Composer global en TODAS
// las vistas (app/Providers/AppServiceProvider.php::boot()). El valor por
// defecto de abajo nunca se usa en producción - solo evita que el IDE marque
// la variable como indefinida y sirve de red de seguridad.
$usuarioNombre = $usuarioNombre ?? '';
$categorias    = $categorias ?? collect();
$iniciales = civinsis_iniciales($usuarioNombre);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('civinsis.crear.titulo_pagina') ?> – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/desafios.css">
  <link rel="stylesheet" href="css/crear.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'crear'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 2rem);padding-bottom:5rem;min-height:100vh">
  <div class="container" style="max-width:820px">

    <div style="margin-bottom:2.5rem" class="animate-fade-up">
      <a href="dashboard.php" style="color:var(--text-muted);font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;margin-bottom:1rem">
        <i class="fas fa-arrow-left"></i> <?= __('civinsis.crear.volver') ?>
      </a>
      <span class="section-label"><?= __('civinsis.crear.section_label') ?></span>
      <h1 class="section-title"><?= __('civinsis.crear.titulo') ?></h1>
      <p style="color:var(--text-muted);font-size:.95rem">
        <?= __('civinsis.crear.subtitulo') ?>
      </p>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;align-items:start" class="create-layout">

      <!-- Formulario principal -->
      <div>
        <form id="createForm" class="animate-fade-up">
          <input type="hidden" id="desafioIdInput" name="desafio_id" value="">

          <!-- ── CIVI · Entrenador cívico integrado ─────────────── -->
          <div class="civi-crear" id="civiCrear">
            <div class="civi-crear-head">
              <span class="civi-crear-title"><i class="fas fa-wand-magic-sparkles"></i> <?= __('civinsis.crear.civi_titulo') ?></span>
              <span class="civi-crear-sub"><?= __('civinsis.crear.civi_sub') ?></span>
            </div>

            <!-- Redactar desde una idea suelta -->
            <div class="civi-idea-row">
              <input type="text" id="civiIdea" class="form-control"
                placeholder="<?= __('civinsis.crear.civi_idea_placeholder') ?>">
              <button type="button" class="btn btn-primary btn-sm" id="civiRedactar">
                <i class="fas fa-wand-magic-sparkles"></i> <?= __('civinsis.crear.civi_redactar') ?>
              </button>
            </div>

            <!-- Herramientas -->
            <div class="civi-tools">
              <button type="button" class="btn btn-outline btn-sm" data-civi="titulos"><i class="fas fa-heading"></i> <?= __('civinsis.crear.civi_sugerir_titulos') ?></button>
              <button type="button" class="btn btn-outline btn-sm" data-civi="categoria"><i class="fas fa-tag"></i> <?= __('civinsis.crear.civi_detectar_categoria') ?></button>
              <button type="button" class="btn btn-outline btn-sm" data-civi="ortografia"><i class="fas fa-spell-check"></i> <?= __('civinsis.crear.civi_corregir_ortografia') ?></button>
              <button type="button" class="btn btn-outline btn-sm" data-civi="argumentos"><i class="fas fa-scale-balanced"></i> <?= __('civinsis.crear.civi_reforzar_argumentos') ?></button>
              <button type="button" class="btn btn-outline btn-sm" data-civi="similares"><i class="fas fa-clone"></i> <?= __('civinsis.crear.civi_ver_similares') ?></button>
              <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('civiFab')?.click()"><i class="fas fa-comments"></i> <?= __('civinsis.crear.civi_preguntar') ?></button>
            </div>

            <!-- Resultados dinámicos de CIVI -->
            <div id="civiResult" class="civi-result" hidden></div>
          </div>

          <!-- Título -->
          <div class="form-group">
            <label class="form-label" for="titulo">
              <i class="fas fa-heading" style="color:var(--verde)"></i> <?= __('civinsis.crear.campo_titulo') ?>
            </label>
            <input type="text" id="titulo" name="titulo" class="form-control"
              placeholder="<?= __('civinsis.crear.campo_titulo_placeholder') ?>"
              maxlength="200" required oninput="updatePreview()">
            <div class="form-hint"><?= __('civinsis.crear.campo_titulo_hint') ?></div>
          </div>

          <!-- Categoría -->
          <div class="form-group">
            <label class="form-label" for="categoria_id">
              <i class="fas fa-tag" style="color:var(--naranja)"></i> <?= __('civinsis.crear.campo_categoria') ?>
            </label>
            <select id="categoria_id" name="categoria_id" class="form-control" required>
              <option value=""><?= __('civinsis.crear.campo_categoria_placeholder') ?></option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" data-icon="<?= $cat['icono'] ?>" data-color="<?= $cat['color'] ?>">
                  <?= htmlspecialchars($cat->translated('nombre')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Descripción corta -->
          <div class="form-group">
            <label class="form-label" for="descripcion">
              <i class="fas fa-align-left" style="color:var(--verde)"></i> <?= __('civinsis.crear.campo_descripcion') ?>
            </label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
              placeholder="<?= __('civinsis.crear.campo_descripcion_placeholder') ?>"
              maxlength="500" required oninput="updatePreview()"></textarea>
            <div class="form-hint"><span id="descCount">0</span>/500 <?= __('civinsis.crear.campo_descripcion_hint') ?></div>
          </div>

          <!-- Contenido enriquecido -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-file-alt" style="color:var(--naranja)"></i> <?= __('civinsis.crear.campo_contenido') ?>
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
              data-placeholder="<?= str_replace("\n", '&#10;', htmlspecialchars(__('civinsis.crear.editor_placeholder'))) ?>">
            </div>
            <input type="hidden" id="contenido" name="contenido">
            <div class="form-hint"><?= __('civinsis.crear.campo_contenido_hint') ?></div>
          </div>

          <!-- Imagen -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-image" style="color:var(--verde)"></i> <?= __('civinsis.crear.campo_imagen') ?> <span style="color:var(--text-muted);font-weight:400"><?= __('civinsis.crear.campo_imagen_opcional') ?></span>
            </label>
            <div class="image-upload-area" id="imageUploadArea">
              <input type="file" id="imagenFile" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImage(this)">
              <div id="imageUploadContent">
                <div class="image-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="image-upload-text">
                  <strong><?= __('civinsis.crear.imagen_upload_titulo') ?></strong>
                  <?= __('civinsis.crear.imagen_upload_desc') ?>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_clasico') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_oscuro') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_gradiente') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_minimalista') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_neon') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_glass') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_sunset') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_ocean') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_retro') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_aurora') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_cyber') ?></span>
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
                <span class="design-label"><?= __('civinsis.crear.diseno_pastel') ?></span>
              </label>
            </div>
          </div>

          <!-- Opciones extra de tarjeta (#2 #5) -->
          <div class="form-group" style="margin-top:1.25rem">
            <label class="form-label"><?= __('civinsis.crear.opciones_tarjeta') ?></label>
            <div style="display:flex;flex-direction:column;gap:.7rem">
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="checkbox" id="efectoCategoria" checked>
                <span><i class="fas fa-wand-magic-sparkles" style="color:var(--verde)"></i>
                  <?= __('civinsis.crear.efecto_categoria') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="checkbox" id="propDestacada">
                <span><i class="fas fa-star" style="color:var(--naranja)"></i>
                  <?= __('civinsis.crear.marcar_destacada') ?></span>
              </label>
              <div style="display:flex;align-items:center;gap:.6rem">
                <i class="fas fa-palette" style="color:var(--text-muted)"></i>
                <span style="font-size:.85rem"><?= __('civinsis.crear.color_acento') ?></span>
                <input type="color" id="colorAcento" value="#36c0a1" style="width:46px;height:34px;padding:.15rem;border:1px solid var(--border);border-radius:8px;cursor:pointer">
                <button type="button" id="limpiarAcento" class="btn btn-ghost btn-sm" style="font-size:.72rem"><?= __('civinsis.crear.sin_acento') ?></button>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="fas fa-rocket"></i> <?= __('civinsis.crear.publicar_propuesta') ?>
            </button>
            <a href="dashboard.php" class="btn btn-ghost btn-lg">
              <i class="fas fa-times"></i> <?= __('civinsis.comun.cancelar') ?>
            </a>
          </div>
        </form>
      </div>

      <!-- Sidebar -->
      <div style="display:flex;flex-direction:column;gap:1.25rem;position:sticky;top:calc(var(--nav-height) + 1rem)">
        <!-- Preview -->
        <div class="proposal-card" id="previewCard" style="pointer-events:none;opacity:.85">
          <div class="card-header">
            <div class="card-cat" id="previewCat"><i class="fas fa-tag"></i> <?= __('civinsis.crear.preview_categoria') ?></div>
            <h3 class="card-title" id="previewTitle" style="color:var(--text-muted);font-style:italic;font-weight:400"><?= __('civinsis.crear.preview_titulo') ?></h3>
            <p class="card-desc" id="previewDesc" style="color:var(--text-muted);font-style:italic"><?= __('civinsis.crear.preview_desc') ?></p>
          </div>
          <div class="card-footer">
            <div class="card-meta">
              <span><i class="fas fa-user"></i><?= htmlspecialchars($usuarioNombre) ?></span>
            </div>
            <span class="vote-btn"><i class="fas fa-arrow-up"></i> 0</span>
          </div>
        </div>

        <!-- Desafío sugerido / vinculado -->
        <div id="desafioSugeridoBox" style="background:var(--naranja-alpha);border:1px solid var(--naranja-200);border-radius:var(--radius-lg);padding:1.25rem"></div>

        <!-- Tips -->
        <div style="background:var(--naranja-alpha);border:1px solid var(--naranja-200);border-radius:var(--radius-lg);padding:1.25rem">
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--naranja-700);margin-bottom:.75rem">
            <i class="fas fa-lightbulb"></i> <?= __('civinsis.crear.tips_titulo') ?>
          </h4>
          <ul style="display:flex;flex-direction:column;gap:.5rem">
            <?php foreach([__('civinsis.crear.tip1'),__('civinsis.crear.tip2'),__('civinsis.crear.tip3'),__('civinsis.crear.tip4'),__('civinsis.crear.tip5')] as $tip): ?>
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
            <i class="fas fa-robot"></i> <?= __('civinsis.crear.auris_titulo') ?>
          </h4>
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem"><?= __('civinsis.crear.auris_desc') ?></p>
          <button class="btn btn-outline btn-sm" style="width:100%;justify-content:center" onclick="Auris.togglePanel()">
            <i class="fas fa-comments"></i> <?= __('civinsis.crear.auris_hablar') ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/desafios.js"></script>
<script src="js/crear-ia.js"></script>
<script src="js/crear.js"></script>
</body>
</html>
