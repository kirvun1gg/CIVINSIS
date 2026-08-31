// ════════════════════════════════════════════════════════════
//  CIVINSIS — Sistema de Perfil Interactivo
// ════════════════════════════════════════════════════════════

// ── Control de Acordeones ──────────────────────────────────
function toggleAccordion(id) {
  const acc = document.getElementById(id);
  if (acc) {
    acc.classList.toggle('open');
  }
}

// ── Control de Civi Inline ─────────────────────────────────
function toggleCiviSection() {
  const content = document.getElementById('civiInlineContent');
  const chev = document.getElementById('civiChevron');
  if (content.style.display === 'none') {
    content.style.display = 'block';
    chev.style.transform = 'rotate(180deg)';
    loadCiviAnalisis();
  } else {
    content.style.display = 'none';
    chev.style.transform = 'rotate(0deg)';
  }
}

// ── Drawer de Cosméticos ───────────────────────────────────
const CosDrawer = {
  open(tipo = 'marco_avatar') {
    document.getElementById('cosDrawerOverlay').classList.add('open');
    document.getElementById('cosDrawer').classList.add('open');
    
    if (!Gam.data) {
      document.getElementById('cosDrawerGrid').innerHTML = '<div class="pf-cos-drawer-empty"><i class="fas fa-spinner fa-spin"></i><p>Cargando cosméticos…</p></div>';
      Gam.init().then(() => this.filter(tipo));
    } else {
      this.filter(tipo);
    }
  },
  
  close() {
    document.getElementById('cosDrawerOverlay').classList.remove('open');
    document.getElementById('cosDrawer').classList.remove('open');
  },
  
  filter(tipo) {
    document.querySelectorAll('.pf-cos-drawer-filter-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.drawerFilter === tipo);
    });
    
    if (!Gam.data) return;
    
    const lista = (Gam.data.cosmeticos||[]).filter(c => c.tipo === tipo);
    const grid = document.getElementById('cosDrawerGrid');
    
    if (!lista.length) {
      grid.innerHTML = '<div class="pf-cos-drawer-empty"><i class="fas fa-box-open"></i><p>No tienes cosméticos de este tipo.</p></div>';
      return;
    }
    
    const accion = { marco_avatar:'marco', fondo_perfil:'fondo', efecto_avatar:'efecto' }[tipo];
    const esFondo = tipo === 'fondo_perfil';
    
    grid.innerHTML = lista.map(c => {
      const isEquipado = c.equipado;
      const isBloqueado = !c.desbloqueado;
      const cls = ['pf-cos-drawer-item'];
      if (isEquipado) cls.push('equipado');
      if (isBloqueado) cls.push('bloqueado');
      
      let previewHtml = '';
      if (c.misterioso && isBloqueado) {
         previewHtml = `<div class="pf-cos-drawer-item-preview${esFondo?' es-fondo':''}">?</div>`;
      } else if (esFondo) {
         previewHtml = `<div class="pf-cos-drawer-item-preview es-fondo pf-hero ${c.valor}"></div>`;
      } else {
         previewHtml = `<div class="pf-cos-drawer-item-preview ${c.valor}">${Gam.iniciales()}</div>`;
      }
      
      const badge = isEquipado ? '<div class="pf-cos-equipped-badge"><i class="fas fa-check"></i></div>' : 
                    (isBloqueado ? '<div class="pf-cos-lock-badge"><i class="fas fa-lock"></i></div>' : '');
      
      return `
        <div class="${cls.join(' ')}" onclick="CosDrawer.equipar('${accion}','${c.clave}',${c.desbloqueado?'true':'false'},'${tipo}')">
          ${badge}
          ${previewHtml}
          <div class="pf-cos-drawer-item-name">${Gam.esc(c.nombre)}</div>
          <div class="pf-cos-drawer-item-rareza ${c.rareza.toLowerCase()}">${c.rareza}</div>
        </div>
      `;
    }).join('');
    
    Gam.montarPreviews();
  },
  
  async equipar(tipo, clave, desbloqueado, currentDrawerTipo) {
    if (!desbloqueado) {
      showToast('Aún no has desbloqueado este cosmético.', 'info');
      return;
    }
    
    const r = await fetch('php/gamificacion.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({accion:'equipar',tipo,clave})
    });
    const d = await r.json();
    if (d.success) {
      showToast('Cosmético equipado correctamente', 'success');
      await Gam.init(); // Recargar data
      Gam.renderWidget();
      if (Gam.data) aplicarCosmeticos(Gam.data);
      this.filter(currentDrawerTipo);
      
      // Actualizar el dot de equipado en los botones rápidos
      actualizarDotsCosmeticos();
    } else {
      showToast(d.mensaje||'Error al equipar.', 'error');
    }
  }
};

