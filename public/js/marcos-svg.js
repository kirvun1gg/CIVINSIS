/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Marcos SVG
   Cinco marcos con SILUETA propia (no cinco anillos):
     Aurora       cintas de luz abiertas, asimetricas
     Dual         mitad geometrica + mitad organica
     Conexion     red irregular de nodos con pulsos
     Constelacion campo de estrellas disperso, sin anillo
     Idea         trazos que convergen hacia una chispa
   Se reconocen en blanco y negro: la diferencia esta en la
   composicion, no en el color.

   Sin dependencias. El SVG se inyecta una sola vez por avatar y
   toda la animacion la resuelve CSS (transform / opacity /
   stroke-dashoffset), que el navegador acelera por GPU.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  /* Lienzo 160x160. El avatar ocupa el circulo central (r=50),
     asi que los marcos dibujan entre r=52 y r=78. */
  const D = {

    // ── AURORA · cintas de luz que fluyen ──────────────────
    'marco-aurora': `
<svg class="mk mk-aurora" viewBox="0 0 160 160" aria-hidden="true">
  <defs>
    <linearGradient id="mkAu1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#36c0a1"/><stop offset=".55" stop-color="#4a9eff"/><stop offset="1" stop-color="#a855f7"/>
    </linearGradient>
    <linearGradient id="mkAu2" x1="1" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#ff7ac6"/><stop offset=".6" stop-color="#a855f7"/><stop offset="1" stop-color="#36c0a1"/>
    </linearGradient>
  </defs>
  <g class="au-a"><path class="cinta" d="M80 12 A68 68 0 0 1 148 80"     stroke="url(#mkAu1)" stroke-width="5"/></g>
  <g class="au-b"><path class="cinta" d="M80 158 A78 78 0 0 1 6 92"      stroke="url(#mkAu2)" stroke-width="3.4"/></g>
  <g class="au-c"><path class="cinta" d="M26 34 A62 62 0 0 1 118 26"     stroke="url(#mkAu2)" stroke-width="2.4"/></g>
  <g class="au-d"><path class="cinta" d="M144 108 A72 72 0 0 1 74 152"   stroke="url(#mkAu1)" stroke-width="4"/></g>
  <g class="au-p">
    <circle class="chispa" cx="80" cy="8"  r="2.2"/>
    <circle class="chispa" cx="152" cy="86" r="1.6"/>
    <circle class="chispa" cx="16" cy="70" r="1.9"/>
  </g>
</svg>`,

    // ── DUAL · dos sistemas opuestos ───────────────────────
    // Izquierda: barras rectas y rigidas. Derecha: curvas suaves.
    'marco-dual': `
<svg class="mk mk-dual" viewBox="0 0 160 160" aria-hidden="true">
  <g class="du-geo">
    <path d="M80 8 V22"/><path d="M46 17 L53 30"/><path d="M21 42 L34 49"/>
    <path d="M12 80 H26"/><path d="M21 118 L34 111"/><path d="M46 143 L53 130"/>
    <path d="M80 152 V138"/>
  </g>
  <g class="du-org">
    <path class="onda" d="M80 10 A70 70 0 0 1 80 150"/>
    <path class="onda o2" d="M80 20 A60 60 0 0 1 80 140"/>
  </g>
  <circle class="du-nodo n1" cx="80" cy="12" r="3.4"/>
  <circle class="du-nodo n2" cx="80" cy="148" r="3.4"/>
</svg>`,

    // ── CONEXION · red irregular con pulsos ────────────────
    'marco-conexion': `
<svg class="mk mk-conexion" viewBox="0 0 160 160" aria-hidden="true">
  <g class="cx-lineas">
    <path class="cx-l l1" d="M80 14 L132 46"/>
    <path class="cx-l l2" d="M132 46 L142 104"/>
    <path class="cx-l l3" d="M142 104 L92 146"/>
    <path class="cx-l l4" d="M92 146 L30 124"/>
    <path class="cx-l l5" d="M30 124 L16 62"/>
    <path class="cx-l l6" d="M16 62 L80 14"/>
    <path class="cx-l l7" d="M132 46 L30 124"/>
    <path class="cx-rama" d="M142 104 L158 118"/>
    <path class="cx-rama" d="M16 62 L2 48"/>
  </g>
  <g class="cx-nodos">
    <circle class="cx-n n1" cx="80"  cy="14"  r="5.5"/>
    <circle class="cx-n n2" cx="132" cy="46"  r="3.6"/>
    <circle class="cx-n n3" cx="142" cy="104" r="6"/>
    <circle class="cx-n n4" cx="92"  cy="146" r="4.2"/>
    <circle class="cx-n n5" cx="30"  cy="124" r="5.2"/>
    <circle class="cx-n n6" cx="16"  cy="62"  r="3.4"/>
  </g>
</svg>`,

    // ── CONSTELACION · campo estelar disperso ──────────────
    // Estrellas de 4 puntas, tamanos distintos, con espacio vacio.
    'marco-constelacion': `
<svg class="mk mk-constelacion" viewBox="0 0 160 160" aria-hidden="true">
  <g class="ct-hilos">
    <path d="M74 10 L120 34"/><path d="M18 96 L44 132"/>
  </g>
  <g class="ct-estrellas">
    <path class="ct-e e1" d="M74 0 l2.6 7.4 L84 10 l-7.4 2.6 L74 20 l-2.6-7.4 L64 10 l7.4-2.6 Z"/>
    <path class="ct-e e2" d="M120 26 l1.8 5.2 L127 34 l-5.2 1.8 L120 41 l-1.8-5.2 L113 34 l5.2-1.8 Z"/>
    <path class="ct-e e3" d="M150 74 l2.2 6.2 L158 82 l-6.2 2.2 L150 90 l-2.2-6.2 L142 82 l6.2-2.2 Z"/>
    <path class="ct-e e4" d="M112 138 l1.5 4.4 L118 144 l-4.4 1.5 L112 150 l-1.5-4.4 L106 144 l4.4-1.5 Z"/>
    <path class="ct-e e5" d="M44 126 l2.4 6.8 L53 135 l-6.8 2.4 L44 144 l-2.4-6.8 L35 135 l6.8-2.4 Z"/>
    <path class="ct-e e6" d="M14 92 l1.7 4.8 L21 98 l-4.8 1.7 L14 104 l-1.7-4.8 L8 98 l4.8-1.7 Z"/>
    <path class="ct-e e7" d="M26 40 l1.4 4 L32 45 l-4 1.4 L26 51 l-1.4-4 L20 45 l4-1.4 Z"/>
    <circle class="ct-polvo" cx="140" cy="46" r="1.3"/>
    <circle class="ct-polvo" cx="60"  cy="152" r="1.1"/>
    <circle class="ct-polvo" cx="6"   cy="66" r="1.2"/>
  </g>
  <path class="ct-fugaz" d="M118 10 L146 30"/>
</svg>`,

    // ── IDEA · trazos que convergen en una chispa ──────────
    'marco-idea': `
<svg class="mk mk-idea" viewBox="0 0 160 160" aria-hidden="true">
  <g class="id-trazos">
    <path d="M80 6 V26"/><path d="M124 20 L113 37"/><path d="M154 60 L135 68"/>
    <path d="M154 100 L135 92"/><path d="M124 140 L113 123"/><path d="M80 154 V134"/>
    <path d="M36 140 L47 123"/><path d="M6 100 L25 92"/>
    <path d="M6 60 L25 68"/><path d="M36 20 L47 37"/>
  </g>
  <g class="id-chispa">
    <path class="id-nucleo" d="M80 52 l4 14 14 4 -14 4 -4 14 -4 -14 -14 -4 14 -4 Z"/>
  </g>
</svg>`,
  };

  const SELECTORES = '.profile-avatar, .comment-avatar, .author-avatar, .avatar-wrap, .cos-preview';

  function aplicar(el) {
    const clave = [...el.classList].find((c) => D[c]);
    const previo = el.querySelector(':scope > .mk-capa');

    if (!clave) { if (previo) previo.remove(); el.classList.remove('mk-activo'); return; }
    if (previo && previo.dataset.marco === clave) return;   // ya está puesto
    if (previo) previo.remove();

    const capa = document.createElement('span');
    capa.className = 'mk-capa';
    capa.dataset.marco = clave;
    capa.innerHTML = D[clave];
    el.appendChild(capa);
    el.classList.add('mk-activo');

    // animación de activación (se quita sola al terminar)
    capa.classList.add('mk-entrando');
    setTimeout(() => capa.classList.remove('mk-entrando'), 1100);
  }

  function escanear(raiz) {
    (raiz || document).querySelectorAll(SELECTORES).forEach(aplicar);
  }
  window.CosMarcos = { aplicar, escanear, disenos: D };

  function iniciar() {
    escanear();
    // El contenido llega por AJAX (comentarios, tarjetas): re-escanear
    let t;
    new MutationObserver(() => { clearTimeout(t); t = setTimeout(() => escanear(), 120); })
      .observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
