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

// Partículas al hacer click en letras CIVINSIS
document.querySelectorAll('.civ-l').forEach(el => {
  el.addEventListener('click', function(e) {
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
  });
});
