/* ============================================================
   CIVINSIS · Aviso de penalización de reputación
   Cuando un moderador censura contenido de un usuario, se le
   descuenta reputación (ver IaController::censurar). Este script
   consulta una vez por carga si hay penalizaciones sin ver y, si
   las hay, muestra un modal explicando el motivo y los puntos
   perdidos. El backend las marca como vistas al devolverlas, así
   que el modal no se repite.
   ============================================================ */
(function () {
  'use strict';

  function t() { return (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.gamificacion) || {}; }
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  function mostrarModal(penalizaciones) {
    const totalPuntos = penalizaciones.reduce((s, p) => s + p.puntos, 0);
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML = `
      <div class="modal" style="max-width:440px">
        <div class="modal-header">
          <h3 style="display:flex;align-items:center;gap:.5rem;color:#e74c3c;margin:0">
            <i class="fas fa-triangle-exclamation"></i> ${esc(t().penalizacion_titulo || 'Perdiste reputación')}
          </h3>
        </div>
        <div class="modal-body">
          <p style="margin:0 0 1rem;color:var(--text-2)">${esc(t().penalizacion_intro || 'Un moderador revisó contenido tuyo y decidió retirarlo:')}</p>
          <div style="display:flex;flex-direction:column;gap:.6rem">
            ${penalizaciones.map((p) => `
              <div style="display:flex;justify-content:space-between;gap:1rem;align-items:center;background:rgba(231,76,60,.08);border:1px solid rgba(231,76,60,.25);border-radius:var(--radius);padding:.6rem .9rem">
                <span style="font-size:.85rem;color:var(--text)">${esc(p.razon)}</span>
                <span style="font-weight:800;color:#e74c3c;white-space:nowrap">${(t().penalizacion_puntos || '{n} de reputación').replace('{n}', p.puntos)}</span>
              </div>`).join('')}
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" id="penalizacionCerrarBtn">${esc(t().penalizacion_boton || 'Entendido')}</button>
        </div>
      </div>`;
    document.body.appendChild(backdrop);
    requestAnimationFrame(() => backdrop.classList.add('open'));

    const cerrar = () => {
      backdrop.classList.remove('open');
      setTimeout(() => backdrop.remove(), 300);
    };
    backdrop.querySelector('#penalizacionCerrarBtn').addEventListener('click', cerrar);
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) cerrar(); });
  }

  async function verificar() {
    try {
      const r = await fetch('php/gamificacion.php?accion=penalizaciones_pendientes');
      const d = await r.json();
      if (d && d.success && d.penalizaciones && d.penalizaciones.length) {
        mostrarModal(d.penalizaciones);
      }
    } catch (e) { /* silencioso: no es critico */ }
  }

  // Solo tiene sentido para usuarios con sesion iniciada. El propio body
  // de las paginas trae data-usuario-id cuando hay sesion (ver civi.php,
  // debate.php); si no esta presente en esta pagina, igual se intenta —
  // el backend responde una lista vacia sin sesion, sin coste extra.
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', verificar);
  else verificar();
})();