function actualizarDotsCosmeticos() {
  if (!Gam.data) return;
  ['fondo_perfil', 'marco_avatar', 'efecto_avatar'].forEach(tipo => {
    const equipado = (Gam.data.cosmeticos||[]).find(c => c.tipo === tipo && c.equipado);
    const accion = { marco_avatar:'marco', fondo_perfil:'fondo', efecto_avatar:'efecto' }[tipo];
    const btn = document.querySelector(`.pf-cos-quick-btn[data-cos-type="${accion}"]`);
    if (btn) {
      let dot = btn.querySelector('.pf-cos-equipped-dot');
      if (equipado) {
        if (!dot) {
          dot = document.createElement('div');
          dot.className = 'pf-cos-equipped-dot';
          btn.appendChild(dot);
        }
      } else {
        if (dot) dot.remove();
      }
    }
  });
}

// ── Control de Pestañas ────────────────────────────────────
document.querySelectorAll('.pf-tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.pf-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.pf-section-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    
    const panel = document.getElementById('tab-' + btn.dataset.tab);
    if (panel) panel.classList.add('active');

    if (btn.dataset.tab === 'propuestas') loadMisProposals();
    if (btn.dataset.tab === 'gamificacion') Gam.init();
  });
});

// ── Cargar Datos del Perfil ────────────────────────────────
let _propuestasCache = [];

async function loadProfileData() {
  try {
    const r = await fetch('php/auth.php?accion=perfil');
    const d = await r.json();
    if (d.success && d.usuario) {
      const u = d.usuario;

      // Inputs Básicos
      document.getElementById('editNombre').value   = u.nombre || '';
      document.getElementById('editApellido').value = u.apellido || '';
      document.getElementById('editEmail').value    = u.email || '';
      document.getElementById('editBio').value      = u.bio || '';
      document.getElementById('bioCount').textContent = (u.bio || '').length;

      // Cabecera Displays
      const nombreCompleto = (u.nombre + ' ' + (u.apellido || '')).trim();
      document.getElementById('profileDisplayName').textContent = nombreCompleto;
      document.getElementById('profileDisplayEmail').innerHTML = `<i class="fas fa-envelope"></i> ${u.email}`;
      document.getElementById('mockName').textContent = nombreCompleto;

      // Insignia
      const insignia = u.insignia || '🌱';
      document.getElementById('editInsignia').value = insignia;
      document.getElementById('profileInsigniaDisplay').textContent = insignia;
      document.getElementById('mockInsignia').textContent = insignia;

      // Frase
      if (u.frase) {
        document.getElementById('editFrase').value = u.frase;
        document.getElementById('profileDisplayFrase').textContent = `"${u.frase}"`;
        document.getElementById('profileFraseBox').style.display = 'flex';
        document.getElementById('mockFrase').textContent = `"${u.frase}"`;
      } else {
        document.getElementById('profileFraseBox').style.display = 'none';
        document.getElementById('mockFrase').textContent = '"Tu voz transforma el mundo"';
      }

      // Ubicación
      if (u.ubicacion) {
        document.getElementById('editUbicacion').value = u.ubicacion;
        document.getElementById('metaUbicacion').textContent = u.ubicacion;
        document.getElementById('metaUbicacionWrap').style.display = 'flex';
      } else {
        document.getElementById('metaUbicacionWrap').style.display = 'none';
      }

      // Sitio Web & Redes
      document.getElementById('editSitioWeb').value = u.sitio_web || '';
      document.getElementById('editTwitter').value   = u.social_twitter || '';
      document.getElementById('editInstagram').value = u.social_instagram || '';
      document.getElementById('editGithub').value    = u.social_github || '';

      renderSocialLink('socialTwitterLink', 'socialTwitterHandle', u.social_twitter, 'https://twitter.com/');
      renderSocialLink('socialInstagramLink', 'socialInstagramHandle', u.social_instagram, 'https://instagram.com/');
      renderSocialLink('socialGithubLink', 'socialGithubHandle', u.social_github, 'https://github.com/');

      // Visibilidad
      const esPublico = (u.perfil_publico !== false);
      document.getElementById('editPerfilPublico').checked = esPublico;
      document.getElementById('profileVisibilityBadge').innerHTML = esPublico
        ? '<i class="fas fa-globe"></i> Perfil público'
        : '<i class="fas fa-lock"></i> Perfil privado';

      // Tema y Colores
      setTema(u.tema_perfil || 'verde');
      setMarco(u.marco_avatar || 'circulo');
      
      const acento = u.color_perfil || '#36c0a1';
      const banner = u.color_banner || '#0f1c19';
      document.getElementById('editColorPerfil').value = acento;
      document.getElementById('editColorPerfilHex').value = acento.toUpperCase();
      document.getElementById('editColorBanner').value = banner;
      document.getElementById('editColorBannerHex').value = banner.toUpperCase();
      aplicarPreview(banner, acento);

      // Estadísticas
      if (u.propuestas !== undefined) {
        document.getElementById('statMisProp').textContent   = u.propuestas || 0;
        document.getElementById('statMisVotos').textContent  = u.votos_recibidos || 0;
        document.getElementById('statMisVistas').textContent = u.vistas_totales || 0;
        document.getElementById('statDesafios').textContent  = u.desafios_completados || 0;
        document.getElementById('badgeTabPropuestas').textContent = u.propuestas || 0;
      }

      // Avatar
      if (u.avatar) {
        const imgHtml = `<img src="${u.avatar}" alt="Avatar">`;
        document.getElementById('profileAvatarDisplay').innerHTML = imgHtml;
        document.getElementById('mockAvatar').innerHTML = imgHtml;
        if (window.refreshNavAvatar) window.refreshNavAvatar(u.avatar);
      }

      aplicarCosmeticos(u);
    }
  } catch (e) {
    console.error('Error al cargar perfil:', e);
  }
}
loadProfileData();

