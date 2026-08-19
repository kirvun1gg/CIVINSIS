/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Marcos cosmeticos · motor GSAP + SVG
   16 marcos. Cada uno es una COMPOSICION alrededor del avatar,
   no un borde animado.

   SISTEMA DE COORDENADAS
     viewBox "0 0 200 200". El avatar es el circulo centrado en
     (100,100) con radio 50. Todo lo demas se dibuja alrededor.
     Al ser SVG, el marco ESCALA SOLO: se ve igual en el perfil
     (avatar de 100 px) que en un comentario (38 px).

   ADAPTACION AL CONTEXTO
     ctx.escala   1 = avatar grande · 0.6 = pequeño
     ctx.piezas(n) devuelve menos elementos en avatares pequeños
     ctx.vel      las animaciones van algo mas rapidas en pequeño
     Es el MISMO marco adaptandose, no tres versiones.

   FULL GSAP: cada marco devuelve una timeline. Sin @keyframes,
   sin setInterval, sin animaciones CSS.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const NS = 'http://www.w3.org/2000/svg';
  const menos = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayGSAP = () => typeof window.gsap !== 'undefined';

  const C = 100, R = 50, TAU = Math.PI * 2;

  /* ── Utilidades reutilizables ────────────────────────────── */
  function nodo(tag, attrs, padre) {
    const e = document.createElementNS(NS, tag);
    for (const k in attrs) e.setAttribute(k, attrs[k]);
    if (padre) padre.appendChild(e);
    return e;
  }
  const pol = (a, d) => ({ x: C + Math.cos(a) * R * d, y: C + Math.sin(a) * R * d });
  const az = (a, b) => a + Math.random() * (b - a);

  /** Particula: circulo con brillo opcional. */
  const particula = (g, x, y, r, color, op) =>
    nodo('circle', { cx: x, cy: y, r, fill: color, opacity: op == null ? 0 : op }, g);

  /** Trazo entre dos puntos, listo para dibujarse progresivamente. */
  function trazo(g, a, b, color, grosor) {
    const l = nodo('line', {
      x1: a.x, y1: a.y, x2: b.x, y2: b.y,
      stroke: color, 'stroke-width': grosor || 1.2, 'stroke-linecap': 'round', opacity: 0,
    }, g);
    return l;
  }
  /** Camino curvo entre dos puntos (para trayectorias). */
  function curva(g, a, b, desvio, color, grosor) {
    const mx = (a.x + b.x) / 2 + desvio, my = (a.y + b.y) / 2 - desvio;
    const p = nodo('path', {
      d: `M${a.x} ${a.y} Q${mx} ${my} ${b.x} ${b.y}`,
      fill: 'none', stroke: color, 'stroke-width': grosor || 1.4,
      'stroke-linecap': 'round', opacity: 0,
    }, g);
    return p;
  }
  /** Prepara un trazo para dibujarse (equivalente a DrawSVG con dasharray). */
  function preparaDibujo(el) {
    const L = el.getTotalLength ? el.getTotalLength() : 200;
    gsap.set(el, { attr: { 'stroke-dasharray': L, 'stroke-dashoffset': L } });
    return L;
  }
  /** Poligono regular pequeño, para fragmentos geometricos. */
  function figura(g, x, y, lados, r, color) {
    let d = '';
    for (let i = 0; i < lados; i++) {
      const a = (i / lados) * TAU - Math.PI / 2;
      d += (i ? 'L' : 'M') + (x + Math.cos(a) * r) + ' ' + (y + Math.sin(a) * r) + ' ';
    }
    return nodo('path', { d: d + 'Z', fill: 'none', stroke: color,
      'stroke-width': 1.3, 'stroke-linejoin': 'round', opacity: 0 }, g);
  }

  /* Paleta por rareza */
  const PAL = {
    comun: '#8fb8c4', poco_comun: '#7fd4a8', raro: '#7fb8ff',
    epico: '#c9a6ff', legendario: '#ffcf7a', mitico: '#ff9ad5',
  };

  /* ═════════════════════════════════════════════════════════
     LOS 16 MARCOS
     crear(g, ctx) -> gsap.timeline()
       g   = grupo <g> del SVG donde dibujar
       ctx = { escala, vel, piezas(n), color }
     ═════════════════════════════════════════════════════════ */
  const MK = {

    /* 1 · NOCHE ESTELAR · poco comun
       Estrellas independientes: cada una con su ciclo. Algunas
       cruzan lentamente una zona. Nada gira en bloque. */
    'marco-noche-estelar': {
      rareza: 'poco_comun',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const n = ctx.piezas(11);
        for (let i = 0; i < n; i++) {
          const a = az(0, TAU), d = az(1.12, 1.9);
          const p = pol(a, d);
          const r = i % 4 === 0 ? az(1.6, 2.4) : az(0.7, 1.3);
          const s = particula(g, p.x, p.y, r, '#eaf2ff');
          // cada estrella tiene su propio ritmo: aparece, brilla, se va
          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 7) })
            .to(s, { opacity: az(0.5, 1), duration: az(0.7, 1.6), ease: 'sine.inOut' })
            .to(s, { attr: { r: r * az(1.3, 1.9) }, duration: az(0.6, 1.2), ease: 'sine.inOut' }, 0)
            .to(s, { x: az(-6, 6), y: az(-5, 5), duration: az(4, 9), ease: 'sine.inOut' }, 0)
            .to(s, { opacity: 0, attr: { r: r * 0.6 }, duration: az(1, 2.2), ease: 'sine.in' })
            .to({}, { duration: az(1, 4) }), 0);
        }
        // una estrella cruza despacio una zona, de vez en cuando
        const fug = particula(g, 0, 0, 1.6, '#ffffff');
        const cola = nodo('line', { x1: 0, y1: 0, x2: 0, y2: 0, stroke: '#fff',
          'stroke-width': 1.2, 'stroke-linecap': 'round', opacity: 0 }, g);
        tl.add(gsap.timeline({ repeat: -1, repeatRefresh: true, repeatDelay: 6 })
          .set([fug, cola], { opacity: 0 })
          .call(() => {
            const y0 = az(30, 170), x0 = -20, x1 = 220, y1 = y0 + az(-30, 30);
            gsap.set(fug, { attr: { cx: x0, cy: y0 } });
            gsap.set(cola, { attr: { x1: x0, y1: y0, x2: x0, y2: y0 } });
            gsap.timeline()
              .to([fug, cola], { opacity: 0.9, duration: 0.3 })
              .to(fug, { attr: { cx: x1, cy: y1 }, duration: 3.4, ease: 'none' }, 0)
              .to(cola, { attr: { x2: x1, y2: y1 }, duration: 3.4, ease: 'none' }, 0)
              .to(cola, { attr: { x1: x1 - 26, y1: y1 - 6 }, duration: 3.2, ease: 'none' }, 0.2)
              .to([fug, cola], { opacity: 0, duration: 0.6 }, 3.0);
          })
          .to({}, { duration: 4.2 }), 0);
        return tl;
      },
    },

    /* 2 · AURORA · raro
       Cintas curvas que se deforman. Movimiento organico: cada
       cinta cambia su propia curvatura, no solo se desplaza. */
    'marco-aurora': {
      rareza: 'raro',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const tonos = ['#36c0a1', '#4a9eff', '#a855f7'];
        const n = ctx.piezas(3);
        for (let i = 0; i < n; i++) {
          const base = 1.18 + i * 0.22;
          const p = nodo('path', {
            fill: 'none', stroke: tonos[i % 3], 'stroke-width': 2.6 - i * 0.5,
            'stroke-linecap': 'round', opacity: 0.5,
          }, g);
          // la forma se recalcula: eso es lo que da sensacion organica
          const est = { fase: az(0, TAU), amp: az(0.1, 0.2) };
          const pintar = () => {
            let d = '';
            for (let k = 0; k <= 14; k++) {
              const a = -Math.PI * 1.05 + (k / 14) * Math.PI * 1.1;
              const dd = base + Math.sin(a * 2.4 + est.fase) * est.amp;
              const q = pol(a, dd);
              d += (k ? 'L' : 'M') + q.x.toFixed(1) + ' ' + q.y.toFixed(1) + ' ';
            }
            p.setAttribute('d', d);
          };
          pintar();
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: i * 0.7 })
            .to(est, { fase: est.fase + TAU, amp: az(0.08, 0.24),
                       duration: az(9, 15), ease: 'sine.inOut', onUpdate: pintar })
            .to(p, { rotation: az(-14, 14), transformOrigin: `${C}px ${C}px`,
                     opacity: az(0.35, 0.9), duration: az(10, 16), ease: 'sine.inOut' }, 0), 0);
        }
        // motas de luz sueltas
        for (let i = 0; i < ctx.piezas(5); i++) {
          const q = pol(az(-Math.PI, 0), az(1.2, 1.7));
          const m = particula(g, q.x, q.y, az(0.8, 1.5), '#d8f4ff');
          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 5) })
            .to(m, { opacity: az(0.5, 1), duration: 1, ease: 'sine.out' })
            .to(m, { y: -az(6, 14), duration: az(4, 7), ease: 'sine.inOut' }, 0)
            .to(m, { opacity: 0, duration: 1.4 }), 0);
        }
        return tl;
      },
    },

    /* 3 · HORIZONTE · comun
       Minimalista: una linea y una luz que la recorre, se
       intensifica, deja rastro y reaparece en otro punto. */
    'marco-horizonte': {
      rareza: 'comun',
      crear(g, ctx) {
        const y = C + R * 1.28;
        const x0 = C - R * 1.75, x1 = C + R * 1.75;
        nodo('line', { x1: x0, y1: y, x2: x1, y2: y,
          stroke: '#8fb8c4', 'stroke-width': 0.9, opacity: 0.3 }, g);

        const luz = particula(g, x0, y, 2.6, '#eaf6ff', 0);
        const rastro = nodo('line', { x1: x0, y1: y, x2: x0, y2: y,
          stroke: '#bfe4f2', 'stroke-width': 1.8, 'stroke-linecap': 'round', opacity: 0 }, g);
        const halo = particula(g, x0, y, 7, '#bfe4f2', 0);

        const tl = gsap.timeline({ repeat: -1, repeatRefresh: true });
        return tl
          .call(() => {
            const izq = Math.random() > 0.5;
            const a = izq ? x0 : x1, b = izq ? x1 : x0;
            gsap.set([luz, halo], { attr: { cx: a } });
            gsap.set(rastro, { attr: { x1: a, x2: a } });
          })
          .to([luz, halo], { opacity: 1, duration: 0.5 })
          .to([luz, halo], { attr: { cx: '+=0' }, duration: 0.01 })
          .to(luz, { attr: { cx: (i, t) => (+t.getAttribute('cx') === x0 ? x1 : x0) },
                     duration: 4.5, ease: 'sine.inOut' }, 0.3)
          .to(halo, { attr: { cx: (i, t) => (+t.getAttribute('cx') === x0 ? x1 : x0) },
                      duration: 4.5, ease: 'sine.inOut' }, 0.3)
          // punto de maxima intensidad a mitad de recorrido
          .to(halo, { attr: { r: 13 }, opacity: 0.55, duration: 1.1, ease: 'sine.inOut',
                      yoyo: true, repeat: 1 }, 1.6)
          .to(rastro, { attr: { x2: (i, t) => (+t.getAttribute('x1') === x0 ? x1 : x0) },
                        opacity: 0.6, duration: 4.5, ease: 'sine.inOut' }, 0.3)
          .to(rastro, { attr: { x1: (i, t) => (+t.getAttribute('x2')) },
                        duration: 2.6, ease: 'sine.in' }, 2.4)
          .to([luz, halo, rastro], { opacity: 0, duration: 0.8 }, 4.4)
          .to({}, { duration: 1.6 });
      },
    },

    /* 4 · ASCENSO · poco comun
       Trayectorias curvas distintas por particula. Algunas se
       desvian, otras se frenan, otras dejan rastro. */
    'marco-ascenso': {
      rareza: 'poco_comun',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const n = ctx.piezas(8);
        for (let i = 0; i < n; i++) {
          const lado = i % 2 ? 1 : -1;
          const x0 = C + lado * R * az(1.05, 1.55);
          const y0 = C + R * az(1.1, 1.5);
          const camino = curva(g, { x: x0, y: y0 },
            { x: x0 + lado * az(-30, 18), y: C - R * az(1.2, 1.6) },
            az(-26, 26), '#7fd4a8', 1);
          const L = preparaDibujo(camino);
          const p = particula(g, x0, y0, az(1.4, 2.6), '#9ef0c4');

          const dur = az(4.5, 8);
          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 5) })
            .to(p, { opacity: 1, duration: 0.5 })
            // la particula recorre SU camino
            .to(p, { motionPath: { path: camino, align: camino, alignOrigin: [0.5, 0.5] },
                     duration: dur, ease: 'power1.out' }, 0)
            // algunas dejan rastro: se dibuja el camino tras ella
            .fromTo(camino, { opacity: i % 3 === 0 ? 0.35 : 0 },
                    { attr: { 'stroke-dashoffset': 0 }, duration: dur, ease: 'power1.out' }, 0)
            .to(camino, { opacity: 0, duration: 1.2 }, dur * 0.7)
            .to(p, { opacity: 0, attr: { r: 0.6 }, duration: 1.2, ease: 'power2.in' }, dur * 0.72)
            .set(camino, { attr: { 'stroke-dashoffset': L } }), 0);
        }
        return tl;
      },
    },

    /* 5 · FLUJO · poco comun
       Lineas diagonales que aparecen, cruzan una zona, cambian
       ligeramente de direccion y desaparecen. */
    'marco-flujo': {
      rareza: 'poco_comun',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const n = ctx.piezas(6);
        for (let i = 0; i < n; i++) {
          const y = az(20, 180), largo = az(38, 78);
          const l = nodo('line', { x1: -largo, y1: y, x2: 0, y2: y,
            stroke: '#7fb8ff', 'stroke-width': az(1, 2.2),
            'stroke-linecap': 'round', opacity: 0 }, g);
          gsap.set(l, { rotation: az(-24, -8), transformOrigin: 'center', opacity: 0 });
          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 6) })
            .to(l, { opacity: az(0.35, 0.85), duration: 0.5 })
            .to(l, { x: 240 + largo, duration: az(4, 8), ease: 'none' }, 0)
            // cambio leve de direccion a mitad de camino
            .to(l, { rotation: `+=${az(-10, 10)}`, duration: az(2, 4), ease: 'sine.inOut' }, 1.2)
            .to(l, { opacity: 0, duration: 1 }, '-=1.2'), 0);
        }
        return tl;
      },
    },

    /* 6 · EVOLUCION · epico
       Fragmentos que aparecen separados, se reorganizan, forman
       una composicion, se transforman y se dispersan. */
    'marco-evolucion': {
      rareza: 'epico',
      crear(g, ctx) {
        const n = ctx.piezas(6);
        const piezas = [], col = PAL.epico;
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU;
          const lejos = pol(a, az(1.9, 2.2));
          const f = figura(g, lejos.x, lejos.y, 3 + (i % 3), az(4, 7), col);
          gsap.set(f, { transformOrigin: `${lejos.x}px ${lejos.y}px` });
          piezas.push({ el: f, a, lejos });
        }
        const tl = gsap.timeline({ repeat: -1, repeatDelay: 1 });

        // 1) aparecen separadas
        tl.to(piezas.map((p) => p.el), { opacity: 0.9, duration: 0.6, stagger: 0.08 })
        // 2) se acercan y 3) se organizan en anillo ordenado
          .to(piezas.map((p) => p.el), {
            x: (i) => pol(piezas[i].a, 1.28).x - piezas[i].lejos.x,
            y: (i) => pol(piezas[i].a, 1.28).y - piezas[i].lejos.y,
            rotation: 180, duration: 1.6, ease: 'power2.inOut', stagger: 0.06,
          }, 0.5)
        // 4) forman composicion: giran juntas un momento
          .to(piezas.map((p) => p.el), {
            rotation: 360, scale: 1.15, duration: 1.2, ease: 'sine.inOut',
          }, 2.2)
        // 5) se transforman: cambian de tamaño y posicion relativa
          .to(piezas.map((p) => p.el), {
            x: (i) => pol(piezas[i].a + 0.5, 1.5).x - piezas[i].lejos.x,
            y: (i) => pol(piezas[i].a + 0.5, 1.5).y - piezas[i].lejos.y,
            scale: 0.7, duration: 1.1, ease: 'power2.inOut', stagger: 0.05,
          }, 3.4)
        // 6) se dispersan
          .to(piezas.map((p) => p.el), {
            x: 0, y: 0, scale: 1, rotation: 0, opacity: 0,
            duration: 1.3, ease: 'power2.in', stagger: 0.05,
          }, 4.6);
        return tl;
      },
    },

    /* 7 · CONEXIONES · raro
       Red que cambia: las lineas se dibujan progresivamente,
       se mantienen un momento y se borran. Nunca estatica. */
    'marco-conexiones': {
      rareza: 'raro',
      crear(g, ctx) {
        const n = ctx.piezas(7);
        const pts = [], nodos = [];
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU + az(-0.25, 0.25);
          const p = pol(a, az(1.2, 1.75));
          pts.push(p);
          nodos.push(particula(g, p.x, p.y, az(1.8, 3.2), '#5ce6c4', 0));
        }
        const lineas = [];
        for (let i = 0; i < n; i++) {
          const j = (i + 1 + Math.floor(Math.random() * (n - 2))) % n;
          const l = trazo(g, pts[i], pts[j], '#5ce6c4', 1);
          preparaDibujo(l);
          lineas.push(l);
        }

        const tl = gsap.timeline({ repeat: -1 });
        tl.to(nodos, { opacity: 0.9, duration: 0.5, stagger: 0.07 });
        // las conexiones se dibujan por turnos y luego se borran
        lineas.forEach((l, i) => {
          const t = 0.4 + i * 0.45;
          tl.to(l, { opacity: 0.7, duration: 0.15 }, t)
            .to(l, { attr: { 'stroke-dashoffset': 0 }, duration: 0.7, ease: 'power2.inOut' }, t)
            .to(nodos[(i + 1) % n], { attr: { r: '+=1.6' }, duration: 0.25,
                                      yoyo: true, repeat: 1, ease: 'power2.out' }, t + 0.6)
            .to(l, { opacity: 0, duration: 0.5 }, t + 1.8)
            .set(l, { attr: { 'stroke-dashoffset': l.getTotalLength() } }, t + 2.3);
        });
        tl.to(nodos, { opacity: 0.25, duration: 0.8, stagger: 0.05 }, '>-1');
        return tl;
      },
    },

    /* 8 · LEGADO · epico
       Una linea recorre una trayectoria curva, gana brillo, deja
       un rastro corto y se desvanece. */
    'marco-legado': {
      rareza: 'epico',
      crear(g, ctx) {
        const camino = nodo('path', {
          d: `M${C - R * 1.7} ${C + R * 0.9} Q${C} ${C - R * 2.1} ${C + R * 1.7} ${C + R * 0.9}`,
          fill: 'none', stroke: '#ffcf7a', 'stroke-width': 2,
          'stroke-linecap': 'round', opacity: 0,
        }, g);
        const L = preparaDibujo(camino);
        const cabeza = particula(g, 0, 0, 3, '#fff3d0', 0);
        const halo = particula(g, 0, 0, 8, '#ffcf7a', 0);

        return gsap.timeline({ repeat: -1, repeatDelay: 1.6 })
          .to(camino, { opacity: 0.9, duration: 0.3 })
          // el trazo se dibuja mientras la cabeza lo recorre
          .to(camino, { attr: { 'stroke-dashoffset': 0 }, duration: 2.6, ease: 'power1.inOut' }, 0)
          .to([cabeza, halo], { opacity: 1, duration: 0.3 }, 0)
          .to([cabeza, halo], { motionPath: { path: camino, align: camino,
                                alignOrigin: [0.5, 0.5] },
                                duration: 2.6, ease: 'power1.inOut' }, 0)
          // gana brillo en el punto alto
          .to(halo, { attr: { r: 15 }, opacity: 0.5, duration: 0.7,
                      yoyo: true, repeat: 1, ease: 'sine.inOut' }, 1)
          // el rastro se borra desde el inicio: queda una estela corta
          .to(camino, { attr: { 'stroke-dashoffset': -L }, duration: 2, ease: 'power1.in' }, 1.4)
          .to([cabeza, halo], { opacity: 0, duration: 0.6 }, 2.5)
          .to(camino, { opacity: 0, duration: 0.5 }, 2.9);
      },
    },

    /* 9 · PULSO CIVICO · raro
       Una señal nace en un punto y genera tres ondas encadenadas,
       cada una mas debil. No son circulos permanentes. */
    'marco-pulso-civico': {
      rareza: 'raro',
      crear(g, ctx) {
        const tl = gsap.timeline({ repeat: -1, repeatRefresh: true, repeatDelay: 1.4 });
        const origen = particula(g, C, C + R * 1.1, 3.4, '#7fb8ff', 0);
        const ondas = [0, 1, 2].map(() =>
          nodo('circle', { cx: C, cy: C + R * 1.1, r: R * 0.4, fill: 'none',
            stroke: '#7fb8ff', 'stroke-width': 2, opacity: 0 }, g));

        tl.call(() => {
          const a = az(0, TAU), p = pol(a, 1.1);
          gsap.set(origen, { attr: { cx: p.x, cy: p.y } });
          ondas.forEach((o) => gsap.set(o, { attr: { cx: p.x, cy: p.y, r: R * 0.35 } }));
        })
          .to(origen, { opacity: 1, attr: { r: 4.5 }, duration: 0.25, ease: 'back.out(3)' })
          .to(origen, { opacity: 0, attr: { r: 1.5 }, duration: 0.4 }, 0.3);

        [[0.15, 0.9, 1.0], [0.5, 0.55, 1.45], [0.9, 0.3, 1.9]].forEach(([t, op, esc], i) => {
          tl.to(ondas[i], { opacity: op, duration: 0.15 }, t)
            .to(ondas[i], { attr: { r: R * esc, 'stroke-width': 0.4 }, opacity: 0,
                            duration: 1.5 + i * 0.2, ease: 'power2.out' }, t);
        });
        return tl;
      },
    },

    /* 10 · CONSTELACION · epico
       Estrellas dispersas que se conectan un instante formando
       una figura y vuelven a separarse. Nunca queda fija. */
    'marco-constelacion': {
      rareza: 'epico',
      crear(g, ctx) {
        const n = ctx.piezas(6);
        const pts = [], est = [];
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU + az(-0.3, 0.3);
          const p = pol(a, az(1.25, 1.85));
          pts.push(p);
          est.push(particula(g, p.x, p.y, i % 3 === 0 ? 2.4 : 1.5, '#eaf2ff', 0));
        }
        const lineas = pts.map((p, i) => {
          const l = trazo(g, p, pts[(i + 1) % n], '#b9d4ff', 0.9);
          preparaDibujo(l);
          return l;
        });

        return gsap.timeline({ repeat: -1, repeatDelay: 1.8 })
          // 1) aparecen dispersas, cada una a su tiempo
          .to(est, { opacity: 0.9, duration: 0.5, stagger: { each: 0.12, from: 'random' } })
          // 2) se conectan formando la figura
          .to(lineas, { opacity: 0.65, duration: 0.2, stagger: 0.13 }, 0.9)
          .to(lineas, { attr: { 'stroke-dashoffset': 0 }, duration: 0.55,
                        ease: 'power2.out', stagger: 0.13 }, 0.9)
          // 3) la figura respira un momento
          .to(est, { attr: { r: '+=0.8' }, duration: 0.5, yoyo: true, repeat: 1,
                     ease: 'sine.inOut' }, 2.1)
          // 4) se desconecta
          .to(lineas, { opacity: 0, duration: 0.6, stagger: 0.07 }, 3.1)
          // 5) las estrellas se dispersan
          .to(est, {
            x: (i) => Math.cos((i / n) * TAU) * 16,
            y: (i) => Math.sin((i / n) * TAU) * 16,
            opacity: 0, duration: 1.2, ease: 'power2.in', stagger: 0.06,
          }, 3.4)
          .set(est, { x: 0, y: 0 })
          .set(lineas, { attr: { 'stroke-dashoffset': (i, t) => t.getTotalLength() } });
      },
    },

    /* 11 · NEXO · epico
       Nodos de distinto tamaño que se desplazan; cuando dos se
       encuentran hay destello, linea breve y separacion. */
    'marco-nexo': {
      rareza: 'epico',
      crear(g, ctx) {
        const n = ctx.piezas(5);
        const nodos = [];
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU, p = pol(a, az(1.3, 1.8));
          const c = particula(g, p.x, p.y, az(1.8, 3.4), '#c9a6ff', 0.85);
          nodos.push({ el: c, a });
        }
        const enlace = trazo(g, { x: C, y: C }, { x: C, y: C }, '#e0d0ff', 1.6);
        const destello = particula(g, C, C, 5, '#f0e6ff', 0);

        const tl = gsap.timeline();
        // cada nodo deriva por su cuenta
        nodos.forEach((nd, i) => {
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 3) })
            .to(nd.el, { x: az(-16, 16), y: az(-14, 14),
                         duration: az(4, 8), ease: 'sine.inOut' })
            .to(nd.el, { opacity: az(0.5, 1), duration: az(3, 6), ease: 'sine.inOut' }, 0), 0);
        });
        // y de vez en cuando dos se encuentran
        tl.add(gsap.timeline({ repeat: -1, repeatRefresh: true, repeatDelay: 2.2 })
          .call(() => {
            const i = Math.floor(Math.random() * n);
            let j = Math.floor(Math.random() * n); if (j === i) j = (j + 1) % n;
            const A = nodos[i].el.getBoundingClientRect ? null : null;
            const pa = pol(nodos[i].a, 1.55), pb = pol(nodos[j].a, 1.55);
            const mx = (pa.x + pb.x) / 2, my = (pa.y + pb.y) / 2;
            gsap.timeline()
              .to([nodos[i].el, nodos[j].el], {
                attr: { cx: mx, cy: my }, x: 0, y: 0,
                duration: 1.1, ease: 'power2.inOut',
              })
              .set(enlace, { attr: { x1: mx, y1: my, x2: mx, y2: my } })
              .to(enlace, { opacity: 0.9, duration: 0.2 }, 0.9)
              .to(destello, { attr: { cx: mx, cy: my }, duration: 0.01 }, 1)
              .to(destello, { opacity: 1, attr: { r: 9 }, duration: 0.25,
                              yoyo: true, repeat: 1, ease: 'power2.out' }, 1.05)
              .to(enlace, { opacity: 0, duration: 0.3 }, 1.5)
              .to([nodos[i].el, nodos[j].el], {
                attr: { cx: (k) => (k === 0 ? pa.x : pb.x), cy: (k) => (k === 0 ? pa.y : pb.y) },
                duration: 1.2, ease: 'power2.inOut',
              }, 1.5);
          })
          .to({}, { duration: 3 }), 0);
        return tl;
      },
    },

    /* 12 · INSPIRACION · raro
       Fragmentos que se acercan, forman brevemente una figura
       abstracta y se dispersan. Sin iconos literales. */
    'marco-inspiracion': {
      rareza: 'raro',
      crear(g, ctx) {
        const n = ctx.piezas(5);
        const foco = pol(-Math.PI / 2, 1.35);
        const frags = [];
        for (let i = 0; i < n; i++) {
          const a = -Math.PI / 2 + (i - (n - 1) / 2) * 0.55;
          const o = pol(a, az(1.9, 2.2));
          const f = nodo('path', {
            d: `M${o.x} ${o.y - 4} L${o.x + 3.5} ${o.y} L${o.x} ${o.y + 4} L${o.x - 3.5} ${o.y} Z`,
            fill: 'none', stroke: '#7fb8ff', 'stroke-width': 1.2, opacity: 0,
          }, g);
          gsap.set(f, { transformOrigin: `${o.x}px ${o.y}px` });
          frags.push({ el: f, o });
        }
        const nucleo = nodo('path', {
          d: `M${foco.x} ${foco.y - 9} L${foco.x + 8} ${foco.y} L${foco.x} ${foco.y + 9} L${foco.x - 8} ${foco.y} Z`,
          fill: 'none', stroke: '#cfe4ff', 'stroke-width': 1.6, opacity: 0,
        }, g);
        gsap.set(nucleo, { transformOrigin: `${foco.x}px ${foco.y}px` });

        return gsap.timeline({ repeat: -1, repeatDelay: 1.5 })
          .to(frags.map((f) => f.el), { opacity: 0.85, duration: 0.5, stagger: 0.09 })
          .to(frags.map((f) => f.el), {
            x: (i) => foco.x - frags[i].o.x, y: (i) => foco.y - frags[i].o.y,
            rotation: 180, scale: 0.5,
            duration: 1.3, ease: 'power2.inOut', stagger: 0.06,
          }, 0.6)
          .to(frags.map((f) => f.el), { opacity: 0, duration: 0.3 }, 1.7)
          .to(nucleo, { opacity: 1, scale: 1.15, duration: 0.4, ease: 'back.out(2.5)' }, 1.75)
          .to(nucleo, { rotation: 90, duration: 0.9, ease: 'sine.inOut' }, 1.9)
          .to(nucleo, { opacity: 0, scale: 1.6, duration: 0.7, ease: 'power2.in' }, 2.7)
          .to(frags.map((f) => f.el), {
            x: 0, y: 0, rotation: 0, scale: 1, opacity: 0, duration: 0.01,
          }, 3.4);
      },
    },

    /* 13 · ORBITA · epico
       Cada particula con velocidad, radio y tamaño propios.
       Algunas abandonan la orbita y regresan. */
    'marco-orbita': {
      rareza: 'epico',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const n = ctx.piezas(6);
        for (let i = 0; i < n; i++) {
          const grupo = nodo('g', {}, g);
          gsap.set(grupo, { transformOrigin: `${C}px ${C}px`, rotation: az(0, 360) });
          const d = az(1.22, 1.72);
          const p = pol(0, d);
          const c = particula(g, 0, 0, az(1.4, 2.8), '#c9a6ff', az(0.5, 1));
          grupo.appendChild(c);
          c.setAttribute('cx', p.x); c.setAttribute('cy', p.y);

          // orbita a su ritmo
          tl.add(gsap.timeline({ repeat: -1 })
            .to(grupo, { rotation: '+=360', duration: az(9, 20), ease: 'none' }), 0);
          // variacion de opacidad
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 3) })
            .to(c, { opacity: az(0.3, 1), duration: az(2, 5), ease: 'sine.inOut' }), 0);
          // algunas se escapan y vuelven
          if (i % 3 === 0) {
            tl.add(gsap.timeline({ repeat: -1, repeatDelay: az(4, 9), delay: az(2, 6) })
              .to(c, { attr: { cx: pol(0, d * 1.6).x }, duration: 1.4, ease: 'power2.out' })
              .to(c, { attr: { cx: p.x }, duration: 1.8, ease: 'power2.inOut' }, 1.6), 0);
          }
        }
        return tl;
      },
    },

    /* 14 · FRAGMENTOS · legendario
       Piezas geometricas que se acercan, se reorganizan en una
       estructura y se separan. Tecnologico y limpio. */
    'marco-fragmentos': {
      rareza: 'legendario',
      crear(g, ctx) {
        const n = ctx.piezas(8);
        const piezas = [];
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU + az(-0.2, 0.2);
          const o = pol(a, az(2.0, 2.4));
          const lados = 3 + (i % 3);
          const f = figura(g, o.x, o.y, lados, az(3.5, 6), '#ffcf7a');
          gsap.set(f, { transformOrigin: `${o.x}px ${o.y}px`, rotation: az(0, 360) });
          piezas.push({ el: f, a, o });
        }
        const anillo = nodo('circle', { cx: C, cy: C, r: R * 1.35, fill: 'none',
          stroke: '#ffe0a8', 'stroke-width': 1, opacity: 0,
          'stroke-dasharray': '4 6' }, g);

        return gsap.timeline({ repeat: -1, repeatDelay: 1.2 })
          .to(piezas.map((p) => p.el), { opacity: 0.9, duration: 0.5,
                                         stagger: { each: 0.07, from: 'random' } })
          // se acercan y se ORGANIZAN en posiciones exactas
          .to(piezas.map((p) => p.el), {
            x: (i) => pol((i / n) * TAU, 1.35).x - piezas[i].o.x,
            y: (i) => pol((i / n) * TAU, 1.35).y - piezas[i].o.y,
            rotation: 0, duration: 1.5, ease: 'power3.inOut', stagger: 0.05,
          }, 0.5)
          // la estructura se cierra
          .to(anillo, { opacity: 0.6, duration: 0.5 }, 1.9)
          .to(anillo, { rotation: 90, transformOrigin: `${C}px ${C}px`,
                        duration: 1.6, ease: 'sine.inOut' }, 1.9)
          .to(piezas.map((p) => p.el), { scale: 1.2, duration: 0.6,
                                         yoyo: true, repeat: 1, ease: 'sine.inOut' }, 2.2)
          // y se separa
          .to(anillo, { opacity: 0, attr: { r: R * 1.8 }, duration: 0.9 }, 3.4)
          .to(piezas.map((p) => p.el), {
            x: 0, y: 0, rotation: 180, opacity: 0,
            duration: 1.2, ease: 'power2.in', stagger: 0.05,
          }, 3.5);
      },
    },

    /* 15 · VORTICE · legendario
       Varias trayectorias curvas distintas. Los elementos
       aceleran, desaceleran, se cruzan y desaparecen. */
    'marco-vortice': {
      rareza: 'legendario',
      crear(g, ctx) {
        const tl = gsap.timeline();
        const n = ctx.piezas(5);
        for (let i = 0; i < n; i++) {
          const giro = i * (TAU / n);
          const a1 = pol(giro, 2.1), a2 = pol(giro + 2.2, 1.15), a3 = pol(giro + 4.2, 1.7);
          const camino = nodo('path', {
            d: `M${a1.x} ${a1.y} Q${C + Math.cos(giro + 1) * R * 2.2} ${C + Math.sin(giro + 1) * R * 2.2} ${a2.x} ${a2.y}
                T${a3.x} ${a3.y}`,
            fill: 'none', stroke: '#ffcf7a', 'stroke-width': 0.8, opacity: 0,
          }, g);
          const p = particula(g, a1.x, a1.y, az(1.6, 2.8), '#fff0cc');

          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 4) })
            .to(p, { opacity: 1, duration: 0.4 })
            // acelera, desacelera: dos tramos con curvas distintas
            .to(p, { motionPath: { path: camino, align: camino, alignOrigin: [0.5, 0.5],
                                   start: 0, end: 0.5 },
                     duration: az(1.6, 2.4), ease: 'power2.in' }, 0)
            .to(p, { motionPath: { path: camino, align: camino, alignOrigin: [0.5, 0.5],
                                   start: 0.5, end: 1 },
                     duration: az(2, 3), ease: 'power2.out' }, '>')
            .to(camino, { opacity: 0.28, duration: 0.6 }, 0.2)
            .to(camino, { opacity: 0, duration: 1 }, '-=1.4')
            .to(p, { opacity: 0, attr: { r: 0.6 }, duration: 0.8 }, '-=0.8'), 0);
        }
        return tl;
      },
    },

    /* 16 · LEGADO VIVO · mitico
       Historia completa: vacio -> puntos -> conexiones ->
       estructura -> resplandor -> dispersion. */
    'marco-legado-vivo': {
      rareza: 'mitico',
      crear(g, ctx) {
        const n = ctx.piezas(7);
        const pts = [], nodos = [];
        for (let i = 0; i < n; i++) {
          const a = (i / n) * TAU - Math.PI / 2;
          const p = pol(a, 1.42);
          pts.push(p);
          nodos.push(particula(g, p.x, p.y, 2.4, '#ff9ad5', 0));
        }
        const lineas = pts.map((p, i) => {
          const l = trazo(g, p, pts[(i + 2) % n], '#ffb8e2', 1);
          preparaDibujo(l);
          return l;
        });
        const halo = nodo('circle', { cx: C, cy: C, r: R * 1.1, fill: 'none',
          stroke: '#ffcfe8', 'stroke-width': 2, opacity: 0 }, g);
        const motas = [];
        for (let i = 0; i < ctx.piezas(6); i++) {
          const q = pol(az(0, TAU), az(1.5, 2));
          motas.push(particula(g, q.x, q.y, az(0.8, 1.6), '#ffd8ee', 0));
        }

        return gsap.timeline({ repeat: -1, repeatDelay: 2 })
          // 1) practicamente vacio -> aparecen puntos
          .to(nodos, { opacity: 0.95, duration: 0.5,
                       stagger: { each: 0.14, from: 'random' } })
          // 2) los puntos generan conexiones
          .to(lineas, { opacity: 0.7, duration: 0.2, stagger: 0.12 }, 1.3)
          .to(lineas, { attr: { 'stroke-dashoffset': 0 }, duration: 0.8,
                        ease: 'power2.inOut', stagger: 0.12 }, 1.3)
          // 3) la estructura se afianza
          .to(nodos, { attr: { r: 3.4 }, duration: 0.6, ease: 'back.out(2)' }, 2.6)
          // 4) resplandor
          .to(halo, { opacity: 0.85, duration: 0.5, ease: 'power2.out' }, 3.1)
          .to(halo, { attr: { r: R * 1.75, 'stroke-width': 0.3 }, opacity: 0,
                      duration: 1.4, ease: 'power2.out' }, 3.4)
          .to(motas, { opacity: 0.9, duration: 0.4, stagger: 0.05 }, 3.4)
          // 5) todo se dispersa lentamente
          .to(motas, { x: () => az(-20, 20), y: () => az(-20, 20), opacity: 0,
                       duration: 2, ease: 'power1.out', stagger: 0.05 }, 3.8)
          .to(lineas, { opacity: 0, duration: 1, stagger: 0.08 }, 4.2)
          .to(nodos, { opacity: 0, attr: { r: 1 }, duration: 1.2,
                       ease: 'power1.in', stagger: 0.07 }, 4.6)
          .set(lineas, { attr: { 'stroke-dashoffset': (i, t) => t.getTotalLength() } })
          .set(motas, { x: 0, y: 0 });
      },
    },
  };

  /* ═════════════════════════════════════════════════════════
     MOTOR
     ═════════════════════════════════════════════════════════ */
  const vivos = new Map();     // host -> { clave, tl, svg }

  function destruir(host) {
    const m = vivos.get(host);
    if (!m) return;
    m.tl.kill();
    gsap.killTweensOf(m.svg.querySelectorAll('*'));
    m.svg.remove();
    vivos.delete(host);
    host.classList.remove('mk-host');
  }

  function montar(host) {
    const clave = [...host.classList].find((c) => MK[c]);
    const previo = vivos.get(host);

    if (!clave) { if (previo) destruir(host); return; }
    if (previo && previo.clave === clave) return;
    if (previo) destruir(host);
    if (!hayGSAP()) return;

    const r = host.getBoundingClientRect();
    const lado = Math.max(20, Math.min(r.width, r.height));

    const svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('class', 'mk-svg');
    svg.setAttribute('viewBox', '0 0 200 200');
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    // posicionamiento en linea: no depende de que el CSS haya cargado
    svg.style.cssText = 'position:absolute;top:-50%;left:-50%;width:200%;height:200%;' +
                        'pointer-events:none;overflow:visible;display:block;z-index:0;';
    if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
    host.appendChild(svg);
    host.classList.add('mk-host');

    const g = document.createElementNS(NS, 'g');
    svg.appendChild(g);

    // adaptacion al contexto: mismo marco, menos piezas si es pequeño
    const escala = Math.max(0.45, Math.min(1, lado / 100));
    const ctx = {
      escala,
      vel: lado < 50 ? 1.25 : 1,
      color: PAL[MK[clave].rareza] || '#fff',
      piezas: (n) => Math.max(2, Math.round(n * (lado < 50 ? 0.55 : lado < 80 ? 0.8 : 1))),
    };

    const tl = MK[clave].crear(g, ctx);
    tl.timeScale(ctx.vel);
    if (menos) tl.progress(0.35).pause();     // fotograma representativo, sin movimiento

    vivos.set(host, { clave, tl, svg });
  }

  const SEL = '.profile-avatar, .comment-avatar, .author-avatar, .avatar-wrap, .cos-preview';
  const escanear = (raiz) => (raiz || document).querySelectorAll(SEL).forEach(montar);

  window.CosMarcos = {
    marcos: MK, montar, escanear, destruir,
    limpiarTodo: () => [...vivos.keys()].forEach(destruir),
    /** Pausa/reanuda todo (util para ahorrar recursos). */
    pausar: (v) => vivos.forEach((m) => (v ? m.tl.pause() : m.tl.resume())),
  };

  function iniciar() {
    if (!hayGSAP()) {
      console.warn('[CIVINSIS] GSAP no esta cargado: los marcos no se animaran.');
      return;
    }
    // MotionPath se usa en Ascenso, Legado y Vortice para que los
    // elementos sigan trayectorias curvas reales. Si no esta, esos
    // tres caen a un movimiento lineal en vez de fallar.
    if (window.MotionPathPlugin) gsap.registerPlugin(window.MotionPathPlugin);
    else console.info('[CIVINSIS] MotionPathPlugin no cargado: trayectorias simplificadas.');
    escanear();
    let t;
    new MutationObserver((muts) => {
      const rel = muts.some((m) => [...m.addedNodes, ...m.removedNodes]
        .some((x) => x.nodeType === 1 && !x.classList.contains('mk-svg')));
      if (!rel) return;
      clearTimeout(t); t = setTimeout(() => escanear(), 200);
    }).observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });

    // con la pestaña oculta no se gasta CPU
    document.addEventListener('visibilitychange', () => {
      window.CosMarcos.pausar(document.hidden);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
