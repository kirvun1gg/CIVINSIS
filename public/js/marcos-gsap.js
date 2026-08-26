/* ═══════════════════════════════════════════════════════════
   CIVINSIS · Marcos cosmeticos · motor GSAP + SVG
   16 marcos rediseñados como ESTRUCTURAS permanentes alrededor
   del avatar (sin partículas flotantes ni efectos temporales).

   SISTEMA DE COORDENADAS
     viewBox "0 0 200 200". El avatar es el circulo centrado en
     (100,100) con radio 50. Todo lo demas se dibuja alrededor.
     Al ser SVG, el marco ESCALA SOLO.
   ═══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  const NS = 'http://www.w3.org/2000/svg';
  const menos = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayGSAP = () => typeof window.gsap !== 'undefined';

  const C = 100, R = 50, TAU = Math.PI * 2;

  function nodo(tag, attrs, padre) {
    const e = document.createElementNS(NS, tag);
    for (const k in attrs) e.setAttribute(k, attrs[k]);
    if (padre) padre.appendChild(e);
    return e;
  }
  const pol = (a, d) => ({ x: C + Math.cos(a) * R * d, y: C + Math.sin(a) * R * d });
  const az = (a, b) => a + Math.random() * (b - a);

  function preparaDibujo(el) {
    const L = el.getTotalLength ? el.getTotalLength() : 400;
    gsap.set(el, { attr: { 'stroke-dasharray': L, 'stroke-dashoffset': L } });
    return L;
  }

  const PAL = {
    comun: '#8fb8c4', poco_comun: '#7fd4a8', raro: '#7fb8ff',
    epico: '#c9a6ff', legendario: '#ffcf7a', mitico: '#ff9ad5',
  };

  const MK = {
    /* 1 · NOCHE ESTELAR · poco comun */
    'marco-noche-estelar': {
      rareza: 'poco_comun',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const pts = [];
        for (let i = 0; i < 8; i++) pts.push(pol((i/8)*TAU + az(-0.2,0.2), az(1.15, 1.25)));
        const d = pts.map((p,i)=>(i===0?'M':'L')+p.x+' '+p.y).join(' ') + ' Z';
        nodo('path', { d, fill:'none', stroke: PAL.poco_comun, 'stroke-width': 1.2, opacity: 0.3 }, g);
        
        pts.forEach(p => {
          const c = nodo('circle', { cx: p.x, cy: p.y, r: az(1.5, 3), fill: '#fff', opacity: 0.5 }, g);
          tl.to(c, { opacity: 1, scale: 1.4, duration: az(1, 2.5), yoyo: true, repeat: 1, ease: 'sine.inOut' }, az(0, 2));
        });
        return tl;
      }
    },

    /* 2 · AURORA · raro */
    'marco-aurora': {
      rareza: 'raro',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const cols = ['#36c0a1', '#4a9eff', '#a855f7'];
        for (let i = 0; i < 3; i++) {
          const baseR = 1.1 + i*0.06;
          const p = nodo('path', { fill:'none', stroke: cols[i], 'stroke-width': 3 - i*0.5, opacity: 0.8 }, g);
          const obj = { fase: i*2 };
          const draw = () => {
            let d = '';
            for(let j=0; j<=20; j++) {
              const a = (j/20)*TAU;
              const r = baseR + Math.sin(a*3 + obj.fase)*0.05;
              const pos = pol(a, r);
              d += (j===0?'M':'L')+pos.x+' '+pos.y;
            }
            p.setAttribute('d', d + ' Z');
          };
          draw();
          tl.to(obj, { fase: obj.fase + TAU, duration: az(8, 12), ease: 'none', onUpdate: draw }, 0);
        }
        return tl;
      }
    },

    /* 3 · HORIZONTE · comun */
    'marco-horizonte': {
      rareza: 'comun',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const p1 = nodo('path', { d: `M ${C-R*1.15} ${C-R*0.5} A ${R*1.15} ${R*1.15} 0 0 1 ${C+R*1.15} ${C-R*0.5}`, fill:'none', stroke: PAL.comun, 'stroke-width': 2, opacity: 0.4 }, g);
        const p2 = nodo('path', { d: `M ${C-R*1.15} ${C+R*0.5} A ${R*1.15} ${R*1.15} 0 0 0 ${C+R*1.15} ${C+R*0.5}`, fill:'none', stroke: PAL.comun, 'stroke-width': 2, opacity: 0.4 }, g);
        
        const glow1 = nodo('path', { d: p1.getAttribute('d'), fill:'none', stroke: '#fff', 'stroke-width': 2 }, g);
        const glow2 = nodo('path', { d: p2.getAttribute('d'), fill:'none', stroke: '#fff', 'stroke-width': 2 }, g);
        const L1 = preparaDibujo(glow1); const L2 = preparaDibujo(glow2);
        
        tl.to(glow1, { attr:{ 'stroke-dashoffset': -L1 }, duration: 4, ease: 'linear' }, 0)
          .to(glow2, { attr:{ 'stroke-dashoffset': L2 }, duration: 4, ease: 'linear' }, 0);
        return tl;
      }
    },

    /* 4 · ASCENSO · poco comun */
    'marco-ascenso': {
      rareza: 'poco_comun',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        for (let i = 0; i < 6; i++) {
          const a = (-Math.PI/6) + (i*(Math.PI*1.33/5)); 
          const p = pol(a, 1.15);
          const chv = nodo('path', { d: `M ${p.x-5} ${p.y+4} L ${p.x} ${p.y-4} L ${p.x+5} ${p.y+4}`, fill:'none', stroke: PAL.poco_comun, 'stroke-width': 2, 'stroke-linecap': 'round', opacity: 0.2 }, g);
          gsap.set(chv, { rotation: a*(180/Math.PI) + 90, transformOrigin: 'center' });
          tl.to(chv, { opacity: 1, duration: 0.5, yoyo: true, repeat: 1 }, i*0.4);
        }
        return tl;
      }
    },

    /* 5 · FLUJO · poco comun */
    'marco-flujo': {
      rareza: 'poco_comun',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        for (let i = 0; i < 3; i++) {
          const p = nodo('circle', { cx: C, cy: C, r: R*(1.1 + i*0.08), fill:'none', stroke: PAL.poco_comun, 'stroke-width': 1.5, 'stroke-dasharray': `${az(30, 80)} ${az(20, 50)}` }, g);
          gsap.set(p, { transformOrigin: 'center', rotation: az(0, 360) });
          tl.to(p, { rotation: `+=${i%2===0?360:-360}`, duration: az(15, 25), ease: 'none' }, 0);
        }
        return tl;
      }
    },

    /* 6 · EVOLUCION · epico */
    'marco-evolucion': {
      rareza: 'epico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        for (let i = 0; i < 12; i++) {
          const a = (i/12)*TAU;
          const p = pol(a, 1.2);
          const size = 3 + (i%4)*2; // Evoluciona en tamaño
          const rect = nodo('rect', { x: p.x-size/2, y: p.y-size/2, width: size, height: size, fill: 'none', stroke: PAL.epico, 'stroke-width': 1.5 }, g);
          gsap.set(rect, { transformOrigin: 'center', rotation: a*(180/Math.PI) });
          tl.to(rect, { scale: 1.3, rotation: '+=90', duration: 2, yoyo: true, repeat: 1, ease: 'power1.inOut' }, i*0.2);
        }
        return tl;
      }
    },

    /* 7 · CONEXIONES · raro */
    'marco-conexiones': {
      rareza: 'raro',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        nodo('circle', { cx: C, cy: C, r: R*1.15, fill:'none', stroke: PAL.raro, 'stroke-width': 1, opacity: 0.3 }, g);
        for(let i=0; i<6; i++) {
          const a = (i/6)*TAU;
          const p = pol(a, 1.15);
          nodo('circle', { cx: p.x, cy: p.y, r: 4, fill: PAL.raro }, g);
          const dot = nodo('circle', { cx: p.x, cy: p.y, r: 2, fill: '#fff' }, g);
          tl.to(dot, { scale: 2, opacity: 0.2, duration: 1, yoyo: true, repeat: 1 }, i*0.5);
        }
        return tl;
      }
    },

    /* 8 · LEGADO · epico */
    'marco-legado': {
      rareza: 'epico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const p = nodo('path', { d: `M ${C} ${C-R*1.1} Q ${C+R*1.3} ${C-R*1.3} ${C+R*1.1} ${C} T ${C} ${C+R*1.1} T ${C-R*1.1} ${C} T ${C} ${C-R*1.1}`, fill:'none', stroke: PAL.epico, 'stroke-width': 3, 'stroke-linecap': 'round' }, g);
        const glow = nodo('path', { d: p.getAttribute('d'), fill:'none', stroke: '#fff', 'stroke-width': 3, 'stroke-linecap': 'round' }, g);
        const L = preparaDibujo(glow);
        gsap.set(glow, { 'stroke-dasharray': `20 ${L}` });
        tl.to(glow, { attr:{ 'stroke-dashoffset': -L }, duration: 4, ease: 'linear' });
        return tl;
      }
    },

    /* 9 · PULSO CIVICO · raro */
    'marco-pulso-civico': {
      rareza: 'raro',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        nodo('circle', { cx: C, cy: C, r: R*1.08, fill:'none', stroke: PAL.raro, 'stroke-width': 2 }, g);
        const ext = nodo('circle', { cx: C, cy: C, r: R*1.18, fill:'none', stroke: PAL.raro, 'stroke-width': 1.5, 'stroke-dasharray': '10 15' }, g);
        gsap.set(ext, { transformOrigin: 'center' });
        tl.to(ext, { scale: 1.05, opacity: 0.5, duration: 1.5, yoyo: true, repeat: 1, ease: 'sine.inOut' })
          .to(ext, { rotation: 360, duration: 20, ease: 'none', repeat: -1 }, 0);
        return tl;
      }
    },

    /* 10 · CONSTELACION · epico */
    'marco-constelacion': {
      rareza: 'epico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const pts = [];
        for (let i = 0; i < 7; i++) pts.push(pol((i/7)*TAU, az(1.1, 1.3)));
        const d = pts.map((p,i)=>(i===0?'M':'L')+p.x+' '+p.y).join(' ') + ' Z';
        const poly = nodo('path', { d, fill:'none', stroke: PAL.epico, 'stroke-width': 1.5 }, g);
        
        pts.forEach((p, i) => {
          nodo('circle', { cx: p.x, cy: p.y, r: 3, fill: '#fff' }, g);
        });
        
        tl.to(poly, { opacity: 0.4, duration: 2, yoyo: true, repeat: 1, ease: 'sine.inOut' });
        return tl;
      }
    },

    /* 11 · NEXO · epico */
    'marco-nexo': {
      rareza: 'epico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const ring = nodo('rect', { x: C-R*1.15, y: C-R*1.15, width: R*2.3, height: R*2.3, rx: 20, fill:'none', stroke: PAL.epico, 'stroke-width': 2 }, g);
        gsap.set(ring, { transformOrigin: 'center', rotation: 45 });
        
        const L = preparaDibujo(ring);
        const energ = nodo('rect', { x: C-R*1.15, y: C-R*1.15, width: R*2.3, height: R*2.3, rx: 20, fill:'none', stroke: '#fff', 'stroke-width': 2 }, g);
        gsap.set(energ, { transformOrigin: 'center', rotation: 45, 'stroke-dasharray': `30 ${L}` });
        
        tl.to(energ, { attr:{ 'stroke-dashoffset': -L }, duration: 3, ease: 'linear' });
        return tl;
      }
    },

    /* 12 · INSPIRACION · raro */
    'marco-inspiracion': {
      rareza: 'raro',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        for(let i=0; i<3; i++) {
          const c = nodo('circle', { cx: C+az(-2,2), cy: C+az(-2,2), r: R*(1.1+i*0.03), fill:'none', stroke: PAL.raro, 'stroke-width': 1, opacity: 0.6 }, g);
          gsap.set(c, { transformOrigin: 'center' });
          tl.to(c, { x: az(-3,3), y: az(-3,3), duration: az(2,4), yoyo: true, repeat: 1, ease: 'sine.inOut' }, 0);
        }
        return tl;
      }
    },

    /* 13 · ORBITA · epico */
    'marco-orbita': {
      rareza: 'epico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const orbit = nodo('ellipse', { cx: C, cy: C, rx: R*1.4, ry: R*0.6, fill:'none', stroke: PAL.epico, 'stroke-width': 2 }, g);
        gsap.set(orbit, { transformOrigin: 'center', rotation: 25 });
        
        const dot1 = nodo('circle', { cx: C+R*1.4, cy: C, r: 4, fill: '#fff' }, g);
        gsap.set(dot1, { transformOrigin: `${C}px ${C}px`, rotation: 25 });
        
        tl.to(dot1, { motionPath: { path: orbit, align: orbit, alignOrigin: [0.5, 0.5] }, duration: 6, ease: 'none' });
        return tl;
      }
    },

    /* 14 · FRAGMENTOS · legendario */
    'marco-fragmentos': {
      rareza: 'legendario',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        for (let i = 0; i < 6; i++) {
          const a = (i/6)*TAU;
          const a2 = ((i+0.8)/6)*TAU; // Hueco entre fragmentos
          const p1 = pol(a, 1.1); const p2 = pol(a2, 1.1);
          const p3 = pol(a2, 1.25); const p4 = pol(a, 1.25);
          
          const d = `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y} L ${p3.x} ${p3.y} L ${p4.x} ${p4.y} Z`;
          const frag = nodo('path', { d, fill: PAL.legendario, opacity: 0.3, stroke: PAL.legendario, 'stroke-width': 1 }, g);
          
          tl.to(frag, { opacity: 0.8, scale: 1.05, transformOrigin: 'center', duration: az(1.5, 3), yoyo: true, repeat: 1, ease: 'sine.inOut' }, az(0, 1));
        }
        return tl;
      }
    },

    /* 15 · VORTICE · legendario */
    'marco-vortice': {
      rareza: 'legendario',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        const grp = nodo('g', {}, g);
        gsap.set(grp, { transformOrigin: 'center' });
        for (let i = 0; i < 8; i++) {
          const a = (i/8)*TAU;
          const p1 = pol(a, 1.1);
          const p2 = pol(a+1, 1.3);
          nodo('path', { d: `M ${p1.x} ${p1.y} Q ${p2.x} ${p1.y} ${p2.x} ${p2.y}`, fill:'none', stroke: PAL.legendario, 'stroke-width': 2, opacity: 0.8 }, grp);
        }
        tl.to(grp, { rotation: 360, duration: 15, ease: 'none' });
        return tl;
      }
    },

    /* 16 · LEGADO VIVO · mitico */
    'marco-legado-vivo': {
      rareza: 'mitico',
      crear(g) {
        const tl = gsap.timeline({ repeat: -1 });
        nodo('circle', { cx: C, cy: C, r: R*1.15, fill:'none', stroke: PAL.mitico, 'stroke-width': 3 }, g);
        for (let i = -2; i <= 2; i++) {
          const a = -Math.PI/2 + (i*0.25);
          const p1 = pol(a, 1.15);
          const p2 = pol(a, 1.35 - Math.abs(i)*0.05);
          const r = nodo('line', { x1: p1.x, y1: p1.y, x2: p2.x, y2: p2.y, stroke: PAL.mitico, 'stroke-width': 2.5, 'stroke-linecap':'round' }, g);
          tl.to(r, { opacity: 0.5, duration: 1.5, yoyo: true, repeat: 1 }, Math.abs(i)*0.3);
        }
        const base = nodo('path', { d: `M ${C-R*0.5} ${C+R*1.15} Q ${C} ${C+R*1.3} ${C+R*0.5} ${C+R*1.15}`, fill:'none', stroke: PAL.mitico, 'stroke-width': 4, 'stroke-linecap':'round' }, g);
        tl.to(base, { strokeWidth: 2, duration: 2, yoyo: true, repeat: 1, ease: 'sine.inOut' }, 0);
        return tl;
      }
    }
  };

  /* ═════════════════════════════════════════════════════════
     MOTOR
     ═════════════════════════════════════════════════════════ */
  const vivos = new Map();

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
    svg.style.cssText = 'position:absolute;top:-50%;left:-50%;width:200%;height:200%;' +
                        'pointer-events:none;overflow:visible;display:block;z-index:1;';
    if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
    host.appendChild(svg);
    host.classList.add('mk-host');

    const g = document.createElementNS(NS, 'g');
    svg.appendChild(g);

    const tl = MK[clave].crear(g);
    tl.timeScale(lado < 50 ? 1.25 : 1);
    if (menos) tl.progress(0.35).pause();

    vivos.set(host, { clave, tl, svg });
  }

  const SEL = '.profile-avatar, .comment-avatar, .author-avatar, .avatar-wrap, .cos-preview';
  const escanear = (raiz) => (raiz || document).querySelectorAll(SEL).forEach(montar);

  window.CosMarcos = {
    marcos: MK, montar, escanear, destruir,
    limpiarTodo: () => [...vivos.keys()].forEach(destruir),
    pausar: (v) => vivos.forEach((m) => (v ? m.tl.pause() : m.tl.resume())),
  };
})();