// ── Helpers de Redes Sociales ─────────────────────────────
function renderSocialLink(linkId, handleId, username, baseUrl) {
  const elLink = document.getElementById(linkId);
  const elHandle = document.getElementById(handleId);
  if (username && username.trim()) {
    const clean = username.trim().replace(/^@/, '');
    elLink.href = baseUrl + clean;
    elHandle.textContent = '@' + clean;
    elLink.style.display = 'inline-flex';
  } else {
    elLink.style.display = 'none';
  }
}

// ── Personalización Visual (Studio) ────────────────────────
function setTema(t) {
  const et = document.getElementById('editTema');
  if (et) et.value = t;
  document.querySelectorAll('#themeGrid .pf-theme-card-opt').forEach(c => {
    c.classList.toggle('active', c.dataset.tema === t);
  });
}

function setMarco(m) {
  document.getElementById('editMarco').value = m;
  document.querySelectorAll('#frameGrid .pf-frame-card-opt').forEach(c => {
    c.classList.toggle('active', c.dataset.marco === m);
  });
  const ava = document.getElementById('profileAvatarDisplay');
  const mockAva = document.getElementById('mockAvatar');
  [ava, mockAva].forEach(el => {
    if (el) {
      el.classList.remove('marco-circulo', 'marco-cuadrado', 'marco-hexagono', 'marco-estrella');
      el.classList.add('marco-' + m);
    }
  });
}

function setInsignia(emoji) {
  document.getElementById('editInsignia').value = emoji;
  document.getElementById('profileInsigniaDisplay').textContent = emoji;
  document.getElementById('mockInsignia').textContent = emoji;
}

function agregarBioTag(tag) {
  const bio = document.getElementById('editBio');
  if (!bio.value.includes(tag)) {
    bio.value = (bio.value.trim() + ' ' + tag).trim();
    document.getElementById('bioCount').textContent = bio.value.length;
  }
}

function aplicarPreview(banner, acento) {
  const hero = document.getElementById('profileHeroBanner');
  const mock = document.getElementById('mockBanner');
  const grad = `linear-gradient(135deg, ${banner || '#0f1c19'}, ${acento || '#36c0a1'})`;
  if (hero && banner) hero.style.background = grad;
  if (mock && banner) mock.style.background = grad;
  document.documentElement.style.setProperty('--pf-accent', acento || '#36c0a1');
  document.documentElement.style.setProperty('--pf-banner', banner || '#0f1c19');
}

