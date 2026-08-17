/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Efectos
   16 eventos visuales. Cada uno tiene UNA escena y DOS modos:

     REAL     se dispara por un evento del sistema, se reproduce
              UNA vez y se destruye (canvas fuera, bucle parado).
     PREVIEW  la misma escena en bucle y algo mas rapida, solo
              dentro de la tarjeta de la coleccion.

   API
     CosEfectos.reproducir(elemento, 'efecto-chispa')   → una vez
     CosEfectos.evento('subida_nivel')                  → dispara el
              efecto que el usuario tenga equipado, si su evento coincide
     CosEfectos.configurar({ efecto: 'efecto-x', evento: 'y' })

   RENDIMIENTO
     · Un solo bucle compartido para todo lo que este activo.
     · El efecto REAL libera su canvas al terminar.
     · Las previews se detienen si salen de pantalla.
     · prefers-reduced-motion: se pinta un fotograma y se sale.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const TAU = Math.PI * 2;
  const quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Utilidades de dibujo compartidas -------------------------
     Todas leen los factores de adaptacion del contexto activo
     (_ctxK = escala, _ctxF = intensidad), fijados en medir().
     Asi un mismo efecto se ve bien en un avatar de 100 px y en uno
     de 38 px sin escribir dos versiones. */
  let _ctxK = 1, _ctxF = 1;

  const brillo = (ctx, x, y, r, color, a) => {
    r = r * _ctxK; a = Math.min(1, a * _ctxF);
    if (a <= 0 || r <= 0) return;
    const g = ctx.createRadialGradient(x, y, 0, x, y, r);
    g.addColorStop(0, `rgba(${color},${a})`);
    g.addColorStop(0.55, `rgba(${color},${a * 0.45})`);
    g.addColorStop(1, `rgba(${color},0)`);
    ctx.fillStyle = g;
    /* Se pinta un CIRCULO, no un rectangulo. Con fillRect, si el halo
       desbordaba el lienzo (avatares pequeños) el degradado quedaba
       cortado y se veia un cuadrado luminoso. */
    ctx.beginPath(); ctx.arc(x, y, r, 0, TAU); ctx.fill();
  };
  const punto = (ctx, x, y, r, color, a) => {
    // los puntos no bajan de 1 px: por debajo dejarian de verse
    r = Math.max(1, r * Math.max(0.55, _ctxK));
    ctx.globalAlpha = Math.max(0, Math.min(1, a * _ctxF));
    ctx.fillStyle = `rgb(${color})`;
    ctx.beginPath(); ctx.arc(x, y, r, 0, TAU); ctx.fill();
    ctx.globalAlpha = 1;
  };
  const anillo = (ctx, x, y, r, color, a, gr) => {
    if (a <= 0 || r <= 0) return;
    ctx.globalAlpha = Math.max(0, Math.min(1, a * _ctxF));
    ctx.strokeStyle = `rgb(${color})`;
    ctx.lineWidth = Math.max(0.8, (gr || 2) * Math.max(0.6, _ctxK));
    ctx.beginPath(); ctx.arc(x, y, r, 0, TAU); ctx.stroke();
    ctx.globalAlpha = 1;
  };
  /* Elige capa: delante si la condicion se cumple, detras si no.
     Regla del proyecto: si un elemento quedaria escondido tras la
     foto, se dibuja DELANTE. La visibilidad manda sobre la profundidad. */
  const capa = (e, delante) => (delante ? e.F : e.ctx);
  /* ¿Este punto quedaria tapado por el avatar? */
  const tapado = (e, x, y, margen) => Math.hypot(x - e.cx, y - e.cy) < e.R * (margen || 1.02);

  // curvas de tiempo
  const salida = (p) => 1 - Math.pow(1 - p, 3);
  const entrada = (p) => p * p;
  const pico = (p) => Math.sin(Math.min(1, Math.max(0, p)) * Math.PI);
  const tramo = (p, a, b) => Math.min(1, Math.max(0, (p - a) / (b - a)));

  /* ═════════════════════════════════════════════════════════
     ESCENAS
     dur   = duracion del efecto REAL en segundos
     draw(e, p)  p va de 0 a 1 a lo largo del evento
     e = { ctx, w, h, cx, cy, R (radio de referencia), datos }
     ═════════════════════════════════════════════════════════ */
  /* ── ZONAS ALREDEDOR DEL AVATAR ───────────────────────────
     El avatar ocupa el centro (radio R). Todo lo importante de
     cada efecto sucede FUERA de ese circulo, en las ocho zonas
     que lo rodean, para que nunca dependa de verse "por detras".
     ────────────────────────────────────────────────────────── */
  const ANG = {
    arriba: -Math.PI / 2,      arribaDer: -Math.PI / 4,
    derecha: 0,                abajoDer: Math.PI / 4,
    abajo: Math.PI / 2,        abajoIzq: 3 * Math.PI / 4,
    izquierda: Math.PI,        arribaIzq: -3 * Math.PI / 4,
  };
  /** Punto en una zona, a una distancia dada en radios de avatar. */
  const zona = (e, nombre, d) => {
    const a = typeof nombre === 'number' ? nombre : ANG[nombre];
    return { x: e.cx + Math.cos(a) * e.R * d, y: e.cy + Math.sin(a) * e.R * d, a };
  };

  const E = {

    // ── CHISPA · comun ───────────────────────────────────────
    // Nace FUERA, arriba a la derecha. Destella y se fragmenta
    // hacia el exterior. Nunca pisa la foto. Solo capa delantera.
    'efecto-chispa': {
      dur: 1.4, color: '255,235,160',
      init(e) {
        e.datos.z = zona(e, 'arribaDer', 1.06);   // pegada al borde del avatar
        e.datos.frag = Array.from({ length: Math.max(3, Math.round(6 * (e.densidad||1))) }, (_, i) => ({
          a: -1.9 + (i / 6) * 3.4, d: 0.55 + Math.random() * 0.7,
        }));
      },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, z = e.datos.z;
        if (p < 0.62) {
          const k = tramo(p, 0, 0.42), f = pico(tramo(p, 0.4, 0.62));
          brillo(ctx, z.x, z.y, 10 + k * 14 + f * 30, e.color, 0.35 + f * 0.6);
          punto(F, z.x, z.y, 2.4 + k * 3 + f * 3.5, e.color, 0.45 + k * 0.55);
        }
        if (p > 0.52) {                                  // se deshace hacia fuera
          const k = tramo(p, 0.52, 1), a = 1 - k;
          e.datos.frag.forEach((f) => {
            const d = k * f.d * R * 1.1;
            punto(F, z.x + Math.cos(f.a) * d, z.y + Math.sin(f.a) * d, 1.8 * a, e.color, a * 0.95);
          });
        }
      },
    },

    // ── REBOTE · comun ───────────────────────────────────────
    // Sale por la izquierda hacia fuera, rebota contra el limite
    // y vuelve. Todo el recorrido queda a la vista.
    'efecto-rebote': {
      dur: 1.3, color: '120,220,255',
      init(e) { e.datos.a = ANG.izquierda + (Math.random() - 0.5) * 1.2; },
      draw(e, p) {
        const { ctx, F, R } = e;
        const ida = salida(tramo(p, 0, 0.42));
        const vuelta = entrada(tramo(p, 0.42, 0.82));
        const d = R * (1.15 + ida * 0.95 - vuelta * 0.9);
        const x = e.cx + Math.cos(e.datos.a) * d, y = e.cy + Math.sin(e.datos.a) * d;
        const reb = pico(tramo(p, 0.36, 0.5));           // marca del rebote
        const fin = pico(tramo(p, 0.8, 1));
        brillo(ctx, x, y, 12 + fin * 28, e.color, 0.4 + fin * 0.55);
        punto(F, x, y, 3.6 + fin * 2, e.color, p < 0.97 ? 1 : 0);
        if (reb > 0) anillo(F, x, y, 5 + reb * 12, e.color, reb * 0.7, 1.4);
      },
    },

    // ── IDEA · raro ──────────────────────────────────────────
    // Las particulas NO convergen en el centro: se juntan en un
    // punto visible ARRIBA del avatar, donde nace el destello.
    'efecto-idea': {
      dur: 2.2, color: '255,225,140',
      init(e) {
        e.datos.foco = zona(e, 'arriba', 1.08);   // justo sobre el borde
        e.datos.p = Array.from({ length: Math.max(4, Math.round(7 * (e.densidad||1))) }, (_, i) => ({
          a: -2.9 + (i / 6) * 5.2, d: 1.55 + Math.random() * 0.45,
        }));
      },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, fo = e.datos.foco;
        const conv = salida(tramo(p, 0.18, 0.6));
        const disp = salida(tramo(p, 0.72, 1));
        const fl = pico(tramo(p, 0.56, 0.8));
        e.datos.p.forEach((q) => {
          const ox = e.cx + Math.cos(q.a) * R * q.d, oy = e.cy + Math.sin(q.a) * R * q.d;
          const x = ox + (fo.x - ox) * conv + Math.cos(q.a) * disp * R * 1.6;
          const y = oy + (fo.y - oy) * conv + Math.sin(q.a) * disp * R * 1.6;
          punto(F, x, y, 2.4, e.color, Math.min(1, p * 8) * (1 - disp));
        });
        if (fl > 0) {                                    // el destello, arriba y visible
          brillo(ctx, fo.x, fo.y, 10 + fl * 42, '255,248,215', fl * 0.95);
          punto(F, fo.x, fo.y, 3 + fl * 4, '255,252,235', fl);
        }
      },
    },

    // ── IMPULSO · raro ───────────────────────────────────────
    // La onda ARRANCA en el borde del avatar y crece hacia fuera:
    // su parte importante nunca cae sobre la foto.
    'efecto-impulso': {
      dur: 1.5, color: '54,192,161',
      draw(e, p) {
        const { ctx, F, R } = e;
        const k = salida(p);
        const r1 = R * (1.02 + k * 1.15);
        anillo(ctx, e.cx, e.cy, r1, e.color, (1 - k) * 0.5, 3 * (1 - k) + 0.4);
        anillo(ctx, e.cx, e.cy, r1, e.color, (1 - k) * 0.95, 3 * (1 - k) + 0.5);
        anillo(F, e.cx, e.cy, r1, e.color, (1 - k) * 0.3, 1);   // apenas un trazo delante
        const k2 = salida(tramo(p, 0.25, 1));
        if (k2 > 0) anillo(F, e.cx, e.cy, R * (1.02 + k2 * 0.8), e.color, (1 - k2) * 0.4, 1.2);
      },
    },

    // ── ECO · raro ───────────────────────────────────────────
    // Tres ondas concentricas que rodean al usuario y se alejan.
    'efecto-eco': {
      dur: 2.4, color: '150,200,255',
      init(e) { e.datos.o = { x: e.cx, y: e.cy }; },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, o = e.datos.o;
        [[0, 1.0], [0.2, 0.62], [0.42, 0.34]].forEach(([ini, fuerza], i) => {
          const k = tramo(p, ini, ini + 0.5);
          if (k <= 0 || k >= 1) return;
          const s = salida(k);
          const rr = R * (1.02 + s * (0.55 + i * 0.3));
          anillo(ctx, o.x, o.y, rr, e.color, (1 - s) * fuerza * 1.1, 2.8 * (1 - s) * fuerza + 0.4);
          anillo(F, o.x, o.y, rr, e.color, (1 - s) * fuerza * 0.3, 1);
        });
        
      },
    },

    // ── ORBITA · raro ────────────────────────────────────────
    // Giran POR FUERA del avatar; al pasar por detras se atenuan
    // (no desaparecen) y por delante brillan: orbita creible.
    'efecto-orbita': {
      dur: 2.6, color: '160,230,255',
      init(e) {
        e.datos.p = Array.from({ length: Math.max(3, Math.round(5 * (e.densidad||1))) }, (_, i) => ({
          a: (i / 5) * TAU, r: 1.32 + (i % 2) * 0.22, v: 1 + (i % 3) * 0.16,
        }));
      },
      draw(e, p) {
        const { ctx, F, R } = e;
        const acel = 1 + entrada(p) * 5;
        const fuga = salida(tramo(p, 0.72, 1));
        const a = Math.min(1, p * 8) * (1 - fuga);
        e.datos.p.forEach((q) => {
          const ang = q.a + p * acel * q.v * 1.5;
          const d = R * q.r * (1 + fuga * 1.5);
          const x = e.cx + Math.cos(ang) * d, y = e.cy + Math.sin(ang) * d;
          const delante = Math.sin(ang) > 0;             // mitad inferior por delante
          const c = delante ? F : ctx;
          punto(c, x, y, 2.6, e.color, a * (delante ? 1 : 0.55));
          const xr = e.cx + Math.cos(ang - 0.2) * d, yr = e.cy + Math.sin(ang - 0.2) * d;
          punto(c, xr, yr, 1.4, e.color, a * (delante ? 0.45 : 0.25));
        });
      },
    },

    // ── PULSO · epico ────────────────────────────────────────
    // Sale del borde inferior izquierdo y se aleja en diagonal,
    // dejando rastro. Nunca cruza el centro.
    'efecto-pulso': {
      dur: 1.8, color: '127,233,255',
      init(e) { e.datos.a = ANG.abajoIzq + (Math.random() - 0.5) * 0.8; },
      draw(e, p) {
        const { F, R } = e, a = e.datos.a;
        const k = salida(tramo(p, 0, 0.7));
        const d = R * (1.0 + k * 1.2);
        const x = e.cx + Math.cos(a) * d, y = e.cy + Math.sin(a) * d;
        for (let i = 1; i <= 5; i++) {                   // rastro
          const dd = d - i * R * 0.17;
          if (dd < R * 0.95) break;
          punto(F, e.cx + Math.cos(a) * dd, e.cy + Math.sin(a) * dd,
                2.6 - i * 0.34, e.color, (1 - i / 6) * 0.55 * (1 - tramo(p, 0.72, 1)));
        }
        if (p < 0.78) punto(F, x, y, 3.2, e.color, 1);
        const fin = pico(tramo(p, 0.66, 1));
        if (fin > 0) anillo(F, x, y, 4 + fin * 22, e.color, fin * 0.85, 1.8);
      },
    },

    // ── ESTRELLA FUGAZ · epico ───────────────────────────────
    // De una esquina a la contraria, ROZANDO el avatar en vez de
    // atravesarlo: se ve entera durante todo el recorrido.
    'efecto-estrella-fugaz': {
      dur: 1.7, color: '235,245,255',
      init(e) {
        const rutas = [
          { x0: -2.1, y0: -1.5, x1: 1.9, y1: 1.3, desv: -0.55 },
          { x0: 2.1, y0: -1.4, x1: -1.9, y1: 1.2, desv: 0.55 },
          { x0: -2.0, y0: 1.4, x1: 2.0, y1: -1.2, desv: -0.5 },
        ];
        e.datos.r = rutas[Math.floor(Math.random() * rutas.length)];
      },
      draw(e, p) {
        const { ctx, F, R } = e, r = e.datos.r;
        const k = salida(p);
        // la trayectoria se curva para bordear el avatar
        const curva = Math.sin(k * Math.PI) * r.desv;
        const x = e.cx + (r.x0 + (r.x1 - r.x0) * k) * R;
        const y = e.cy + (r.y0 + (r.y1 - r.y0) * k + curva) * R;
        const a = pico(p);
        const dx = (r.x1 - r.x0) * R, dy = (r.y1 - r.y0) * R;
        const L = Math.hypot(dx, dy) || 1;
        // por delante solo mientras roza el avatar; el resto, detras
        const c = Math.hypot(x - e.cx, y - e.cy) < R * 1.6 ? F : ctx;
        const g = c.createLinearGradient(x, y, x - dx / L * 52, y - dy / L * 52);
        g.addColorStop(0, `rgba(${e.color},${a})`);
        g.addColorStop(1, `rgba(${e.color},0)`);
        c.strokeStyle = g; c.lineWidth = 2.2; c.lineCap = 'round';
        c.beginPath(); c.moveTo(x, y); c.lineTo(x - dx / L * 52, y - dy / L * 52); c.stroke();
        punto(c, x, y, 2.4, e.color, a);
        brillo(c, x, y, 16, e.color, a * 0.6);
      },
    },

    // ── ENLACE · epico ───────────────────────────────────────
    // Sale del borde derecho hacia un destino lejano y visible.
    'efecto-enlace': {
      dur: 2.0, color: '92,230,196',
      init(e) {
        e.datos.o = zona(e, 'derecha', 1.02);
        e.datos.d = zona(e, ANG.arribaDer - 0.25, 2.05);
      },
      draw(e, p) {
        const { ctx } = e;
        const { F } = e, o = e.datos.o, d = e.datos.d;
        const k = salida(tramo(p, 0, 0.6));
        const x = o.x + (d.x - o.x) * k, y = o.y + (d.y - o.y) * k;
        if (p < 0.64) {
          F.strokeStyle = `rgba(${e.color},.4)`; F.lineWidth = 1.3;
          F.beginPath(); F.moveTo(o.x, o.y); F.lineTo(x, y); F.stroke();
          punto(F, x, y, 3.2, e.color, 1);
          brillo(ctx, x, y, 15, e.color, 0.6);
        }
        const lleg = pico(tramo(p, 0.55, 0.95));
        if (lleg > 0) {
          anillo(F, d.x, d.y, 3 + lleg * 18, e.color, lleg * 0.9, 1.8);
          punto(F, d.x, d.y, 2.8, e.color, lleg);
        }
      },
    },

    // ── ASCENSO · epico ──────────────────────────────────────
    // Suben por los LATERALES, no por el centro. Solo una cruza
    // brevemente por delante.
    'efecto-ascenso': {
      dur: 2.4, color: '158,240,212',
      init(e) {
        e.datos.p = Array.from({ length: Math.max(5, Math.round(10 * (e.densidad||1))) }, (_, i) => {
          const lado = i % 2 ? 1 : -1;
          return {
            x: lado * (1.05 + (i % 3) * 0.32),           // fuera del avatar
            ret: (i / 10) * 0.32, v: 0.9 + Math.random() * 0.45,
            dv: (Math.random() - 0.5) * 0.5, s: 1.5 + Math.random() * 1.4,
            estela: i % 3 !== 0, cruza: i === 4,          // solo una cruza delante
          };
        });
      },
      draw(e, p) {
        const { ctx, F, R } = e;
        e.datos.p.forEach((q) => {
          const k = tramo(p, q.ret, q.ret + 0.68) * q.v;
          if (k <= 0) return;
          const derivar = q.cruza ? -q.x * k * 0.85 : 0;  // la que cruza se acerca
          const x = e.cx + (q.x + derivar) * R + Math.sin(k * 4) * q.dv * R * 0.25;
          const y = e.cy + R * (1.25 - k * 2.6);
          const a = Math.min(1, k * 4) * (1 - tramo(k, 0.74, 1));
          const c = q.cruza && k > 0.35 ? F : (Math.abs(q.x) > 1 ? F : ctx);
          if (q.estela) {
            const g = c.createLinearGradient(x, y, x, y + 20);
            g.addColorStop(0, `rgba(${e.color},${a * 0.55})`);
            g.addColorStop(1, `rgba(${e.color},0)`);
            c.strokeStyle = g; c.lineWidth = q.s * 0.7;
            c.beginPath(); c.moveTo(x, y); c.lineTo(x, y + 20); c.stroke();
          }
          punto(c, x, y, q.s, e.color, a);
          if (k > 0.76) {                                 // destello al llegar arriba
            const f = pico(tramo(k, 0.76, 1));
            brillo(c, x, y, 4 + f * 16, e.color, f * 0.75);
          }
        });
      },
    },

    // ── REACCION · epico ─────────────────────────────────────
    // Nace en un lateral y provoca puntos en las zonas de alrededor.
    'efecto-reaccion': {
      dur: 2.4, color: '255,190,110',
      init(e) {
        e.datos.o = zona(e, 'izquierda', 1.02);   // sobre el borde
        // los nodos rodean al avatar por todos lados
        e.datos.h = ['arribaIzq', 'arriba', 'arribaDer', 'derecha', 'abajoDer', 'abajo', 'abajoIzq']
          .map((z, i) => ({ ...zona(e, z, 1.32 + (i % 2) * 0.22), ret: 0.24 + i * 0.06 }));
      },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, o = e.datos.o;
        punto(F, o.x, o.y, 3.4 * (1 - tramo(p, 0, 0.22)), e.color, 1 - tramo(p, 0, 0.3));
        const k = salida(tramo(p, 0, 0.5));
        anillo(ctx, o.x, o.y, R * (0.15 + k * 1.15), e.color, (1 - k) * 0.95, 3 * (1 - k) + 0.4);
        e.datos.h.forEach((h) => {
          const kh = tramo(p, h.ret, h.ret + 0.42);
          if (kh <= 0) return;
          punto(F, h.x, h.y, 2.6 * (1 - kh * 0.4), e.color, 1 - kh);
          const f = pico(kh);
          anillo(F, h.x, h.y, 2 + f * 16, e.color, f * 0.6, 1.3);
        });
      },
    },

    // ── CONVERGENCIA · epico ─────────────────────────────────
    // Se unen en un punto lateral visible, no sobre la cara.
    'efecto-convergencia': {
      dur: 2.2, color: '168,140,255',
      init(e) {
        e.datos.foco = { x: e.cx, y: e.cy };   // convergen hacia el usuario
        e.datos.p = ['arribaIzq', 'arriba', 'arribaDer', 'derecha', 'abajoDer', 'abajo', 'abajoIzq', 'izquierda']
          .map((z, i) => ({ ...zona(e, z, 2 + (i % 2) * 0.35), s: 2.2 + (i % 3) * 0.7,
                            ang: ANG[z] }));
      },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, fo = e.datos.foco;
        const ven = salida(tramo(p, 0.05, 0.55));
        const van = salida(tramo(p, 0.7, 1));
        const fl = pico(tramo(p, 0.52, 0.76));
        // Caen hacia el avatar y se detienen en su BORDE (1.05R):
        // asi el efecto rodea al usuario en vez de juntarse a un lado.
        e.datos.p.forEach((q) => {
          const dIni = 2.2, dFin = 1.05;
          const d = R * (dIni - (dIni - dFin) * ven + van * 1.6);
          const x = e.cx + Math.cos(q.ang) * d, y = e.cy + Math.sin(q.ang) * d;
          const a = Math.min(1, p * 8) * (1 - van);
          if (ven > 0.05 && van === 0) {
            const d2 = d + R * 0.22;
            F.strokeStyle = `rgba(${e.color},${a * 0.28})`; F.lineWidth = 1;
            F.beginPath(); F.moveTo(x, y);
            F.lineTo(e.cx + Math.cos(q.ang) * d2, e.cy + Math.sin(q.ang) * d2); F.stroke();
          }
          punto(F, x, y, q.s, e.color, a);
        });
        if (fl > 0) {                                    // el anillo se enciende
          // el anillo de union y su halo nacen detras de la foto
          anillo(ctx, e.cx, e.cy, R * 1.05, '225,205,255', fl * 0.95, 3 + fl * 4);
          brillo(ctx, e.cx, e.cy, R * (1.05 + fl * 0.6), '225,205,255', fl * 0.45);
        }
      },
    },

    // ── AURORA · legendario ──────────────────────────────────
    // Dos cortinas de luz a los LADOS del avatar, no un disco que
    // lo tape. La zona central queda despejada.
    'efecto-aurora': {
      dur: 3.0, color: '120,220,220',
      init(e) {
        e.datos.p = Array.from({ length: Math.max(5, Math.round(10 * (e.densidad||1))) }, (_, i) => ({
          a: (i % 2 ? -1 : 1) * (0.5 + Math.random() * 1.2) + (i % 2 ? 0 : Math.PI),
          d: 1.35 + Math.random() * 0.6,
        }));
      },
      draw(e, p) {
        const { ctx, F, R } = e;
        const k = salida(tramo(p, 0, 0.72));
        const a = pico(p);
        const tonos = [[54, 192, 161], [74, 158, 255], [168, 85, 247], [255, 122, 198]];
        const idx = Math.min(tonos.length - 2, Math.floor(k * (tonos.length - 1)));
        const f = k * (tonos.length - 1) - idx;
        const c = tonos[idx].map((v, i) => Math.round(v + (tonos[idx + 1][i] - v) * f)).join(',');
        // dos cortinas laterales
        [-1, 1].forEach((lado) => {
          const cx = e.cx + lado * R * (0.95 + k * 0.45);
          const rr = R * (0.75 + k * 0.85);
          const g = ctx.createRadialGradient(cx, e.cy, 0, cx, e.cy, rr);
          g.addColorStop(0, `rgba(${c},${a * 0.55})`);
          g.addColorStop(1, `rgba(${c},0)`);
          ctx.fillStyle = g; ctx.fillRect(cx - rr, e.cy - rr, rr * 2, rr * 2);
        });
        // arco superior por delante, muy tenue
        // el arco de luz va detras; solo las particulas quedan delante
        ctx.strokeStyle = `rgba(${c},${a * 0.65})`; ctx.lineWidth = 3;
        ctx.beginPath(); ctx.arc(e.cx, e.cy, R * (1.1 + k * 0.6), Math.PI * 1.12, Math.PI * 1.88); ctx.stroke();
        if (p > 0.4) {
          const kp = tramo(p, 0.4, 1);
          e.datos.p.forEach((q) => {
            const d = R * q.d * (1 + kp * 0.45);
            punto(F, e.cx + Math.cos(q.a) * d, e.cy + Math.sin(q.a) * d, 1.9, '225,245,255', (1 - kp) * 0.95);
          });
        }
      },
    },

    // ── METAMORFOSIS · legendario ────────────────────────────
    // Orbitan lejos, se concentran en el BORDE (no en el centro),
    // destello sobre el borde y dispersion.
    'efecto-metamorfosis': {
      dur: 3.2, color: '255,225,150',
      init(e) {
        e.datos.p = Array.from({ length: Math.max(5, Math.round(10 * (e.densidad||1))) }, (_, i) => ({
          a: (i / 10) * TAU, r: 1.75 + (i % 3) * 0.22, s: 2 + (i % 2) * 1,
        }));
        e.datos.foco = zona(e, 'arribaDer', 1.02);   // sobre el borde
      },
      draw(e, p) {
        const { ctx, F, R } = e, fo = e.datos.foco;
        const ini = pico(tramo(p, 0, 0.13));
        // El resplandor va DETRAS: simula una luz que nace tras la foto
        if (ini > 0) brillo(ctx, fo.x, fo.y, R * (0.4 + ini * 0.7), e.color, ini * 0.75);
        const gira = tramo(p, 0.1, 0.7);
        const acel = 1 + entrada(gira) * 5;
        const conv = salida(tramo(p, 0.52, 0.76));
        const disp = salida(tramo(p, 0.82, 1));
        const a = Math.min(1, tramo(p, 0.06, 0.2)) * (1 - disp);
        e.datos.p.forEach((q) => {
          const ang = q.a + gira * acel * 1.3;
          const d = R * (q.r * (1 - conv * 0.42) + disp * 1.8);   // se juntan al borde
          const x = e.cx + Math.cos(ang) * d, y = e.cy + Math.sin(ang) * d;
          const delante = Math.sin(ang) > -0.2 || conv > 0.2;
          punto(delante ? F : ctx, x, y, q.s, e.color, a * (delante ? 1 : 0.5));
        });
        const fl = pico(tramo(p, 0.7, 0.9));
        if (fl > 0) {
          // el halo y el anillo, detras; solo un pequeño nucleo delante
          anillo(ctx, e.cx, e.cy, R * 1.05, e.color, fl * 0.95, 3 + fl * 4);
          brillo(ctx, fo.x, fo.y, 12 + fl * 52, '255,250,225', fl * 0.95);
          brillo(F, fo.x, fo.y, 5 + fl * 14, '255,252,235', fl * 0.45);
        }
      },
    },

    // ── ONDA CIVICA · legendario ─────────────────────────────
    // El pulso nace junto al avatar y la propagacion recorre TODO
    // el espacio del perfil, con nodos en las ocho zonas.
    'efecto-onda-civica': {
      dur: 3.6, color: '54,192,161',
      init(e) {
        e.datos.o = { x: e.cx, y: e.cy + e.R * 0.55 };   // junto al avatar
        e.datos.n1 = ['izquierda', 'abajoIzq', 'abajoDer', 'derecha']
          .map((z, i) => ({ ...zona(e, z, 1.5), ret: 0.22 + i * 0.05 }));
        e.datos.n2 = ['arribaIzq', 'arriba', 'arribaDer', 'izquierda', 'derecha']
          .map((z, i) => ({ ...zona(e, z, 2.05), ret: 0.46 + i * 0.045 }));
      },
      draw(e, p) {
        const { ctx, F, R } = e, o = e.datos.o;
        punto(F, o.x, o.y, 3.6 * (1 - tramo(p, 0, 0.18)), e.color, 1 - tramo(p, 0, 0.24));
        // ondas que recorren todo el espacio
        [[0.02, 1], [0.16, 0.7]].forEach(([ini, fu]) => {
          const k = salida(tramo(p, ini, ini + 0.5));
          if (k <= 0 || k >= 1) return;
          const rr = R * (0.15 + k * 2.1);
          // la onda nace DETRAS: es luz que se propaga desde el usuario
          anillo(ctx, o.x, o.y, rr, e.color, (1 - k) * 0.95 * fu, 3.4 * (1 - k) + 0.5);
          brillo(ctx, o.x, o.y, rr * 0.9, e.color, (1 - k) * 0.18 * fu);
          // solo un trazo tenue delante, para que se siga leyendo el borde
          anillo(F, o.x, o.y, rr, e.color, (1 - k) * 0.28 * fu, 1);
        });
        const nodos = (lista, color) => lista.forEach((n) => {
          const kn = tramo(p, n.ret, n.ret + 0.4);
          if (kn <= 0) return;
          punto(F, n.x, n.y, 2.8 * (1 - kn * 0.4), color, 1 - kn * 0.85);
          const f = pico(kn);
          anillo(F, n.x, n.y, 2 + f * 17, color, f * 0.65, 1.4);
        });
        nodos(e.datos.n1, e.color);
        nodos(e.datos.n2, '120,235,255');
      },
    },

    // ── DESTELLO · legendario ────────────────────────────────
    // La luz se concentra arriba a la izquierda y estalla hacia
    // fuera. El avatar queda despejado.
    'efecto-destello': {
      dur: 1.8, color: '255,245,220',
      init(e) {
        e.datos.foco = zona(e, 'arribaIzq', 1.0);   // sobre el borde
        e.datos.p = Array.from({ length: Math.max(6, Math.round(12 * (e.densidad||1))) }, (_, i) => ({
          a: (i / 12) * TAU, d: 1.1 + Math.random() * 0.9,
        }));
      },
      draw(e, p) {
        const { ctx } = e;
        const { F, R } = e, fo = e.datos.foco;
        const carga = entrada(tramo(p, 0, 0.34));
        const fl = pico(tramo(p, 0.3, 0.56));
        // la luz se concentra DETRAS (por eso "destella" desde el fondo)
        brillo(ctx, fo.x, fo.y, R * (0.34 + carga * 0.7 + fl * 1.35), e.color, 0.25 + carga * 0.45 + fl * 0.65);
        if (fl > 0.05) {                                  // rayos hacia fuera
          F.strokeStyle = `rgba(${e.color},${fl * 0.75})`; F.lineWidth = 1.2;
          for (let i = 0; i < 9; i++) {
            const a = (i / 9) * TAU + 0.2;
            const r1 = R * (0.22 + fl * 0.2), r2 = R * (0.5 + fl * 0.9);
            F.beginPath();
            F.moveTo(fo.x + Math.cos(a) * r1, fo.y + Math.sin(a) * r1);
            F.lineTo(fo.x + Math.cos(a) * r2, fo.y + Math.sin(a) * r2);
            F.stroke();
          }
        }
        if (p > 0.45) {
          const k = salida(tramo(p, 0.45, 1));
          e.datos.p.forEach((q) => {
            const d = R * (0.25 + k * q.d);
            punto(F, fo.x + Math.cos(q.a) * d, fo.y + Math.sin(q.a) * d, 2 * (1 - k), e.color, 1 - k);
          });
        }
      },
    },
  };

  /* ═════════════════════════════════════════════════════════
     MOTOR
     ═════════════════════════════════════════════════════════ */
  const activos = new Set();
  let corriendo = false;

  function crear(host, clave, modo) {
    const esc = E[clave];
    if (!esc) return null;

    /* DOS lienzos: uno DETRAS del avatar y otro DELANTE.
       Asi un mismo efecto puede nacer detras y cruzar por delante,
       dando profundidad sin que la foto lo esconda. */
    /* El posicionamiento se fija tambien EN LINEA, no solo por CSS.
       Motivo: los avatares son contenedores flex. Si por lo que sea la
       hoja de estilos no ha llegado a aplicarse, los lienzos entran
       como elementos de la caja y EMPUJAN la foto hacia la izquierda.
       Con el estilo en linea eso no puede ocurrir nunca. */
    const colocar = (c, z) => {
      c.style.cssText =
        'position:absolute;top:-80%;left:-80%;right:-80%;bottom:-80%;' +
        'width:260%;height:260%;pointer-events:none;display:block;z-index:' + z + ';';
    };

    const canvas = document.createElement('canvas');           // capa trasera
    canvas.className = 'fx-efecto fx-atras' + (modo === 'preview' ? ' es-preview' : '');
    canvas.dataset.efecto = clave;
    colocar(canvas, 1);
    host.appendChild(canvas);

    const frente = document.createElement('canvas');           // capa delantera
    frente.className = 'fx-efecto fx-frente' + (modo === 'preview' ? ' es-preview' : '');
    frente.dataset.efecto = clave;
    colocar(frente, 50);
    host.appendChild(frente);

    /* Se marca el anfitrion con una clase en vez de depender de :has(),
       que no todos los navegadores soportan. Sin esto el avatar sigue
       recortando y solo se ve el trozo del efecto que cae sobre la foto:
       parece que ocurre "por detras" cuando en realidad esta cortado. */
    host.classList.add('fx-host');

    const e = {
      canvas, frente, ctx: canvas.getContext('2d'), F: frente.getContext('2d'),
      esc, clave, modo,
      color: esc.color, datos: {}, t0: performance.now(), visible: true,
      // la preview corre un 35% mas rapido y hace una pausa entre ciclos
      dur: (esc.dur || 2) * (modo === 'preview' ? 0.65 : 1),
      // La PREVIEW no tiene espera: encadena un ciclo con el siguiente
      // sin pausas ni tiempos muertos, para que se entienda de un
      // vistazo que hace el efecto.
      espera: 0,
    };
    if (modo === 'preview') {                    // en la tarjeta, a ras
      [canvas, frente].forEach((c) => {
        c.style.top = c.style.left = c.style.right = c.style.bottom = '0';
        c.style.width = c.style.height = '100%';
      });
    }
    canvas._fx = e;
    medir(e);
    if (esc.init) esc.init(e);
    return e;
  }

  function medir(e) {
    /* Se mide el CANVAS, no el avatar. El canvas es mas grande que el
       avatar (el CSS lo estira al 220%) porque el efecto ocurre fuera:
       si se le imponia el tamano del avatar, el dibujo salia recortado
       en un recuadro y descentrado. */
    const cr = e.canvas.getBoundingClientRect();
    const pr = e.canvas.parentElement.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    e.w = Math.max(1, Math.round(cr.width));
    e.h = Math.max(1, Math.round(cr.height));
    [[e.canvas, e.ctx], [e.frente, e.F]].forEach(([c, ctx]) => {
      c.width = e.w * dpr; c.height = e.h * dpr;   // resolucion interna
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    });
    e.cx = e.w / 2; e.cy = e.h / 2;
    // Radio de referencia = borde del avatar (o del recuadro, en preview)
    e.R = e.modo === 'preview'
      ? Math.min(e.w, e.h) * 0.22
      : Math.max(8, Math.min(pr.width, pr.height) / 2);

    /* ── ADAPTACION AL TAMAÑO DEL AVATAR ────────────────────
       El mismo efecto debe funcionar en el perfil (avatar de 100 px)
       y en un comentario (38 px). No se desactiva ni se recorta: se
       ADAPTA. La referencia es un radio de 50 px (perfil).

       e.k       factor de escala general (1 en el perfil, ~0,4 en
                 un comentario). Multiplica tamaños de particula,
                 grosores y radios de halo.
       e.densidad  cuantas particulas usar: menos en avatares
                 pequeños, para no convertirlo en una mancha.
       e.fuerza  intensidad: se SUBE en avatares pequeños, porque a
                 ese tamaño un halo tenue no se percibiria.
       e.aire    cuanto puede expandirse fuera del avatar: mas
                 margen relativo cuando es pequeño. */
    const ref = 50;
    e.k = Math.max(0.34, Math.min(1.25, e.R / ref));
    e.densidad = e.R < 22 ? 0.55 : (e.R < 34 ? 0.75 : 1);
    e.fuerza = e.R < 22 ? 1.5 : (e.R < 34 ? 1.25 : 1);
    e.aire = e.R < 34 ? 1.35 : 1;

    /* En la TARJETA no hay avatar de referencia: el efecto es el
       protagonista. Se le da mas radio, escala completa e intensidad
       alta para que se entienda de un vistazo. */
    if (e.modo === 'preview') {
      e.R = Math.min(e.w, e.h) * 0.3;
      e.k = 0.85;
      e.densidad = 1;
      e.fuerza = 1.6;
    }

    /* En avatares pequeños el evento dura un poco mas: si no, el
       usuario no llega a percibirlo antes de que desaparezca. */
    if (e.modo === 'evento' && e.R < 30) e.dur = (e.esc.dur || 2) * 1.25;
  }

  function bucle(ahora) {
    activos.forEach((e) => {
      if (!e.visible) return;
      let p = (ahora - e.t0) / 1000 / e.dur;

      if (p >= 1) {
        if (e.modo === 'preview') {
          /* Bucle continuo: se reinicia en el mismo fotograma y se
             sigue dibujando. Antes se limpiaba el lienzo y se esperaba
             medio segundo, asi que la tarjeta parpadeaba en negro
             entre pase y pase. */
          e.t0 = ahora;
          if (e.esc.init) { e.datos = {}; e.esc.init(e); }
          p = 0;
        } else {
          destruir(e);                               // el efecto real termina y se va
          return;
        }
      }
      e.ctx.clearRect(0, 0, e.w, e.h);
      e.F.clearRect(0, 0, e.w, e.h);
      _ctxK = e.k || 1; _ctxF = e.fuerza || 1;     // adaptacion al tamaño
      e.esc.draw(e, Math.max(0, p));
      _ctxK = 1; _ctxF = 1;
    });
    if (activos.size) requestAnimationFrame(bucle);
    else corriendo = false;
  }

  function arrancar(e) {
    activos.add(e);
    if (obs && e.modo === 'preview') obs.observe(e.canvas);
    if (!corriendo) { corriendo = true; requestAnimationFrame(bucle); }
  }

  function destruir(e) {
    activos.delete(e);
    if (obs) obs.unobserve(e.canvas);
    const host = e.canvas.parentElement;
    e.canvas.remove();
    if (e.frente) e.frente.remove();
    if (host && !host.querySelector(':scope > .fx-efecto')) host.classList.remove('fx-host');
  }

  const obs = ('IntersectionObserver' in window)
    ? new IntersectionObserver((ent) => ent.forEach((x) => {
        const e = x.target._fx; if (e) e.visible = x.isIntersecting;
      }), { threshold: 0.01 })
    : null;

  /* ═════════════════════════════════════════════════════════
     API PUBLICA
     ═════════════════════════════════════════════════════════ */
  let equipado = null;     // clase CSS del efecto que lleva el usuario
  let eventoDe = {};       // clave → evento que lo dispara

  const API = {
    escenas: E,

    /** Configura que efecto lleva el usuario y que evento lo dispara. */
    configurar({ efecto, eventos }) {
      if (efecto !== undefined) equipado = efecto || null;
      if (eventos) eventoDe = eventos;
    },

    /** Reproduce un efecto UNA vez sobre un elemento. */
    reproducir(host, clave, opciones) {
      if (!host || !E[clave]) return null;
      if (quieto) return null;                       // respeta la preferencia del sistema
      /* Si aun quedaba un pase anterior, se retira: cada repeticion
         debe empezar la animacion DESDE EL PRINCIPIO, nunca mezclarse
         con el resto de la anterior. */
      host.querySelectorAll(':scope > .fx-efecto').forEach((c) => {
        if (c._fx) destruir(c._fx); else c.remove();
      });
      const e = crear(host, clave, 'evento');
      if (!e) return null;
      if (opciones && opciones.enTarjeta) e.R = Math.min(e.w, e.h) * 0.24;
      arrancar(e);
      return e;
    },

    /** Dispara el efecto equipado si su evento coincide. */
    evento(nombre, host) {
      if (!equipado) return false;
      const suEvento = eventoDe[equipado];
      if (suEvento && suEvento !== nombre) return false;
      const destino = host || document.querySelector('#profileAvatarDisplay, .profile-avatar');
      if (!destino) return false;
      API.reproducir(destino, equipado);
      return true;
    },

    /** Detiene y borra todo efecto en marcha (al cambiar de cosmetico). */
    detenerTodo(soloEventos) {
      [...activos].forEach((e) => {
        if (soloEventos && e.modo === 'preview') return;
        destruir(e);
      });
      document.querySelectorAll('.fx-efecto').forEach((c) => {
        if (soloEventos && c.classList.contains('es-preview')) return;
        const h = c.parentElement; c.remove();
        if (h && !h.querySelector(':scope > .fx-efecto')) h.classList.remove('fx-host');
      });
    },

    /** Monta las previews de las tarjetas de la coleccion. */
    montarPreviews(raiz) {
      (raiz || document).querySelectorAll('.cos-preview').forEach((prev) => {
        const clave = [...prev.classList].find((c) => E[c]);
        const ya = prev.querySelector(':scope > .fx-efecto');
        if (!clave) { if (ya) destruir(ya._fx || { canvas: ya, modo: 'x' }); return; }
        if (ya && ya.dataset.efecto === clave) { if (ya._fx) medir(ya._fx); return; }
        if (ya && ya._fx) destruir(ya._fx);
        if (quieto) return;
        const e = crear(prev, clave, 'preview');
        if (e) arrancar(e);
      });
    },
  };

  window.CosEfectos = API;

  function iniciar() {
    API.montarPreviews();
    let t;
    new MutationObserver(() => { clearTimeout(t); t = setTimeout(() => API.montarPreviews(), 160); })
      .observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });
    let t2;
    window.addEventListener('resize', () => {
      clearTimeout(t2); t2 = setTimeout(() => activos.forEach(medir), 200);
    });
    document.addEventListener('visibilitychange', () => {
      activos.forEach((e) => { if (e.modo === 'preview') e.visible = !document.hidden; });
      if (!document.hidden && activos.size && !corriendo) {
        corriendo = true; requestAnimationFrame(bucle);
      }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
