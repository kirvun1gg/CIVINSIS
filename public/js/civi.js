/* ============================================================
   CIVINSIS · Página exclusiva de chat con CIVI (civi.php)
   Historial de conversaciones por usuario — usa las mismas acciones
   de php/ia.php que la burbuja, más chat_conversaciones/chat_conversacion/
   chat_eliminar para la memoria persistente (ver IaController.php).
   ============================================================ */
(function () {
  'use strict';

  const form = document.getElementById('civiForm');
  if (!form) return; // solo corre en civi.php

  function t() { return (window.CIVI_I18N && CIVI_I18N.civi_pagina) || {}; }

  const side        = document.getElementById('civiSide');
  const sideList    = document.getElementById('civiSideList');
  const sideToggle  = document.getElementById('civiSideToggle');
  const sideOverlay = document.getElementById('civiSideOverlay');
  const nuevaBtn   = document.getElementById('civiNuevaBtn');
  const body       = document.getElementById('civiMainBody');
  const vacio      = document.getElementById('civiVacio');
  const msgsEl     = document.getElementById('civiMsgs');
  const input      = document.getElementById('civiPageInput');
  const sendBtn    = document.getElementById('civiPageSend');

  let conversaciones = [];
  let activaId = null;
  let enviando = false;

  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  function scrollBottom() { body.scrollTop = body.scrollHeight; }

  function mostrarVacio() {
    vacio.style.display = 'flex';
    msgsEl.style.display = 'none';
    msgsEl.innerHTML = '';
  }

  function mostrarMsgs() {
    vacio.style.display = 'none';
    msgsEl.style.display = 'flex';
  }

  function addMsg(rol, contenido) {
    mostrarMsgs();
    const m = document.createElement('div');
    m.className = 'civi-msg ' + (rol === 'user' ? 'user' : 'bot');
    m.textContent = contenido;
    msgsEl.appendChild(m);
    scrollBottom();
    return m;
  }

  function renderSide() {
    if (!conversaciones.length) {
      sideList.innerHTML = `<div class="civi-side-empty">${esc(t().sin_conversaciones || 'Todavía no tienes conversaciones con CIVI. ¡Empieza una!')}</div>`;
      return;
    }
    sideList.innerHTML = conversaciones.map(c => `
      <div class="civi-side-item ${c.id === activaId ? 'active' : ''}" data-id="${c.id}">
        <div class="civi-side-item-info">
          <div class="civi-side-item-titulo" data-titulo>${esc(c.titulo)}</div>
          <div class="civi-side-item-fecha">${esc(c.fecha || '')}</div>
        </div>
        <div class="civi-side-item-actions">
          <button class="civi-side-item-edit" data-edit="${c.id}" type="button" aria-label="${esc(t().renombrar_conversacion || 'Renombrar conversación')}">
            <i class="fas fa-pen"></i>
          </button>
          <button class="civi-side-item-del" data-del="${c.id}" type="button" aria-label="${esc(t().eliminar_conversacion || 'Eliminar conversación')}">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    `).join('');
  }

  function iniciarEdicionTitulo(id) {
    const item = sideList.querySelector(`.civi-side-item[data-id="${id}"]`);
    if (!item) return;
    const tituloEl = item.querySelector('[data-titulo]');
    if (!tituloEl || item.classList.contains('editando')) return;

    const actual = conversaciones.find(c => c.id === id);
    const valorActual = actual ? actual.titulo : tituloEl.textContent;

    item.classList.add('editando');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'civi-side-item-titulo-input';
    input.value = valorActual;
    input.maxLength = 60;
    input.setAttribute('aria-label', t().guardar_titulo || 'Guardar título');
    tituloEl.replaceWith(input);
    input.focus();
    input.select();

    let resuelto = false;
    const terminar = async (guardar) => {
      if (resuelto) return;
      resuelto = true;
      const nuevo = input.value.trim();
      if (guardar && nuevo && nuevo !== valorActual) {
        await guardarTitulo(id, nuevo);
      } else {
        renderSide(); // reconstruye el item tal cual estaba
      }
    };

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); terminar(true); }
      else if (e.key === 'Escape') { e.preventDefault(); terminar(false); }
    });
    input.addEventListener('blur', () => terminar(true));
  }

  async function guardarTitulo(id, nuevoTitulo) {
    const d = await API.post('php/ia.php', { accion: 'chat_renombrar', id, titulo: nuevoTitulo });
    if (!d || !d.success) {
      if (window.Toast) Toast.show((d && d.message) || t().error_generico || 'Algo salió mal. Inténtalo de nuevo.', 'error');
      renderSide();
      return;
    }
    const c = conversaciones.find(x => x.id === id);
    if (c) c.titulo = d.titulo;
    renderSide();
  }

  async function cargarConversaciones() {
    const d = await API.get('php/ia.php', { accion: 'chat_conversaciones' });
    conversaciones = (d && d.success && d.conversaciones) ? d.conversaciones : [];
    renderSide();
  }

  function cerrarSideMovil() {
    side.classList.remove('open');
    if (sideOverlay) sideOverlay.classList.remove('visible');
  }

  function abrirSideMovil() {
    side.classList.add('open');
    if (sideOverlay) sideOverlay.classList.add('visible');
  }

  /* ── Cerrar deslizando el dedo hacia la izquierda (móvil) ──────
     Sin esto, en pantallas pequeñas el panel lateral solo se podía
     cerrar volviendo a tocar el botón de hamburguesa (poco intuitivo
     y a veces tapado por el propio panel). */
  (function habilitarSwipeCierre() {
    let x0 = null, y0 = null, arrastrando = false;

    side.addEventListener('touchstart', (e) => {
      if (!side.classList.contains('open') || e.touches.length !== 1) return;
      x0 = e.touches[0].clientX;
      y0 = e.touches[0].clientY;
      arrastrando = true;
    }, { passive: true });

    side.addEventListener('touchmove', (e) => {
      if (!arrastrando || x0 === null) return;
      const dx = e.touches[0].clientX - x0;
      const dy = e.touches[0].clientY - y0;
      // Solo tratamos como swipe horizontal si el gesto es mas horizontal
      // que vertical (si no, interferiria con el scroll de la lista).
      if (Math.abs(dx) > Math.abs(dy) && dx < -50) {
        arrastrando = false;
        cerrarSideMovil();
      }
    }, { passive: true });

    side.addEventListener('touchend', () => { arrastrando = false; x0 = null; y0 = null; });
  })();

  function nuevaConversacion() {
    activaId = null;
    mostrarVacio();
    renderSide();
    cerrarSideMovil();
    input.focus();
  }

  async function abrirConversacion(id) {
    if (id === activaId) { cerrarSideMovil(); return; }
    activaId = id;
    renderSide();
    cerrarSideMovil();
    mostrarMsgs();
    msgsEl.innerHTML = `<div class="civi-side-loading">${esc(t().cargando || 'Cargando...')}</div>`;

    const d = await API.get('php/ia.php', { accion: 'chat_conversacion', id });
    if (!d || !d.success) {
      mostrarVacio();
      if (window.Toast) Toast.show((d && d.message) || t().error_cargar || 'No se pudo cargar la conversación', 'error');
      return;
    }

    msgsEl.innerHTML = '';
    (d.mensajes || []).forEach(m => addMsg(m.rol, m.contenido));
    scrollBottom();
  }

  async function eliminarConversacion(id) {
    if (!confirm(t().confirmar_eliminar || '¿Eliminar esta conversación? No se puede deshacer.')) return;

    const d = await API.post('php/ia.php', { accion: 'chat_eliminar', id });
    if (!d || !d.success) {
      if (window.Toast) Toast.show((d && d.message) || t().error_generico || 'Algo salió mal. Inténtalo de nuevo.', 'error');
      return;
    }

    conversaciones = conversaciones.filter(c => c.id !== id);
    if (activaId === id) nuevaConversacion();
    else renderSide();
    if (window.Toast) Toast.show(d.message, 'success');
  }

  async function enviar(e) {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg || enviando) return;

    enviando = true;
    sendBtn.disabled = true;
    input.value = '';
    addMsg('user', msg);

    const typing = document.createElement('div');
    typing.className = 'civi-typing';
    typing.innerHTML = '<i class="fas fa-ellipsis fa-fade"></i> ' + esc(t().escribiendo || 'CIVI está escribiendo…');
    msgsEl.appendChild(typing);
    scrollBottom();

    const d = await API.post('php/ia.php', { accion: 'chat', mensaje: msg, conversacion_id: activaId });
    typing.remove();

    if (!d || !d.success) {
      if (window.Toast) Toast.show((d && d.message) || t().error_generico || 'Algo salió mal. Inténtalo de nuevo.', 'error');
      enviando = false;
      sendBtn.disabled = false;
      input.focus();
      return;
    }

    addMsg('assistant', d.respuesta);

    if (d.conversacion_id) {
      const esNueva = activaId === null;
      activaId = d.conversacion_id;
      if (esNueva) {
        conversaciones.unshift({ id: d.conversacion_id, titulo: d.titulo, fecha: '' });
      } else {
        const c = conversaciones.find(x => x.id === activaId);
        conversaciones = c ? [c, ...conversaciones.filter(x => x.id !== activaId)] : conversaciones;
      }
      renderSide();
    }

    enviando = false;
    sendBtn.disabled = false;
    input.focus();
  }

  form.addEventListener('submit', enviar);
  nuevaBtn.addEventListener('click', nuevaConversacion);
  sideToggle.addEventListener('click', () => {
    if (side.classList.contains('open')) cerrarSideMovil();
    else abrirSideMovil();
  });
  if (sideOverlay) sideOverlay.addEventListener('click', cerrarSideMovil);
  sideList.addEventListener('click', (e) => {
    const edit = e.target.closest('[data-edit]');
    if (edit) { e.stopPropagation(); iniciarEdicionTitulo(parseInt(edit.dataset.edit, 10)); return; }
    const del = e.target.closest('[data-del]');
    if (del) { e.stopPropagation(); eliminarConversacion(parseInt(del.dataset.del, 10)); return; }
    if (e.target.closest('.civi-side-item-titulo-input')) return; // clic dentro del input: no navegar
    const item = e.target.closest('.civi-side-item');
    if (item) abrirConversacion(parseInt(item.dataset.id, 10));
  });

  cargarConversaciones();
})();
