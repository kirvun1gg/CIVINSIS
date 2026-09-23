async function loadMisMensajes() {
  const list = document.getElementById('misMensajesList');
  if (!list) return; // sección solo existe si hay sesión iniciada
  const tc = (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.contacto) || {};
  const esc = (x) => { const d = document.createElement('div'); d.textContent = x ?? ''; return d.innerHTML; };

  try {
    const r = await fetch('php/contacto.php?accion=mis_mensajes');
    const d = await r.json();
    if (!d.success || !d.mensajes || !d.mensajes.length) {
      list.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>${esc(tc.mis_mensajes_vacio || 'Todavía no has enviado ningún mensaje.')}</p></div>`;
      return;
    }

    list.innerHTML = d.mensajes.map((m) => `
      <div style="border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem 1.25rem;margin-bottom:1rem;background:var(--bg-card)">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.5rem">
          <strong style="color:var(--text)">${esc(m.asunto)}</strong>
          <span style="font-size:.75rem;color:var(--text-muted)">${esc(m.fecha_formateada)}</span>
        </div>
        <p style="color:var(--text-2);font-size:.88rem;margin:0 0 .75rem">${esc(m.mensaje)}</p>
        ${m.respuesta
          ? `<div style="background:var(--verde-alpha);border:1px solid var(--verde-alpha2);border-radius:var(--radius);padding:.75rem 1rem">
               <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--verde-600);margin-bottom:.3rem">
                 <i class="fas fa-reply"></i> ${esc(tc.mis_mensajes_respuesta || 'Respuesta del equipo CIVINSIS')}
               </div>
               <p style="margin:0;color:var(--text);font-size:.88rem;white-space:pre-line">${esc(m.respuesta)}</p>
             </div>`
          : `<span style="display:inline-flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--naranja-600);background:var(--naranja-alpha);padding:.3rem .7rem;border-radius:100px">
               <i class="fas fa-clock"></i> ${esc(tc.mis_mensajes_pendiente || 'Pendiente de respuesta')}
             </span>`}
      </div>`).join('');
  } catch (e) {
    list.innerHTML = `<div class="empty-state"><i class="fas fa-triangle-exclamation"></i><p>${esc((window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.comunes && CIVI_I18N.toast.comunes.error_conexion) || 'Error de conexión')}</p></div>`;
  }
}
loadMisMensajes();

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
      const tc = (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.contacto) || {};
      Toast.show(d.message || tc.error_enviar || 'Error al enviar', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
    }
  } catch(err) {
    const tcm = (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.comunes) || {};
    Toast.show(tcm.error_conexion || 'Error de conexión', 'error');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
  }
});