// Event Listeners de Estudio de Diseño
document.querySelectorAll('#themeGrid .pf-theme-card-opt').forEach(c => {
  c.addEventListener('click', () => {
    const t = c.dataset.tema;
    setTema(t);
    const map = {
      verde:   { acento: '#36c0a1', banner: '#0f1c19' },
      naranja: { acento: '#ef7e22', banner: '#1f1309' },
      azul:    { acento: '#3b82f6', banner: '#091528' },
      morado:  { acento: '#a855f7', banner: '#1b0a2a' },
      rosa:    { acento: '#ec4899', banner: '#270817' },
      dark:    { acento: '#4b5563', banner: '#111827' }
    };
    const pair = map[t] || map.verde;
    document.getElementById('editColorPerfil').value = pair.acento;
    document.getElementById('editColorPerfilHex').value = pair.acento.toUpperCase();
    document.getElementById('editColorBanner').value = pair.banner;
    document.getElementById('editColorBannerHex').value = pair.banner.toUpperCase();
    aplicarPreview(pair.banner, pair.acento);
  });
});

document.querySelectorAll('#frameGrid .pf-frame-card-opt').forEach(c => {
  c.addEventListener('click', () => setMarco(c.dataset.marco));
});

['editColorPerfil', 'editColorBanner'].forEach(id => {
  const el = document.getElementById(id);
  const hexEl = document.getElementById(id + 'Hex');
  el.addEventListener('input', () => {
    hexEl.value = el.value.toUpperCase();
    aplicarPreview(document.getElementById('editColorBanner').value, document.getElementById('editColorPerfil').value);
  });
  hexEl.addEventListener('input', () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(hexEl.value)) {
      el.value = hexEl.value;
      aplicarPreview(document.getElementById('editColorBanner').value, document.getElementById('editColorPerfil').value);
    }
  });
});

document.getElementById('editBio').addEventListener('input', function() {
  document.getElementById('bioCount').textContent = this.value.length;
});

document.getElementById('editInsignia').addEventListener('input', function() {
  const val = this.value || '🌱';
  document.getElementById('profileInsigniaDisplay').textContent = val;
  document.getElementById('mockInsignia').textContent = val;
});

document.getElementById('editFrase').addEventListener('input', function() {
  document.getElementById('mockFrase').textContent = this.value ? `"${this.value}"` : '"Tu voz transforma el mundo"';
});

// ── Cosméticos (Marcos / Fondos / Efectos) ─────────────────
function aplicarCosmeticos(u) {
  const marco  = u.marco_clase  || (u.marco_equipado  ? u.marco_equipado.replace(/_/g,'-')  : null);
  const fondo  = u.fondo_clase  || (u.fondo_equipado  ? u.fondo_equipado.replace(/_/g,'-')  : null);
  const efecto = u.efecto_clase || (u.efecto_equipado ? u.efecto_equipado.replace(/_/g,'-') : null);
  const quitar = (el, pref) => [...el.classList].filter(c => c.startsWith(pref)).forEach(c => el.classList.remove(c));

  document.querySelectorAll('#profileAvatarDisplay, #mockAvatar, .profile-avatar').forEach(el => {
    quitar(el, 'marco-');
    quitar(el, 'efecto-');
    if (marco) el.classList.add(marco);

    let capa = el.querySelector(':scope > .cos-fx');
    if (efecto) {
      if (!capa) { capa = document.createElement('span'); capa.className = 'cos-fx'; el.appendChild(capa); }
      quitar(capa, 'efecto-');
      capa.classList.add(efecto);
      el.classList.add('tiene-fx');
    } else if (capa) {
      capa.remove();
      el.classList.remove('tiene-fx');
    }
  });

  if (fondo) {
    const hero = document.getElementById('profileHeroBanner');
    if (hero) {
      [...hero.classList].filter(c => c.startsWith('fondo-')).forEach(c => hero.classList.remove(c));
      hero.classList.add(fondo);
    }
  }
}

