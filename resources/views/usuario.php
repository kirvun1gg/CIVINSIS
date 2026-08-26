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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
  <link rel="stylesheet" href="css/cosmeticos.css">
  <link rel="stylesheet" href="css/marcos-gsap.css">
  <link rel="stylesheet" href="css/fondos.css">
  <link rel="stylesheet" href="css/efectos.css">
  <link rel="stylesheet" href="css/perfil.css">
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
  <script src="js/fondos.js" defer></script>
  <script src="js/efectos-gsap.js" defer></script>
  <script src="js/efectos-eventos.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/MotionPathPlugin.min.js" defer></script>
  <script src="js/marcos-gsap.js" defer></script>
  <script src="js/marcos-descubrimiento.js" defer></script>
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero de perfil público -->
<section class="pf-hero" id="publicHero">
  <div class="container pf-hero-inner">
    <div class="pf-header-card">

      <div class="pf-header-main">
        <div class="pf-avatar-cluster">
          <div class="pf-avatar-box profile-avatar" id="pubAvatar">
            <span id="pubInitials">?</span>
          </div>
        </div>

        <div class="pf-info-cluster">
          <div class="pf-name-row">
            <h1 class="pf-display-name" id="pubName">Cargando...</h1>
            <span class="pf-insignia-badge" id="pubInsigniaDisplay">🌱</span>
          </div>

          <div class="pf-badges-row" id="pubTitleWrap"></div>

          <div class="pf-frase-box" id="pubBioBox" style="display:none">
            <i class="fas fa-quote-left"></i>
            <span id="pubBio"></span>
          </div>

          <div class="pf-meta-row">
            <div class="pf-meta-item">
              <i class="fas fa-calendar-check"></i>
              <span id="pubMiembroDesde">Ciudadano activo</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats de impacto -->
      <div class="pf-stats-grid">
        <div class="pf-stat-card propuestas">
          <div class="pf-stat-icon-wrap"><i class="fas fa-lightbulb"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatProp">0</span>
            <span class="pf-stat-label">Propuestas</span>
          </div>
        </div>

        <div class="pf-stat-card votos">
          <div class="pf-stat-icon-wrap"><i class="fas fa-heart"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatVotos">0</span>
            <span class="pf-stat-label">Votos recibidos</span>
          </div>
        </div>

        <div class="pf-stat-card vistas">
          <div class="pf-stat-icon-wrap"><i class="fas fa-comments"></i></div>
          <div class="pf-stat-info">
            <span class="pf-stat-num" id="pubStatCom">0</span>
            <span class="pf-stat-label">Comentarios</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Contenido del perfil público -->
<div class="container pf-content-area" style="max-width:960px">

  <!-- Widget de nivel y ciudadanía -->
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

  <!-- Insignias obtenidas -->
  <div class="pf-card" style="margin-bottom:1.5rem">
    <h3 class="pf-card-title">
      <i class="fas fa-shield-alt" style="color:var(--verde)"></i> Insignias desbloqueadas
    </h3>
    <p class="pf-card-subtitle">Méritos y reconocimientos alcanzados por este ciudadano.</p>
    <div class="insignias-grid" id="pubInsignias">
      <p style="color:var(--text-muted);font-size:.85rem">Sin insignias aún.</p>
    </div>
  </div>

  <!-- Logros desbloqueados -->
  <div class="pf-card">
    <h3 class="pf-card-title">
      <i class="fas fa-medal" style="color:var(--naranja)"></i> Logros de ciudadanía
      (<span id="pubLogrosCount">0</span>)
    </h3>
    <p class="pf-card-subtitle">Hitos cívicos alcanzados en debates y propuestas.</p>
    <div class="logros-grid" id="pubLogros"></div>
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
    document.getElementById('pubName').textContent = u.nombre;
    document.title = u.nombre + ' – CIVINSIS';
    const initials = (u.nombre || 'C').charAt(0).toUpperCase();
    const avatarEl = document.getElementById('pubAvatar');
    if (u.avatar) {
      avatarEl.innerHTML = `<img src="${u.avatar}" alt="${u.nombre}">`;
    } else {
      document.getElementById('pubInitials').textContent = initials;
    }

    if (u.bio) {
      document.getElementById('pubBio').textContent = `"${u.bio}"`;
      document.getElementById('pubBioBox').style.display = 'flex';
    }

    if (u.miembro_desde) {
      document.getElementById('pubMiembroDesde').textContent = 'Miembro desde ' + u.miembro_desde;
    }

    // Marco equipado en el avatar
    const marcoCls  = d.marco_clase  || (d.marco_equipado  ? d.marco_equipado.replace(/_/g,'-')  : null);
    const efectoCls = d.efecto_clase || (d.efecto_equipado ? d.efecto_equipado.replace(/_/g,'-') : null);
    if (efectoCls) {
      const capa = document.createElement('span');
      capa.className = 'cos-fx ' + efectoCls;
      avatarEl.appendChild(capa);
      avatarEl.classList.add('tiene-fx');
    }
    if (marcoCls) {
      avatarEl.classList.add(marcoCls);
    }

    // Fondo equipado en el hero
    const fondoCls = d.fondo_clase || (d.fondo_equipado ? d.fondo_equipado.replace(/_/g,'-') : null);
    if (fondoCls) {
      document.getElementById('publicHero').classList.add(fondoCls);
    }

    // Título equipado
    let titleHtml = '';
    if (d.titulo) {
      titleHtml += `<span class="titulo-chip ${d.titulo.rareza}" style="color:${d.titulo.color};border-color:${d.titulo.color}">${d.titulo.nombre}</span>`;
    }
    titleHtml += `<span class="pf-role-badge"><i class="fas fa-user-check"></i> ${u.rol}</span>`;
    document.getElementById('pubTitleWrap').innerHTML = titleHtml;

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
