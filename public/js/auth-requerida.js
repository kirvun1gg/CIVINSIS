/* ============================================================
   CIVINSIS · Aviso "necesitas una cuenta"
   Modal reutilizable en toda la plataforma para las acciones que
   exigen sesión: crear propuesta, crear debate, comentar y aceptar
   un desafío (ver uno NO la exige — solo aceptarlo).
   Uso: CiviRequiereCuenta('crear_propuesta')
   ============================================================ */
(function () {
  'use strict';

  function t() { return (window.CIVI_I18N && CIVI_I18N.toast && CIVI_I18N.toast.comunes) || {}; }

  const MENSAJES = {
    crear_propuesta: 'auth_msg_crear_propuesta',
    crear_debate:    'auth_msg_crear_debate',
    comentar:        'auth_msg_comentar',
    aceptar_desafio: 'auth_msg_aceptar_desafio',
  };

  let modal = null;

  function construir() {
    if (modal) return modal;
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.id = 'authRequiredModal';
    backdrop.innerHTML = `
      <div class="confirm-modal" style="--confirm-color:#36c0a1">
        <button class="confirm-modal-close" type="button" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        <div class="confirm-modal-icon-ring">
          <div class="confirm-modal-icon-wrap"><i class="fas fa-user-lock"></i></div>
        </div>
        <h3 class="confirm-modal-title" id="authRequiredTitle"></h3>
        <p class="confirm-modal-message" id="authRequiredMessage"></p>
        <div class="confirm-modal-actions stack">
          <a href="auth.php" class="confirm-modal-btn confirm-modal-btn-confirm">
            <i class="fas fa-right-to-bracket"></i> <span id="authRequiredLoginLabel"></span>
          </a>
          <a href="auth.php?tab=registro" class="confirm-modal-btn confirm-modal-btn-ghost">
            <span id="authRequiredRegisterLabel"></span>
          </a>
        </div>
      </div>`;
    document.body.appendChild(backdrop);

    const cerrar = () => backdrop.classList.remove('open');
    backdrop.querySelector('.confirm-modal-close').addEventListener('click', cerrar);
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) cerrar(); });

    modal = backdrop;
    return backdrop;
  }

  function requerirCuenta(accion) {
    const m = construir();
    const claveMsg = MENSAJES[accion];
    m.querySelector('#authRequiredTitle').textContent = t().auth_titulo_requiere_cuenta || 'Necesitas una cuenta';
    m.querySelector('#authRequiredMessage').textContent = (claveMsg && t()[claveMsg]) || t().auth_msg_generico || 'Necesitas tener una cuenta en CIVINSIS para continuar.';
    m.querySelector('#authRequiredLoginLabel').textContent = t().auth_iniciar_sesion || 'Iniciar sesión';
    m.querySelector('#authRequiredRegisterLabel').textContent = t().auth_crear_cuenta || 'Crear cuenta gratis';
    requestAnimationFrame(() => m.classList.add('open'));
  }

  window.CiviRequiereCuenta = requerirCuenta;
})();