// ── Guardar Perfil ────────────────────────────────────────
document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardarPerfil');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

  const data = {
    accion: 'actualizar_perfil',
    nombre: document.getElementById('editNombre').value.trim(),
    apellido: document.getElementById('editApellido').value.trim(),
    email: document.getElementById('editEmail').value.trim(),
    bio: document.getElementById('editBio').value.trim(),
    frase: document.getElementById('editFrase').value.trim(),
    ubicacion: document.getElementById('editUbicacion').value.trim(),
    sitio_web: document.getElementById('editSitioWeb').value.trim(),
    social_twitter: document.getElementById('editTwitter').value.trim(),
    social_instagram: document.getElementById('editInstagram').value.trim(),
    social_github: document.getElementById('editGithub').value.trim(),
    perfil_publico: document.getElementById('editPerfilPublico').checked,
    tema_perfil: document.getElementById('editTema').value,
    color_perfil: document.getElementById('editColorPerfil').value,
    color_banner: document.getElementById('editColorBanner').value,
    marco_avatar: document.getElementById('editMarco').value,
    insignia: document.getElementById('editInsignia').value.trim() || '🌱'
  };

  try {
    const r = await fetch('php/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const d = await r.json();
    if (d.success) {
      showToast('¡Perfil actualizado con éxito!', 'success');
      loadProfileData();
    } else {
      showToast(d.mensaje || 'Error al actualizar el perfil', 'error');
    }
  } catch (err) {
    showToast('Error de conexión con el servidor', 'error');
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar cambios';
});

// ── Cambiar Avatar ────────────────────────────────────────
async function changeAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 2 * 1024 * 1024) {
    showToast('La imagen no puede superar 2MB', 'error');
    return;
  }
  const reader = new FileReader();
  reader.onload = async e => {
    const base64 = e.target.result;
    const imgHtml = `<img src="${base64}" alt="Avatar">`;
    document.getElementById('profileAvatarDisplay').innerHTML = imgHtml;
    document.getElementById('mockAvatar').innerHTML = imgHtml;
    
    try {
      const r = await fetch('php/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'actualizar_avatar', avatar: base64 })
      });
      const d = await r.json();
      if (d.success) {
        showToast('Foto de perfil actualizada correctamente', 'success');
        if (window.refreshNavAvatar) window.refreshNavAvatar(base64);
      } else {
        showToast(d.mensaje || 'Error al actualizar foto', 'error');
      }
    } catch (err) {
      showToast('Error de conexión', 'error');
    }
  };
  reader.readAsDataURL(file);
}

// ── Mis Propuestas ────────────────────────────────────────
async function loadMisProposals() {
  const container = document.getElementById('misProposals');
  try {
    const r = await fetch('php/propuestas.php?accion=mis_propuestas');
    const d = await r.json();
    if (!d.success || !d.propuestas || d.propuestas.length === 0) {
      _propuestasCache = [];
      container.innerHTML = `
        <div class="pf-empty-state">
          <div class="pf-empty-icon"><i class="fas fa-lightbulb"></i></div>
          <h4 class="pf-empty-title">Aún no has creado propuestas</h4>
          <p class="pf-empty-desc">Tus ideas tienen el poder de transformar tu comunidad. ¡Publica tu primera propuesta ciudadana hoy!</p>
          <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus"></i> Crear mi primera propuesta</a>
        </div>`;
      return;
    }
    _propuestasCache = d.propuestas;
    renderPropuestasLista(_propuestasCache);
  } catch (err) {
    container.innerHTML = '<div class="pf-empty-state"><p style="color:var(--text-muted)">Error al cargar tus propuestas.</p></div>';
  }
}

function filtrarMisPropuestas(estado) {
  document.querySelectorAll('#propuestasFiltros .pf-filter-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  if (estado === 'todas') {
    renderPropuestasLista(_propuestasCache);
  } else {
    const filtradas = _propuestasCache.filter(p => p.estado === estado);
    renderPropuestasLista(filtradas);
  }
}

