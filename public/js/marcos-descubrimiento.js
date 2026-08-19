/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Descubrimiento de marcos
   Dos cosas:
     1. Registra que secciones y categorias visita el usuario, para
        alimentar los desbloqueos de exploracion.
     2. Cuando se desbloquea un marco, lo anuncia con una animacion
        breve y elegante (GSAP). Nada de ventanas invasivas.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const hayGSAP = () => typeof window.gsap !== 'undefined';

  /* ── 1 · REGISTRO DE EXPLORACION ──────────────────────────
     Se deduce la seccion de la propia URL. Una sola peticion por
     carga, y el backend ignora los repetidos. */
  function registrar(tipo, valor) {
    if (!valor) return Promise.resolve();
    return fetch('php/gamificacion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'explorar', tipo, valor }),
    }).catch(() => {});
  }

  function seccionActual() {
    const p = location.pathname.split('/').pop() || 'inicio';
    return p.replace('.php', '') || 'inicio';
  }

  function rastrear() {
    registrar('seccion', seccionActual());

    // categoria: si la pagina la declara o viene en la URL
    const cat = new URLSearchParams(location.search).get('categoria')
      || document.body.dataset.categoria;
    if (cat) registrar('categoria', cat);

    // al pulsar un filtro de categoria tambien cuenta como explorar
    document.addEventListener('click', (e) => {
      const b = e.target.closest('[data-categoria], [data-cat]');
      if (!b) return;
      const v = b.dataset.categoria || b.dataset.cat;
      if (v && v !== 'todas' && v !== '') registrar('categoria', v);
    }, { passive: true });
  }

  /* ── 2 · AVISO DE DESCUBRIMIENTO ──────────────────────────
     Tarjeta pequeña, abajo a la derecha, con el marco animandose
     dentro. Se va sola. */
  function aviso({ nombre, rareza, descripcion, clase, secreto }) {
    const RAR = {
      comun: '#8fb8c4', poco_comun: '#7fd4a8', raro: '#7fb8ff',
      epico: '#c9a6ff', legendario: '#ffcf7a', mitico: '#ff9ad5',
    };
    const col = RAR[rareza] || '#7fd4a8';

    const caja = document.createElement('div');
    caja.className = 'mk-aviso';
    caja.style.cssText = `position:fixed;right:20px;bottom:20px;z-index:10040;
      display:flex;align-items:center;gap:14px;
      background:var(--bg-card,#121a1f);border:1px solid ${col}55;
      border-left:3px solid ${col};border-radius:14px;
      padding:14px 18px 14px 14px;box-shadow:0 12px 32px rgba(0,0,0,.4);
      max-width:min(340px,calc(100vw - 40px));`;

    const muestra = document.createElement('div');
    muestra.className = 'profile-avatar ' + (clase || '');
    muestra.style.cssText = `width:54px;height:54px;border-radius:50%;flex-shrink:0;
      background:linear-gradient(135deg,#36c0a1,#ef7e22);position:relative;`;

    const txt = document.createElement('div');
    txt.innerHTML =
      `<div style="font-size:.64rem;font-weight:800;letter-spacing:.08em;
         text-transform:uppercase;color:${col};margin-bottom:3px">
         ${secreto ? '✦ Marco descubierto' : 'Marco desbloqueado'}</div>
       <div style="font-family:var(--font-display,inherit);font-weight:800;
         font-size:.95rem;color:var(--text,#eaf3f0)">${nombre}</div>
       <div style="font-size:.74rem;color:var(--text-muted,#9fb3bd);
         line-height:1.4;margin-top:2px">${descripcion || ''}</div>`;

    caja.appendChild(muestra); caja.appendChild(txt);
    document.body.appendChild(caja);

    // que el motor de marcos monte su animacion dentro de la muestra
    if (window.CosMarcos) window.CosMarcos.montar(muestra);

    if (!hayGSAP()) {
      setTimeout(() => caja.remove(), 6000);
      return;
    }
    gsap.timeline()
      .from(caja, { x: 40, opacity: 0, duration: 0.5, ease: 'power3.out' })
      // la muestra entra un poco despues: se mira primero el texto
      .from(muestra, { scale: 0.4, opacity: 0, duration: 0.6,
                       ease: 'back.out(2.2)' }, 0.15)
      .to(caja, { x: 40, opacity: 0, duration: 0.45, ease: 'power2.in',
                  delay: 6, onComplete: () => {
                    if (window.CosMarcos) window.CosMarcos.destruir(muestra);
                    caja.remove();
                  } });
  }

  /* Detecta marcos nuevos comparando con lo que ya se conocia. */
  async function comprobarNuevos() {
    try {
      const r = await fetch('php/gamificacion.php?accion=perfil');
      const d = await r.json();
      if (!d || !d.cosmeticos) return;

      const marcos = d.cosmeticos.filter((c) => c.tipo === 'marco_avatar' && c.desbloqueado);
      const clave = 'mk_conocidos';
      let previos = [];
      try { previos = JSON.parse(localStorage.getItem(clave) || '[]'); } catch (e) {}

      // primera vez: solo se memoriza, sin avisar de todo el catalogo
      if (!previos.length) {
        localStorage.setItem(clave, JSON.stringify(marcos.map((m) => m.clave)));
        return;
      }
      const nuevos = marcos.filter((m) => !previos.includes(m.clave));
      localStorage.setItem(clave, JSON.stringify(marcos.map((m) => m.clave)));

      // se anuncian de uno en uno, no todos a la vez
      nuevos.forEach((m, i) => setTimeout(() => aviso({
        nombre: m.nombre, rareza: m.rareza, descripcion: m.descripcion,
        clase: m.valor, secreto: !!m.oculto,
      }), i * 1200));
    } catch (e) { /* silencioso */ }
  }

  window.CosDescubrimiento = { aviso, comprobarNuevos, registrar };

  function iniciar() {
    rastrear();
    // se comprueba sin prisa, cuando el navegador tenga un hueco
    if ('requestIdleCallback' in window) requestIdleCallback(comprobarNuevos);
    else setTimeout(comprobarNuevos, 2000);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
