<?php
$iniciales = strtoupper(substr($usuarioNombre, 0, 1));
$esAdmin   = ($usuarioRol === 'admin' || $usuarioRol === 'moderador');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/gamificacion.css">
</head>
<body>


<?php echo view('layouts.navbar', ['activeNav' => 'perfil'])->render(); ?>

<!-- Hero de perfil -->
<section class="profile-hero">
  <div class="container profile-hero-inner">
    <div style="display:flex;align-items:flex-end;gap:2rem;flex-wrap:wrap">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar" id="profileAvatarDisplay">
          <span id="profileInitials"><?= $iniciales ?></span>
        </div>
        <label class="profile-avatar-edit" title="Cambiar foto" for="avatarInput">
          <i class="fas fa-camera"></i>
        </label>
        <input type="file" id="avatarInput" class="edit-avatar-input" accept="image/*" onchange="changeAvatar(this)">
      </div>
      <div>
        <div class="profile-name" id="profileDisplayName"><?= htmlspecialchars($usuarioNombre) ?></div>
        <div class="profile-email" id="profileDisplayEmail">Cargando...</div>
        <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem;flex-wrap:wrap">
          <span class="profile-badge <?= $esAdmin ? 'admin' : '' ?>">
            <i class="fas fa-<?= $esAdmin ? 'shield-alt' : 'user' ?>"></i>
            <?= ucfirst($usuarioRol) ?>
          </span>
          <?php if ($esAdmin): ?>
          <a href="admin.php" class="btn btn-sm" style="background:rgba(239,126,34,.2);color:var(--naranja-400);border:1px solid rgba(239,126,34,.3)">
            <i class="fas fa-cog"></i> Panel Admin
          </a>
          <?php endif; ?>
        </div>
        <div class="profile-stats" id="profileStats">
          <div class="profile-stat">
            <span class="profile-stat-num" id="statMisProp">–</span>
            <span class="profile-stat-label">Propuestas</span>
          </div>
          <div class="profile-stat">
            <span class="profile-stat-num" id="statMisVotos">–</span>
            <span class="profile-stat-label">Votos recibidos</span>
          </div>
          <div class="profile-stat">
            <span class="profile-stat-num" id="statMisVistas">–</span>
            <span class="profile-stat-label">Vistas totales</span>
          </div>
          <div class="profile-stat">
            <span class="profile-stat-num" id="statDesafios">–</span>
            <span class="profile-stat-label">Desafíos completados</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contenido principal -->