function renderPropuestasLista(lista) {
  const container = document.getElementById('misProposals');
  if (!lista.length) {
    container.innerHTML = `
      <div class="pf-empty-state">
        <div class="pf-empty-icon"><i class="fas fa-folder-open"></i></div>
        <h4 class="pf-empty-title">No hay propuestas en esta categoría</h4>
        <p class="pf-empty-desc">Explora otras secciones o crea una nueva iniciativa.</p>
      </div>`;
    return;
  }

  container.innerHTML = lista.map(p => `
    <div class="pf-proposal-card">
      <div class="pf-proposal-main">
        <div class="pf-proposal-cat-row">
          <span class="pf-cat-tag">
            <i class="${p.categoria_icono || 'fas fa-tag'}"></i> ${p.categoria || 'General'}
          </span>
          <span class="estado-chip estado-${p.estado}">
            ${p.estado === 'activa' ? 'En debate' : p.estado.replace('_', ' ')}
          </span>
        </div>
        <a href="propuesta.php?id=${p.id}" class="pf-proposal-title">${p.titulo}</a>
        <div class="pf-proposal-meta">
          <span class="pf-proposal-meta-item"><i class="fas fa-heart" style="color:var(--naranja)"></i> ${p.votos} votos</span>
          <span class="pf-proposal-meta-item"><i class="fas fa-eye" style="color:var(--verde)"></i> ${p.vistas} vistas</span>
          <span class="pf-proposal-meta-item"><i class="fas fa-calendar"></i> ${p.fecha_formateada || 'Reciente'}</span>
        </div>
      </div>
      <div class="pf-proposal-actions">
        <a href="propuesta.php?id=${p.id}" class="btn btn-sm btn-ghost" title="Ver propuesta"><i class="fas fa-eye"></i></a>
        <a href="crear.php?editar=${p.id}" class="btn btn-sm btn-outline" title="Editar propuesta"><i class="fas fa-pen-to-square"></i></a>
      </div>
    </div>
  `).join('');
}

// ── Medidor de Fuerza de Contraseña ───────────────────────
function evaluarPassword(p) {
  let score = 0;
  const reqLongitud = document.getElementById('reqLongitud');
  const reqNumero   = document.getElementById('reqNumero');
  const reqMayus    = document.getElementById('reqMayus');
  const reqEspecial = document.getElementById('reqEspecial');

  // Longitud
  if (p.length >= 8) { score++; updateCheckItem(reqLongitud, true); }
  else updateCheckItem(reqLongitud, false);

  // Número
  if (/\d/.test(p)) { score++; updateCheckItem(reqNumero, true); }
  else updateCheckItem(reqNumero, false);

  // Mayúscula
  if (/[A-Z]/.test(p)) { score++; updateCheckItem(reqMayus, true); }
  else updateCheckItem(reqMayus, false);

  // Símbolo
  if (/[^A-Za-z0-9]/.test(p)) { score++; updateCheckItem(reqEspecial, true); }
  else updateCheckItem(reqEspecial, false);

  // Pintar barras
  for (let i = 1; i <= 4; i++) {
    const bar = document.getElementById('strBar' + i);
    bar.className = 'pf-strength-bar';
    if (i <= score) bar.classList.add('active-' + score);
  }

  const label = document.getElementById('strText');
  const textos = ['Muy débil', 'Débil', 'Buena', '¡Excelente y segura!'];
  label.textContent = p.length ? (textos[score - 1] || 'Muy débil') : 'Ingresa una nueva contraseña';
}

function updateCheckItem(el, valid) {
  el.className = 'pf-check-item' + (valid ? ' valid' : '');
  el.querySelector('i').className = valid ? 'fas fa-circle-check' : 'fas fa-circle-xmark';
}

function togglePass(id) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// ── Cambiar Contraseña ────────────────────────────────────
document.getElementById('changePassForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const nueva   = document.getElementById('passNueva').value;
  const confirm = document.getElementById('passConfirm').value;
  if (nueva !== confirm) { showToast('Las contraseñas no coinciden', 'error'); return; }
  if (nueva.length < 8)  { showToast('La contraseña debe tener al menos 8 caracteres', 'error'); return; }

  const btn = this.querySelector('[type=submit]');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';

  try {
    const r = await fetch('php/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        accion: 'cambiar_password',
        pass_actual: document.getElementById('passActual').value,
        pass_nueva: nueva
      })
    });
    const d = await r.json();
    if (d.success) {
      showToast('¡Contraseña actualizada con éxito!', 'success');
      this.reset();
      evaluarPassword('');
    } else {
      showToast(d.mensaje || 'Error al cambiar contraseña', 'error');
    }
  } catch (err) {
    showToast('Error de conexión', 'error');
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-shield-halved"></i> Actualizar contraseña';
});

