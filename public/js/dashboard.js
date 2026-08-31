Proposals.filterCat = function(cat, btn) {
  document.querySelectorAll('[data-cat]').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  document.querySelectorAll('.sidebar-link').forEach(b => b.classList.remove('active'));
  if (btn && btn.closest('.sidebar')) btn.classList.add('active');
  this.currentCat = cat;
  this.currentPage = 1;
  this.load();
};

(async () => {
  const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
  const d = await r.json();
  if (d.success) {
    document.getElementById('kpiTotal').textContent = d.total;
    const r2 = await fetch('php/propuestas.php?accion=top&limit=100');
    const d2 = await r2.json();
    if (d2.success) {
      const votos  = d2.propuestas.reduce((s, p) => s + parseInt(p.votos),  0);
      const vistas = d2.propuestas.reduce((s, p) => s + parseInt(p.vistas), 0);
      document.getElementById('kpiVotos').textContent  = votos.toLocaleString('es');
      document.getElementById('kpiVistas').textContent = vistas.toLocaleString('es');
    }
  }
})();
