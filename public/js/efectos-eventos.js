/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Disparador de efectos
   El motor (efectos.js) sabe DIBUJAR, pero alguien tiene que
   decirle CUANDO. Eso es este archivo.

   Escucha lo que ya ocurre en la plataforma:
     · los avisos de exito (comentar, votar, crear, guardar…)
     · las subidas de nivel y los logros que detecta CIVI
     · eventos propios lanzados a mano

   Y reproduce el efecto que el usuario lleva equipado sobre su
   avatar, alli donde este visible (perfil, comentario, tarjeta).

   Uso manual:  CIVINSIS.evento('subida_nivel')
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  let equipado = null;      // clase CSS, p. ej. 'efecto-aurora'
  let suEvento = null;      // evento que lo dispara, p. ej. 'subida_nivel'
  let cargado = false;
  let ultimo = 0;           // para no encadenar efectos sin parar

  /* Un mismo efecto no deberia repetirse mas de una vez cada 2,5 s */
  const ESPERA = 2500;

  async function cargarConfig() {
    if (cargado) return;
    cargado = true;
    try {
      /* En el perfil de otro usuario se muestra SU cosmetico, no el mio:
         asi quien visita ve el efecto del dueño del perfil. */
      const otro = new URLSearchParams(location.search).get('id');
      const esPerfilAjeno = location.pathname.includes('usuario.php') && otro;
      const url = esPerfilAjeno
        ? 'php/gamificacion.php?accion=perfil_publico&id=' + encodeURIComponent(otro)
        : 'php/gamificacion.php?accion=perfil';
      const r = await fetch(url);
      const d = await r.json();
      if (d && d.success !== false) {
        equipado = d.efecto_clase || null;
        suEvento = d.efecto_evento || null;
        if (window.CosEfectos) {
          window.CosEfectos.configurar({
            efecto: equipado,
            eventos: equipado ? { [equipado]: suEvento } : {},
          });
        }
      }
    } catch (e) { /* silencioso: sin efecto no pasa nada grave */ }
  }

  /* ── ¿Donde se dibuja? ────────────────────────────────────
     En el perfil hay un avatar grande y se usa ese. Pero al
     comentar o votar estas en OTRA pagina, donde solo existe el
     avatar diminuto de la barra superior… que ademas queda
     recortado por la propia barra. Por eso el efecto "no aparecia":
     se dibujaba, pero en un sitio invisible.
     Solucion: si no hay avatar grande, se monta una capa flotante
     sobre el avatar de la barra, fuera de cualquier recorte.
     ───────────────────────────────────────────────────────── */
  /* Un elemento cuenta como visible si tiene tamaño real y esta dentro
     de la ventana. El minimo son 24 px: los avatares de comentario
     miden 38 px y el umbral anterior (40) los descartaba a todos. */
  function visible(el) {
    if (!el) return false;
    const r = el.getBoundingClientRect();
    return r.width >= 24 && r.bottom > 0 && r.top < window.innerHeight;
  }

  /* Para elegir DONDE reproducir el efecto propio se pide un avatar
     grande: en el perfil hay uno de 100 px y es el sitio natural. */
  function visibleGrande(el) {
    return visible(el) && el.getBoundingClientRect().width > 60;
  }

  /* ¿Donde se dibuja?
     SOLO sobre avatares de verdad del usuario: el grande del perfil
     o el que aparece junto a sus comentarios y propuestas.
     Nunca en la barra de navegacion: ahi el cosmetico no pinta nada
     y ademas quedaria como una animacion permanente en el menu. */
  function destino() {
    const grande = document.getElementById('profileAvatarDisplay')
      || document.querySelector('.profile-avatar');
    if (visibleGrande(grande)) return grande;

    // avatar propio dentro de un comentario o tarjeta, si esta a la vista
    const propio = [...document.querySelectorAll('.comment-avatar, .author-avatar')]
      .find((el) => visible(el) && el.closest('[data-propio="1"], .es-mio'));
    if (propio) return propio;

    return null;   // sin avatar visible no se reproduce nada
  }

  /**
   * Dispara el efecto equipado.
   * @param {string} nombre  evento ocurrido
   * @param {boolean} exacto si es true, solo se reproduce cuando el
   *        evento coincide con el asignado al efecto.
   */
  async function disparar(nombre, exacto) {
    await cargarConfig();
    if (!equipado || !window.CosEfectos) return false;
    if (exacto && suEvento && suEvento !== nombre) return false;

    const ahora = Date.now();
    if (ahora - ultimo < ESPERA) return false;
    ultimo = ahora;

    const host = destino();
    if (!host) return false;                 // sin avatar visible, no se pinta nada
    window.CosEfectos.reproducir(host, equipado);
    return true;
  }

  /* Los avisos de exito aparecen al comentar, votar, crear una
     propuesta o guardar el perfil: es la señal mas fiable de que el
     usuario acaba de hacer algo. */
  function observarAvisos() {
    new MutationObserver((muts) => {
      for (const m of muts) {
        for (const n of m.addedNodes) {
          if (n.nodeType !== 1) continue;
          if (!n.classList || !n.classList.contains('toast')) continue;
          if (!n.querySelector || !n.querySelector('.toast-icon.success')) continue;

          const txt = (n.textContent || '').toLowerCase();
          let ev = 'accion_exitosa';
          if (txt.includes('propuesta') && txt.includes('cre')) ev = 'propuesta_creada';
          else if (txt.includes('coment')) ev = 'comentario_creado';
          else if (txt.includes('vot') || txt.includes('apoy') || txt.includes('valorad')) ev = 'voto_emitido';
          else if (txt.includes('desaf')) ev = 'desafio_completado';
          else if (txt.includes('mision') || txt.includes('misión')) ev = 'mision_completada';
          else if (txt.includes('respuesta') || txt.includes('debate')) ev = 'comentario_creado';
          else if (txt.includes('guard') || txt.includes('actualiz')) ev = 'perfil_actualizado';
          disparar(ev, false);
          return;
        }
      }
    }).observe(document.body, { childList: true, subtree: true });
  }

  /* CIVI ya detecta subidas de nivel y logros: se aprovecha eso. */
  function observarCelebraciones() {
    new MutationObserver((muts) => {
      for (const m of muts) {
        for (const n of m.addedNodes) {
          if (n.nodeType !== 1) continue;
          if (n.classList && n.classList.contains('cv-celebra')) {
            const t = (n.textContent || '').toLowerCase();
            disparar(t.includes('logro') ? 'logro_desbloqueado' : 'subida_nivel', false);
            return;
          }
        }
      }
    }).observe(document.body, { childList: true, subtree: true });
  }

  /* ── REPETICION AMBIENTAL ─────────────────────────────────
     El efecto no se reproduce solo una vez y ya: vuelve cada cierto
     tiempo, SIEMPRE la animacion completa desde el principio (cada
     pase crea una escena nueva, no un resto de la anterior).
     El intervalo es largo y con algo de variacion para que no
     resulte repetitivo ni parezca un metronomo.
     ───────────────────────────────────────────────────────── */
  const CADA = 16000;          // ~16 s entre repeticiones
  const VARIACION = 6000;      // +0 a 6 s al azar
  let temporizador = null;

  let primerPase = true;

  /* ── EFECTOS EN COMENTARIOS Y TARJETAS ────────────────────
     Cada avatar con data-efecto reproduce el suyo de vez en cuando,
     escalonado para que no salten todos a la vez, y solo mientras
     esta a la vista. */
  const vistos = new WeakSet();

  function ambientarLista() {
    // No se filtra por visibilidad aqui: un comentario mas abajo de la
    // pagina tambien debe quedar programado, y ya se comprueba que este
    // a la vista en el momento de reproducir.
    const avs = [...document.querySelectorAll('[data-efecto]')];
    avs.forEach((el, i) => {
      if (vistos.has(el)) return;
      vistos.add(el);
      const clave = el.dataset.efecto;
      if (!clave || !window.CosEfectos) return;
      // primer pase escalonado (0,8 s entre avatares) y luego cada ciclo
      const arranque = 2500 + i * 800 + Math.random() * 1200;
      const lanzar = () => {
        if (!document.hidden && visible(el)) window.CosEfectos.reproducir(el, clave);
        setTimeout(lanzar, CADA + Math.random() * VARIACION);
      };
      setTimeout(lanzar, arranque);
    });
  }

  function programar() {
    clearTimeout(temporizador);
    /* El PRIMER pase llega pronto (3-5 s) para que quien entra al
       perfil —el propio usuario o alguien que lo visita— vea el
       cosmetico sin tener que esperar. Los siguientes ya van
       espaciados. */
    const espera = primerPase
      ? 3000 + Math.random() * 2000
      : CADA + Math.random() * VARIACION;
    primerPase = false;
    temporizador = setTimeout(async () => {
      // solo si la pestana esta a la vista y hay un avatar visible
      if (!document.hidden) {
        await cargarConfig();
        if (equipado && window.CosEfectos) {
          const host = destino();
          if (host) {
            ultimo = 0;                       // la repeticion no cuenta como accion
            window.CosEfectos.reproducir(host, equipado);
          }
        }
      }
      programar();
    }, espera);
  }

  /* Algunas acciones no muestran aviso (votar cambia el boton y ya).
     Se escuchan tambien esos clics para que el efecto no se pierda. */
  function observarClics() {
    document.addEventListener('click', (ev) => {
      const b = ev.target.closest('[data-voto], .vote-btn, .btn-votar, .aspecto-btn, .vote-aspect');
      if (b) setTimeout(() => disparar('voto_emitido', false), 500);
    }, { passive: true });
  }

  /* ── API publica ─────────────────────────────────────────── */
  window.CIVINSIS = window.CIVINSIS || {};
  window.CIVINSIS.evento = (nombre) => disparar(nombre, true);
  window.CIVINSIS.efectoAhora = () => disparar('manual', false);
  window.CIVINSIS.recargarEfecto = () => { cargado = false; return cargarConfig(); };
  /** Ajustar o detener la repeticion ambiental desde fuera. */
  window.CIVINSIS.repetirEfecto = (activar) => {
    clearTimeout(temporizador);
    if (activar !== false) programar();
  };

  function iniciar() {
    observarAvisos();
    observarCelebraciones();
    observarClics();
    programar();
    ambientarLista();
    // los comentarios llegan por AJAX: revisar cuando cambie el DOM
    let tl;
    new MutationObserver((muts) => {
      // Ignorar los cambios que provoca el propio efecto (sus lienzos),
      // o el observador se retroalimentaria sin parar.
      const relevante = muts.some((m) =>
        [...m.addedNodes, ...m.removedNodes].some((n) =>
          n.nodeType === 1 && !n.classList.contains('fx-efecto')));
      if (!relevante) return;
      clearTimeout(tl); tl = setTimeout(ambientarLista, 400);
    }).observe(document.body, { childList: true, subtree: true });                 // repeticion ambiental cada ~16-22 s
    // se carga la configuracion en cuanto haya un momento libre
    if ('requestIdleCallback' in window) requestIdleCallback(cargarConfig);
    else setTimeout(cargarConfig, 1200);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