<div style="padding:2.5rem 0 5rem">
  <div class="container" style="max-width:860px">

    <!-- Tabs -->
    <div class="profile-tabs">
      <button class="profile-tab active" data-tab="editar">
        <i class="fas fa-edit"></i> Editar perfil
      </button>
      <button class="profile-tab" data-tab="propuestas">
        <i class="fas fa-file-alt"></i> Mis propuestas
      </button>
      <button class="profile-tab" data-tab="gamificacion">
        <i class="fas fa-trophy"></i> Gamificación
      </button>
      <button class="profile-tab" data-tab="seguridad">
        <i class="fas fa-lock"></i> Contraseña
      </button>
    </div>

    <!-- Tab: Editar perfil -->
    <div class="profile-section active" id="tab-editar">
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:2rem">
        <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;color:var(--text);margin-bottom:1.5rem">
          <i class="fas fa-user-edit" style="color:var(--verde)"></i> Información personal
        </h3>
        <form id="editProfileForm">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Nombre *</label>
              <input type="text" class="form-control" id="editNombre" name="nombre" required placeholder="Tu nombre">
            </div>
            <div class="form-group">
              <label class="form-label">Apellido *</label>
              <input type="text" class="form-control" id="editApellido" name="apellido" required placeholder="Tu apellido">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Correo electrónico *</label>
            <input type="email" class="form-control" id="editEmail" name="email" required placeholder="tu@correo.com">
          </div>
          <div class="form-group">
            <label class="form-label">Biografía <span style="color:var(--text-muted);font-weight:400">(opcional)</span></label>
            <textarea class="form-control" id="editBio" name="bio" rows="4" placeholder="Cuéntanos un poco sobre ti..." maxlength="500"></textarea>
            <div class="form-hint"><span id="bioCount">0</span>/500 caracteres</div>
          </div>

          <!-- ── Personalización ampliada (#2) ───────────────── -->
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.05rem;color:var(--text);margin:1.8rem 0 1rem">
            <i class="fas fa-palette" style="color:var(--naranja)"></i> Personaliza tu perfil
          </h3>

          <div class="form-group">
            <label class="form-label">Tema de color</label>
            <div class="theme-grid" id="themeGrid">
              <div class="theme-chip" data-tema="verde"   title="Verde"></div>
              <div class="theme-chip" data-tema="naranja" title="Naranja"></div>
              <div class="theme-chip" data-tema="azul"    title="Azul"></div>
              <div class="theme-chip" data-tema="morado"  title="Morado"></div>
              <div class="theme-chip" data-tema="rosa"    title="Rosa"></div>
              <div class="theme-chip" data-tema="dark"    title="Oscuro"></div>
            </div>
            <input type="hidden" id="editTema" name="tema_perfil" value="verde">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Color de acento</label>
              <input type="color" class="form-control" id="editColorPerfil" value="#36c0a1" style="height:46px;padding:.2rem">
            </div>
            <div class="form-group">
              <label class="form-label">Color del banner</label>
              <input type="color" class="form-control" id="editColorBanner" value="#0f1c19" style="height:46px;padding:.2rem">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Marco del avatar</label>
            <div class="frame-grid" id="frameGrid">
              <div class="frame-opt frame-circulo"  data-marco="circulo"  title="Círculo"><i class="fas fa-circle"></i></div>
              <div class="frame-opt frame-cuadrado" data-marco="cuadrado" title="Cuadrado"><i class="fas fa-square"></i></div>
              <div class="frame-opt frame-hexagono" data-marco="hexagono" title="Hexágono"><i class="fas fa-cube"></i></div>
              <div class="frame-opt frame-estrella" data-marco="estrella" title="Estrella"><i class="fas fa-star"></i></div>
            </div>
            <input type="hidden" id="editMarco" name="marco_avatar" value="circulo">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Insignia (emoji)</label>
              <input type="text" class="form-control" id="editInsignia" maxlength="4" placeholder="🌱 👑 🚀">
            </div>
            <div class="form-group">
              <label class="form-label">Ubicación</label>
              <input type="text" class="form-control" id="editUbicacion" maxlength="80" placeholder="San Salvador">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Frase o lema</label>
            <input type="text" class="form-control" id="editFrase" maxlength="120" placeholder="Tu voz transforma el mundo">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label"><i class="fab fa-twitter"></i> Twitter/X</label>
              <input type="text" class="form-control" id="editTwitter" placeholder="usuario">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fab fa-instagram"></i> Instagram</label>
              <input type="text" class="form-control" id="editInstagram" placeholder="usuario">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fab fa-github"></i> GitHub</label>
              <input type="text" class="form-control" id="editGithub" placeholder="usuario">
            </div>
          </div>

          <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Guardar cambios
            </button>
            <button type="button" class="btn btn-ghost" onclick="loadProfileData()">
              <i class="fas fa-undo"></i> Descartar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab: Mis propuestas -->
    <div class="profile-section" id="tab-propuestas">
      <div id="misProposals">
        <div class="skeleton" style="height:80px;border-radius:var(--radius-lg);margin-bottom:.75rem"></div>
        <div class="skeleton" style="height:80px;border-radius:var(--radius-lg);margin-bottom:.75rem;opacity:.7"></div>
        <div class="skeleton" style="height:80px;border-radius:var(--radius-lg);opacity:.5"></div>
      </div>
    </div>

    <!-- Tab: Gamificación -->
    <div class="profile-section" id="tab-gamificacion">

      <div class="nivel-widget" id="gamNivelWidget" style="margin-bottom:1.25rem">
        <div class="nivel-header">
          <div class="nivel-badge" id="gamNivelBadge">1</div>
          <div class="nivel-info">
            <div class="nivel-nombre">Nivel de ciudadanía</div>
            <div class="nivel-num">Nivel <span id="gamNivel">1</span></div>
          </div>
          <div id="gamTituloWrap"></div>
        </div>
        <div class="xp-bar-wrap">
          <div class="xp-bar-track">
            <div class="xp-bar-fill" id="gamXpFill" style="width:0%"></div>
          </div>
          <div class="xp-labels">
            <span id="gamXpActual">0 XP</span>
            <span class="xp-pct" id="gamXpPct">0%</span>
            <span id="gamXpSig">100 XP</span>
          </div>
        </div>
        <div class="gam-stats-row">
          <div class="gam-stat-box">
            <span class="icon">⭐</span>
            <div class="val" id="gamRepVal">0</div>
            <div class="lbl">Reputación</div>
          </div>
          <div class="gam-stat-box">
            <span class="icon">🔥</span>
            <div class="val" id="gamRachaVal">0</div>
            <div class="lbl">Racha días</div>
          </div>
        </div>
      </div>

      <div class="gam-tabs">
        <button class="gam-tab active" data-gam="misiones"><i class="fas fa-tasks"></i> Misiones</button>
        <button class="gam-tab" data-gam="logros"><i class="fas fa-medal"></i> Logros</button>
        <button class="gam-tab" data-gam="insignias"><i class="fas fa-shield-alt"></i> Insignias</button>
        <button class="gam-tab" data-gam="titulos"><i class="fas fa-crown"></i> Títulos</button>
        <button class="gam-tab" data-gam="cosmeticos"><i class="fas fa-palette"></i> Cosméticos</button>
        <button class="gam-tab" data-gam="ranking"><i class="fas fa-list-ol"></i> Ranking</button>
        <button class="gam-tab" data-gam="historial"><i class="fas fa-history"></i> Historial XP</button>
      </div>

      <div id="gam-misiones" class="gam-panel">
        <div style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap">
          <button class="btn btn-sm btn-outline" onclick="Gam.filtrarMisiones('diaria')" id="btnDiaria">Diarias</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarMisiones('semanal')" id="btnSemanal">Semanales</button>
        </div>
        <div id="gamMisionesList"><div class="skeleton" style="height:64px;border-radius:12px;margin-bottom:.5rem"></div></div>
      </div>

      <div id="gam-logros" class="gam-panel" style="display:none">
        <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap" id="gamLogrosFiltros">
          <button class="btn btn-sm btn-outline" onclick="Gam.filtrarLogros('todos')">Todos</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('propuestas')">Propuestas</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('comunidad')">Comunidad</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('racha')">Racha</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarLogros('nivel')">Nivel</button>
        </div>
        <div class="logros-grid" id="gamLogrosList"></div>
      </div>

      <div id="gam-insignias" class="gam-panel" style="display:none">
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">Insignias desbloqueadas. Haz clic para equipar.</p>
        <div class="insignias-grid" id="gamInsigniasList"></div>
      </div>

      <div id="gam-titulos" class="gam-panel" style="display:none">
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">Elige el título que aparece en tu perfil.</p>
        <div style="display:flex;flex-wrap:wrap;gap:.6rem" id="gamTitulosList"></div>
      </div>

      <div id="gam-cosmeticos" class="gam-panel" style="display:none">
        <div style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap">
          <button class="btn btn-sm btn-outline" onclick="Gam.filtrarCosmeticos('marco_avatar')" id="btnMarco">Marcos</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.filtrarCosmeticos('fondo_perfil')" id="btnFondo">Fondos</button>
        </div>
        <div class="cosmeticos-grid" id="gamCosmeticosList"></div>
      </div>

      <div id="gam-ranking" class="gam-panel" style="display:none">
        <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
          <button class="btn btn-sm btn-outline" onclick="Gam.cargarRanking('xp')">Por XP</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.cargarRanking('reputacion')">Por Reputación</button>
          <button class="btn btn-sm btn-ghost" onclick="Gam.cargarRanking('nivel')">Por Nivel</button>
        </div>
        <div class="table-wrap" style="overflow-x:auto">
          <table class="ranking-table">
            <thead><tr><th>#</th><th>Usuario</th><th>Nivel</th><th>XP</th><th>Reputación</th><th>Título</th></tr></thead>
            <tbody id="gamRankingBody"><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <div id="gam-historial" class="gam-panel" style="display:none">
        <div id="gamHistorialList"><div class="skeleton" style="height:40px;margin-bottom:.5rem"></div></div>
      </div>

    </div>

    <!-- Tab: Contraseña -->
    <div class="profile-section" id="tab-seguridad">
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:2rem">
        <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;color:var(--text);margin-bottom:1.5rem">
          <i class="fas fa-lock" style="color:var(--naranja)"></i> Cambiar contraseña
        </h3>
        <form id="changePassForm">
          <div class="form-group">
            <label class="form-label">Contraseña actual *</label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" class="form-control" id="passActual" name="pass_actual" required placeholder="Tu contraseña actual">
              <span class="input-icon-right" onclick="togglePass('passActual')"><i class="fas fa-eye"></i></span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nueva contraseña *</label>
            <div class="input-group">
              <i class="fas fa-key input-icon"></i>
              <input type="password" class="form-control" id="passNueva" name="pass_nueva" required minlength="8" placeholder="Mínimo 8 caracteres">
              <span class="input-icon-right" onclick="togglePass('passNueva')"><i class="fas fa-eye"></i></span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirmar nueva contraseña *</label>
            <div class="input-group">
              <i class="fas fa-key input-icon"></i>
              <input type="password" class="form-control" id="passConfirm" name="pass_confirm" required placeholder="Repite la nueva contraseña">
              <span class="input-icon-right" onclick="togglePass('passConfirm')"><i class="fas fa-eye"></i></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:1rem">
            <i class="fas fa-lock"></i> Actualizar contraseña
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
// ── Tabs ──────────────────────────────────────────────────
document.querySelectorAll('.profile-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-section').forEach(s => s.classList.remove('active'));
    tab.classList.add('active');
    const _tabEl = document.getElementById('tab-' + tab.dataset.tab);
    if (_tabEl) _tabEl.classList.add('active');
    if (tab.dataset.tab === 'propuestas') loadMisProposals();
  });
});

