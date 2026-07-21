/* ═══════════════════════════════════════════════════════════════
   CIVI · Entrenador cívico integrado en la página de crear propuesta
   Reutiliza los helpers globales API y Toast (definidos en app.js).
   ═══════════════════════════════════════════════════════════════ */
(function () {
  const panel = document.getElementById('civiCrear');
  if (!panel) return; // solo corre en crear.php

  const $ = (id) => document.getElementById(id);
  const titulo   = $('titulo');
  const desc     = $('descripcion');
  const catSel   = $('categoria_id');
  const editor   = $('richEditor');
  const hidden   = $('contenido');
  const result   = $('civiResult');
  const idea     = $('civiIdea');
  const btnRedac = $('civiRedactar');

  const IA_URL = 'php/ia.php';

  // ── utilidades ────────────────────────────────────────────────
  const esc = (s) => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
  const nl2p = (s) => (s || '').trim().split(/\n{2,}|\n/).filter(Boolean)
                        .map((p) => `<p>${esc(p)}</p>`).join('');
  const sync = () => { if (hidden && editor) hidden.value = editor.innerHTML; };
  const refresh = () => { if (typeof updatePreview === 'function') updatePreview(); };

  function busy(btn, on) {
    if (!btn) return;
    if (on) {
      btn.dataset.html = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> CIVI…';
      btn.classList.add('civi-busy');
    } else {
      btn.innerHTML = btn.dataset.html || btn.innerHTML;
      btn.classList.remove('civi-busy');
    }
  }

  function showResult(html) {
    result.innerHTML = html;
    result.hidden = false;
    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function hideResult() { result.hidden = true; result.innerHTML = ''; }

  const head = (icon, txt) =>
    `<div class="civi-result-head"><i class="fas ${icon}"></i> ${esc(txt)}</div>`;

  // Validaciones compartidas
  function tituloDesc() {
    return { titulo: (titulo.value || '').trim(), descripcion: (desc.value || '').trim() };
  }
  function requiereDesc() {
    if ((desc.value || '').trim().length < 8 && (titulo.value || '').trim().length < 8) {
      Toast.show('Escribe primero un título o una descripción', 'error');
      return false;
    }
    return true;
  }

  // ── 1) Redactar borrador desde una idea ───────────────────────
  btnRedac?.addEventListener('click', async () => {
    const txt = (idea.value || '').trim();
    if (txt.length < 6) { Toast.show('Cuéntame tu idea en una frase', 'error'); return; }
    busy(btnRedac, true);
    const res = await API.post(IA_URL, { accion: 'redactar', idea: txt });
    busy(btnRedac, false);
    if (!res.success) { Toast.show(res.message || 'No se pudo redactar', 'error'); return; }
    const b = res.borrador || {};
    if (b.titulo)      titulo.value = b.titulo;
    if (b.descripcion) desc.value   = b.descripcion;
    if (b.contenido)   { editor.innerHTML = nl2p(b.contenido); sync(); }
    refresh();
    hideResult();
    Toast.show('Borrador listo — revísalo y edítalo a tu gusto', 'success');
  });

  // ── Router de las herramientas ────────────────────────────────
  panel.querySelectorAll('[data-civi]').forEach((btn) => {
    btn.addEventListener('click', () => tools[btn.dataset.civi]?.(btn));
  });

  const tools = {
    // 2) Sugerir títulos
    async titulos(btn) {
      if (!requiereDesc()) return;
      busy(btn, true);
      const res = await API.post(IA_URL, { accion: 'titulos', ...tituloDesc() });
      busy(btn, false);
      if (!res.success) { Toast.show(res.message, 'error'); return; }
      const opts = (res.titulos || []).map((t) =>
        `<button type="button" class="civi-titulo-opt" data-t="${esc(t)}">${esc(t)}</button>`).join('');
      showResult(head('fa-heading', 'Elige un título (clic para usarlo):') + (opts || '<p>Sin sugerencias.</p>'));
      result.querySelectorAll('.civi-titulo-opt').forEach((o) => {
        o.onclick = () => { titulo.value = o.dataset.t; refresh(); hideResult(); Toast.show('Título aplicado', 'success'); };
      });
    },

    // 3) Detectar categoría
    async categoria(btn) {
      if (!requiereDesc()) return;
      busy(btn, true);
      const res = await API.post(IA_URL, { accion: 'categoria', ...tituloDesc() });
      busy(btn, false);
      if (!res.success) { Toast.show(res.message, 'error'); return; }
      if (res.detectada) {
        catSel.value = String(res.categoria_id);
        catSel.dispatchEvent(new Event('change'));
        refresh();
        Toast.show('Categoría detectada: ' + res.categoria_nombre, 'success');
      } else {
        Toast.show('CIVI sugiere: ' + (res.sugerida || 'sin coincidencia clara'), 'info');
      }
    },

    // 4) Corregir ortografía (sobre la descripción)
    async ortografia(btn) {
      const texto = (desc.value || '').trim();
      if (texto.length < 4) { Toast.show('Escribe la descripción primero', 'error'); return; }
      busy(btn, true);
      const res = await API.post(IA_URL, { accion: 'ortografia', texto });
      busy(btn, false);
      if (!res.success) { Toast.show(res.message, 'error'); return; }
      showResult(
        head('fa-spell-check', 'Descripción corregida:') +
        `<div>${nl2p(res.respuesta)}</div>` +
        `<button type="button" class="btn btn-primary btn-sm" id="civiApplyOrto"><i class="fas fa-check"></i> Aplicar a la descripción</button>`
      );
      $('civiApplyOrto').onclick = () => { desc.value = res.respuesta.trim(); refresh(); hideResult(); Toast.show('Descripción actualizada', 'success'); };
    },

    // 5) Reforzar argumentos (sobre el contenido completo)
    async argumentos(btn) {
      const texto = (editor.innerText || '').trim();
      if (texto.length < 20) { Toast.show('Escribe primero el contenido completo', 'error'); return; }
      busy(btn, true);
      const res = await API.post(IA_URL, { accion: 'argumentos', texto });
      busy(btn, false);
      if (!res.success) { Toast.show(res.message, 'error'); return; }
      showResult(
        head('fa-scale-balanced', 'Versión con argumentos reforzados:') +
        `<div>${nl2p(res.respuesta)}</div>` +
        `<button type="button" class="btn btn-primary btn-sm" id="civiApplyArg"><i class="fas fa-check"></i> Reemplazar contenido</button>`
      );
      $('civiApplyArg').onclick = () => { editor.innerHTML = nl2p(res.respuesta); sync(); hideResult(); Toast.show('Contenido actualizado', 'success'); };
    },

    // 6) Detectar propuestas similares
    async similares(btn) {
      if (!requiereDesc()) return;
      busy(btn, true);
      const res = await API.post(IA_URL, { accion: 'similares', ...tituloDesc() });
      busy(btn, false);
      if (!res.success) { Toast.show(res.message, 'error'); return; }
      const items = res.similares || [];
      if (!items.length) {
        showResult(head('fa-clone', 'Propuestas similares') +
          '<p>No encontramos propuestas parecidas. ¡Tu idea parece original! 🎉</p>');
        return;
      }
      const list = items.map((s) =>
        `<div class="civi-sim-item">
           <span>${esc(s.titulo)}${s.categoria ? ` <span style="color:var(--text-muted);font-size:.76rem">· ${esc(s.categoria)}</span>` : ''}</span>
           <span class="civi-sim-badge">${s.similitud}% similar</span>
         </div>`).join('');
      showResult(head('fa-clone', 'Propuestas parecidas ya existentes:') + list +
        '<p style="color:var(--text-muted);font-size:.78rem;margin-top:.5rem">Revisa si tu propuesta aporta algo distinto antes de publicar.</p>');
    },
  };

  // ── Percepción en tiempo real mientras escribes (entrenador de propuestas) ──
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const perceive = debounce(() => {
    if (!window.CIVI) return;
    const t = (titulo.value || '').trim();
    const d = (desc.value || '').trim();
    const body = (editor.innerText || '').trim();

    // 1) describió la propuesta pero no eligió categoría
    if (d.length > 15 && catSel && !catSel.value) {
      CIVI.suggest({ id: 'crear_cat', once: true,
        texto: 'Veo que ya describiste tu propuesta pero falta elegir la categoría. ¿Quieres que la detecte por ti?',
        cta_texto: 'Detectar categoría', cta_fn: () => panel.querySelector('[data-civi="categoria"]')?.click() });
      return;
    }
    // 2) contenido demasiado corto: explica POR QUÉ conviene desarrollarlo
    if (body.length > 0 && body.length < 120) {
      CIVI.suggest({ id: 'crear_corto', once: true,
        texto: 'Tu contenido va corto. Una propuesta se entiende mejor si explicas primero el problema, luego tu solución, el impacto esperado y los recursos necesarios.' });
      return;
    }
    // 3) escribe en MAYÚSCULAS (tono)
    const letras = body.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ]/g, '');
    if (letras.length > 25 && letras === letras.toUpperCase()) {
      CIVI.suggest({ id: 'crear_mayus', once: true,
        texto: 'Escribir todo en MAYÚSCULAS puede leerse como gritar. Un tono calmado hace que tu propuesta convenza más.' });
      return;
    }
    // 4) ya hay descripción pero falta título
    if (!t && d.length > 20) {
      CIVI.suggest({ id: 'crear_titulo', once: true,
        texto: 'Ya tienes una buena descripción, pero aún te falta el título. ¿Te propongo algunos?',
        cta_texto: 'Sugerir títulos', cta_fn: () => panel.querySelector('[data-civi="titulos"]')?.click() });
    }
  }, 1600);

  [titulo, desc].forEach((el) => el && el.addEventListener('input', perceive));
  if (editor) editor.addEventListener('input', perceive);
})();
