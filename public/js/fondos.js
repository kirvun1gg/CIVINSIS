/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Fondos de perfil animados
   15 escenas, cada una con su propia historia visual.

   DECISIONES TECNICAS
   · UN canvas por banner y UN SOLO bucle compartido para todos:
     asi no hay 15 requestAnimationFrame compitiendo.
   · El bucle se DETIENE cuando el banner sale de pantalla
     (IntersectionObserver) y cuando la pestana pierde el foco.
   · Numero de particulas limitado y proporcional al area.
   · GSAP solo donde hay una SECUENCIA narrativa que coordinar
     (Evolucion, Sinergia, Legado, Legado Vivo, Inspiracion).
     El resto se resuelve con matematica ligera en el propio draw.
   · prefers-reduced-motion: se pinta un unico fotograma estatico.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const TAU = Math.PI * 2;
  const quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayGSAP = () => typeof window.gsap !== 'undefined';

  /* Aleatorio con semilla: las estrellas caen siempre en el mismo
     sitio, para que el fondo no "salte" al redimensionar. */
  function rnd(semilla) {
    let s = semilla;
    return () => (s = (s * 16807) % 2147483647) / 2147483647;
  }

  // ═══════════════════════════════════════════════════════════
  //  ESCENAS
  //  Cada una: { base, init(e), dibuja(e, t) }
  //  e = { ctx, w, h, r (random), datos, mx, my }
  // ═══════════════════════════════════════════════════════════
  const ESCENAS = {

    // ── NOCHE ESTELAR · comun ────────────────────────────────
    // Tres capas de profundidad + fugaz muy ocasional.
    'fondo-noche': {
      base: ['#0a1420', '#101d2e'],
      init(e) {
        const n = Math.round(e.w * e.h / 9000);
        e.datos.capas = [0.6, 1, 1.6].map((esc, i) =>
          Array.from({ length: Math.round(n / (i + 1.2)) }, () => ({
            x: e.r() * e.w, y: e.r() * e.h, s: esc * (0.5 + e.r()),
            f: 0.3 + e.r() * 0.7, ph: e.r() * TAU, fijo: e.r() > 0.6,
          })));
        e.datos.fugaz = { t: -1, x: 0, y: 0 };
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        // nubes tenues al fondo
        const g = ctx.createRadialGradient(w * 0.7, h * 0.3, 0, w * 0.7, h * 0.3, w * 0.5);
        g.addColorStop(0, 'rgba(40,70,110,.22)'); g.addColorStop(1, 'transparent');
        ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);

        e.datos.capas.forEach((capa, i) => {
          const par = (i + 1) * 0.6;          // parallax por capa
          capa.forEach((s) => {
            const br = s.fijo ? s.f : s.f * (0.55 + 0.45 * Math.sin(t * 0.0009 + s.ph));
            ctx.globalAlpha = br;
            ctx.fillStyle = i === 2 ? '#fff' : '#dbe9ff';
            ctx.beginPath();
            ctx.arc(s.x + e.mx * par, s.y + e.my * par, s.s, 0, TAU);
            ctx.fill();
          });
        });
        ctx.globalAlpha = 1;

        // estrella fugaz: muy de vez en cuando (fondo comun)
        const f = e.datos.fugaz;
        if (f.t < 0 && Math.random() < 0.0012) {
          f.t = 0; f.x = e.r() * w * 0.6; f.y = e.r() * h * 0.4;
        }
        if (f.t >= 0) {
          f.t += 0.02;
          const p = f.t, a = Math.max(0, 1 - p);
          ctx.strokeStyle = `rgba(255,255,255,${a})`; ctx.lineWidth = 1.4;
          ctx.beginPath();
          ctx.moveTo(f.x + p * 120, f.y + p * 70);
          ctx.lineTo(f.x + p * 120 - 26, f.y + p * 70 - 15);
          ctx.stroke();
          if (p >= 1) f.t = -1;
        }
      },
    },

    // ── AURORA · comun ───────────────────────────────────────
    // Cortinas de luz onduladas, cada una a su velocidad.
    'fondo-aurora': {
      base: ['#08211c', '#0d2b26'],
      init(e) {
        e.datos.cortinas = [
          { c: '54,192,161', amp: 0.16, vel: 0.00022, y: 0.42, gr: 0.30 },
          { c: '74,158,255', amp: 0.12, vel: 0.00031, y: 0.55, gr: 0.24 },
          { c: '120,230,200', amp: 0.20, vel: 0.00016, y: 0.34, gr: 0.18 },
        ];
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        e.datos.cortinas.forEach((o, i) => {
          ctx.beginPath();
          ctx.moveTo(0, h);
          for (let x = 0; x <= w; x += 12) {
            const k = x / w;
            const y = h * (o.y
              + o.amp * Math.sin(k * 3.2 + t * o.vel * 7 + i)
              + o.amp * 0.5 * Math.sin(k * 6.1 - t * o.vel * 4));
            ctx.lineTo(x, y + e.my * (i + 1) * 0.5);
          }
          ctx.lineTo(w, h); ctx.closePath();
          const g = ctx.createLinearGradient(0, h * 0.15, 0, h);
          const pulso = 0.75 + 0.25 * Math.sin(t * 0.0004 + i * 2);
          g.addColorStop(0, `rgba(${o.c},${o.gr * pulso})`);
          g.addColorStop(1, `rgba(${o.c},0)`);
          ctx.fillStyle = g; ctx.fill();
        });
      },
    },

    // ── HORIZONTE · comun ────────────────────────────────────
    // Amanecer abstracto: la luz respira y suben motas.
    'fondo-horizonte': {
      base: ['#0b1a24', '#16303a'],
      init(e) {
        e.datos.motas = Array.from({ length: 14 }, () => ({
          x: e.r() * e.w, y: e.r() * e.h, v: 0.12 + e.r() * 0.28, s: 0.6 + e.r() * 1.2,
        }));
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const hz = h * 0.72;
        const resp = 0.72 + 0.28 * Math.sin(t * 0.00035);
        // resplandor del horizonte
        const g = ctx.createRadialGradient(w * 0.5, hz, 0, w * 0.5, hz, w * 0.55);
        g.addColorStop(0, `rgba(255,190,110,${0.38 * resp})`);
        g.addColorStop(0.5, `rgba(255,140,80,${0.12 * resp})`);
        g.addColorStop(1, 'transparent');
        ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        // superficie oscura
        ctx.fillStyle = 'rgba(6,14,20,.82)';
        ctx.fillRect(0, hz, w, h - hz);
        // linea del horizonte
        ctx.strokeStyle = `rgba(255,205,140,${0.5 * resp})`; ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(0, hz); ctx.lineTo(w, hz); ctx.stroke();
        // motas que se elevan
        e.datos.motas.forEach((m) => {
          m.y -= m.v; if (m.y < h * 0.25) { m.y = hz; m.x = e.r() * w; }
          const a = Math.max(0, (m.y - h * 0.25) / (hz - h * 0.25));
          ctx.globalAlpha = a * 0.7;
          ctx.fillStyle = '#ffd9a8';
          ctx.beginPath(); ctx.arc(m.x, m.y, m.s, 0, TAU); ctx.fill();
        });
        ctx.globalAlpha = 1;
      },
    },

    // ── ASCENSO · raro ───────────────────────────────────────
    // Sistema de energia: tamanos, estelas, desvios y destellos.
    'fondo-ascenso': {
      base: ['#1a0d05', '#2e1608'],
      init(e) {
        const n = Math.min(34, Math.round(e.w / 22));
        e.datos.p = Array.from({ length: n }, () => nuevaP(e, true));
        function nuevaP(e, inicial) {
          return {
            x: e.r() * e.w, y: inicial ? e.r() * e.h : e.h + 6,
            v: 0.25 + e.r() * 0.85, s: 0.7 + e.r() * 2,
            desv: (e.r() - 0.5) * 0.35, estela: e.r() > 0.6,
            fase: e.r() * TAU, destello: 0,
          };
        }
        e.datos.nueva = nuevaP;
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        e.datos.p.forEach((p, i) => {
          p.y -= p.v; p.x += Math.sin(t * 0.001 + p.fase) * p.desv;
          const alt = 1 - p.y / h;                      // 0 abajo, 1 arriba
          if (p.y < h * 0.16) {                          // destella y desaparece
            if (p.destello < 1) p.destello += 0.12;
            if (p.destello >= 1) { e.datos.p[i] = e.datos.nueva(e, false); return; }
          }
          const a = Math.min(1, alt * 2.2) * (1 - Math.max(0, alt - 0.72) * 3);
          if (p.estela) {
            const g = ctx.createLinearGradient(p.x, p.y, p.x, p.y + 22);
            g.addColorStop(0, `rgba(255,180,90,${0.5 * a})`);
            g.addColorStop(1, 'rgba(255,140,60,0)');
            ctx.strokeStyle = g; ctx.lineWidth = p.s * 0.8;
            ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(p.x, p.y + 22); ctx.stroke();
          }
          ctx.globalAlpha = Math.max(0, a);
          ctx.fillStyle = p.destello > 0 ? '#fff0d0' : '#ffb15e';
          const rr = p.s * (1 + p.destello * 2.5);
          ctx.beginPath(); ctx.arc(p.x, p.y, rr, 0, TAU); ctx.fill();
        });
        ctx.globalAlpha = 1;
      },
    },

    // ── FLUJO · raro ─────────────────────────────────────────
    // Corrientes con pulsos que las recorren y saltan de una a otra.
    'fondo-flujo': {
      base: ['#0a1f24', '#103038'],
      init(e) {
        const n = Math.max(5, Math.round(e.h / 26));
        e.datos.lineas = Array.from({ length: n }, (_, i) => ({
          y: (i + 0.5) * (e.h / n), largo: 0.35 + e.r() * 0.5,
          x0: e.r() * 0.4, br: 0.3 + e.r() * 0.5,
        }));
        e.datos.pulsos = Array.from({ length: 4 }, () => ({
          l: Math.floor(e.r() * n), p: e.r(), v: 0.004 + e.r() * 0.006,
        }));
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const sesgo = 0.35;                        // inclinacion de las corrientes
        e.datos.lineas.forEach((L, i) => {
          const x1 = L.x0 * w, x2 = (L.x0 + L.largo) * w;
          const yy = L.y + Math.sin(t * 0.0004 + i) * 3;
          ctx.strokeStyle = `rgba(90,200,220,${L.br * 0.32})`;
          ctx.lineWidth = 1.1;
          ctx.beginPath();
          ctx.moveTo(x1, yy + sesgo * (x1 - w / 2) * 0.12);
          ctx.lineTo(x2, yy + sesgo * (x2 - w / 2) * 0.12);
          ctx.stroke();
        });
        e.datos.pulsos.forEach((P) => {
          const L = e.datos.lineas[P.l];
          P.p += P.v;
          if (P.p > 1) { P.p = 0; P.l = Math.floor(Math.random() * e.datos.lineas.length); return; }
          const x1 = L.x0 * w, x2 = (L.x0 + L.largo) * w;
          const x = x1 + (x2 - x1) * P.p;
          const y = L.y + sesgo * (x - w / 2) * 0.12;
          const a = Math.sin(P.p * Math.PI);
          const g = ctx.createRadialGradient(x, y, 0, x, y, 16);
          g.addColorStop(0, `rgba(160,240,255,${a})`);
          g.addColorStop(1, 'transparent');
          ctx.fillStyle = g;
          ctx.fillRect(x - 16, y - 16, 32, 32);
        });
      },
    },

    // ── COSMOS · epico ───────────────────────────────────────
    // Tres capas con parallax + nebulosas + fugaz ocasional.
    'fondo-cosmos': {
      base: ['#070a1c', '#14092b'],
      init(e) {
        e.datos.capas = [
          { n: Math.round(e.w * e.h / 5000), s: 0.5, p: 0.25, c: '#8fa6d8' },
          { n: Math.round(e.w * e.h / 11000), s: 1.0, p: 0.6,  c: '#dbe9ff' },
          { n: Math.round(e.w * e.h / 26000), s: 1.7, p: 1.1,  c: '#ffffff' },
        ].map((c) => ({
          ...c,
          e: Array.from({ length: c.n }, () => ({ x: e.r() * e.w, y: e.r() * e.h, ph: e.r() * TAU })),
        }));
        e.datos.neb = [
          { x: 0.24, y: 0.32, r: 0.42, c: '150,90,255' },
          { x: 0.76, y: 0.64, r: 0.36, c: '60,150,255' },
        ];
        e.datos.fugaz = -1;
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        e.datos.neb.forEach((n, i) => {
          const pul = 0.7 + 0.3 * Math.sin(t * 0.00025 + i * 2);
          const cx = n.x * w + e.mx * 0.3, cy = n.y * h + e.my * 0.3;
          const g = ctx.createRadialGradient(cx, cy, 0, cx, cy, n.r * w * pul);
          g.addColorStop(0, `rgba(${n.c},.3)`); g.addColorStop(1, 'transparent');
          ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        });
        e.datos.capas.forEach((capa) => {
          ctx.fillStyle = capa.c;
          capa.e.forEach((s) => {
            ctx.globalAlpha = 0.45 + 0.55 * Math.abs(Math.sin(t * 0.0005 + s.ph));
            const dx = (t * 0.004 * capa.p) % (w + 10);
            ctx.beginPath();
            ctx.arc((s.x + dx) % w, s.y + e.my * capa.p, capa.s, 0, TAU);
            ctx.fill();
          });
        });
        ctx.globalAlpha = 1;
        if (e.datos.fugaz < 0 && Math.random() < 0.003) e.datos.fugaz = 0;
        if (e.datos.fugaz >= 0) {
          const p = (e.datos.fugaz += 0.022), a = Math.max(0, 1 - p);
          const x = w * 0.15 + p * w * 0.7, y = h * 0.2 + p * h * 0.35;
          ctx.strokeStyle = `rgba(255,255,255,${a})`; ctx.lineWidth = 1.5;
          ctx.beginPath(); ctx.moveTo(x, y); ctx.lineTo(x - 34, y - 17); ctx.stroke();
          if (p >= 1) e.datos.fugaz = -1;
        }
      },
    },

    // ── CONEXIONES · epico ───────────────────────────────────
    // Red irregular; el pulso salta de nodo en nodo por la red.
    'fondo-conexiones': {
      base: ['#08201d', '#0d2a2f'],
      init(e) {
        const n = Math.min(16, Math.max(8, Math.round(e.w / 62)));
        e.datos.nodos = Array.from({ length: n }, () => ({
          x: e.r() * e.w, y: e.r() * e.h, r: 1.6 + e.r() * 2.6, luz: 0,
        }));
        // cada nodo se une a sus 2 vecinos mas cercanos: red irregular
        e.datos.enl = [];
        e.datos.nodos.forEach((a, i) => {
          e.datos.nodos.map((b, j) => ({ j, d: Math.hypot(a.x - b.x, a.y - b.y) }))
            .filter((o) => o.j !== i).sort((x, y) => x.d - y.d).slice(0, 2)
            .forEach((o) => { if (!e.datos.enl.some((k) => k.a === o.j && k.b === i)) e.datos.enl.push({ a: i, b: o.j }); });
        });
        e.datos.pulso = { enl: 0, p: 0 };
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const N = e.datos.nodos;
        ctx.lineWidth = 1;
        e.datos.enl.forEach((k, i) => {
          const a = N[k.a], b = N[k.b];
          const vis = 0.1 + 0.12 * (1 + Math.sin(t * 0.0004 + i));
          ctx.strokeStyle = `rgba(90,230,200,${vis})`;
          ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
        });
        // el pulso recorre un enlace y enciende el nodo de destino
        const P = e.datos.pulso, k = e.datos.enl[P.enl];
        if (k) {
          const a = N[k.a], b = N[k.b];
          P.p += 0.02;
          if (P.p >= 1) {
            P.p = 0; N[k.b].luz = 1;
            const sig = e.datos.enl.filter((x) => x.a === k.b || x.b === k.b);
            P.enl = e.datos.enl.indexOf(sig.length ? sig[Math.floor(Math.random() * sig.length)]
              : e.datos.enl[Math.floor(Math.random() * e.datos.enl.length)]);
          }
          const x = a.x + (b.x - a.x) * P.p, y = a.y + (b.y - a.y) * P.p;
          const g = ctx.createRadialGradient(x, y, 0, x, y, 12);
          g.addColorStop(0, 'rgba(150,255,230,.95)'); g.addColorStop(1, 'transparent');
          ctx.fillStyle = g; ctx.fillRect(x - 12, y - 12, 24, 24);
        }
        N.forEach((n) => {
          n.luz *= 0.965;
          ctx.fillStyle = `rgba(90,230,200,${0.35 + n.luz * 0.65})`;
          ctx.beginPath(); ctx.arc(n.x, n.y, n.r * (1 + n.luz * 0.9), 0, TAU); ctx.fill();
        });
      },
    },

    // ── NEBULOSA · epico ─────────────────────────────────────
    // Gas que se expande y comprime + particulas internas.
    'fondo-nebulosa': {
      base: ['#0a0f24', '#17103a'],
      init(e) {
        e.datos.gas = Array.from({ length: 5 }, (_, i) => ({
          x: 0.2 + e.r() * 0.6, y: 0.2 + e.r() * 0.6, r: 0.22 + e.r() * 0.26,
          c: ['120,80,255', '50,190,200', '255,120,200', '90,120,255', '160,90,230'][i],
          v: 0.00012 + e.r() * 0.0002, ph: e.r() * TAU,
        }));
        e.datos.p = Array.from({ length: 22 }, () => ({
          x: e.r() * e.w, y: e.r() * e.h, ph: e.r() * TAU, s: 0.5 + e.r() * 1.1,
        }));
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        e.datos.gas.forEach((n) => {
          const esc = 1 + 0.24 * Math.sin(t * n.v + n.ph);
          const cx = (n.x + Math.sin(t * n.v * 0.6 + n.ph) * 0.04) * w + e.mx * 0.4;
          const cy = (n.y + Math.cos(t * n.v * 0.5 + n.ph) * 0.04) * h + e.my * 0.4;
          const g = ctx.createRadialGradient(cx, cy, 0, cx, cy, n.r * w * esc);
          g.addColorStop(0, `rgba(${n.c},.26)`);
          g.addColorStop(0.6, `rgba(${n.c},.08)`);
          g.addColorStop(1, 'transparent');
          ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        });
        e.datos.p.forEach((p) => {
          const br = Math.max(0, Math.sin(t * 0.0006 + p.ph));
          ctx.globalAlpha = br * 0.9;
          ctx.fillStyle = '#e9dcff';
          ctx.beginPath(); ctx.arc(p.x, p.y, p.s * (0.6 + br), 0, TAU); ctx.fill();
        });
        ctx.globalAlpha = 1;
      },
    },

    // ── EVOLUCION · legendario ───────────────────────────────
    // Un punto se ramifica hasta formar una estructura compleja
    // y despues se reorganiza. Secuencia coordinada con GSAP.
    'fondo-evolucion': {
      base: ['#071a1f', '#0d2733'],
      init(e) {
        e.datos.fase = { v: 0 };                       // 0 → 1 a lo largo del ciclo
        e.datos.nodos = [{ x: 0.5, y: 0.5, gen: 0 }];
        // se generan 4 generaciones de ramas deterministas
        for (let g = 1; g <= 4; g++) {
          const padres = e.datos.nodos.filter((n) => n.gen === g - 1);
          padres.forEach((p, i) => {
            const ramas = g === 1 ? 4 : 2;
            for (let k = 0; k < ramas; k++) {
              const a = (i * 1.7 + k * (TAU / ramas) + g) % TAU;
              const d = 0.1 + g * 0.075;
              e.datos.nodos.push({
                x: Math.min(0.96, Math.max(0.04, 0.5 + Math.cos(a) * d * 1.5)),
                y: Math.min(0.94, Math.max(0.06, 0.5 + Math.sin(a) * d)),
                gen: g, padre: e.datos.nodos.indexOf(p),
              });
            }
          });
        }
        if (hayGSAP() && !quieto) {
          gsap.to(e.datos.fase, {
            v: 1, duration: 9, ease: 'power1.inOut',
            repeat: -1, repeatDelay: 1.2, yoyo: true,
          });
        } else {
          e.datos.auto = true;                          // respaldo sin GSAP
        }
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        if (e.datos.auto) e.datos.fase.v = (Math.sin(t * 0.00012) + 1) / 2;
        const f = e.datos.fase.v;
        const visibles = f * 4.6;                       // cuantas generaciones se ven
        e.datos.nodos.forEach((n, i) => {
          const ap = Math.min(1, Math.max(0, visibles - n.gen));
          if (ap <= 0) return;
          const x = n.x * w, y = n.y * h;
          if (n.padre !== undefined) {
            const p = e.datos.nodos[n.padre];
            ctx.strokeStyle = `rgba(90,220,255,${0.28 * ap})`;
            ctx.lineWidth = Math.max(0.6, 2 - n.gen * 0.35);
            ctx.beginPath();
            ctx.moveTo(p.x * w, p.y * h);
            ctx.lineTo(p.x * w + (x - p.x * w) * ap, p.y * h + (y - p.y * h) * ap);
            ctx.stroke();
          }
          ctx.globalAlpha = ap;
          ctx.fillStyle = n.gen === 0 ? '#ffffff' : '#7fe4ff';
          ctx.beginPath();
          ctx.arc(x, y, Math.max(1, 4 - n.gen * 0.7), 0, TAU);
          ctx.fill();
        });
        ctx.globalAlpha = 1;
      },
    },

    // ── LEGADO · legendario ──────────────────────────────────
    // Una linea recorre el banner y deja marcas que tardan en irse.
    'fondo-legado': {
      base: ['#16110a', '#241a08'],
      init(e) {
        e.datos.marcas = [];
        e.datos.rec = { p: -0.2 };
        if (hayGSAP() && !quieto) {
          gsap.to(e.datos.rec, { p: 1.2, duration: 6, ease: 'power2.inOut',
            repeat: -1, repeatDelay: 3.5 });
        } else { e.datos.auto = true; }
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        if (e.datos.auto) e.datos.rec.p = ((t * 0.00008) % 1.6) - 0.2;
        const p = e.datos.rec.p;
        if (p > -0.05 && p < 1.05) {
          const x = p * w;
          const g = ctx.createLinearGradient(x - 60, 0, x + 12, 0);
          g.addColorStop(0, 'rgba(255,210,120,0)');
          g.addColorStop(1, 'rgba(255,225,150,.5)');
          ctx.fillStyle = g; ctx.fillRect(x - 60, 0, 72, h);
          ctx.strokeStyle = 'rgba(255,235,180,.85)'; ctx.lineWidth = 1.6;
          ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke();
          // va dejando huella
          if (Math.random() < 0.5) {
            e.datos.marcas.push({ x, y: Math.random() * h, s: 0.8 + Math.random() * 2, a: 1 });
          }
        }
        e.datos.marcas = e.datos.marcas.filter((m) => (m.a -= 0.004) > 0);
        e.datos.marcas.forEach((m) => {
          ctx.globalAlpha = m.a * 0.9;
          ctx.fillStyle = '#ffd98a';
          ctx.beginPath(); ctx.arc(m.x, m.y, m.s, 0, TAU); ctx.fill();
        });
        ctx.globalAlpha = 1;
      },
    },

    // ── PERSPECTIVA · raro (nuevo) ───────────────────────────
    // Un cubo de aristas girando: la geometria cambia de punto de vista.
    'fondo-perspectiva': {
      base: ['#0e1626', '#152238'],
      init(e) {
        const V = [[-1,-1,-1],[1,-1,-1],[1,1,-1],[-1,1,-1],[-1,-1,1],[1,-1,1],[1,1,1],[-1,1,1]];
        e.datos.V = V;
        e.datos.A = [[0,1],[1,2],[2,3],[3,0],[4,5],[5,6],[6,7],[7,4],[0,4],[1,5],[2,6],[3,7]];
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const cx = w / 2 + e.mx, cy = h / 2 + e.my, esc = Math.min(w, h) * 0.3;
        const a = t * 0.00022, b = t * 0.00015;
        const proy = ([x, y, z]) => {
          let X = x * Math.cos(a) - z * Math.sin(a), Z = x * Math.sin(a) + z * Math.cos(a);
          let Y = y * Math.cos(b) - Z * Math.sin(b); Z = y * Math.sin(b) + Z * Math.cos(b);
          const k = 2.6 / (2.6 + Z);
          return [cx + X * esc * k, cy + Y * esc * k, k];
        };
        const P = e.datos.V.map(proy);
        e.datos.A.forEach(([i, j]) => {
          const prof = (P[i][2] + P[j][2]) / 2;
          ctx.strokeStyle = `rgba(120,190,255,${0.12 + prof * 0.3})`;
          ctx.lineWidth = prof * 1.6;
          ctx.beginPath(); ctx.moveTo(P[i][0], P[i][1]); ctx.lineTo(P[j][0], P[j][1]); ctx.stroke();
        });
        P.forEach((p) => {
          ctx.fillStyle = `rgba(170,220,255,${p[2] * 0.5})`;
          ctx.beginPath(); ctx.arc(p[0], p[1], p[2] * 2, 0, TAU); ctx.fill();
        });
      },
    },

    // ── SINERGIA · epico (nuevo) ─────────────────────────────
    // Separacion → acercamiento → fusion → expansion → separacion.
    'fondo-sinergia': {
      base: ['#0b1f1a', '#10302a'],
      init(e) {
        e.datos.n = 7;
        e.datos.piezas = Array.from({ length: 7 }, (_, i) => {
          const a = (i / 7) * TAU;
          return { a, d: 0.34 + (i % 3) * 0.05, r: 4 + (i % 3) * 2 };
        });
        e.datos.c = { v: 0 };
        if (hayGSAP() && !quieto) {
          gsap.to(e.datos.c, { v: 1, duration: 4.2, ease: 'power2.inOut',
            repeat: -1, yoyo: true, repeatDelay: 1.4 });
        } else { e.datos.auto = true; }
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        if (e.datos.auto) e.datos.c.v = (Math.sin(t * 0.0004) + 1) / 2;
        const c = e.datos.c.v;                          // 0 separadas, 1 fundidas
        const cx = w / 2 + e.mx * 0.5, cy = h / 2 + e.my * 0.5;
        const R = Math.min(w, h);
        // halo de fusion
        if (c > 0.55) {
          const k = (c - 0.55) / 0.45;
          const g = ctx.createRadialGradient(cx, cy, 0, cx, cy, R * 0.4 * k);
          g.addColorStop(0, `rgba(120,240,200,${0.32 * k})`); g.addColorStop(1, 'transparent');
          ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        }
        e.datos.piezas.forEach((p, i) => {
          const d = p.d * (1 - c * 0.86) * R;
          const ang = p.a + t * 0.00018 + c * 0.7;
          const x = cx + Math.cos(ang) * d, y = cy + Math.sin(ang) * d * 0.72;
          ctx.strokeStyle = `rgba(120,240,200,${0.35 + c * 0.5})`;
          ctx.lineWidth = 1.6;
          ctx.beginPath();
          ctx.arc(x, y, p.r * (1 + c * 0.5), 0, TAU);
          ctx.stroke();
          if (c > 0.3) {                                 // se tienden lazos
            ctx.strokeStyle = `rgba(120,240,200,${(c - 0.3) * 0.35})`;
            ctx.lineWidth = 0.8;
            ctx.beginPath(); ctx.moveTo(x, y); ctx.lineTo(cx, cy); ctx.stroke();
          }
        });
      },
    },

    // ── INSPIRACION · epico (nuevo) ──────────────────────────
    // Actividad ambiental continua + convergencias con destello.
    'fondo-inspiracion': {
      base: ['#141024', '#1e1636'],
      init(e) {
        e.datos.p = Array.from({ length: 26 }, () => ({
          x: e.r() * e.w, y: e.r() * e.h,
          vx: (e.r() - 0.5) * 0.22, vy: (e.r() - 0.5) * 0.22, s: 0.6 + e.r() * 1.3,
        }));
        e.datos.ev = { p: 0, x: 0.5, y: 0.5, activo: false };
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const ev = e.datos.ev;
        if (!ev.activo && Math.random() < 0.004) {
          ev.activo = true; ev.p = 0; ev.x = 0.2 + Math.random() * 0.6; ev.y = 0.2 + Math.random() * 0.6;
        }
        if (ev.activo) { ev.p += 0.012; if (ev.p >= 1) ev.activo = false; }
        const fx = ev.x * w, fy = ev.y * h;
        const atrae = ev.activo ? Math.sin(Math.min(1, ev.p / 0.6) * Math.PI) : 0;

        e.datos.p.forEach((p) => {
          p.x += p.vx; p.y += p.vy;
          if (p.x < 0 || p.x > w) p.vx *= -1;
          if (p.y < 0 || p.y > h) p.vy *= -1;
          if (atrae > 0) { p.x += (fx - p.x) * 0.02 * atrae; p.y += (fy - p.y) * 0.02 * atrae; }
          ctx.globalAlpha = 0.5 + atrae * 0.5;
          ctx.fillStyle = '#d9c9ff';
          ctx.beginPath(); ctx.arc(p.x, p.y, p.s, 0, TAU); ctx.fill();
        });
        ctx.globalAlpha = 1;
        if (ev.activo && ev.p > 0.6) {                   // destello
          const k = 1 - (ev.p - 0.6) / 0.4;
          const g = ctx.createRadialGradient(fx, fy, 0, fx, fy, 90 * (1 - k) + 12);
          g.addColorStop(0, `rgba(255,245,210,${k})`); g.addColorStop(1, 'transparent');
          ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        }
      },
    },

    // ── HORIZONTE INFINITO · legendario (nuevo) ──────────────
    // Rejilla en perspectiva avanzando hacia un punto de fuga.
    'fondo-horizonte-infinito': {
      base: ['#0a0d1e', '#131a35'],
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const hz = h * 0.45, fugaX = w / 2 + e.mx * 1.5;
        const g = ctx.createRadialGradient(fugaX, hz, 0, fugaX, hz, w * 0.4);
        g.addColorStop(0, 'rgba(130,190,255,.34)'); g.addColorStop(1, 'transparent');
        ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
        // radiales
        ctx.lineWidth = 1;
        for (let i = -9; i <= 9; i++) {
          ctx.strokeStyle = `rgba(120,190,255,${0.24 - Math.abs(i) * 0.018})`;
          ctx.beginPath(); ctx.moveTo(fugaX, hz);
          ctx.lineTo(fugaX + i * w * 0.22, h); ctx.stroke();
        }
        // transversales que se acercan
        for (let i = 0; i < 9; i++) {
          const k = ((t * 0.00013 + i / 9) % 1);
          const y = hz + (h - hz) * k * k;               // aceleran al acercarse
          ctx.strokeStyle = `rgba(150,210,255,${0.3 * k})`;
          ctx.lineWidth = 0.6 + k * 1.4;
          ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
        }
      },
    },

    // ── LEGADO VIVO · legendario (nuevo) ─────────────────────
    // Una senal genera ondas; las ondas encienden nuevas senales.
    'fondo-legado-vivo': {
      base: ['#14110c', '#241c0f'],
      init(e) {
        e.datos.ondas = [];
        e.datos.semilla = 0;
      },
      dibuja(e, t) {
        const { ctx, w, h } = e;
        const O = e.datos.ondas;
        if (O.length === 0 || (O.length < 7 && Math.random() < 0.012)) {
          O.push({ x: Math.random() * w, y: Math.random() * h, r: 0, gen: 0 });
        }
        for (let i = O.length - 1; i >= 0; i--) {
          const o = O[i];
          o.r += 0.5 + o.gen * 0.1;
          const max = Math.min(w, h) * 0.42;
          const a = Math.max(0, 1 - o.r / max);
          ctx.strokeStyle = `rgba(255,205,120,${a * 0.55})`;
          ctx.lineWidth = 1.4 * a + 0.3;
          ctx.beginPath(); ctx.arc(o.x, o.y, o.r, 0, TAU); ctx.stroke();
          ctx.fillStyle = `rgba(255,230,170,${a})`;
          ctx.beginPath(); ctx.arc(o.x, o.y, 2 * a + 0.6, 0, TAU); ctx.fill();
          // al expandirse, inspira una nueva senal
          if (o.r > max * 0.55 && !o.hijo && o.gen < 2 && O.length < 9) {
            o.hijo = true;
            const ang = Math.random() * TAU;
            O.push({ x: o.x + Math.cos(ang) * o.r, y: o.y + Math.sin(ang) * o.r, r: 0, gen: o.gen + 1 });
          }
          if (a <= 0) O.splice(i, 1);
        }
      },
    },
  };

  // ═══════════════════════════════════════════════════════════
  //  MOTOR
  // ═══════════════════════════════════════════════════════════
  const activos = new Set();
  let corriendo = false;

  function medir(e) {
    const c = e.canvas, r = c.parentElement.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);   // techo 2x: mas no aporta
    e.w = Math.max(1, Math.round(r.width));
    e.h = Math.max(1, Math.round(r.height));
    c.width = e.w * dpr; c.height = e.h * dpr;
    c.style.width = e.w + 'px'; c.style.height = e.h + 'px';
    e.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    e.r = rnd(e.semilla);
    e.datos = {};
    if (e.escena.init) e.escena.init(e);
  }

  function pintar(e, t) {
    const { ctx, w, h } = e;
    const g = ctx.createLinearGradient(0, 0, w * 0.6, h);
    g.addColorStop(0, e.escena.base[0]); g.addColorStop(1, e.escena.base[1]);
    ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
    e.escena.dibuja(e, t);
  }

  function bucle(t) {
    activos.forEach((e) => { if (e.visible) pintar(e, t); });
    if (activos.size) requestAnimationFrame(bucle);
    else corriendo = false;
  }

  const obs = ('IntersectionObserver' in window)
    ? new IntersectionObserver((ent) => ent.forEach((x) => {
        const e = x.target._fx; if (e) e.visible = x.isIntersecting;
      }), { threshold: 0.01 })
    : null;

  function montar(host) {
    const clave = [...host.classList].find((c) => ESCENAS[c]);
    const previo = host.querySelector(':scope > .fx-canvas');

    if (!clave) { if (previo) desmontar(previo); return; }
    if (previo && previo.dataset.fondo === clave) return;
    if (previo) desmontar(previo);

    const canvas = document.createElement('canvas');
    canvas.className = 'fx-canvas';
    canvas.dataset.fondo = clave;
    host.insertBefore(canvas, host.firstChild);

    const e = {
      canvas, ctx: canvas.getContext('2d'), escena: ESCENAS[clave],
      semilla: 1 + [...clave].reduce((a, c) => a + c.charCodeAt(0), 0) * 7919,
      mx: 0, my: 0, visible: true, datos: {},
    };
    canvas._fx = e;
    medir(e);

    /* Si el banner estaba oculto (pestana inactiva) su ancho es 0 y el
       canvas nacia de 1x1: al mostrarse se estiraba y se veia un
       degradado plano. Con ResizeObserver se vuelve a medir en cuanto
       tiene tamano real. */
    if ('ResizeObserver' in window) {
      let ancho = 0;
      const ro = new ResizeObserver((ent) => {
        const w = Math.round(ent[0].contentRect.width);
        if (w > 2 && Math.abs(w - ancho) > 1) { ancho = w; medir(e); }
      });
      ro.observe(host);
      e.ro = ro;
    }

    if (quieto) { pintar(e, 0); return; }   // un solo fotograma

    activos.add(e);
    if (obs) obs.observe(canvas);
    if (!corriendo) { corriendo = true; requestAnimationFrame(bucle); }

    // parallax muy leve con el raton
    host.addEventListener('pointermove', (ev) => {
      const r = host.getBoundingClientRect();
      e.mx = ((ev.clientX - r.left) / r.width - 0.5) * 6;
      e.my = ((ev.clientY - r.top) / r.height - 0.5) * 4;
    }, { passive: true });
    host.addEventListener('pointerleave', () => { e.mx = 0; e.my = 0; }, { passive: true });

    // aparición suave al equipar
    canvas.classList.add('fx-entrando');
    setTimeout(() => canvas.classList.remove('fx-entrando'), 900);
  }

  function desmontar(canvas) {
    const e = canvas._fx;
    if (e) { activos.delete(e); if (obs) obs.unobserve(canvas); if (e.ro) e.ro.disconnect(); }
    canvas.remove();
  }

  const SEL = '.profile-hero, .public-hero, .cos-preview.es-fondo';
  function escanear(raiz) { (raiz || document).querySelectorAll(SEL).forEach(montar); }

  window.CosFondos = { escanear, montar, escenas: ESCENAS, remedir: () => activos.forEach(medir) };

  /* Punto unico para refrescar los tres tipos de cosmetico sin recargar. */
  window.CosRefrescar = function () {
    if (window.CosFondos) { window.CosFondos.escanear(); window.CosFondos.remedir(); }
    if (window.CosMarcos) window.CosMarcos.escanear();
  };

  function iniciar() {
    escanear();
    let t1;
    new MutationObserver(() => { clearTimeout(t1); t1 = setTimeout(() => escanear(), 150); })
      .observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });
    let t2;
    window.addEventListener('resize', () => {
      clearTimeout(t2); t2 = setTimeout(() => activos.forEach(medir), 200);
    });
    // no gastar CPU con la pestana en segundo plano
    document.addEventListener('visibilitychange', () => {
      activos.forEach((e) => { e.visible = !document.hidden; });
      if (!document.hidden && activos.size && !corriendo) {
        corriendo = true; requestAnimationFrame(bucle);
      }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