// ── Cargar datos del perfil ───────────────────────────────
async function loadProfileData() {
  try {
    const r = await fetch('php/auth.php?accion=perfil');
    const d = await r.json();
    if (d.success && d.usuario) {
      const u = d.usuario;
      document.getElementById('editNombre').value  = u.nombre  || '';
      document.getElementById('editApellido').value = u.apellido || '';
      document.getElementById('editEmail').value   = u.email   || '';
      document.getElementById('editBio').value     = u.bio     || '';
      document.getElementById('bioCount').textContent = (u.bio || '').length;
      document.getElementById('profileDisplayName').textContent = u.nombre + ' ' + u.apellido;
      document.getElementById('profileDisplayEmail').textContent = u.email;

      // ── Personalización (#2) ──────────────────────────
      setTema(u.tema_perfil || 'verde');
      setMarco(u.marco_avatar || 'circulo');
      document.getElementById('editColorPerfil').value = u.color_perfil || '#36c0a1';
      document.getElementById('editColorBanner').value = u.color_banner || '#0f1c19';
      document.getElementById('editInsignia').value  = u.insignia  || '';
      document.getElementById('editUbicacion').value = u.ubicacion || '';
      document.getElementById('editFrase').value     = u.frase     || '';
      document.getElementById('editTwitter').value   = u.social_twitter   || '';
      document.getElementById('editInstagram').value = u.social_instagram || '';
      document.getElementById('editGithub').value    = u.social_github    || '';
      aplicarPreview(u.color_banner, u.color_perfil);
      // Stats
      if (u.propuestas !== undefined) {
        document.getElementById('statMisProp').textContent  = u.propuestas || 0;
        document.getElementById('statMisVotos').textContent = u.votos_recibidos || 0;
        document.getElementById('statMisVistas').textContent = u.vistas_totales || 0;
        document.getElementById('statDesafios').textContent = u.desafios_completados || 0;
      }
      if (u.avatar) {
        document.getElementById('profileAvatarDisplay').innerHTML = `<img src="${u.avatar}" alt="Avatar">`;
        // Sync nav avatar
        const navAv = document.getElementById('navUserAvatar');
        if (navAv) navAv.innerHTML = `<img src="${u.avatar}" alt="Avatar">`;
        const mobileAv = document.querySelector('.mobile-drawer-avatar');
        if (mobileAv) mobileAv.innerHTML = `<img src="${u.avatar}" alt="Avatar">`;
      }
    }
  } catch(e) {}
}
loadProfileData();

