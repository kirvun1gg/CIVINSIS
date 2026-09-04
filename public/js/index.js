// Contadores del hero
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
    const d = await r.json();
    if (d.total !== undefined) animateCounter('statPropuestas', d.total);
  } catch(e) {}
  animateCounter('statUsuarios', 247);
  animateCounter('statVotos', 1842);
  function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let cur = 0;
    const step = Math.ceil(target / 40);
    const t = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = cur.toLocaleString('es');
      if (cur >= target) clearInterval(t);
    }, 40);
  }
})();

// Top votadas
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=top&limit=5');
    const d = await r.json();
    if (!d.success || !d.propuestas.length) return;
    const maxV = Math.max(...d.propuestas.map(p => p.votos));
    const medals = ['gold','silver','bronze','other','other'];
    const icons  = ['🥇','🥈','🥉','4','5'];
    document.getElementById('topProposals').innerHTML = d.propuestas.map((p,i) => `
      <a href="propuesta.php?id=${p.id}" style="text-decoration:none">
        <div class="top-card">
          <div class="top-medal ${medals[i]}">${i < 3 ? icons[i] : i+1}</div>
          <div class="top-info">
            <div class="top-title">${p.titulo}</div>
            <div class="top-cat"><i class="${p.cat_icono||'fas fa-tag'}" style="color:${p.cat_color||'#36c0a1'}"></i> ${p.categoria||''}</div>
            <div class="top-vote-bar" style="margin-top:.4rem">
              <div class="top-vote-bar-fill" style="width:${Math.round((p.votos/maxV)*100)}%"></div>
            </div>
          </div>
          <div class="top-votes-wrap">
            <div class="top-votes-num">${p.votos}</div>
            <div class="top-votes-label">votos</div>
          </div>
        </div>
      </a>
    `).join('');
  } catch(e) {}
})();

// Responsive top-votadas
const s = document.createElement('style');
s.textContent = `@media(max-width:768px){.top-votadas-grid{grid-template-columns:1fr!important;gap:2rem!important}}`;
document.head.appendChild(s);