// ── Análisis de CIVI (Coach con IA) ───────────────────────
let _civiAnalisisCargado = false;
async function loadCiviAnalisis() {
  if (_civiAnalisisCargado) return;
  const box = document.getElementById('civiAnalisis');
  const esc = (x) => { const e = document.createElement('div'); e.textContent = x ?? ''; return e.innerHTML; };
  try {
    const res = await fetch('php/ia.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'coach' }),
    });
    const d = await res.json();
    if (!d || !d.success) {
      box.innerHTML = '<div class="civi-an-loading">No se pudo cargar tu análisis ahora mismo. Vuelve a intentarlo en un momento.</div>';
      return;
    }
    _civiAnalisisCargado = true;

    const s = d.stats || {};
    const estilos  = { comentarista: 'Comentarista', proponente: 'Proponente Innovador', debatiente: 'Debatiente Cívico', equilibrado: 'Ciudadano Equilibrado', nuevo: 'Explorador' };
    const aspectos = { creativa: 'Creatividad', argumentada: 'Buenos Argumentos', comunidad: 'Enfoque Comunitario', factible: 'Propuestas Factibles', innovadora: 'Innovación' };

    const chips = [];
    if (s.estilo) chips.push(`<span class="civi-an-chip"><i class="fas fa-user-tag"></i> Estilo: <b>${esc(estilos[s.estilo] || s.estilo)}</b></span>`);
    if (s.categoria_favorita) chips.push(`<span class="civi-an-chip"><i class="fas fa-tag"></i> Tema fuerte: <b>${esc(s.categoria_favorita)}</b></span>`);
    if (s.aspecto_fuerte) chips.push(`<span class="civi-an-chip"><i class="fas fa-star"></i> Destacas por: <b>${esc(aspectos[s.aspecto_fuerte] || s.aspecto_fuerte)}</b></span>`);
    chips.push(`<span class="civi-an-chip"><i class="fas fa-arrow-trend-up"></i> Nivel <b>${s.nivel || 1}</b></span>`);
    if (s.racha) chips.push(`<span class="civi-an-chip"><i class="fas fa-fire"></i> Racha <b>${s.racha} días</b></span>`);

    const o = d.objetivo || {};
    const objHtml = o.titulo ? `
      <div style="margin-top:1.25rem">
        <div class="civi-an-section-t">Tu objetivo ahora</div>
        <div class="civi-an-objetivo">
          <i class="fas fa-bullseye ic"></i>
          <div style="flex:1;min-width:0">
            <h4>${esc(o.titulo)}</h4>
            <p>${esc(o.descripcion || '')}</p>
            ${o.cta_texto && o.cta_url ? `<a class="btn btn-primary btn-sm" href="${esc(o.cta_url)}"><i class="fas fa-arrow-right"></i> ${esc(o.cta_texto)}</a>` : ''}
          </div>
        </div>
      </div>` : '';

    const prog = d.progreso || [];
    const progHtml = prog.length ? `
      <div style="margin-top:1.25rem">
        <div class="civi-an-section-t">Tu progreso y habilidades</div>
        <div class="civi-an-progreso">
          ${prog.map((p) => `<div class="civi-an-prog-item"><i class="fas ${esc(p.icono || 'fa-circle-check')}"></i> ${esc(p.texto)}</div>`).join('')}
        </div>
      </div>` : '';

    box.innerHTML = `
      <div class="civi-an-head">
        <div class="civi-an-ava"><i class="fas fa-robot"></i></div>
        <div>
          <div class="civi-an-title">Análisis de CIVI · Coach Cívico</div>
          <div class="civi-an-sub">${esc(d.saludo || 'Esto es lo que veo en tu progreso.')}</div>
        </div>
      </div>
      <div class="civi-an-parrafo">${esc(d.analisis || '')}</div>
      ${chips.length ? `<div class="civi-an-chips" style="margin-top:.75rem">${chips.join('')}</div>` : ''}
      ${objHtml}
      ${progHtml}
    `;
  } catch (e) {
    box.innerHTML = '<div class="civi-an-loading">No se pudo conectar con CIVI en este momento.</div>';
  }
}