// ── Helpers de personalización (#2) ───────────────────────
function setTema(t) {
  const et = document.getElementById('editTema');
  if (et) et.value = t;
  document.querySelectorAll('#themeGrid .theme-chip').forEach(c => {
    if (c) c.classList.toggle('active', c.dataset.tema === t);
  });
}
function setMarco(m) {
  document.getElementById('editMarco').value = m;
document.querySelectorAll('#frameGrid .frame-opt').forEach(c => {
    if (c) c.classList.toggle('active', c.dataset.marco === m);
});
  const ava = document.getElementById('profileAvatarDisplay');
  if (ava) {
    ava.classList.remove('marco-circulo','marco-cuadrado','marco-hexagono','marco-estrella');
    ava.classList.add('marco-' + m);
  }
}
function aplicarPreview(banner, acento) {
  const hero = document.querySelector('.profile-hero');
  if (hero && banner) hero.style.background = `linear-gradient(135deg, ${banner}, ${acento || '#36c0a1'})`;
}
document.querySelectorAll('#themeGrid .theme-chip').forEach(c =>
  c.addEventListener('click', () => {
    setTema(c.dataset.tema);
    const map = {verde:'#36c0a1',naranja:'#ef7e22',azul:'#3b82f6',morado:'#a855f7',rosa:'#ec4899',dark:'#1f2937'};
    document.getElementById('editColorPerfil').value = map[c.dataset.tema] || '#36c0a1';
    aplicarPreview(document.getElementById('editColorBanner').value, map[c.dataset.tema]);
  }));
document.querySelectorAll('#frameGrid .frame-opt').forEach(c =>
  c.addEventListener('click', () => setMarco(c.dataset.marco)));
['editColorPerfil','editColorBanner'].forEach(id =>
  document.getElementById(id).addEventListener('input', () =>
    aplicarPreview(document.getElementById('editColorBanner').value, document.getElementById('editColorPerfil').value)));

document.getElementById('editBio').addEventListener('input', function() {
  document.getElementById('bioCount').textContent = this.value.length;
});

