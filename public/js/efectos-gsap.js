/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Efectos · motor GSAP + SVG
   16 microeventos visuales. Cada uno con mecanica propia.

   ARQUITECTURA
     crear(capas, ctx) -> gsap.timeline()
       capas.atras / capas.delante  son dos grupos <g> de SVG:
       uno detras de la fotografia y otro delante.
     El motor se encarga de montar, reproducir y DESTRUIR.

   SISTEMA DE COORDENADAS
     Ambos SVG usan viewBox "0 0 200 200". El avatar es el circulo
     centrado en (100,100) con radio 50. Todo lo que se dibuje fuera
     de ese radio queda alrededor del avatar.
     Como es SVG, el efecto ESCALA SOLO: se ve igual en un avatar de
     100 px (perfil) que en uno de 38 px (comentario), sin factores
     de correccion.

   REGLA: toda la animacion la hace GSAP. Sin @keyframes, sin
   setInterval, sin sistemas paralelos.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const NS = 'http://www.w3.org/2000/svg';
  const menos = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayGSAP = () => typeof window.gsap !== 'undefined';

  const C = 100, R = 50;                       // centro y radio del avatar

  /* Crear nodos SVG de forma breve ---------------------------- */
  function n(tag, attrs, padre) {
    const e = document.createElementNS(NS, tag);
    for (const k in attrs) e.setAttribute(k, attrs[k]);
    if (padre) padre.appendChild(e);
    return e;
  }
  /** Punto en la circunferencia del avatar, a distancia d (en radios). */
  const pol = (ang, d) => ({ x: C + Math.cos(ang) * R * d, y: C + Math.sin(ang) * R * d });
  const TAU = Math.PI * 2;

  /* Paleta por rareza (la rareza tambien se lee en el color) */
  const COL = {
    comun: '#9fe8d8', raro: '#7fd4ff', epico: '#c9a6ff', legendario: '#ffd98a',
  };

  /* ═════════════════════════════════════════════════════════
     LOS 16 EFECTOS
     dur = duracion del evento real (s)
     ═════════════════════════════════════════════════════════ */
  const EF = {

    /* ── CHISPA · comun ─────────────────────────────────────
       Mecanica: una linea energetica corta que nace, se estira
       y se rompe en 3 chispas. Sin explosion. */
    'efecto-chispa': {
      dur: 1.1, rareza: 'comun',
      crear(cp) {
        const g = cp.delante, col = COL.comun;
        const a = -Math.PI / 3;
        const p1 = pol(a, 1.02), p2 = pol(a, 1.5);
        const linea = n('line', { x1: p1.x, y1: p1.y, x2: p1.x, y2: p1.y,
          stroke: col, 'stroke-width': 2.4, 'stroke-linecap': 'round', opacity: 0 }, g);
        const chispas = [0, 1, 2].map((i) =>
          n('circle', { cx: p2.x, cy: p2.y, r: 1.8, fill: col, opacity: 0 }, g));

        return gsap.timeline()
          .to(linea, { opacity: 1, duration: 0.1 })
          .to(linea, { attr: { x2: p2.x, y2: p2.y }, duration: 0.24, ease: 'power3.out' }, 0)
          .to(linea, { attr: { x1: p2.x, y1: p2.y }, duration: 0.2, ease: 'power2.in' }, 0.26)
          .to(linea, { opacity: 0, duration: 0.14 }, 0.34)
          .to(chispas, {
            opacity: 1, duration: 0.1,
            stagger: 0.04,
          }, 0.34)
          .to(chispas, {
            x: (i) => Math.cos(a + (i - 1) * 0.9) * 22,
            y: (i) => Math.sin(a + (i - 1) * 0.9) * 22,
            opacity: 0, scale: 0.4, transformOrigin: 'center',
            duration: 0.5, ease: 'power2.out', stagger: 0.04,
          }, 0.4);
      },
    },

    /* ── REBOTE · comun ─────────────────────────────────────
       Mecanica: una forma sale, rebota y vuelve. Reaccion
       inmediata, lo mas simple del catalogo. */
    'efecto-rebote': {
      dur: 1.0, rareza: 'comun',
      crear(cp) {
        const g = cp.delante, col = COL.comun;
        const a = Math.PI;                       // sale por la izquierda
        const ini = pol(a, 1.05), fin = pol(a, 1.75);
        const b = n('circle', { cx: ini.x, cy: ini.y, r: 4, fill: col, opacity: 0 }, g);
        const onda = n('circle', { cx: fin.x, cy: fin.y, r: 3, fill: 'none',
          stroke: col, 'stroke-width': 1.6, opacity: 0 }, g);

        return gsap.timeline()
          .to(b, { opacity: 1, duration: 0.08 })
          .to(b, { attr: { cx: fin.x, cy: fin.y }, duration: 0.3, ease: 'power2.out' }, 0)
          .to(onda, { opacity: 0.9, attr: { r: 12 }, duration: 0.3, ease: 'power2.out' }, 0.28)
          .to(onda, { opacity: 0, duration: 0.2 }, 0.5)
          .to(b, { attr: { cx: ini.x, cy: ini.y }, duration: 0.34, ease: 'back.in(2)' }, 0.34)
          .to(b, { opacity: 0, attr: { r: 1 }, duration: 0.18 }, 0.62);
      },
    },

    /* ── IDEA · raro ────────────────────────────────────────
       Mecanica: CONVERGENCIA. Cuatro puntos dispersos se juntan
       arriba y forman un rombo que destella. */
    'efecto-idea': {
      dur: 1.9, rareza: 'raro',
      crear(cp) {
        const g = cp.delante, col = COL.raro;
        const foco = pol(-Math.PI / 2, 1.22);
        const pts = [0, 1, 2, 3].map((i) => {
          const o = pol(-Math.PI / 2 + (i - 1.5) * 0.75, 1.95);
          return n('circle', { cx: o.x, cy: o.y, r: 2.2, fill: col, opacity: 0 }, g);
        });
        const rombo = n('path', {
          d: `M${foco.x} ${foco.y - 9} L${foco.x + 7} ${foco.y} L${foco.x} ${foco.y + 9} L${foco.x - 7} ${foco.y} Z`,
          fill: 'none', stroke: col, 'stroke-width': 1.6, opacity: 0,
        }, g);
        const nucleo = n('circle', { cx: foco.x, cy: foco.y, r: 2, fill: '#fff', opacity: 0 }, g);

        return gsap.timeline()
          .to(pts, { opacity: 1, duration: 0.2, stagger: 0.06 })
          // se acercan al foco: el movimiento ES la idea formandose
          .to(pts, {
            attr: { cx: foco.x, cy: foco.y }, duration: 0.75,
            ease: 'power2.inOut', stagger: 0.05,
          }, 0.25)
          .to(pts, { opacity: 0, duration: 0.15 }, 0.95)
          .to(rombo, { opacity: 1, duration: 0.2, ease: 'power2.out' }, 0.95)
          .fromTo(rombo, { scale: 0.4, transformOrigin: `${foco.x}px ${foco.y}px` },
                         { scale: 1, duration: 0.4, ease: 'back.out(2.4)' }, 0.95)
          .to(nucleo, { opacity: 1, attr: { r: 4 }, duration: 0.18 }, 1.05)
          .to(nucleo, { opacity: 0, attr: { r: 1.5 }, duration: 0.3 }, 1.25)
          .to(rombo, { opacity: 0, scale: 1.5, duration: 0.45, ease: 'power2.in' }, 1.3);
      },
    },

    /* ── IMPULSO · raro ─────────────────────────────────────
       Mecanica: una onda unica, rapida y controlada, que nace
       en el borde. Nada de explosion. */
    'efecto-impulso': {
      dur: 1.1, rareza: 'raro',
      crear(cp) {
        const col = COL.raro;
        const o1 = n('circle', { cx: C, cy: C, r: R, fill: 'none',
          stroke: col, 'stroke-width': 3, opacity: 0 }, cp.delante);
        const o2 = n('circle', { cx: C, cy: C, r: R, fill: 'none',
          stroke: col, 'stroke-width': 1.4, opacity: 0 }, cp.atras);

        return gsap.timeline()
          .to(o1, { opacity: 0.95, duration: 0.08 })
          .to(o1, { attr: { r: R * 1.85, 'stroke-width': 0.4 }, opacity: 0,
                    duration: 0.85, ease: 'power2.out' }, 0)
          .to(o2, { opacity: 0.5, duration: 0.08 }, 0.14)
          .to(o2, { attr: { r: R * 1.6, 'stroke-width': 0.3 }, opacity: 0,
                    duration: 0.8, ease: 'power2.out' }, 0.14);
      },
    },

    /* ── ECO · raro ─────────────────────────────────────────
       Mecanica: tres ondas encadenadas, cada una mas grande y
       mas tenue. Propagacion, no impacto. */
    'efecto-eco': {
      dur: 2.0, rareza: 'raro',
      crear(cp) {
        const col = COL.raro;
        const tl = gsap.timeline();
        [[1.0, 0.95, 3], [1.35, 0.6, 2], [1.75, 0.3, 1.2]].forEach(([esc, op, gr], i) => {
          const c = n('circle', { cx: C, cy: C, r: R * 0.98, fill: 'none',
            stroke: col, 'stroke-width': gr, opacity: 0 }, i === 0 ? cp.delante : cp.atras);
          tl.to(c, { opacity: op, duration: 0.1 }, i * 0.3)
            .to(c, { attr: { r: R * esc }, duration: 0.9, ease: 'power2.out' }, i * 0.3)
            .to(c, { opacity: 0, duration: 0.5 }, i * 0.3 + 0.5);
        });
        return tl;
      },
    },

    /* ── ORBITA · raro ──────────────────────────────────────
       Mecanica: tres particulas dan UNA vuelta corta, aceleran
       y salen despedidas. Orbita breve, no permanente. */
    'efecto-orbita': {
      dur: 1.9, rareza: 'raro',
      crear(cp) {
        const col = COL.raro;
        const grupo = n('g', {}, cp.delante);
        const ps = [0, 1, 2].map((i) => {
          const p = pol((i / 3) * TAU, 1.3);
          return n('circle', { cx: p.x, cy: p.y, r: 3, fill: col, opacity: 0 }, grupo);
        });
        gsap.set(grupo, { transformOrigin: `${C}px ${C}px` });

        return gsap.timeline()
          .to(ps, { opacity: 1, duration: 0.2, stagger: 0.07 })
          // una vuelta y media, acelerando
          .to(grupo, { rotation: 540, duration: 1.25, ease: 'power2.in' }, 0.1)
          // y salen por la tangente
          .to(ps, {
            attr: { r: 1.5 },
            x: (i) => Math.cos((i / 3) * TAU + 1.6) * 46,
            y: (i) => Math.sin((i / 3) * TAU + 1.6) * 46,
            opacity: 0, duration: 0.55, ease: 'power2.out', stagger: 0.05,
          }, 1.25);
      },
    },

    /* ── PULSO · epico ──────────────────────────────────────
       Mecanica: TRANSMISION. Una señal recorre una linea con
       rastro y al llegar emite un destello. No es una onda. */
    'efecto-pulso': {
      dur: 1.5, rareza: 'epico',
      crear(cp) {
        const g = cp.delante, col = COL.epico;
        const a = -Math.PI * 0.72;
        const ini = pol(a, 1.0), fin = pol(a, 1.95);
        // guia tenue: la "linea de transmision"
        const guia = n('line', { x1: ini.x, y1: ini.y, x2: fin.x, y2: fin.y,
          stroke: col, 'stroke-width': 1, opacity: 0, 'stroke-dasharray': '3 4' }, g);
        const senal = n('circle', { cx: ini.x, cy: ini.y, r: 3.4, fill: '#fff', opacity: 0 }, g);
        const rastro = n('line', { x1: ini.x, y1: ini.y, x2: ini.x, y2: ini.y,
          stroke: col, 'stroke-width': 2.4, 'stroke-linecap': 'round', opacity: 0 }, g);
        const eco = n('circle', { cx: fin.x, cy: fin.y, r: 2, fill: 'none',
          stroke: col, 'stroke-width': 1.6, opacity: 0 }, g);

        return gsap.timeline()
          .to(guia, { opacity: 0.35, duration: 0.15 })
          .to([senal, rastro], { opacity: 1, duration: 0.12 }, 0.1)
          .to(senal, { attr: { cx: fin.x, cy: fin.y }, duration: 0.6, ease: 'power2.inOut' }, 0.15)
          // el rastro persigue a la señal con retardo
          .to(rastro, { attr: { x2: fin.x, y2: fin.y }, duration: 0.6, ease: 'power2.inOut' }, 0.15)
          .to(rastro, { attr: { x1: fin.x, y1: fin.y }, duration: 0.45, ease: 'power2.in' }, 0.4)
          .to(eco, { opacity: 1, attr: { r: 13 }, duration: 0.45, ease: 'power2.out' }, 0.72)
          .to([senal, eco, guia], { opacity: 0, duration: 0.3 }, 0.9);
      },
    },

    /* ── ESTRELLA FUGAZ · epico ─────────────────────────────
       Mecanica: UNA estrella cruza en diagonal con rastro corto.
       Protagonista unica. */
    'efecto-estrella-fugaz': {
      dur: 1.4, rareza: 'epico',
      crear(cp) {
        const g = cp.delante;
        const x0 = -20, y0 = 30, x1 = 220, y1 = 150;
        const rastro = n('line', { x1: x0, y1: y0, x2: x0, y2: y0,
          stroke: '#fff', 'stroke-width': 2, 'stroke-linecap': 'round', opacity: 0 }, g);
        const estrella = n('circle', { cx: x0, cy: y0, r: 2.6, fill: '#fff', opacity: 0 }, g);

        return gsap.timeline()
          .to([estrella, rastro], { opacity: 1, duration: 0.12 })
          .to(estrella, { attr: { cx: x1, cy: y1 }, duration: 0.95, ease: 'power1.in' }, 0)
          .to(rastro, { attr: { x2: x1, y2: y1 }, duration: 0.95, ease: 'power1.in' }, 0)
          // la cola se recoge: rastro corto, no una linea entera
          .to(rastro, { attr: { x1: x1 - 34, y1: y1 - 17 }, duration: 0.9, ease: 'power1.in' }, 0.06)
          .to([estrella, rastro], { opacity: 0, duration: 0.25 }, 0.85);
      },
    },

    /* ── ENLACE · epico ─────────────────────────────────────
       Mecanica: una señal viaja del usuario a un destino externo
       y alli ocurre un pequeño evento. Una sola conexion. */
    'efecto-enlace': {
      dur: 1.7, rareza: 'epico',
      crear(cp) {
        const g = cp.delante, col = COL.epico;
        const ini = pol(0.1, 1.0), fin = pol(-0.55, 2.05);
        const trazo = n('path', {
          d: `M${ini.x} ${ini.y} Q${(ini.x + fin.x) / 2 + 16} ${(ini.y + fin.y) / 2 - 22} ${fin.x} ${fin.y}`,
          fill: 'none', stroke: col, 'stroke-width': 1.8, 'stroke-linecap': 'round', opacity: 0,
        }, g);
        const senal = n('circle', { r: 3.2, fill: '#fff', opacity: 0 }, g);
        const destino = n('circle', { cx: fin.x, cy: fin.y, r: 3, fill: 'none',
          stroke: col, 'stroke-width': 1.8, opacity: 0 }, g);

        const largo = trazo.getTotalLength ? trazo.getTotalLength() : 120;
        gsap.set(trazo, { attr: { 'stroke-dasharray': largo, 'stroke-dashoffset': largo } });
        gsap.set(senal, { attr: { cx: ini.x, cy: ini.y } });

        return gsap.timeline()
          .to(trazo, { opacity: 0.75, duration: 0.1 })
          // el trazo se dibuja mientras la señal lo recorre
          .to(trazo, { attr: { 'stroke-dashoffset': 0 }, duration: 0.6, ease: 'power2.inOut' }, 0)
          .to(senal, { opacity: 1, duration: 0.1 }, 0)
          .to(senal, { attr: { cx: fin.x, cy: fin.y }, duration: 0.6, ease: 'power2.inOut' }, 0)
          .to(destino, { opacity: 1, attr: { r: 11 }, duration: 0.4, ease: 'back.out(2)' }, 0.6)
          .to(senal, { opacity: 0, duration: 0.2 }, 0.62)
          .to(trazo, { attr: { 'stroke-dashoffset': -largo }, opacity: 0,
                       duration: 0.55, ease: 'power2.in' }, 0.75)
          .to(destino, { opacity: 0, attr: { r: 16 }, duration: 0.4 }, 0.95);
      },
    },

    /* ── ASCENSO · epico ────────────────────────────────────
       Mecanica: cinco particulas suben por trayectorias
       distintas y destellan al llegar. Progreso, no fuego. */
    'efecto-ascenso': {
      dur: 2.0, rareza: 'epico',
      crear(cp) {
        const col = COL.epico;
        const tl = gsap.timeline();
        const xs = [-1.35, -0.8, 0.75, 1.3, -1.1];
        xs.forEach((k, i) => {
          const g = i % 2 ? cp.delante : cp.atras;   // reparto entre capas
          const x = C + k * R, y = C + R * 1.15;
          const p = n('circle', { cx: x, cy: y, r: 2.4 + (i % 2), fill: col, opacity: 0 }, g);
          const t0 = i * 0.16;
          tl.to(p, { opacity: 1, duration: 0.2 }, t0)
            .to(p, { attr: { cy: C - R * 1.25 }, duration: 1.25, ease: 'power1.out' }, t0)
            // deriva lateral: ninguna sube recta
            .to(p, { attr: { cx: x + (i % 2 ? 12 : -12) }, duration: 1.25, ease: 'sine.inOut' }, t0)
            .to(p, { attr: { r: 0.8 }, opacity: 0, duration: 0.4, ease: 'power2.in' }, t0 + 0.95);
        });
        return tl;
      },
    },

    /* ── REACCION · epico ───────────────────────────────────
       Mecanica: CADENA. Un punto genera dos, y esos dos generan
       otros. Se entiende la propagacion. */
    'efecto-reaccion': {
      dur: 2.1, rareza: 'epico',
      crear(cp) {
        const g = cp.delante, col = COL.epico;
        const raiz = pol(Math.PI * 0.85, 1.05);
        const hijos = [pol(Math.PI * 1.15, 1.5), pol(Math.PI * 0.55, 1.5)];
        const nietos = [pol(Math.PI * 1.35, 1.95), pol(Math.PI * 0.3, 1.95), pol(Math.PI * 0.8, 2.0)];
        const tl = gsap.timeline();

        const nodo = (p, r) => n('circle', { cx: p.x, cy: p.y, r, fill: col, opacity: 0 }, g);
        const enlace = (a, b) => n('line', { x1: a.x, y1: a.y, x2: a.x, y2: a.y,
          stroke: col, 'stroke-width': 1.2, opacity: 0.5 }, g);

        const nr = nodo(raiz, 3.4);
        tl.to(nr, { opacity: 1, attr: { r: 4.5 }, duration: 0.2, ease: 'back.out(3)' });

        hijos.forEach((h, i) => {
          const l = enlace(raiz, h), nd = nodo(h, 2.8);
          tl.to(l, { attr: { x2: h.x, y2: h.y }, duration: 0.3, ease: 'power2.out' }, 0.22 + i * 0.06)
            .to(nd, { opacity: 1, duration: 0.16, ease: 'back.out(3)' }, 0.5 + i * 0.06)
            .to(l, { opacity: 0, duration: 0.4 }, 0.9);
        });
        nietos.forEach((q, i) => {
          const desde = hijos[i % 2];
          const l = enlace(desde, q), nd = nodo(q, 2.2);
          tl.to(l, { attr: { x2: q.x, y2: q.y }, duration: 0.28, ease: 'power2.out' }, 0.7 + i * 0.09)
            .to(nd, { opacity: 1, duration: 0.15, ease: 'back.out(3)' }, 0.95 + i * 0.09)
            .to([l, nd], { opacity: 0, duration: 0.45 }, 1.4 + i * 0.05);
        });
        tl.to(nr, { opacity: 0, duration: 0.4 }, 1.4);
        return tl;
      },
    },

    /* ── CONVERGENCIA · epico ───────────────────────────────
       Mecanica: seis elementos EXTERNOS caen hacia el avatar y
       se funden en su borde. Se diferencia de Idea en que aqui
       vienen de fuera y rodean, no forman una figura. */
    'efecto-convergencia': {
      dur: 1.9, rareza: 'epico',
      crear(cp) {
        const col = COL.epico;
        const tl = gsap.timeline();
        const anillo = n('circle', { cx: C, cy: C, r: R * 1.06, fill: 'none',
          stroke: col, 'stroke-width': 2, opacity: 0 }, cp.atras);

        for (let i = 0; i < 6; i++) {
          const a = (i / 6) * TAU + 0.4;
          const lejos = pol(a, 2.3), cerca = pol(a, 1.06);
          const p = n('circle', { cx: lejos.x, cy: lejos.y, r: 2.6, fill: col, opacity: 0 }, cp.delante);
          const est = n('line', { x1: lejos.x, y1: lejos.y, x2: lejos.x, y2: lejos.y,
            stroke: col, 'stroke-width': 1, opacity: 0 }, cp.atras);
          tl.to([p, est], { opacity: 1, duration: 0.18 }, i * 0.04)
            .to(p, { attr: { cx: cerca.x, cy: cerca.y }, duration: 0.85,
                     ease: 'power2.in' }, 0.15 + i * 0.04)
            .to(est, { attr: { x1: cerca.x, y1: cerca.y }, duration: 0.85,
                       ease: 'power2.in' }, 0.15 + i * 0.04)
            .to([p, est], { opacity: 0, duration: 0.25 }, 1.0);
        }
        // el borde se enciende cuando todos llegan
        tl.to(anillo, { opacity: 0.9, duration: 0.2, ease: 'power2.out' }, 0.95)
          .to(anillo, { attr: { r: R * 1.35, 'stroke-width': 0.4 }, opacity: 0,
                        duration: 0.7, ease: 'power2.out' }, 1.15);
        return tl;
      },
    },

    /* ── AURORA · legendario ────────────────────────────────
       Mecanica: una gran forma de luz nace DETRAS, se expande
       cambiando de tono y se apaga. Sin blur pesado. */
    'efecto-aurora': {
      dur: 2.6, rareza: 'legendario',
      crear(cp, ctx) {
        const idg = 'auGrad' + ctx.id;
        const defs = n('defs', {}, cp.atras);
        const grad = n('radialGradient', { id: idg }, defs);
        const s1 = n('stop', { offset: '0%', 'stop-color': '#36c0a1', 'stop-opacity': 0 }, grad);
        const s2 = n('stop', { offset: '62%', 'stop-color': '#4a9eff', 'stop-opacity': 0.55 }, grad);
        const s3 = n('stop', { offset: '100%', 'stop-color': '#a855f7', 'stop-opacity': 0 }, grad);
        const halo = n('circle', { cx: C, cy: C, r: R * 0.8, fill: `url(#${idg})`, opacity: 0 }, cp.atras);

        // arco de luz por delante, muy fino
        const arco = n('path', {
          d: `M${C - R * 1.2} ${C} A${R * 1.2} ${R * 1.2} 0 0 1 ${C + R * 1.2} ${C}`,
          fill: 'none', stroke: '#cfe9ff', 'stroke-width': 2, 'stroke-linecap': 'round', opacity: 0,
        }, cp.delante);

        return gsap.timeline()
          .to(halo, { opacity: 1, duration: 0.5, ease: 'power1.out' })
          .to(halo, { attr: { r: R * 1.9 }, duration: 1.9, ease: 'power2.out' }, 0)
          // el tono se desplaza mientras se expande
          .to(s2, { attr: { 'stop-color': '#a855f7' }, duration: 1.6, ease: 'none' }, 0.3)
          .to(s3, { attr: { 'stop-color': '#ff7ac6' }, duration: 1.6, ease: 'none' }, 0.3)
          .to(arco, { opacity: 0.85, duration: 0.4 }, 0.35)
          .to(arco, { attr: { 'stroke-width': 0.5 }, scale: 1.28,
                      transformOrigin: `${C}px ${C}px`, duration: 1.5, ease: 'power2.out' }, 0.35)
          .to([halo, arco], { opacity: 0, duration: 0.7, ease: 'power2.in' }, 1.7);
      },
    },

    /* ── METAMORFOSIS · legendario ──────────────────────────
       Mecanica: secuencia completa. destello -> particulas ->
       orbita acelerada -> convergencia -> destello mayor ->
       dispersion. El mas elaborado, pero limpio. */
    'efecto-metamorfosis': {
      dur: 3.0, rareza: 'legendario',
      crear(cp) {
        const col = COL.legendario;
        const tl = gsap.timeline();
        const chispa = n('circle', { cx: C, cy: C, r: R * 0.9, fill: 'none',
          stroke: col, 'stroke-width': 2, opacity: 0 }, cp.atras);
        const grupo = n('g', {}, cp.delante);
        gsap.set(grupo, { transformOrigin: `${C}px ${C}px` });

        const ps = [];
        for (let i = 0; i < 6; i++) {
          const p = pol((i / 6) * TAU, 1.75);
          ps.push(n('circle', { cx: p.x, cy: p.y, r: 2.6, fill: col, opacity: 0 }, grupo));
        }
        const fogonazo = n('circle', { cx: C, cy: C, r: R * 1.05, fill: 'none',
          stroke: '#fff6d5', 'stroke-width': 3, opacity: 0 }, cp.delante);

        return tl
          // 1) destello inicial, detras
          .to(chispa, { opacity: 0.8, duration: 0.25, ease: 'power2.out' })
          .to(chispa, { attr: { r: R * 1.3 }, opacity: 0, duration: 0.6, ease: 'power2.out' }, 0.15)
          // 2) aparecen las particulas
          .to(ps, { opacity: 1, duration: 0.3, stagger: 0.05 }, 0.4)
          // 3) orbitan y aceleran
          .to(grupo, { rotation: 400, duration: 1.5, ease: 'power2.in' }, 0.5)
          // 4) convergen al borde
          .to(ps, { attr: { cx: C, cy: C }, duration: 0.55, ease: 'power3.in' }, 1.55)
          .to(ps, { opacity: 0, duration: 0.2 }, 2.0)
          // 5) destello mayor
          .to(fogonazo, { opacity: 1, duration: 0.15 }, 2.05)
          .to(fogonazo, { attr: { r: R * 1.75, 'stroke-width': 0.4 }, opacity: 0,
                          duration: 0.85, ease: 'power2.out' }, 2.15);
      },
    },

    /* ── ONDA CIVICA · legendario ───────────────────────────
       Mecanica: UNA ACCION -> IMPACTO -> PROPAGACION ->
       COMUNIDAD. La onda no es el final: al pasar despierta
       nodos que responden. Limpio pese a ser legendario. */
    'efecto-onda-civica': {
      dur: 3.0, rareza: 'legendario',
      crear(cp) {
        const col = '#5ce6c4';
        const tl = gsap.timeline();
        const chispa = n('circle', { cx: C, cy: C + R * 0.5, r: 3.6, fill: col, opacity: 0 }, cp.delante);
        const onda = n('circle', { cx: C, cy: C, r: R * 0.9, fill: 'none',
          stroke: col, 'stroke-width': 3, opacity: 0 }, cp.atras);

        // 1) la accion
        tl.to(chispa, { opacity: 1, attr: { r: 5 }, duration: 0.2, ease: 'back.out(3)' })
          .to(chispa, { opacity: 0, attr: { r: 1 }, duration: 0.3 }, 0.25)
        // 2) el impacto se propaga
          .to(onda, { opacity: 0.9, duration: 0.15 }, 0.2)
          .to(onda, { attr: { r: R * 2.0, 'stroke-width': 0.5 }, opacity: 0,
                      duration: 1.5, ease: 'power2.out' }, 0.2);

        // 3) la comunidad responde: cinco nodos se encienden al paso
        for (let i = 0; i < 5; i++) {
          const a = (i / 5) * TAU + 0.6;
          const q = pol(a, 1.55);
          const nd = n('circle', { cx: q.x, cy: q.y, r: 2.8, fill: col, opacity: 0 }, cp.delante);
          const ec = n('circle', { cx: q.x, cy: q.y, r: 2, fill: 'none',
            stroke: col, 'stroke-width': 1.4, opacity: 0 }, cp.delante);
          const t0 = 0.75 + i * 0.11;
          tl.to(nd, { opacity: 1, duration: 0.15, ease: 'back.out(3)' }, t0)
            .to(ec, { opacity: 0.8, attr: { r: 13 }, duration: 0.6, ease: 'power2.out' }, t0)
            .to(ec, { opacity: 0, duration: 0.3 }, t0 + 0.45)
            .to(nd, { opacity: 0, duration: 0.5 }, t0 + 0.7);
        }
        return tl;
      },
    },

    /* ── DESTELLO · legendario ──────────────────────────────
       Mecanica: la luz se CONCENTRA y estalla en un instante,
       con cuatro esquirlas. Breve y elegante, no explosion. */
    'efecto-destello': {
      dur: 1.6, rareza: 'legendario',
      crear(cp, ctx) {
        const idg = 'deGrad' + ctx.id;
        const defs = n('defs', {}, cp.atras);
        const grad = n('radialGradient', { id: idg }, defs);
        n('stop', { offset: '0%', 'stop-color': '#fff6d5', 'stop-opacity': 0.95 }, grad);
        n('stop', { offset: '100%', 'stop-color': '#ffd98a', 'stop-opacity': 0 }, grad);

        const luz = n('circle', { cx: C, cy: C, r: R * 1.5, fill: `url(#${idg})`, opacity: 0 }, cp.atras);
        const aro = n('circle', { cx: C, cy: C, r: R * 1.02, fill: 'none',
          stroke: '#fff6d5', 'stroke-width': 2.5, opacity: 0 }, cp.delante);
        const esquirlas = [0, 1, 2, 3].map((i) => {
          const a = i * (TAU / 4) + 0.4, p = pol(a, 1.05);
          return n('line', { x1: p.x, y1: p.y, x2: p.x, y2: p.y,
            stroke: '#fff6d5', 'stroke-width': 2, 'stroke-linecap': 'round', opacity: 0 }, cp.delante);
        });

        return gsap.timeline()
          // la luz se concentra
          .fromTo(luz, { scale: 1.5, opacity: 0, transformOrigin: `${C}px ${C}px` },
                       { scale: 0.55, opacity: 0.9, duration: 0.55, ease: 'power2.in' })
          // y estalla
          .to(luz, { scale: 1.5, opacity: 0, duration: 0.55, ease: 'power2.out' }, 0.55)
          .to(aro, { opacity: 1, duration: 0.1 }, 0.55)
          .to(aro, { attr: { r: R * 1.65, 'stroke-width': 0.4 }, opacity: 0,
                     duration: 0.7, ease: 'power3.out' }, 0.6)
          .to(esquirlas, { opacity: 1, duration: 0.1 }, 0.58)
          .to(esquirlas, {
            attr: {
              x2: (i) => pol(i * (TAU / 4) + 0.4, 1.85).x,
              y2: (i) => pol(i * (TAU / 4) + 0.4, 1.85).y,
            },
            duration: 0.5, ease: 'power3.out',
          }, 0.6)
          .to(esquirlas, {
            attr: {
              x1: (i) => pol(i * (TAU / 4) + 0.4, 1.8).x,
              y1: (i) => pol(i * (TAU / 4) + 0.4, 1.8).y,
            },
            opacity: 0, duration: 0.45, ease: 'power2.in',
          }, 0.75);
      },
    },
  };

  /* ═════════════════════════════════════════════════════════
     MOTOR
     ═════════════════════════════════════════════════════════ */
  let seq = 0;
  const vivos = new Map();          // host -> [{tl, svgs}]

  function svgCapa(host, clase, z) {
    const s = document.createElementNS(NS, 'svg');
    s.setAttribute('class', 'fx-svg ' + clase);
    s.setAttribute('viewBox', '0 0 200 200');
    s.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    // posicionamiento en linea: no depende de que el CSS haya cargado
    s.style.cssText = 'position:absolute;top:-50%;left:-50%;width:200%;height:200%;' +
                      'pointer-events:none;overflow:visible;display:block;z-index:' + z + ';';
    host.appendChild(s);
    return s;
  }

  function limpiar(host, soloEventos) {
    const lista = vivos.get(host);
    if (!lista) return;
    const quedan = [];
    lista.forEach((r) => {
      if (soloEventos && r.modo === 'preview') { quedan.push(r); return; }
      r.tl.kill();
      r.svgs.forEach((s) => { gsap.killTweensOf(s.querySelectorAll('*')); s.remove(); });
    });
    if (quedan.length) vivos.set(host, quedan); else vivos.delete(host);
    if (!quedan.length) host.classList.remove('fx-host');
  }

  /**
   * Reproduce un efecto.
   * @param {Element} host   avatar (o tarjeta de preview)
   * @param {string} clave   'efecto-chispa', etc.
   * @param {string} modo    'evento' (una vez) | 'preview' (en bucle)
   */
  function reproducir(host, clave, modo) {
    const def = EF[clave];
    if (!host || !def || !hayGSAP()) return null;

    // un avatar solo reproduce un efecto a la vez
    limpiar(host, modo !== 'preview');

    if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
    host.classList.add('fx-host');

    const atras = svgCapa(host, 'fx-atras', 1);
    const delante = svgCapa(host, 'fx-frente', 50);
    const gA = document.createElementNS(NS, 'g'); atras.appendChild(gA);
    const gD = document.createElementNS(NS, 'g'); delante.appendChild(gD);

    const ctx = { id: ++seq, rareza: def.rareza };
    const tl = def.crear({ atras: gA, delante: gD }, ctx);

    const reg = { tl, svgs: [atras, delante], modo, clave };
    const lista = vivos.get(host) || [];
    lista.push(reg); vivos.set(host, lista);

    if (modo === 'preview') {
      // La preview NO tiene espera: encadena un ciclo con el siguiente
      // para que se entienda de un vistazo que hace el efecto.
      tl.repeat(-1).repeatDelay(0).timeScale(1.25);
      if (menos) tl.timeScale(0.6);
    } else {
      // menos movimiento: version corta, pero el efecto sigue ocurriendo
      if (menos) tl.timeScale(2);
      tl.eventCallback('onComplete', () => {
        const l = vivos.get(host) || [];
        const i = l.indexOf(reg);
        if (i >= 0) l.splice(i, 1);
        tl.kill();
        reg.svgs.forEach((s) => { gsap.killTweensOf(s.querySelectorAll('*')); s.remove(); });
        if (!l.length) { vivos.delete(host); host.classList.remove('fx-host'); }
      });
    }
    return reg;
  }

  /* Previews de las tarjetas de la coleccion ----------------- */
  function montarPreviews(raiz) {
    (raiz || document).querySelectorAll('.cos-preview').forEach((pv) => {
      const clave = [...pv.classList].find((c) => EF[c]);
      const yaLista = vivos.get(pv);
      if (!clave) { if (yaLista) limpiar(pv); return; }
      if (yaLista && yaLista.some((r) => r.clave === clave)) return;
      limpiar(pv);
      reproducir(pv, clave, 'preview');
    });
  }

  /* Compatibilidad: el disparador (efectos-eventos.js) llama a
     window.CosEfectos. Se expone el mismo nombre para no tener que
     tocarlo, ahora respaldado por GSAP. */
  window.CosEfectos = {
    escenas: EF,
    reproducir: (host, clave) => reproducir(host, clave, 'evento'),
    montarPreviews,
    detenerTodo: (soloEventos) => [...vivos.keys()].forEach((h) => limpiar(h, soloEventos)),
    configurar() {},
  };

  window.CosEfectosGSAP = {
    efectos: EF,
    reproducir: (host, clave) => reproducir(host, clave, 'evento'),
    preview: (host, clave) => reproducir(host, clave, 'preview'),
    montarPreviews,
    detenerTodo: (soloEventos) => [...vivos.keys()].forEach((h) => limpiar(h, soloEventos)),
    limpiar,
  };

  function iniciar() {
    if (!hayGSAP()) {
      console.warn('[CIVINSIS] GSAP no esta cargado: los efectos no se reproduciran.');
      return;
    }
    montarPreviews();
    let t;
    new MutationObserver((muts) => {
      const rel = muts.some((m) => [...m.addedNodes, ...m.removedNodes]
        .some((x) => x.nodeType === 1 && !x.classList.contains('fx-svg')));
      if (!rel) return;
      clearTimeout(t); t = setTimeout(() => montarPreviews(), 200);
    }).observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