// ── Gamificación Completa ──────────────────────────────────
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

  renderWidget() {
    const d = this.data;
    if (!d) return;
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
      const titHtml = `<span class="titulo-chip ${d.titulo.rareza}" style="color:${d.titulo.color};border-color:${d.titulo.color}">${d.titulo.nombre}</span>`;
      document.getElementById('gamTituloWrap').innerHTML = titHtml;
      document.getElementById('gamTituloDisplayHero').innerHTML = titHtml;
    }
  },

  renderMisiones() {
    const lista = (this.data.misiones||[]).filter(m => m.tipo === this.misionFiltro);
    const el = document.getElementById('gamMisionesList');
    if (!lista.length) {
      el.innerHTML = '<div class="pf-empty-state"><i class="fas fa-check-circle" style="font-size:2rem;color:var(--verde);margin-bottom:.5rem;display:block"></i><p>¡Has completado todas las misiones disponibles!</p></div>';
      return;
    }
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

  renderCosmeticos(tipo) {
    const lista = (this.data.cosmeticos||[]).filter(c => c.tipo === tipo);
    const el = document.getElementById('gamCosmeticosList');
    if (!lista.length) { el.innerHTML = '<p style="color:var(--text-muted);font-size:.85rem">Todavía no hay cosméticos de este tipo.</p>'; return; }

    const accion = { marco_avatar:'marco', fondo_perfil:'fondo', efecto_avatar:'efecto' }[tipo];
    const esFondo = tipo === 'fondo_perfil';

    el.innerHTML = lista.map(c => {
      const clases = ['cos-card', 'rar-' + c.rareza];
      if (!c.desbloqueado) clases.push('bloqueado');
      if (c.equipado)      clases.push('equipado');
      if (c.misterioso)    clases.push('misterioso');

      let preview;
      if (c.misterioso) {
        preview = `<div class="cos-preview${esFondo?' es-fondo':''}">?</div>`;
      } else if (esFondo) {
        preview = `<div class="cos-preview es-fondo pf-hero ${c.valor}"></div>`;
      } else {
        preview = `<div class="cos-preview ${c.valor}">${this.iniciales()}</div>`;
      }

      const req = c.desbloqueado
        ? '<span style="color:var(--verde)"><i class="fas fa-check"></i> Desbloqueado</span>'
        : `<i class="fas fa-lock"></i> ${this.esc(c.requisito||('Nivel '+c.nivel_requerido))}`;

      return `
        <div class="${clases.join(' ')}" data-clave="${c.clave}"
             onclick="Gam.equipar('${accion}','${c.clave}',${c.desbloqueado?'true':'false'})"
             title="${c.desbloqueado ? 'Clic para equipar' : 'Bloqueado'}">
          ${preview}
          <div class="cos-nombre">${this.esc(c.nombre)}</div>
          <div class="cos-rareza">${c.rareza}</div>
          <div class="cos-desc">${this.esc(c.descripcion||'')}</div>
          <div class="cos-req">${req}</div>
        </div>`;
    }).join('');
    this.montarPreviews();
  },

  montarPreviews() {
    requestAnimationFrame(() => {
      if (window.CosFondos) { window.CosFondos.escanear(); window.CosFondos.remedir(); }
      if (window.CosMarcos) window.CosMarcos.escanear();
      if (window.CosEfectos) window.CosEfectos.montarPreviews();
    });
  },

  iniciales() {
    const el = document.getElementById('profileInitials');
    return el ? el.textContent.trim().slice(0,2) : 'CV';
  },

  filtrarCosmeticos(tipo) {
    this.cosmeticoFiltro = tipo;
    const botones = { marco_avatar:'btnMarco', fondo_perfil:'btnFondo', efecto_avatar:'btnEfecto' };
    Object.entries(botones).forEach(([t, id]) => {
      const b = document.getElementById(id);
      if (b) b.className = (t === tipo) ? 'btn btn-sm btn-outline' : 'btn btn-sm btn-ghost';
    });
    this.renderCosmeticos(tipo);
  },

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

  async equipar(tipo, clave, desbloqueado) {
    if (desbloqueado === false) {
      showToast('Todavía no has desbloqueado este cosmético', 'info');
      return;
    }
    const r = await fetch('php/gamificacion.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({accion:'equipar',tipo,clave})
    });
    const d = await r.json();
    if (d.success) {
      showToast('Cosmético equipado correctamente', 'success');
      await this.init();
      this.renderWidget();
      this.renderTitulos();
      this.renderCosmeticos(this.cosmeticoFiltro);
      if (this.data) aplicarCosmeticos(this.data);
      if (window.CosRefrescar) window.CosRefrescar();
    } else {
      showToast(d.mensaje||'No puedes equipar ese ítem', 'error');
    }
  },

  esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
};

// Subpestañas de Gamificación Listeners
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

// ── Toasts de Notificación ────────────────────────────────
function showToast(msg, type='info') {
  if (window.Toast) { Toast.show(msg, type); return; }
  const d = document.createElement('div');
  d.className = 'toast';
  d.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'} toast-icon ${type}"></i><span class="toast-msg">${msg}</span>`;
  document.querySelector('.toast-container').appendChild(d);
  setTimeout(() => { d.classList.add('removing'); setTimeout(() => d.remove(), 300); }, 3500);
}