// ── Guardar perfil ────────────────────────────────────────
document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = this.querySelector('[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
  const data = {
    accion: 'actualizar_perfil',
    nombre: document.getElementById('editNombre').value,
    apellido: document.getElementById('editApellido').value,
    email: document.getElementById('editEmail').value,
    bio: document.getElementById('editBio').value,
    tema_perfil:  document.getElementById('editTema').value,
    color_perfil: document.getElementById('editColorPerfil').value,
    color_banner: document.getElementById('editColorBanner').value,
    marco_avatar: document.getElementById('editMarco').value,
    insignia:  document.getElementById('editInsignia').value,
    ubicacion: document.getElementById('editUbicacion').value,
    frase:     document.getElementById('editFrase').value,
    social_twitter:   document.getElementById('editTwitter').value,
    social_instagram: document.getElementById('editInstagram').value,
    social_github:    document.getElementById('editGithub').value
  };
  try {
    const r = await fetch('php/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const d = await r.json();
    if (d.success) {
      showToast('Perfil actualizado correctamente', 'success');
      document.getElementById('profileDisplayName').textContent = data.nombre + ' ' + data.apellido;
      document.getElementById('profileDisplayEmail').textContent = data.email;
      const initEl = document.getElementById('profileInitials');
      if (initEl) initEl.textContent = data.nombre.charAt(0).toUpperCase();
      // Update nav user name
      const navName = document.querySelector('.nav-user-name');
      if (navName) navName.textContent = data.nombre;
      const drawerName = document.querySelector('.mobile-drawer-name');
      if (drawerName) drawerName.textContent = data.nombre + ' ' + data.apellido;
      aplicarPreview(data.color_banner, data.color_perfil);
    } else {
      showToast(d.mensaje || 'Error al actualizar', 'error');
    }
  } catch(e) { showToast('Error de conexión', 'error'); }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar cambios';
});

// ── Cambiar contraseña ────────────────────────────────────
document.getElementById('changePassForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const nueva   = document.getElementById('passNueva').value;
  const confirm = document.getElementById('passConfirm').value;
  if (nueva !== confirm) { showToast('Las contraseñas no coinciden', 'error'); return; }
  if (nueva.length < 8)  { showToast('La contraseña debe tener al menos 8 caracteres', 'error'); return; }
  const btn = this.querySelector('[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
  try {
    const r = await fetch('php/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        accion: 'cambiar_password',
        pass_actual: document.getElementById('passActual').value,
        pass_nueva: nueva
      })
    });
    const d = await r.json();
    if (d.success) { showToast('Contraseña actualizada', 'success'); this.reset(); }
    else showToast(d.mensaje || 'Error al cambiar contraseña', 'error');
  } catch(e) { showToast('Error de conexión', 'error'); }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Actualizar contraseña';
});

// ── Cambiar avatar ────────────────────────────────────────
async function changeAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 2 * 1024 * 1024) { showToast('La imagen no puede superar 2MB', 'error'); return; }
  const reader = new FileReader();
  reader.onload = async e => {
    const base64 = e.target.result;
    document.getElementById('profileAvatarDisplay').innerHTML = `<img src="${base64}" alt="Avatar">`;
    try {
      const r = await fetch('php/auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ accion: 'actualizar_avatar', avatar: base64 })
      });
      const d = await r.json();
      if (d.success) {
        showToast('Foto de perfil actualizada', 'success');
        // Update nav avatar immediately
        const navAv = document.getElementById('navUserAvatar');
        if (navAv) navAv.innerHTML = `<img src="${base64}" alt="Avatar">`;
        // Also update any mobile drawer avatar
        const mobileAv = document.querySelector('.mobile-drawer-avatar');
        if (mobileAv) mobileAv.innerHTML = `<img src="${base64}" alt="Avatar">`;
      } else showToast(d.mensaje || 'Error al actualizar foto', 'error');
    } catch(e) { showToast('Error de conexión', 'error'); }
  };
  reader.readAsDataURL(file);
}

