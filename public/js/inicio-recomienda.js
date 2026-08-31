// ── CIVI te recomienda (consume la acción recomendar) ──
(async function () {
  const box = document.getElementById('civiRecomienda');
  if (!box) return;
  const esc = (x) => { const e = document.createElement('div'); e.textContent = x ?? ''; return e.innerHTML; };
  try {
    const res = await fetch('php/ia.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'recomendar' }),
    });
    const d = await res.json();
    if (!d || !d.success) return;

    const props = (d.propuestas || []).map((p) => ({ ...p, tipo: 'Propuesta', ti: 'fa-file-alt' }));
    const debs  = (d.debates   || []).map((x) => ({ ...x, tipo: 'Debate',    ti: 'fa-comments' }));
    const items = [...props, ...debs];
    if (!items.length && !d.desafio) return; // nada nuevo → no ocupamos espacio

    const cards = items.map((it) => `
      <a href="${esc(it.url)}" class="civi-reco-card">
        <span class="civi-reco-badge" style="background:${esc(it.color)}22;color:${esc(it.color)}">
          <i class="fas ${esc(it.ti)}"></i> ${esc(it.tipo)}${it.categoria ? ' · ' + esc(it.categoria) : ''}
        </span>
        <div class="civi-reco-card-title">${esc(it.titulo)}</div>
        <div class="civi-reco-razon"><i class="fas fa-lightbulb"></i> ${esc(it.razon)}</div>
      </a>`).join('');

    const des = d.desafio ? `
      <a href="${esc(d.desafio.url)}" class="civi-reco-desafio">
        <div>
          <div class="txt"><i class="fas fa-bolt" style="color:var(--naranja)"></i> Reto: ${esc(d.desafio.titulo)}</div>
          <div class="razon">${esc(d.desafio.razon)}</div>
        </div>
        <span class="btn btn-sm btn-primary">Aceptar</span>
      </a>` : '';

    box.innerHTML = `
      <div class="civi-reco-head">
        <div class="civi-reco-ava"><i class="fas fa-robot"></i></div>
        <div>
          <div class="civi-reco-title">CIVI te recomienda</div>
          <div class="civi-reco-intro">${esc(d.intro)}</div>
        </div>
      </div>
      <div class="civi-reco-grid">${cards}</div>
      ${des}`;
    box.hidden = false;
  } catch (e) { /* silencioso: CIVI no molesta si algo falla */ }
})();
