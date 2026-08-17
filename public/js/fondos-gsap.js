/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Fondos de perfil · motor GSAP
   Un modulo por cosmetico. Cada uno:
     · construye sus propios elementos (DOM ligero + SVG)
     · devuelve una timeline de GSAP que lo anima
     · se destruye por completo al cambiar de cosmetico

   REGLA: TODO el movimiento lo hace GSAP. El CSS solo aporta
   estilo (gradientes, mascaras, bordes, tamaños). No se usan
   @keyframes, animaciones CSS, setInterval ni setTimeout.

   CAPAS: cada modulo declara si va 'atras' o 'delante' de la
   fotografia de perfil.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayGSAP = () => typeof window.gsap !== 'undefined';

  /* Ayudas breves -------------------------------------------- */
  const az = (a, b) => a + Math.random() * (b - a);          // valor al azar
  const el = (tag, clase, css) => {                           // crear elemento
    const n = document.createElementNS(
      tag === 'svg' || tag === 'g' || tag === 'circle' || tag === 'line' || tag === 'path'
        ? 'http://www.w3.org/2000/svg' : 'http://www.w3.org/1999/xhtml', tag);
    if (clase) n.setAttribute('class', clase);
    if (css) n.style.cssText = css;
    return n;
  };
  const capa = (host, z) => {
    const d = el('div', 'fx-capa',
      `position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:${z};`);
    host.appendChild(d);
    return d;
  };

  /* ═════════════════════════════════════════════════════════
     MODULOS
     cada uno: { capa, construir(host, ancho, alto) -> timeline }
     ═════════════════════════════════════════════════════════ */
  const M = {

    // ── 1 · ASCENSO ────────────────────────────────────────
    // Corriente constante de particulas doradas que suben con
    // deriva horizontal. Nunca sincronizadas.
    'fondo-ascenso': {
      capa: 'atras',
      fondo: 'linear-gradient(180deg,#1a0d05 0%,#2e1608 60%,#1a0d05 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const n = Math.min(34, Math.max(18, Math.round(w / 24)));

        for (let i = 0; i < n; i++) {
          const r = az(1.2, 3.4);
          const p = el('div', 'as-p', `position:absolute;border-radius:50%;
            width:${r * 2}px;height:${r * 2}px;
            background:radial-gradient(circle,#ffd9a0,rgba(255,150,60,0));
            will-change:transform,opacity;`);
          zona.appendChild(p);

          const x0 = az(0, w), dur = az(4.5, 9), deriva = az(-26, 26);
          gsap.set(p, { x: x0, y: h + 10, opacity: 0 });

          // cada particula tiene su propia timeline: duracion, retardo y
          // deriva distintos, para que el conjunto no lata a la vez
          const sub = gsap.timeline({ repeat: -1, delay: az(0, dur * 0.9) })
            .to(p, { opacity: az(0.6, 1), duration: dur * 0.12, ease: 'none' })
            .to(p, {
              y: -20, x: `+=${deriva}`, duration: dur, ease: 'none',
            }, 0)
            .to(p, { opacity: 0, duration: dur * 0.3, ease: 'power1.in' }, dur * 0.7)
            .set(p, { y: h + 10, x: az(0, w) });
          tl.add(sub, 0);
        }
        return tl;
      },
    },

    // ── 2 · FLUJO ──────────────────────────────────────────
    // Lineas diagonales finas que cruzan el banner a distinta
    // velocidad y reaparecen por el lado contrario.
    'fondo-flujo': {
      capa: 'atras',
      fondo: 'linear-gradient(135deg,#0a1f24 0%,#103038 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const n = Math.max(6, Math.min(14, Math.round(h / 16)));

        for (let i = 0; i < n; i++) {
          const largo = az(w * 0.18, w * 0.5);
          const l = el('div', 'fl-l', `position:absolute;height:1px;width:${largo}px;
            background:linear-gradient(90deg,rgba(90,200,220,0),rgba(140,230,245,${az(0.3, 0.75)}),rgba(90,200,220,0));
            transform:rotate(-14deg);will-change:transform;`);
          zona.appendChild(l);

          const y = (i + 0.5) * (h / n) + az(-6, 6);
          gsap.set(l, { x: -largo, y });
          tl.add(gsap.timeline({ repeat: -1, delay: az(0, 6) })
            .fromTo(l, { x: -largo }, { x: w + largo, duration: az(6, 15), ease: 'none' }), 0);
        }
        return tl;
      },
    },

    // ── 3 · EVOLUCION ──────────────────────────────────────
    // Un grid que se reduce y se transforma; al desvanecerse ya
    // hay otra copia detras, asi el ciclo no tiene corte visible.
    'fondo-evolucion': {
      capa: 'atras',
      fondo: 'linear-gradient(140deg,#071a1f 0%,#0d2733 55%,#091d28 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const capas = 3;

        for (let i = 0; i < capas; i++) {
          const g = el('div', 'ev-g', `position:absolute;inset:-25%;
            background-image:
              linear-gradient(rgba(90,220,255,${0.18 - i * 0.04}) 1px,transparent 1px),
              linear-gradient(90deg,rgba(90,220,255,${0.18 - i * 0.04}) 1px,transparent 1px);
            background-size:${52 - i * 8}px ${52 - i * 8}px;
            will-change:transform,opacity;`);
          zona.appendChild(g);

          const ciclo = 11;
          gsap.set(g, { scale: 1, opacity: 0, rotate: i * 4 });
          // desfase entre capas: siempre hay una entrando y otra saliendo
          tl.add(gsap.timeline({ repeat: -1, delay: (ciclo / capas) * i })
            .to(g, { opacity: 1, duration: ciclo * 0.15, ease: 'power1.out' })
            .to(g, { scale: 0.42, rotate: `+=${8 + i * 3}`, duration: ciclo * 0.7,
                     ease: 'power2.in' }, 0)
            .to(g, { opacity: 0, duration: ciclo * 0.25, ease: 'power2.in' }, ciclo * 0.6)
            .set(g, { scale: 1, rotate: i * 4 }), 0);
        }
        return tl;
      },
    },

    // ── 4 · LEGADO ─────────────────────────────────────────
    // Una fuente de luz recorre el perimetro del banner. Se usa un
    // trazo SVG y se anima su desplazamiento: la luz sigue la forma
    // exacta del marco, no es un borde parpadeando.
    'fondo-legado': {
      capa: 'delante',
      fondo: 'linear-gradient(140deg,#16110a 0%,#241a08 55%,#120d05 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const r = 14;
        const svg = el('svg', 'lg-svg',
          'position:absolute;inset:0;width:100%;height:100%;overflow:visible;');
        svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
        svg.setAttribute('preserveAspectRatio', 'none');

        const base = el('path', 'lg-base');
        const d = `M${r} 1 H${w - r} Q${w - 1} 1 ${w - 1} ${r} V${h - r}
                   Q${w - 1} ${h - 1} ${w - r} ${h - 1} H${r}
                   Q1 ${h - 1} 1 ${h - r} V${r} Q1 1 ${r} 1 Z`;
        base.setAttribute('d', d);
        base.setAttribute('fill', 'none');
        base.setAttribute('stroke', 'rgba(255,205,120,.16)');
        base.setAttribute('stroke-width', '1');

        const luz = el('path', 'lg-luz');
        luz.setAttribute('d', d);
        luz.setAttribute('fill', 'none');
        luz.setAttribute('stroke', '#ffe6a8');
        luz.setAttribute('stroke-width', '2');
        luz.setAttribute('stroke-linecap', 'round');
        luz.style.filter = 'drop-shadow(0 0 6px rgba(255,215,140,.9))';

        svg.appendChild(base); svg.appendChild(luz);
        zona.appendChild(svg);

        const largo = luz.getTotalLength ? luz.getTotalLength() : (w + h) * 2;
        const trazo = Math.max(60, largo * 0.13);
        gsap.set(luz, { attr: { 'stroke-dasharray': `${trazo} ${largo}` } });

        // la luz recorre el perimetro y su brillo respira al avanzar
        return gsap.timeline({ repeat: -1 })
          .fromTo(luz, { attr: { 'stroke-dashoffset': largo } },
                       { attr: { 'stroke-dashoffset': -trazo }, duration: 9, ease: 'none' })
          .to(luz, { opacity: 0.55, duration: 1.5, ease: 'sine.inOut',
                     repeat: 5, yoyo: true }, 0);
      },
    },

    // ── 5 · AURORA ─────────────────────────────────────────
    // Bandas curvas difuminadas que ondulan con trayectorias
    // distintas. Movimiento muy lento.
    'fondo-aurora': {
      capa: 'atras',
      fondo: 'linear-gradient(160deg,#08211c 0%,#0d2b26 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const tonos = [
          ['rgba(54,192,161,.42)', 'rgba(54,192,161,0)'],
          ['rgba(74,158,255,.34)', 'rgba(74,158,255,0)'],
          ['rgba(120,230,200,.28)', 'rgba(120,230,200,0)'],
          ['rgba(150,120,255,.22)', 'rgba(150,120,255,0)'],
        ];
        tonos.forEach((t, i) => {
          const b = el('div', 'au-b', `position:absolute;
            left:${-20 + i * 8}%;top:${8 + i * 14}%;
            width:${az(75, 115)}%;height:${az(26, 46)}%;
            background:linear-gradient(100deg,${t[1]},${t[0]},${t[1]});
            border-radius:50%;filter:blur(${az(16, 28)}px);
            will-change:transform,opacity;`);
          zona.appendChild(b);

          gsap.set(b, { rotate: az(-14, 14), scaleX: az(0.9, 1.2) });
          // cada banda combina desplazamiento, escala y giro a su ritmo
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 4) })
            .to(b, { x: az(-40, 40), y: az(-16, 16), duration: az(12, 22), ease: 'sine.inOut' })
            .to(b, { scaleX: az(0.85, 1.35), scaleY: az(0.9, 1.2),
                     duration: az(14, 24), ease: 'sine.inOut' }, 0)
            .to(b, { rotate: `+=${az(-10, 10)}`, duration: az(18, 30), ease: 'sine.inOut' }, 0)
            .to(b, { opacity: az(0.55, 1), duration: az(8, 16), ease: 'sine.inOut' }, 0), 0);
        });
        return tl;
      },
    },

    // ── 6 · COSMOS ─────────────────────────────────────────
    // Espacio tranquilo: estrellas casi quietas, deriva minima y
    // destellos ocasionales.
    'fondo-cosmos': {
      capa: 'atras',
      fondo: 'linear-gradient(150deg,#070a1c 0%,#14092b 55%,#060d1e 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();

        // dos nebulosas muy tenues, para dar fondo
        [[0.24, 0.3, '150,90,255'], [0.78, 0.66, '60,150,255']].forEach(([nx, ny, c], i) => {
          const n = el('div', 'co-neb', `position:absolute;
            left:${nx * 100}%;top:${ny * 100}%;width:60%;height:80%;
            transform:translate(-50%,-50%);border-radius:50%;
            background:radial-gradient(circle,rgba(${c},.26),rgba(${c},0) 70%);
            filter:blur(10px);will-change:transform,opacity;`);
          zona.appendChild(n);
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: i * 3 })
            .to(n, { scale: az(1.08, 1.25), opacity: az(0.6, 1),
                     duration: az(14, 20), ease: 'sine.inOut' }), 0);
        });

        const n = Math.min(46, Math.max(20, Math.round(w * h / 5200)));
        const estrellas = [];
        for (let i = 0; i < n; i++) {
          const gr = i % 9 === 0 ? az(1.8, 2.8) : az(0.6, 1.4);
          const s = el('div', 'co-e', `position:absolute;border-radius:50%;
            width:${gr * 2}px;height:${gr * 2}px;background:#fff;
            left:${az(0, 100)}%;top:${az(0, 100)}%;will-change:transform,opacity;`);
          zona.appendChild(s); estrellas.push(s);
          gsap.set(s, { opacity: az(0.25, 0.9) });
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 6) })
            .to(s, { opacity: az(0.15, 1), scale: az(0.8, 1.4),
                     duration: az(3, 9), ease: 'sine.inOut' })
            .to(s, { x: az(-8, 8), y: az(-6, 6), duration: az(18, 30), ease: 'sine.inOut' }, 0), 0);
        }

        // destellos OCASIONALES: una estrella al azar brilla de golpe
        const destello = gsap.timeline({ repeat: -1, repeatRefresh: true })
          .to({}, { duration: 'random(3, 9)' })
          .add(() => {
            const s = estrellas[Math.floor(Math.random() * estrellas.length)];
            gsap.fromTo(s,
              { scale: 1, opacity: 0.7 },
              { scale: 3.4, opacity: 1, duration: 0.35, ease: 'power2.out',
                yoyo: true, repeat: 1 });
          });
        tl.add(destello, 0);
        return tl;
      },
    },

    // ── 7 · NEBULOSA ───────────────────────────────────────
    // Capas de gas superpuestas que se expanden, comprimen y
    // derivan muy despacio.
    'fondo-nebulosa': {
      capa: 'atras',
      fondo: 'linear-gradient(140deg,#0a0f24 0%,#17103a 60%,#0b1430 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const nubes = [
          ['120,80,255', 0.3, 0.34, 62], ['50,190,200', 0.72, 0.62, 54],
          ['255,120,200', 0.52, 0.24, 44], ['90,120,255', 0.24, 0.74, 58],
          ['160,90,230', 0.82, 0.28, 40],
        ];
        nubes.forEach(([c, nx, ny, tam], i) => {
          const n = el('div', 'ne-c', `position:absolute;
            left:${nx * 100}%;top:${ny * 100}%;
            width:${tam}%;height:${tam * 1.25}%;
            transform:translate(-50%,-50%);border-radius:50%;
            background:radial-gradient(circle,rgba(${c},.3),rgba(${c},.08) 55%,rgba(${c},0) 75%);
            filter:blur(${az(10, 22)}px);mix-blend-mode:screen;
            will-change:transform,opacity;`);
          zona.appendChild(n);
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 5) })
            .to(n, { scale: az(1.12, 1.4), duration: az(16, 26), ease: 'sine.inOut' })
            .to(n, { x: az(-30, 30), y: az(-20, 20), duration: az(20, 34), ease: 'sine.inOut' }, 0)
            .to(n, { opacity: az(0.45, 1), duration: az(12, 20), ease: 'sine.inOut' }, 0), 0);
        });
        return tl;
      },
    },

    // ── 8 · CONEXIONES ─────────────────────────────────────
    // Nodos irregulares. Lo importante NO son las lineas sino la
    // COMUNICACION: un pulso viaja de un nodo a otro por turnos.
    'fondo-conexiones': {
      capa: 'atras',
      fondo: 'linear-gradient(135deg,#08201d 0%,#0d2a2f 100%)',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const svg = el('svg', 'cx-svg',
          'position:absolute;inset:0;width:100%;height:100%;');
        svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
        svg.setAttribute('preserveAspectRatio', 'none');
        zona.appendChild(svg);

        const N = Math.min(13, Math.max(7, Math.round(w / 74)));
        const nodos = Array.from({ length: N }, () => ({ x: az(w * 0.06, w * 0.94), y: az(h * 0.1, h * 0.9) }));

        // cada nodo se une a sus dos vecinos mas cercanos: red irregular
        const enl = [];
        nodos.forEach((a, i) => {
          nodos.map((b, j) => ({ j, d: Math.hypot(a.x - b.x, a.y - b.y) }))
            .filter((o) => o.j !== i).sort((p, q) => p.d - q.d).slice(0, 2)
            .forEach((o) => {
              if (!enl.some((k) => (k.a === o.j && k.b === i) || (k.a === i && k.b === o.j))) {
                enl.push({ a: i, b: o.j });
              }
            });
        });

        const lineas = enl.map((k) => {
          const l = el('line', 'cx-l');
          l.setAttribute('x1', nodos[k.a].x); l.setAttribute('y1', nodos[k.a].y);
          l.setAttribute('x2', nodos[k.b].x); l.setAttribute('y2', nodos[k.b].y);
          l.setAttribute('stroke', 'rgba(90,230,200,.16)');
          l.setAttribute('stroke-width', '1');
          svg.appendChild(l);
          return l;
        });

        const puntos = nodos.map((p) => {
          const c = el('circle', 'cx-n');
          c.setAttribute('cx', p.x); c.setAttribute('cy', p.y);
          c.setAttribute('r', az(1.8, 3.4));
          c.setAttribute('fill', 'rgba(90,230,200,.75)');
          svg.appendChild(c);
          return c;
        });

        const tl = gsap.timeline();

        // los nodos derivan un poco; las lineas siguen su posicion
        nodos.forEach((p, i) => {
          const objetivo = { x: p.x, y: p.y };
          tl.add(gsap.timeline({ repeat: -1, yoyo: true, delay: az(0, 4) })
            .to(objetivo, {
              x: p.x + az(-12, 12), y: p.y + az(-10, 10),
              duration: az(9, 16), ease: 'sine.inOut',
              onUpdate() {
                puntos[i].setAttribute('cx', objetivo.x);
                puntos[i].setAttribute('cy', objetivo.y);
                enl.forEach((k, j) => {
                  if (k.a === i) { lineas[j].setAttribute('x1', objetivo.x); lineas[j].setAttribute('y1', objetivo.y); }
                  if (k.b === i) { lineas[j].setAttribute('x2', objetivo.x); lineas[j].setAttribute('y2', objetivo.y); }
                });
              },
            }), 0);
        });

        // EL PROTAGONISTA: un pulso viaja por una conexion, enciende el
        // nodo de destino y desde alli salta a otra. Nunca todas a la vez.
        const pulso = el('circle', 'cx-p');
        pulso.setAttribute('r', 3.6);
        pulso.setAttribute('fill', '#c8fff0');
        pulso.style.filter = 'drop-shadow(0 0 6px rgba(140,255,225,.95))';
        svg.appendChild(pulso);

        let actual = 0;
        const viaje = gsap.timeline({ repeat: -1, repeatRefresh: true });
        viaje.add(() => {
          const salidas = enl.map((k, j) => ({ k, j }))
            .filter((o) => o.k.a === actual || o.k.b === actual);
          const elegido = salidas.length
            ? salidas[Math.floor(Math.random() * salidas.length)]
            : { k: enl[0], j: 0 };
          const desde = actual === elegido.k.a ? elegido.k.a : elegido.k.b;
          const hasta = desde === elegido.k.a ? elegido.k.b : elegido.k.a;
          const A = { x: +puntos[desde].getAttribute('cx'), y: +puntos[desde].getAttribute('cy') };
          const B = { x: +puntos[hasta].getAttribute('cx'), y: +puntos[hasta].getAttribute('cy') };

          gsap.set(pulso, { attr: { cx: A.x, cy: A.y }, opacity: 0 });
          gsap.timeline()
            .to(pulso, { opacity: 1, duration: 0.2 })
            .to(pulso, { attr: { cx: B.x, cy: B.y }, duration: 1.1, ease: 'power1.inOut' }, 0)
            // la conexion se ilumina al paso del pulso
            .fromTo(lineas[elegido.j], { attr: { stroke: 'rgba(90,230,200,.16)' } },
                    { attr: { stroke: 'rgba(150,255,230,.8)' }, duration: 0.5,
                      yoyo: true, repeat: 1 }, 0)
            .to(pulso, { opacity: 0, duration: 0.25 }, 1.1)
            // y el nodo de destino responde
            .fromTo(puntos[hasta], { attr: { r: +puntos[hasta].getAttribute('r') } },
                    { attr: { r: +puntos[hasta].getAttribute('r') * 2.1 }, duration: 0.3,
                      yoyo: true, repeat: 1, ease: 'power2.out' }, 1.0);
          actual = hasta;
        }).to({}, { duration: 'random(1.6, 3.4)' });

        tl.add(viaje, 0);
        return tl;
      },
    },

    // ── 9 · DUAL (marco) ───────────────────────────────────
    // Dos grupos opuestos que se aproximan, se intensifican al
    // encontrarse y vuelven a separarse. Sin explosion.
    'marco-dual': {
      capa: 'atras',
      construir(host, w, h) {
        const zona = capa(host, 0);
        const tl = gsap.timeline();
        const cx = w / 2, cy = h / 2;
        const R = Math.min(w, h) * 0.5;

        [[-1, '74,158,255'], [1, '168,85,247']].forEach(([lado, color]) => {
          const grupo = el('div', 'du-g', 'position:absolute;inset:0;will-change:transform,opacity;');
          zona.appendChild(grupo);

          for (let i = 0; i < 5; i++) {
            const r = az(1.6, 3.2);
            const p = el('div', 'du-p', `position:absolute;border-radius:50%;
              width:${r * 2}px;height:${r * 2}px;
              background:radial-gradient(circle,rgb(${color}),rgba(${color},0));
              left:${cx + lado * R * az(0.85, 1.25)}px;
              top:${cy + az(-R * 0.7, R * 0.7)}px;`);
            grupo.appendChild(p);
          }

          const ciclo = 7;
          tl.add(gsap.timeline({ repeat: -1, yoyo: true })
            // se aproximan al centro y ganan intensidad
            .to(grupo, { x: -lado * R * 0.55, duration: ciclo * 0.5, ease: 'sine.inOut' })
            .to(grupo, { opacity: 1, scale: 1.12, duration: ciclo * 0.5, ease: 'sine.inOut' }, 0)
            .fromTo(grupo, { opacity: 0.45, scale: 0.95 },
                    { opacity: 0.45, scale: 0.95, duration: 0.01 }, 0), 0);
        });

        // destello suave en el punto de encuentro
        const chispa = el('div', 'du-f', `position:absolute;left:50%;top:50%;
          width:${R * 0.7}px;height:${R * 0.7}px;transform:translate(-50%,-50%);
          border-radius:50%;background:radial-gradient(circle,rgba(220,200,255,.5),rgba(220,200,255,0) 70%);
          opacity:0;will-change:opacity,transform;`);
        zona.appendChild(chispa);
        tl.add(gsap.timeline({ repeat: -1, repeatDelay: 3.5 })
          .to(chispa, { opacity: 1, scale: 1.3, duration: 1.2, ease: 'sine.inOut' }, 3.5)
          .to(chispa, { opacity: 0, scale: 0.8, duration: 1.2, ease: 'sine.in' }), 0);

        return tl;
      },
    },
  };

  /* ═════════════════════════════════════════════════════════
     MOTOR
     ═════════════════════════════════════════════════════════ */
  const montados = new Map();     // host -> { clave, tl, zonas[] }

  function destruir(host) {
    const m = montados.get(host);
    if (!m) return;
    // limpieza completa: timeline, tweens hijos y elementos creados
    if (m.tl) { m.tl.kill(); }
    gsap.killTweensOf(host.querySelectorAll('.fx-capa *'));
    host.querySelectorAll(':scope > .fx-capa').forEach((z) => z.remove());
    host.style.removeProperty('background');
    montados.delete(host);
  }

  function montar(host) {
    const clave = [...host.classList].find((c) => M[c]);
    const previo = montados.get(host);

    if (!clave) { if (previo) destruir(host); return; }
    if (previo && previo.clave === clave) return;
    if (previo) destruir(host);
    if (!hayGSAP()) return;

    const mod = M[clave];
    const r = host.getBoundingClientRect();
    const w = Math.max(40, Math.round(r.width));
    const h = Math.max(30, Math.round(r.height));

    if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
    if (mod.fondo) host.style.background = mod.fondo;

    if (quieto) {                       // sin movimiento: solo el fondo base
      montados.set(host, { clave, tl: null });
      return;
    }

    const tl = mod.construir(host, w, h);
    montados.set(host, { clave, tl });
  }

  const SEL = '.profile-hero, .public-hero, .cos-preview.es-fondo, .profile-avatar, .comment-avatar';

  function escanear(raiz) {
    (raiz || document).querySelectorAll(SEL).forEach(montar);
  }

  window.FondosGSAP = {
    modulos: M, montar, escanear, destruir,
    /** Detiene y limpia TODO: usado al cambiar de cosmetico. */
    limpiarTodo() { [...montados.keys()].forEach(destruir); },
  };

  function iniciar() {
    if (!hayGSAP()) {
      console.warn('[CIVINSIS] GSAP no esta cargado: los fondos no se animaran.');
      return;
    }
    escanear();
    let t;
    new MutationObserver((muts) => {
      const relevante = muts.some((m) =>
        [...m.addedNodes, ...m.removedNodes].some((n) =>
          n.nodeType === 1 && !n.classList.contains('fx-capa')));
      if (!relevante) return;
      clearTimeout(t); t = setTimeout(() => escanear(), 220);
    }).observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });

    let t2;
    window.addEventListener('resize', () => {
      clearTimeout(t2);
      t2 = setTimeout(() => { const h = [...montados.keys()]; h.forEach(destruir); h.forEach(montar); }, 300);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