// ── Mis propuestas ────────────────────────────────────────
async function loadMisProposals() {
  const container = document.getElementById('misProposals');
  try {
    const r = await fetch('php/propuestas.php?accion=mis_propuestas');
    const d = await r.json();
    if (!d.success || !d.propuestas || d.propuestas.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-file-alt"></i>
          <p>Aún no has publicado propuestas.</p>
          <a href="crear.php" class="btn btn-primary" style="margin-top:1rem"><i class="fas fa-plus"></i> Crear primera propuesta</a>
        </div>`;
      return;
    }
    container.innerHTML = d.propuestas.map(p => `
      <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);margin-bottom:.75rem;transition:var(--trans);" onmouseover="this.style.boxShadow='var(--shadow-color)'" onmouseout="this.style.boxShadow=''">
        <div style="flex:1;min-width:0">
          <a href="propuesta.php?id=${p.id}" style="font-family:var(--font-display);font-weight:700;font-size:.95rem;color:var(--text)">${p.titulo}</a>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem">
            <span class="estado-chip estado-${p.estado}" style="margin-right:.5rem">${p.estado}</span>
            <i class="fas fa-arrow-up"></i> ${p.votos} votos &nbsp;
            <i class="fas fa-eye"></i> ${p.vistas} vistas &nbsp;
            <i class="fas fa-calendar"></i> ${new Date(p.fecha_creacion).toLocaleDateString('es')}
          </div>
        </div>
        <div style="display:flex;gap:.5rem">
          <a href="propuesta.php?id=${p.id}" class="btn btn-sm btn-ghost"><i class="fas fa-eye"></i></a>
          <a href="crear.php?editar=${p.id}" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
        </div>
      </div>
    `).join('');
  } catch(e) {
    container.innerHTML = '<p style="color:var(--text-muted)">Error al cargar propuestas.</p>';
  }
}

function togglePass(id) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

function showToast(msg, type='info') {
  if (window.Toast) { Toast.show(msg, type); return; }
  const d = document.createElement('div');
  d.className = 'toast';
  d.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'} toast-icon ${type}"></i><span class="toast-msg">${msg}</span>`;
  document.querySelector('.toast-container').appendChild(d);
  setTimeout(() => { d.classList.add('removing'); setTimeout(() => d.remove(), 300); }, 3500);
}
</script>
<?php echo view('layouts.footer')->render(); ?>

<script>
// ════════════════════════════════════════════════════════════
//  CIVINSIS GAMIFICACIÓN — Sistema completo
// ════════════════════════════════════════════════════════════
const Gam = {
  data: null,
  misionFiltro: 'diaria',
  cosmeticoFiltro: 'marco_avatar',

  async init() {
    try {
      const r = await fetch('php/gamificacion.php?accion=perfil');
      const d = await r.json();
      if (!d.success) return;
      this.data = d;
      this.renderWidget();
      this.renderMisiones();
    } catch(e) {}
  },

  // ── Widget de nivel ─────────────────────────────────────
  renderWidget() {
    const d = this.data;
    document.getElementById('gamNivel').textContent      = d.nivel;
    document.getElementById('gamNivelBadge').textContent = d.nivel;
    document.getElementById('gamXpActual').textContent   = (d.xp_nivel_actual||0).toLocaleString('es') + ' XP';
    document.getElementById('gamXpSig').textContent      = (d.xp_siguiente_nivel||0).toLocaleString('es') + ' XP';
    document.getElementById('gamXpPct').textContent      = (d.porcentaje_nivel||0) + '%';
    document.getElementById('gamRepVal').textContent     = (d.reputacion||0).toLocaleString('es');
    document.getElementById('gamRachaVal').textContent   = d.racha_dias||0;

    setTimeout(() => {
      const fill = document.getElementById('gamXpFill');
      if (fill) fill.style.width = (d.porcentaje_nivel||0) + '%';
    }, 300);

    if (d.titulo) {
      document.getElementById('gamTituloWrap').innerHTML =
        `<span class="titulo-chip ${d.titulo.rareza}" style="color:${d.titulo.color};border-color:${d.titulo.color}">
          ${d.titulo.nombre}
        </span>`;
    }

    // Aplicar marco equipado al avatar del perfil
    const ava = document.getElementById('profileAvatarDisplay');
    if (ava && d.marco_equipado) {
      ava.classList.remove('marco-basico','marco-dorado','marco-epico','marco-legendario','marco-hexagono');
      ava.classList.add(d.marco_equipado);
    }
    // Aplicar fondo equipado al hero del perfil
    const hero = document.querySelector('.profile-hero');
    if (hero && d.fondo_equipado) {
      hero.classList.remove('fondo-oscuro','fondo-aurora','fondo-fuego','fondo-cosmo','fondo-leyenda');
      hero.classList.add(d.fondo_equipado);
    }
  },

  // ── Misiones ─────────────────────────────────────────────
  renderMisiones() {
    const lista = (this.data.misiones||[]).filter(m => m.tipo === this.misionFiltro);
    const el = document.getElementById('gamMisionesList');
    if (!lista.length) { el.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No hay misiones disponibles.</p></div>'; return; }
    el.innerHTML = lista.map(m => {
      const pct = Math.min(100, Math.round((m.progreso/m.cantidad)*100));
      return `<div class="mision-card ${m.completada?'completada':''}">
        <div class="mision-icon"><i class="fas fa-${m.tipo==='diaria'?'sun':'calendar-week'}"></i></div>
        <div class="mision-info">
          <div class="mision-nombre">
            <span class="mision-tipo-badge ${m.tipo}">${m.tipo}</span>${this.esc(m.nombre)}
          </div>
          <div class="mision-desc">${this.esc(m.descripcion)}</div>
          <div class="mision-progress">
            <div class="mision-progress-fill" style="width:${pct}%"></div>
          </div>
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:.2rem">${m.progreso}/${m.cantidad}</div>
        </div>
        ${m.completada
          ? '<div class="mision-check"><i class="fas fa-check-circle"></i></div>'
          : `<div class="mision-xp">+${m.xp} XP</div>`}
      </div>`;
    }).join('');
  },

  filtrarMisiones(tipo) {
    this.misionFiltro = tipo;
    document.getElementById('btnDiaria').className = tipo==='diaria'?'btn btn-sm btn-outline':'btn btn-sm btn-ghost';
    document.getElementById('btnSemanal').className = tipo==='semanal'?'btn btn-sm btn-outline':'btn btn-sm btn-ghost';
    this.renderMisiones();
  },

  // ── Logros ───────────────────────────────────────────────
  async renderLogros(cat='todos') {
    const r = await fetch('php/gamificacion.php?accion=logros');
    const d = await r.json();
    const lista = cat==='todos' ? d.logros : d.logros.filter(l=>l.categoria===cat);
    const el = document.getElementById('gamLogrosList');
    el.innerHTML = lista.map(l => `
      <div class="logro-card ${l.rareza} ${l.desbloqueado?'':'bloqueado'}">
        <div class="logro-icono">${l.icono}</div>
        <div>
          <div style="display:flex;align-items:center;gap:.3rem;margin-bottom:.2rem">
            <span class="rareza-dot ${l.rareza}"></span>
            <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted)">${l.rareza}</span>
          </div>
          <div class="logro-nombre">${this.esc(l.nombre)}</div>
          <div class="logro-desc">${this.esc(l.descripcion)}</div>
          ${l.xp_recompensa>0?`<div class="logro-xp">+${l.xp_recompensa} XP</div>`:''}
          ${l.desbloqueado?'<div style="font-size:.7rem;color:var(--verde);margin-top:.2rem">✓ Desbloqueado</div>':'<div style="font-size:.7rem;color:var(--text-muted);margin-top:.2rem">🔒 Bloqueado</div>'}
        </div>
      </div>`).join('');
  },

  filtrarLogros(cat) {
    document.querySelectorAll('#gamLogrosFiltros .btn').forEach(b=>b.className='btn btn-sm btn-ghost');
    event.target.className='btn btn-sm btn-outline';
    this.renderLogros(cat);
  },

  // ── Insignias ────────────────────────────────────────────
  renderInsignias() {
    const lista = this.data.insignias||[];
    const el = document.getElementById('gamInsigniasList');
    if (!lista.length) { el.innerHTML='<p style="color:var(--text-muted);font-size:.85rem">Aún no tienes insignias desbloqueadas.</p>'; return; }
    el.innerHTML = lista.map(i => `
      <div class="insignia-item ${i.rareza}" onclick="Gam.equipar('insignia','${i.clave}')" title="${i.nombre}">
        ${i.icono}
        <div class="insignia-tooltip">${this.esc(i.nombre)}</div>
      </div>`).join('');
  },

  // ── Títulos ──────────────────────────────────────────────
  renderTitulos() {
    const lista = this.data.titulos||[];
    const el = document.getElementById('gamTitulosList');
    if (!lista.length) { el.innerHTML='<p style="color:var(--text-muted);font-size:.85rem">Aún no tienes títulos desbloqueados.</p>'; return; }
    el.innerHTML = lista.map(t => `
      <span class="titulo-chip ${t.rareza} ${t.equipado?'':'opacity-60'}"
        style="color:${t.color};border-color:${t.color};cursor:pointer"
        onclick="Gam.equipar('titulo','${t.clave}')">
        ${t.equipado?'✓ ':''} ${this.esc(t.nombre)}
      </span>`).join('');
  },

  // ── Cosméticos ───────────────────────────────────────────
  renderCosmeticos(tipo) {
    const lista = (this.data.cosmeticos||[]).filter(c=>c.tipo===tipo);
    const el = document.getElementById('gamCosmeticosList');
    const rarColor = {comun:'#8892a4',raro:'#4a9eff',epico:'#9b59b6',legendario:'#ffe066'};
    if (!lista.length) { el.innerHTML='<p style="color:var(--text-muted);font-size:.85rem">No tienes cosméticos de este tipo todavía.</p>'; return; }
    el.innerHTML = lista.map(c => `
      <div class="cosmetico-card ${c.equipado?'equipado':''}" onclick="Gam.equipar('${tipo==='marco_avatar'?'marco':'fondo'}','${c.clave}')">
        ${c.equipado?'<div class="cosmetico-equipado-badge"><i class="fas fa-check"></i></div>':''}
        <div class="cosmetico-preview" style="${c.preview||''}"></div>
        <div class="cosmetico-nombre">${this.esc(c.nombre)}</div>
        <div class="cosmetico-rareza" style="color:${rarColor[c.rareza]||'#8892a4'}">${c.rareza}</div>
        <div class="cosmetico-req">Nv. ${c.nivel_requerido}</div>
      </div>`).join('');
  },

  filtrarCosmeticos(tipo) {
    this.cosmeticoFiltro = tipo;
    document.getElementById('btnMarco').className = tipo==='marco_avatar'?'btn btn-sm btn-outline':'btn btn-sm btn-ghost';
    document.getElementById('btnFondo').className = tipo==='fondo_perfil'?'btn btn-sm btn-outline':'btn btn-sm btn-ghost';
    this.renderCosmeticos(tipo);
  },

  // ── Ranking ──────────────────────────────────────────────
  async cargarRanking(tipo='xp') {
    const r = await fetch(`php/gamificacion.php?accion=ranking&tipo=${tipo}`);
    const d = await r.json();
    const posClass = ['gold','silver','bronze'];
    const tbody = document.getElementById('gamRankingBody');
    tbody.innerHTML = d.ranking.map((u,i) => `
      <tr>
        <td><span class="ranking-pos ${posClass[i]||''}">${i<3?['🥇','🥈','🥉'][i]:i+1}</span></td>
        <td>
          <div style="display:flex;align-items:center;gap:.6rem">
            <div class="ranking-avatar">${u.nombre.charAt(0)}</div>
            <span class="ranking-nombre">${this.esc(u.nombre)}</span>
          </div>
        </td>
        <td><strong>${u.nivel}</strong></td>
        <td style="color:var(--xp-verde);font-weight:700">${(u.xp||0).toLocaleString('es')}</td>
        <td style="color:var(--xp-naranja);font-weight:700">${(u.reputacion||0).toLocaleString('es')}</td>
        <td>${u.titulo?`<span class="titulo-chip ${u.titulo.rareza}" style="color:${u.titulo.color};border-color:${u.titulo.color}">${u.titulo.nombre}</span>`:'—'}</td>
      </tr>`).join('');
  },

  // ── Historial XP ─────────────────────────────────────────
  async cargarHistorial() {
    const r = await fetch('php/gamificacion.php?accion=historial_xp');
    const d = await r.json();
    const el = document.getElementById('gamHistorialList');
    if (!d.historial?.length) { el.innerHTML='<p style="color:var(--text-muted);font-size:.85rem">No hay historial aún.</p>'; return; }
    el.innerHTML = d.historial.map(h => `
      <div class="xp-historial-item">
        <span class="xp-amount ${h.xp<0?'neg':''}">+${h.xp} XP</span>
        <span style="flex:1;color:var(--text-2)">${this.esc(h.descripcion||h.accion)}</span>
        <span style="color:var(--text-muted);font-size:.72rem">${new Date(h.created_at).toLocaleDateString('es')}</span>
      </div>`).join('');
  },

  // ── Equipar ítem ─────────────────────────────────────────
  async equipar(tipo, clave) {
    const r = await fetch('php/gamificacion.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({accion:'equipar',tipo,clave})
    });
    const d = await r.json();
    if (d.success) {
      if (window.Toast) Toast.show('¡Ítem equipado!', 'success');
      await this.init();
      this.renderWidget();
      this.renderTitulos();
      this.renderCosmeticos(this.cosmeticoFiltro);
    } else {
      if (window.Toast) Toast.show(d.mensaje||'No puedes equipar ese ítem', 'error');
    }
  },

  esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); },
};

