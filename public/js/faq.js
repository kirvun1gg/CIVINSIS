// FAQ Tabs
const tabBtns = document.querySelectorAll('.faq-tab');
const groups  = document.querySelectorAll('.faq-category-group');
tabBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.dataset.tab;
    groups.forEach(g => {
      g.style.display = g.dataset.cat === tab ? 'block' : 'none';
    });
    document.getElementById('faqSearch').value = '';
    document.getElementById('faqNoResults').style.display = 'none';
  });
});

// FAQ Search
const searchInput = document.getElementById('faqSearch');
const clearBtn    = document.getElementById('faqSearchClear');
searchInput.addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  clearBtn.style.display = q ? 'flex' : 'none';
  if (!q) {
    tabBtns.forEach(b => b.classList.remove('active'));
    tabBtns[0].classList.add('active');
    groups.forEach((g,i) => g.style.display = i===0 ? 'block' : 'none');
    document.getElementById('faqNoResults').style.display = 'none';
    return;
  }
  let found = 0;
  groups.forEach(g => {
    g.style.display = 'block';
    const items = g.querySelectorAll('.faq-item');
    let groupHas = false;
    items.forEach(item => {
      const text = (item.textContent + (item.dataset.keywords||'')).toLowerCase();
      const match = text.includes(q);
      item.style.display = match ? 'block' : 'none';
      if (match) { groupHas = true; found++; }
    });
    g.style.display = groupHas ? 'block' : 'none';
  });
  tabBtns.forEach(b => b.classList.remove('active'));
  document.getElementById('faqNoResults').style.display = found === 0 ? 'block' : 'none';
  if (found === 0) document.getElementById('searchTermDisplay').textContent = q;
});
clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  searchInput.dispatchEvent(new Event('input'));
  clearBtn.style.display = 'none';
});

// Load stats
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
    const d = await r.json();
    if (d.total !== undefined) document.getElementById('faqStatProp').textContent = d.total;
  } catch(e) {}
})();

// Rotating tips
const tips = [
  "Cada voto en CIVINSIS representa una persona real que cree que el cambio es posible. ¡Tu voz cuenta!",
  "Las propuestas con imágenes y formato reciben en promedio 3x más votos.",
  "CIVI puede ayudarte a mejorar el texto de tu propuesta para que sea más persuasiva.",
  "Puedes personalizar tu tarjeta de foro con 8 estilos visuales distintos.",
  "Los comentarios constructivos aumentan la visibilidad de las propuestas en el ranking.",
  "Crea una propuesta y gana 80 XP de inmediato. ¡Sube de nivel participando!",
  "Inicia sesión cada día para mantener tu racha y ganar XP extra.",
  "Completa misiones diarias y semanales para acelerar tu progreso en CIVINSIS.",
  "Los logros legendarios dan hasta 3000 XP de recompensa.",
  "Tu reputación es independiente del XP — se gana con el apoyo de la comunidad.",
];
let tipIdx = 0;
setInterval(() => {
  tipIdx = (tipIdx + 1) % tips.length;
  const el = document.getElementById('faqTipText');
  el.style.opacity = 0;
  setTimeout(() => { el.textContent = tips[tipIdx]; el.style.opacity = 1; el.style.transition = 'opacity .5s'; }, 300);
}, 5000);

// Toggle FAQ
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