// ── Efectos GSAP por letra de "CIVINSIS" ────────────────────
// Cada letra tiene una animación propia al pasar el mouse (antes eran
// :hover + @keyframes en CSS; ahora se controlan con timelines de GSAP
// para poder afinar cada paso). gsap.quickTo/timeline vuelve a dejar la
// letra en su estado de reposo al salir el mouse.
(function initCivLetterEffects() {
  if (typeof gsap === 'undefined') return;
  gsap.set('.civinsis-word', { perspective: 400 });

  const RESET = { x: 0, y: 0, scale: 1, rotation: 0, rotationX: 0, rotationY: 0, skewX: 0, filter: 'none' };

  const EFECTOS = {
    'civ-C': (el) => gsap.timeline()
      .to(el, { duration: .18, scale: 1.55, y: -10, rotation: -8, filter: 'drop-shadow(0 0 12px #36c0a1) drop-shadow(0 0 30px rgba(54,192,161,.5))', ease: 'power2.out' })
      .to(el, { duration: .22, scale: 1.4, y: -8, rotation: 0, ease: 'elastic.out(1,.5)' }),

    'civ-I1': (el) => gsap.timeline()
      .to(el, { duration: .15, scaleX: 3, scaleY: .8, y: -4, filter: 'drop-shadow(0 0 20px #ff5078)', ease: 'power1.in' })
      .to(el, { duration: .18, scaleX: .4, scaleY: 1.4, y: -8, ease: 'power1.inOut' })
      .to(el, { duration: .2, scaleX: 1, scaleY: 1.15, y: -6, filter: 'drop-shadow(0 0 14px rgba(255,80,120,.8))', ease: 'back.out(2)' }),

    'civ-V': (el) => gsap.timeline()
      .fromTo(el, { y: -40, scaleY: .5, opacity: .5 },
        { duration: .35, y: -6, scaleY: 1, scale: 1.2, opacity: 1, filter: 'drop-shadow(0 10px 20px rgba(0,200,255,.6))', ease: 'bounce.out' }),

    'civ-I2': (el) => gsap.timeline()
      .to(el, { duration: .16, scaleX: 1.8, scaleY: .6, y: 6, filter: 'drop-shadow(0 0 16px rgba(255,255,255,.6))', ease: 'sine.inOut' })
      .to(el, { duration: .18, scaleX: .6, scaleY: 1.6, y: -8, ease: 'sine.inOut' })
      .to(el, { duration: .16, scaleX: 1.2, scaleY: .9, ease: 'sine.inOut' })
      .to(el, { duration: .18, scaleX: 1, scaleY: 1.15, y: -5, filter: 'drop-shadow(0 0 30px rgba(54,192,161,.4))', ease: 'sine.out' }),

    'civ-N': (el) => gsap.timeline()
      .to(el, { duration: .05, x: -4, y: 2, rotation: -3, skewX: -8, filter: 'drop-shadow(0 0 8px #ffe066) drop-shadow(0 0 20px #ef7e22)', ease: 'none' })
      .to(el, { duration: .05, x: 4, y: -2, rotation: 3, skewX: 8, ease: 'none' })
      .to(el, { duration: .05, x: -3, y: 1, skewX: -5, ease: 'none' })
      .to(el, { duration: .05, x: 3, y: -1, skewX: 5, ease: 'none' })
      .to(el, { duration: .05, x: -2, skewX: -3, ease: 'none' })
      .to(el, { duration: .05, x: 2, skewX: 3, ease: 'none' })
      .to(el, { duration: .1, x: 0, y: -6, scale: 1.15, skewX: 0, ease: 'power1.out' }),

    'civ-S1': (el) => gsap.timeline()
      .to(el, { duration: .24, rotationY: 180, scale: 1.3, y: -8, filter: 'drop-shadow(-8px 0 15px rgba(150,50,255,.7)) drop-shadow(8px 0 15px rgba(54,192,161,.5))', ease: 'power1.in' })
      .to(el, { duration: .18, rotationY: 270, scale: 1.1, ease: 'power1.inOut' })
      .to(el, { duration: .18, rotationY: 360, scale: 1.2, y: -6, ease: 'power1.out' }),

    'civ-I3': (el) => gsap.timeline()
      .to(el, { duration: .18, rotationX: 90, scaleY: 1.5, filter: 'drop-shadow(0 6px 18px rgba(255,200,0,.7))', ease: 'power1.in' })
      .to(el, { duration: .17, rotationX: -20, y: -8, ease: 'power1.out' })
      .to(el, { duration: .15, rotationX: 0, y: -6, scale: 1.15, filter: 'drop-shadow(0 0 30px rgba(239,126,34,.3))', ease: 'back.out(1.7)' }),

    'civ-S2': (el) => gsap.timeline()
      .to(el, { duration: .12, scale: 1.5, rotation: -10, filter: 'drop-shadow(0 0 20px #ff0080) drop-shadow(0 0 40px #ff0080)', ease: 'power1.out' })
      .to(el, { duration: .12, scale: .8, rotation: 8, filter: 'drop-shadow(0 0 20px #ffff00) drop-shadow(0 0 40px #ff8c00)', ease: 'sine.inOut' })
      .to(el, { duration: .12, scale: 1.4, rotation: -5, filter: 'drop-shadow(0 0 20px #00ff80) drop-shadow(0 0 40px #0080ff)', ease: 'sine.inOut' })
      .to(el, { duration: .12, scale: 1.1, rotation: 3, filter: 'drop-shadow(0 0 20px #8000ff) drop-shadow(0 0 40px #ff0080)', ease: 'sine.inOut' })
      .to(el, { duration: .16, scale: 1.25, rotation: 0, y: -6, filter: 'drop-shadow(0 0 16px rgba(255,100,200,.8))', ease: 'power1.out' }),
  };

  // Reposo con vida propia: cada letra flota suavemente y de forma
  // escalonada mientras nadie interactúa, para que el título no se
  // sienta estático. Se pausa en cuanto el mouse entra o se hace click.
  const idle = new Map();
  function startIdle(el, i) {
    idle.set(el, gsap.to(el, {
      y: -6, duration: 1.6 + (i % 3) * .25, ease: 'sine.inOut',
      repeat: -1, yoyo: true, delay: i * .12,
    }));
  }
  function stopIdle(el) {
    const t = idle.get(el);
    if (t) { t.kill(); idle.delete(el); }
  }

  // Efecto de click: un "golpe" elástico con destello de color, propio
  // de cada letra y distinto del hover — además de las chispas (abajo).
  function clickPunch(el) {
    stopIdle(el);
    gsap.killTweensOf(el);
    return gsap.timeline({ onComplete: () => startIdle(el, 0) })
      .to(el, { duration: .09, scale: .65, rotation: gsap.utils.random(-18, 18), ease: 'power2.in' })
      .to(el, {
        duration: .55, scale: 1.35, rotation: 0, ease: 'elastic.out(1,.4)',
        filter: 'drop-shadow(0 0 22px #ff0080) drop-shadow(0 0 45px #36c0a1) drop-shadow(0 0 70px #ffe066)',
      })
      .to(el, { ...RESET, duration: .35, ease: 'power2.out' }, '+=.15');
  }

  document.querySelectorAll('.civ-l').forEach((el, i) => {
    const clave = Object.keys(EFECTOS).find(c => el.classList.contains(c));
    if (!clave) return;
    startIdle(el, i);
    let tl = null;
    el.addEventListener('mouseenter', () => {
      stopIdle(el);
      if (tl) tl.kill();
      tl = EFECTOS[clave](el);
    });
    el.addEventListener('mouseleave', () => {
      if (tl) tl.kill();
      gsap.to(el, { ...RESET, duration: .3, ease: 'power2.out', onComplete: () => startIdle(el, i) });
    });
    el.addEventListener('click', (e) => {
      if (tl) tl.kill();
      clickPunch(el);
      spawnSparks(el, e);
    });
  });

  // Partículas al hacer click en letras CIVINSIS
  function spawnSparks(el, e) {
    const colors = ['#36c0a1','#ef7e22','#00c8ff','#ffe066','#ff5078','#9632ff','#00ffcc'];
    for (let i = 0; i < 12; i++) {
      const spark = document.createElement('div');
      spark.className = 'civ-spark';
      const angle = (i / 12) * 360;
      const dist  = 40 + Math.random() * 60;
      const tx    = Math.cos(angle * Math.PI / 180) * dist;
      const ty    = Math.sin(angle * Math.PI / 180) * dist;
      spark.style.cssText = `
        left:${e.clientX}px; top:${e.clientY}px;
        background:${colors[i % colors.length]};
        --tx:${tx}px; --ty:${ty}px;
        animation-duration:${.5 + Math.random() * .4}s;
      `;
      document.body.appendChild(spark);
      setTimeout(() => spark.remove(), 900);
    }
  }
})();