// ── Tabs de gamificación ──────────────────────────────────
document.querySelectorAll('.gam-tab').forEach(tab => {
  tab.addEventListener('click', async () => {
    document.querySelectorAll('.gam-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.gam-panel').forEach(p => p.style.display='none');
    tab.classList.add('active');
    const panel = document.getElementById('gam-' + tab.dataset.gam);
    if (panel) panel.style.display='block';

    switch(tab.dataset.gam) {
      case 'logros':     Gam.renderLogros(); break;
      case 'insignias':  Gam.renderInsignias(); break;
      case 'titulos':    Gam.renderTitulos(); break;
      case 'cosmeticos': Gam.renderCosmeticos('marco_avatar'); break;
      case 'ranking':    Gam.cargarRanking(); break;
      case 'historial':  Gam.cargarHistorial(); break;
    }
  });
});

// Cargar gamificación al abrir el tab de perfil
document.querySelectorAll('[data-tab="gamificacion"]').forEach(btn => {
  btn.addEventListener('click', () => Gam.init());
});

// ── Toast de subida de nivel (se llama desde respuestas de API) ──
function mostrarNivelUp(nivel, titulo) {
  const toast = document.createElement('div');
  toast.className = 'nivel-up-toast';
  toast.innerHTML = `
    <div class="nivel-up-title">⚡ ¡Subiste al Nivel ${nivel}!</div>
    <div class="nivel-up-desc">${titulo ? 'Desbloqueaste el título: <strong>' + titulo + '</strong>' : '¡Sigue participando para subir más!'}</div>
  `;
  document.body.appendChild(toast);
  setTimeout(() => { toast.classList.add('saliendo'); setTimeout(()=>toast.remove(),500); }, 4000);
}
</script>
</body>
</html>