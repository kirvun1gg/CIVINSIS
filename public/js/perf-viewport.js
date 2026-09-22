/* ============================================================
   CIVINSIS · PerfShared — utilidades de rendimiento compartidas
   No sabe nada de cosméticos ni de ningún motor en particular: solo
   expone dos primitivas reutilizables para que marcos-gsap.js,
   efectos-gsap.js, fondos-gsap.js y otros scripts puedan:

   A) pausar trabajo (timelines GSAP, rAF loops) cuando su elemento
      sale del viewport o la pestaña se oculta, y reanudarlo tal cual
      estaba (nunca kill+remount, para no reiniciar posiciones
      aleatorias ni el progreso de la animación).
   B) compartir un único MutationObserver sobre document.body en vez
      de que cada archivo cree el suyo — cada suscriptor conserva su
      propio debounce y filtrado de mutaciones exactamente igual que
      si tuviera su propio observer.
   ============================================================ */
(function () {
  'use strict';

  // ── A. watch()/unwatch() — pausa/reanuda fuera de viewport o pestaña oculta ──
  const callbacks = new WeakMap(); // el -> {onEnter, onExit}
  const visibleEnViewport = new WeakMap(); // el -> bool (última intersección conocida)
  const observados = new Set(); // WeakMap no es iterable; esto es solo para el fallback de visibilitychange

  let io = null;
  function getIO() {
    if (io) return io;
    io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        const cb = callbacks.get(entry.target);
        if (!cb) return;
        visibleEnViewport.set(entry.target, entry.isIntersecting);
        if (document.hidden) return; // la pestaña oculta manda; ver el listener de visibilitychange
        if (entry.isIntersecting) { if (cb.onEnter) cb.onEnter(); }
        else if (cb.onExit) cb.onExit();
      });
    }, { threshold: 0 });
    return io;
  }

  function watch(el, opciones) {
    if (!el) return;
    const { onEnter, onExit } = opciones || {};
    callbacks.set(el, { onEnter, onExit });
    observados.add(el);
    getIO().observe(el);
  }

  function unwatch(el) {
    if (!el) return;
    callbacks.delete(el);
    visibleEnViewport.delete(el);
    observados.delete(el);
    if (io) io.unobserve(el);
  }

  // Pestaña oculta/visible: manda por encima del viewport. Al ocultarse se
  // pausa TODO lo observado (esté o no en pantalla); al volver, se reanuda
  // solo lo que además siga intersectando el viewport.
  document.addEventListener('visibilitychange', () => {
    observados.forEach((el) => {
      const cb = callbacks.get(el);
      if (!cb) return;
      if (document.hidden) {
        if (cb.onExit) cb.onExit();
      } else if (visibleEnViewport.get(el) && cb.onEnter) {
        cb.onEnter();
      }
    });
  });

  // ── B. onMutations()/offMutations() — un solo MutationObserver compartido ──
  const suscriptores = new Set(); // callbacks(records) registrados
  let mo = null;
  let opcionesAcumuladas = { childList: false, subtree: false, attributes: false, attributeFilter: undefined };

  function unionOpciones(a, b) {
    const attrFilterUnion = (a.attributeFilter || b.attributeFilter)
      ? Array.from(new Set([...(a.attributeFilter || []), ...(b.attributeFilter || [])]))
      : undefined;
    return {
      childList: a.childList || !!b.childList,
      subtree: a.subtree || !!b.subtree,
      attributes: a.attributes || !!b.attributes,
      // Si algún suscriptor pide 'attributes' sin restringir a un filtro
      // específico, hay que observar TODOS los atributos (superset real),
      // no solo la unión de los filtros — si no, ese suscriptor dejaría de
      // recibir mutaciones de atributos que no estén en la lista de otros.
      attributeFilter: (a.attributes && !a.attributeFilter) || (b.attributes && !b.attributeFilter)
        ? undefined
        : attrFilterUnion,
    };
  }

  function reobservar() {
    if (!mo) return;
    mo.disconnect();
    mo.observe(document.body, opcionesAcumuladas);
  }

  function onMutations(callback, opciones) {
    if (typeof callback !== 'function') return;
    suscriptores.add(callback);
    opcionesAcumuladas = unionOpciones(opcionesAcumuladas, opciones || {});
    if (!mo) {
      mo = new MutationObserver((records) => {
        suscriptores.forEach((cb) => {
          try { cb(records); } catch (e) { /* un suscriptor roto no debe tumbar a los demás */ }
        });
      });
    }
    reobservar();
  }

  function offMutations(callback) {
    suscriptores.delete(callback);
  }

  // ── C. Pausa/reanuda TODOS los cosméticos de una — útil si algún día se
  //      agrega un toggle de "reducir animaciones"; no tiene consumidor hoy. ──
  function pausarTodoCosmetico(v) {
    if (window.CosMarcos) window.CosMarcos.pausar(v);
    if (window.CosEfectosGSAP) window.CosEfectosGSAP.pausar(v);
    if (window.FondosGSAP) window.FondosGSAP.pausarTodo(v);
  }

  window.PerfShared = { watch, unwatch, onMutations, offMutations, pausarTodoCosmetico };
})();
