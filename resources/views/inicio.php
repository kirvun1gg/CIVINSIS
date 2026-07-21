<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/debates.css">
  <link rel="stylesheet" href="css/inicio.css">
  <style>
    /* ── CIVI te recomienda ────────────────────────────────── */
    .civi-reco { margin-top:1.75rem; background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:1.4rem 1.5rem; }
    .civi-reco-head { display:flex; align-items:center; gap:.85rem; margin-bottom:1.15rem; }
    .civi-reco-ava { width:46px; height:46px; border-radius:50%; background:var(--grad-primary); color:#fff;
      display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 4px 12px var(--verde-alpha2); flex-shrink:0; }
    .civi-reco-title { font-family:var(--font-display); font-weight:800; font-size:1.1rem; color:var(--text); }
    .civi-reco-intro { font-size:.86rem; color:var(--text-muted); line-height:1.45; }
    .civi-reco-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:.9rem; }
    .civi-reco-card { display:block; text-decoration:none; background:var(--bg); border:1px solid var(--border);
      border-radius:14px; padding:.9rem 1rem; transition:var(--trans); }
    .civi-reco-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md); border-color:var(--verde-200); }
    .civi-reco-badge { display:inline-flex; align-items:center; gap:.35rem; font-size:.7rem; font-weight:700;
      padding:.2rem .55rem; border-radius:999px; margin-bottom:.5rem; }
    .civi-reco-card-title { font-weight:700; font-size:.92rem; color:var(--text); line-height:1.35; margin-bottom:.45rem;
      display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .civi-reco-razon { font-size:.78rem; color:var(--verde-700); display:flex; align-items:center; gap:.4rem; }
    .civi-reco-razon i { color:var(--naranja); flex-shrink:0; }
    .civi-reco-desafio { margin-top:.9rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;
      background:linear-gradient(135deg,var(--verde-alpha),var(--naranja-alpha)); border:1px solid var(--verde-200);
      border-radius:14px; padding:.9rem 1.1rem; text-decoration:none; }
    .civi-reco-desafio .txt { font-size:.9rem; color:var(--text); font-weight:600; }
    .civi-reco-desafio .razon { font-size:.76rem; color:var(--text-muted); margin-top:.15rem; }
  </style>
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'inicio'])->render(); ?>

<main style="padding-top:calc(var(--nav-height) + 1.5rem);padding-bottom:4rem;min-height:100vh">
  <div class="container" style="max-width:1140px">
    <div id="actividadPanel">
      <div class="inicio-skeleton">
        <div class="skeleton" style="height:120px;border-radius:20px;margin-bottom:1.5rem"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
          <div class="skeleton" style="height:200px;border-radius:16px"></div>
          <div class="skeleton" style="height:200px;border-radius:16px"></div>
        </div>
      </div>
    </div>

    <div class="container" style="max-width:1140px">
      <div id="civiRecomienda" class="civi-reco" hidden></div>
    </div>
  </div>
</main>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script src="js/inicio.js"></script>
<script>CentroActividad.init();</script>
<script>
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
</script>
</body>
</html>
