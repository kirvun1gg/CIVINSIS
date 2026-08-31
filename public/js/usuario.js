const PERFIL_ID = Number(document.body.dataset.perfilId);

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
          <div class="insignia-tooltip">${escHtml(i.nombre)}</div>
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
            <div class="logro-nombre">${escHtml(l.nombre)}</div>
            <div class="logro-desc">${escHtml(l.descripcion)}</div>
          </div>
        </div>`).join('');
    } else {
      document.getElementById('pubLogros').innerHTML = '<p style="color:var(--text-muted);font-size:.85rem">Este usuario aún no ha desbloqueado logros.</p>';
    }

  } catch(e) {
    document.getElementById('pubName').textContent = 'Error al cargar el perfil';
  }
})();

