<?php
$perfilId = $perfilId ?? intval(request('id'));
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de usuario – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero de perfil público -->
<section class="profile-hero" id="publicHero">
  <div class="container profile-hero-inner">
    <div style="display:flex;align-items:flex-end;gap:2rem;flex-wrap:wrap">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar" id="pubAvatar">
          <span id="pubInitials">?</span>
        </div>
      </div>
      <div style="flex:1">
        <div class="profile-name" id="pubName">Cargando...</div>
        <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem;flex-wrap:wrap" id="pubTitleWrap"></div>
        <div class="profile-stats" id="pubStats">
          <div class="profile-stat">
            <span class="profile-stat-num" id="pubStatProp">–</span>
            <span class="profile-stat-label">Propuestas</span>
          </div>
          <div class="profile-stat">
            <span class="profile-stat-num" id="pubStatVotos">–</span>
            <span class="profile-stat-label">Votos recibidos</span>
          </div>
          <div class="profile-stat">
            <span class="profile-stat-num" id="pubStatCom">–</span>
            <span class="profile-stat-label">Comentarios</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contenido -->
<div style="padding:2.5rem 0 5rem">
  <div class="container" style="max-width:860px">

    <!-- Widget de nivel -->
    <div class="nivel-widget" id="pubNivelWidget" style="margin-bottom:1.5rem">
      <div class="nivel-header">
        <div class="nivel-badge" id="pubNivelBadge">1</div>
        <div class="nivel-info">
          <div class="nivel-nombre">Nivel de ciudadanía</div>
          <div class="nivel-num">Nivel <span id="pubNivel">1</span></div>
        </div>
      </div>
      <div class="xp-bar-wrap">
        <div class="xp-bar-track">
          <div class="xp-bar-fill" id="pubXpFill" style="width:0%"></div>
        </div>
        <div class="xp-labels">
          <span id="pubXpActual">0 XP</span>
          <span class="xp-pct" id="pubXpPct">0%</span>
          <span id="pubXpSig">100 XP</span>
        </div>
      </div>
      <div class="gam-stats-row">
        <div class="gam-stat-box">
          <span class="icon">⭐</span>
          <div class="val" id="pubRep">0</div>
          <div class="lbl">Reputación</div>
        </div>
        <div class="gam-stat-box">
          <span class="icon">🔥</span>
          <div class="val" id="pubRacha">0</div>
          <div class="lbl">Racha días</div>
        </div>
      </div>
    </div>

    <!-- Insignias equipadas -->
    <div class="nivel-widget" style="margin-bottom:1.5rem">
      <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;margin-bottom:1rem">
        <i class="fas fa-shield-alt" style="color:var(--verde)"></i> Insignias
      </h3>
      <div class="insignias-grid" id="pubInsignias">
        <p style="color:var(--text-muted);font-size:.85rem">Sin insignias aún.</p>
      </div>
    </div>

    <!-- Logros desbloqueados -->
    <div class="nivel-widget">
      <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;margin-bottom:1rem">
        <i class="fas fa-medal" style="color:var(--naranja-400)"></i> Logros desbloqueados
        (<span id="pubLogrosCount">0</span>)
      </h3>
      <div class="logros-grid" id="pubLogros"></div>
    </div>

  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
const PERFIL_ID = <?= $perfilId ?>;

(async function loadPublicProfile() {
  try {
    const r = await fetch('php/gamificacion.php?accion=perfil_publico&id=' + PERFIL_ID);
    const d = await r.json();
    if (!d.success) {
      document.getElementById('pubName').textContent = 'Usuario no encontrado';
      return;
    }

    const u = d.usuario;
    // Nombre + avatar
    document.getElementById('pubName').textContent = u.nombre;
    document.title = u.nombre + ' – CIVINSIS';
    const initials = u.nombre.charAt(0).toUpperCase();
    const avatarEl = document.getElementById('pubAvatar');
    if (u.avatar) {
      avatarEl.innerHTML = `<img src="${u.avatar}" alt="${u.nombre}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">`;
    } else {
      document.getElementById('pubInitials').textContent = initials;
    }

    // Marco equipado en el avatar
    if (d.marco_equipado) {
      avatarEl.classList.add(d.marco_equipado);
    }

    // Fondo equipado en el hero
    if (d.fondo_equipado) {
      document.getElementById('publicHero').classList.add(d.fondo_equipado);
    }

    // Título equipado
    if (d.titulo) {
      document.getElementById('pubTitleWrap').innerHTML =
        `<span class="titulo-chip ${d.titulo.rareza}" style="color:${d.titulo.color};border-color:${d.titulo.color}">
          ${d.titulo.nombre}
        </span>
        <span class="profile-badge"><i class="fas fa-user"></i> ${u.rol}</span>`;
    }

    // Stats
    document.getElementById('pubStatProp').textContent  = d.stats.propuestas;
    document.getElementById('pubStatVotos').textContent = d.stats.votos;
    document.getElementById('pubStatCom').textContent   = d.stats.comentarios;

    // Nivel + XP
    document.getElementById('pubNivel').textContent      = d.nivel;
    document.getElementById('pubNivelBadge').textContent = d.nivel;
    document.getElementById('pubXpActual').textContent   = (d.xp_nivel_actual||0).toLocaleString('es') + ' XP';
    document.getElementById('pubXpSig').textContent      = (d.xp_siguiente_nivel||0).toLocaleString('es') + ' XP';
    document.getElementById('pubXpPct').textContent      = (d.porcentaje_nivel||0) + '%';
    document.getElementById('pubRep').textContent        = (d.reputacion||0).toLocaleString('es');
    document.getElementById('pubRacha').textContent      = d.racha_dias||0;
    setTimeout(() => document.getElementById('pubXpFill').style.width = (d.porcentaje_nivel||0) + '%', 300);

    // Insignias
    if (d.insignias && d.insignias.length) {
      document.getElementById('pubInsignias').innerHTML = d.insignias.map(i => `
        <div class="insignia-item ${i.rareza}" title="${i.nombre}">
          ${i.icono}
          <div class="insignia-tooltip">${esc(i.nombre)}</div>
        </div>`).join('');
    }

    // Logros
    const logros = d.logros || [];
    document.getElementById('pubLogrosCount').textContent = logros.length;
    if (logros.length) {
      document.getElementById('pubLogros').innerHTML = logros.map(l => `
        <div class="logro-card ${l.rareza}">
          <div class="logro-icono">${l.icono}</div>
          <div>
            <div class="logro-nombre">${esc(l.nombre)}</div>
            <div class="logro-desc">${esc(l.descripcion)}</div>
          </div>
        </div>`).join('');
    } else {
      document.getElementById('pubLogros').innerHTML = '<p style="color:var(--text-muted);font-size:.85rem">Este usuario aún no ha desbloqueado logros.</p>';
    }

  } catch(e) {
    document.getElementById('pubName').textContent = 'Error al cargar el perfil';
  }
})();

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
