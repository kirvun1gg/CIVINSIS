<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Términos de Uso – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* ── Legal page layout ─────────────────────────────── */
    .legal-hero {
      background: var(--grad-hero);
      padding: calc(var(--nav-height) + 4rem) 0 4rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .legal-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(54,192,161,.13) 0%, transparent 70%);
      pointer-events: none;
    }
    .legal-hero-badge {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(54,192,161,.12); border: 1px solid rgba(54,192,161,.25);
      color: var(--verde); border-radius: 100px;
      font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
      padding: .35rem 1rem; margin-bottom: 1.25rem;
    }
    .legal-hero h1 {
      font-family: var(--font-display);
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 800;
      color: #fff;
      margin-bottom: .75rem;
    }
    .legal-hero h1 span {
      background: var(--grad-primary);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .legal-hero-meta {
      color: rgba(255,255,255,.45);
      font-size: .85rem;
      display: flex; align-items: center; justify-content: center; gap: 1.5rem;
      flex-wrap: wrap;
    }
    .legal-hero-meta span { display: flex; align-items: center; gap: .4rem; }

    /* ── Body layout ───────────────────────────────────── */
    .legal-wrap {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 2.5rem;
      padding: 3.5rem 0 5rem;
      align-items: start;
    }
    @media (max-width: 768px) {
      .legal-wrap { grid-template-columns: 1fr; }
      .legal-toc { display: none; }
    }

    /* ── Table of Contents ─────────────────────────────── */
    .legal-toc {
      position: sticky; top: calc(var(--nav-height) + 1.5rem);
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      box-shadow: var(--shadow);
    }
    .legal-toc-title {
      font-family: var(--font-display); font-weight: 700;
      font-size: .8rem; letter-spacing: .08em; text-transform: uppercase;
      color: var(--text-muted); margin-bottom: 1rem;
    }
    .legal-toc a {
      display: flex; align-items: center; gap: .6rem;
      padding: .55rem .75rem; border-radius: var(--radius);
      font-size: .85rem; color: var(--text-2);
      transition: var(--trans);
      border-left: 2px solid transparent;
    }
    .legal-toc a:hover, .legal-toc a.active {
      background: var(--verde-alpha);
      color: var(--verde);
      border-left-color: var(--verde);
    }
    .legal-toc a i { width: 16px; text-align: center; font-size: .8rem; opacity: .7; }

    /* ── Content ───────────────────────────────────────── */
    .legal-content { min-width: 0; }

    .legal-section {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 2rem 2.5rem;
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow);
      scroll-margin-top: calc(var(--nav-height) + 1.5rem);
      transition: border-color .3s;
    }
    .legal-section:hover { border-color: rgba(54,192,161,.25); }

    .legal-section-header {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 1.25rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border);
    }
    .legal-section-icon {
      width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
    }
    .legal-section-icon.green  { background: var(--verde-alpha);   color: var(--verde); }
    .legal-section-icon.orange { background: var(--naranja-alpha);  color: var(--naranja); }
    .legal-section-num {
      font-family: var(--font-display); font-size: .75rem; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase;
      color: var(--text-muted);
    }
    .legal-section h2 {
      font-family: var(--font-display); font-weight: 700;
      font-size: 1.2rem; color: var(--text);
    }
    .legal-section p {
      color: var(--text-2); line-height: 1.8; font-size: .95rem;
      margin-bottom: .9rem;
    }
    .legal-section p:last-child { margin-bottom: 0; }
    .legal-section ul {
      list-style: none; padding: 0;
      display: flex; flex-direction: column; gap: .5rem;
      margin: .75rem 0 .9rem;
    }
    .legal-section ul li {
      display: flex; align-items: flex-start; gap: .6rem;
      color: var(--text-2); font-size: .95rem; line-height: 1.7;
    }
    .legal-section ul li::before {
      content: '';
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--grad-primary);
      flex-shrink: 0; margin-top: .55rem;
    }
    .legal-highlight {
      background: var(--grad-soft);
      border: 1px solid rgba(54,192,161,.2);
      border-radius: var(--radius);
      padding: 1rem 1.25rem;
      margin: 1rem 0;
      font-size: .9rem; color: var(--text-2);
      display: flex; gap: .75rem; align-items: flex-start;
    }
    .legal-highlight i { color: var(--verde); margin-top: .15rem; flex-shrink: 0; }

    /* ── Related pages strip ───────────────────────────── */
    .legal-related {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem; margin-top: 2rem;
    }
    .legal-related-card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 1.25rem 1.5rem;
      display: flex; align-items: center; gap: 1rem;
      transition: var(--trans); box-shadow: var(--shadow);
    }
    .legal-related-card:hover {
      border-color: rgba(54,192,161,.35);
      transform: translateY(-3px); box-shadow: var(--shadow-color);
    }
    .legal-related-card .icon {
      width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
      background: var(--verde-alpha); color: var(--verde);
      display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .legal-related-card .icon.orange { background: var(--naranja-alpha); color: var(--naranja); }
    .legal-related-card strong { font-family: var(--font-display); font-size: .9rem; color: var(--text); display: block; }
    .legal-related-card span   { font-size: .78rem; color: var(--text-muted); }
  </style>
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero -->
<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-file-contract"></i> Documento legal</div>
    <h1>Términos de <span>Uso</span></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> Última actualización: enero 2025</span>
      <span><i class="fas fa-clock"></i> Lectura estimada: 8 min</span>
      <span><i class="fas fa-globe-americas"></i> Aplicable en toda la plataforma</span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <!-- Sidebar TOC -->
    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i>Contenido</div>
      <nav id="tocNav">
        <a href="#aceptacion"><i class="fas fa-check-circle"></i> Aceptación</a>
        <a href="#uso-plataforma"><i class="fas fa-laptop"></i> Uso de la plataforma</a>
        <a href="#cuentas"><i class="fas fa-user-circle"></i> Cuentas de usuario</a>
        <a href="#contenido"><i class="fas fa-file-alt"></i> Contenido publicado</a>
        <a href="#propiedad"><i class="fas fa-copyright"></i> Propiedad intelectual</a>
        <a href="#prohibiciones"><i class="fas fa-ban"></i> Conductas prohibidas</a>
        <a href="#privacidad"><i class="fas fa-shield-alt"></i> Privacidad</a>
        <a href="#responsabilidad"><i class="fas fa-balance-scale"></i> Responsabilidad</a>
        <a href="#modificaciones"><i class="fas fa-edit"></i> Modificaciones</a>
        <a href="#contacto"><i class="fas fa-envelope"></i> Contacto</a>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="legal-content">

      <div class="legal-section" id="aceptacion">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="legal-section-num">Sección 01</div>
            <h2>Aceptación de los términos</h2>
          </div>
        </div>
        <p>Bienvenido a CIVINSIS, el Sistema Innovador de Participación Ciudadana. Los presentes Términos y Condiciones de Uso, referidos desde ahora como los Términos, constituyen un acuerdo legalmente vinculante entre el usuario, denominado en este documento como el Usuario, y los desarrolladores de la plataforma, identificados como Grupo N° 4, quienes serán llamados CIVINSIS o los Administradores, un proyecto tecnológico de desarrollo de software enfocado en la innovación cívica y social en la República de El Salvador.</p>

      </div>

      <div class="legal-section" id="uso-plataforma">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-laptop"></i></div>
          <div>
            <div class="legal-section-num">Sección 02</div>
            <h2>Uso de la plataforma</h2>
          </div>
        </div>
        <p>El uso de CIVINSIS debe ser correcto, lícito y orientado al bienestar comunitario. Los administradores se reservan el derecho de suspender de forma inmediata las cuentas que violen estas normas de convivencia.</p>
        <ul>
          <li>Registro obligatorio con correo válido y verificación para evitar perfiles falsos.</li>
          <li>Credenciales personales e intransferibles bajo el cuidado exclusivo de cada usuario.</li>
          <li>Publicación de propuestas verídicas estructuradas con título, descripción y categoría.</li>
          <li>Sistema democrático de un voto único por propuesta sin manipulaciones técnicas.</li>
          <li>Límite de veinticuatro horas para editar o eliminar el contenido publicado.</li>
        </ul>
        <p>Al terminar el plazo de veinticuatro horas el contenido será definitivo. Esto garantiza la total transparencia e integridad de los debates ciudadanos.</p>
      </div>

      <div class="legal-section" id="cuentas">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-user-circle"></i></div>
          <div>
            <div class="legal-section-num">Sección 03</div>
            <h2>Cuentas de usuario</h2>
          </div>
        </div>
        <p>El registro en CIVINSIS otorga la condición de usuario e implica la obligación de administrar la cuenta de manera responsable. Los administradores se reservan el derecho de suspender los perfiles que pongan en riesgo la seguridad de la comunidad digital.</p>
        <ul>
          <li>Proporcionar información veraz, precisa y completa durante el registro.</li>
          <li>Mantener la confidencialidad de tu contraseña y datos de acceso.</li>
          <li>Notificar de inmediato cualquier uso no autorizado de tu cuenta.</li>
          <li>Ser responsable de toda actividad realizada desde tu cuenta.</li>
        </ul>
        <p>La protección de las credenciales es fundamental para garantizar un entorno de participación transparente. El descuido en la seguridad de la cuenta facultará al equipo técnico para restringir el acceso a la plataforma de forma definitiva.</p>
      </div>

      <div class="legal-section" id="contenido">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-file-alt"></i></div>
          <div>
            <div class="legal-section-num">Sección 04</div>
            <h2>Contenido publicado</h2>
          </div>
        </div>
        <p>Todo contenido publicado en CIVINSIS debe promover el diálogo democrático y constructivo. El usuario retiene la autoría de sus ideas pero otorga a la plataforma una licencia gratuita para su difusión cívica.</p>
        <p>Al interactuar o subir material a la plataforma se deben cumplir de forma obligatoria las siguientes directrices de publicación:</p>
        <ul>
          <li>El contenido debe ser original o contar con los permisos necesarios.</li>
          <li>No se permite publicar información falsa, engañosa o difamatoria.</li>
          <li>Las propuestas deben estar relacionadas con el bien común ciudadano.</li>
          <li>Los comentarios deben ser respetuosos y constructivos.</li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-exclamation-triangle" style="color:var(--naranja)"></i>
          <div>CIVINSIS utiliza herramientas de inteligencia artificial para la moderación automatizada y se reserva el derecho de eliminar contenido que viole estas normas sin previo aviso.</div>
        </div>
      </div>

      <div class="legal-section" id="propiedad">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-copyright"></i></div>
          <div>
            <div class="legal-section-num">Sección 05</div>
            <h2>Propiedad intelectual</h2>
          </div>
        </div>
        <p>Todos los elementos gráficos, logotipos, interfaces, diseños, bases de datos y el código fuente general de CIVINSIS pertenecen de forma exclusiva a los desarrolladores del Grupo N° 4. Queda terminantemente prohibida su reproducción, copia, distribución o ingeniería inversa con fines comerciales o ajenos al proyecto sin una autorización previa por escrito.</p>
        <p>Por su parte, el usuario conserva la propiedad intelectual de las propuestas e ideas de mejora ciudadana que decida publicar en la plataforma. No obstante, por el hecho de registrarse y compartir dicho contenido, otorga a CIVINSIS una licencia de uso gratuita, no exclusiva y de ámbito nacional para exhibir el material dentro del sistema con propósitos exclusivamente educativos, sociales y de debate cívico.</p>
      </div>

      <div class="legal-section" id="prohibiciones">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-ban"></i></div>
          <div>
            <div class="legal-section-num">Sección 06</div>
            <h2>Conductas prohibidas</h2>
          </div>
        </div>
        <p>CIVINSIS promueve un espacio digital constructivo, seguro y de respeto mutuo. Queda terminantemente prohibido el uso de la plataforma para realizar cualquiera de las acciones detalladas en el siguiente listado:</p>
        <ul>
          <li>Acosar, amenazar o intimidar a otros usuarios de la plataforma.</li>
          <li>Publicar contenido odioso, discriminatorio o que incite a la violencia.</li>
          <li>Intentar acceder sin autorización a sistemas o datos de terceros.</li>
          <li>Usar la plataforma con fines comerciales no autorizados por CIVINSIS.</li>
          <li>Suplantar la identidad de personas físicas o jurídicas.</li>
          <li>Distribuir malware, spam o cualquier código dañino.</li>
        </ul>
      </div>

      <div class="legal-section" id="privacidad">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-shield-alt"></i></div>
          <div>
            <div class="legal-section-num">Sección 07</div>
            <h2>Privacidad y datos</h2>
          </div>
        </div>
        <p>El tratamiento de tus datos personales se rige por nuestra Política de Privacidad, la cual forma parte integral de estos términos. CIVINSIS se compromete a proteger la información recopilada durante el registro y la verificación para asegurar que el entorno cívico sea confiable y seguro para la juventud salvadoreña.<a href="privacidad.php" style="color:var(--verde);text-decoration:underline">Política de Privacidad</a>, la cual forma parte integral de estos términos.</p>
        <p>Los datos recolectados, tales como el correo electrónico y el nombre de usuario, se procesarán exclusivamente para el funcionamiento interno de la plataforma, el control de las votaciones democráticas y la personalización del sistema de insignias. Bajo ninguna circunstancia se venderá o transferirá esta información a terceras entidades sin el consentimiento expreso del titular.</p>
      </div>

      <div class="legal-section" id="responsabilidad">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-balance-scale"></i></div>
          <div>
            <div class="legal-section-num">Sección 08</div>
            <h2>Limitación de responsabilidad</h2>
          </div>
        </div>
        <p>CIVINSIS se proporciona en el estado técnico en que se encuentra y según su disponibilidad para fines estrictamente académicos y sociales. Los desarrolladores no garantizan la ausencia total de fallos de conectividad, interrupciones del servidor local o imprecisiones derivadas de las herramientas de inteligencia artificial empleadas para la moderación automatizada y la asistencia en la redacción cívica de propuestas.</p>
        <p>Por tanto, el equipo administrador no asume ninguna responsabilidad legal por las opiniones vertidas por los usuarios, las consecuencias de accesos no autorizados por descuidos en las credenciales individuales, los falsos positivos de los filtros automáticos de comentarios, ni por la falta de ejecución material de las propuestas comunitarias por parte de las autoridades reales del país.</p>
      </div>

      <div class="legal-section" id="modificaciones">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-edit"></i></div>
          <div>
            <div class="legal-section-num">Sección 09</div>
            <h2>Modificaciones</h2>
          </div>
        </div>
        <p>CIVINSIS se reserva el derecho de modificar estos Términos de Uso en cualquier momento. Los cambios entrarán en vigor de forma inmediata tras su publicación oficial en esta sección de la plataforma, por lo que se recomienda revisarlos periódicamente.</p>
        <p>El uso continuo de los servicios y herramientas de participación cívica con posterioridad a la publicación de las reformas constituirá la aceptación tácita y obligatoria de los nuevos términos establecidos por el equipo administrador del sistema.</p>
      </div>

      <div class="legal-section" id="contacto">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="legal-section-num">Sección 10</div>
            <h2>Contacto</h2>
          </div>
        </div>
        <p>Si tienes preguntas o dudas sobre estos Términos de Uso, puedes contactarnos a través de nuestra página de <a href="contacto.php" style="color:var(--verde);text-decoration:underline">Contacto</a></p>
      </div>

      <!-- Related pages -->
      <div class="legal-related">
        <a href="privacidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-lock"></i></div>
          <div><strong>Política de Privacidad</strong><span>Cómo tratamos tus datos</span></div>
        </a>
        <a href="comunidad.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-users"></i></div>
          <div><strong>Guía de Comunidad</strong><span>Normas de convivencia</span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-envelope"></i></div>
          <div><strong>Contacto</strong><span>Habla con nosotros</span></div>
        </a>
      </div>

    </main>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>

<script>
// Dark mode
const saved = localStorage.getItem('civitas-theme');
if (saved) document.documentElement.setAttribute('data-theme', saved);
document.querySelectorAll('[data-dark-toggle]').forEach(btn => {
  btn.addEventListener('click', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('civitas-theme', next);
  });
});

// Active TOC on scroll
const sections = document.querySelectorAll('.legal-section');
const tocLinks = document.querySelectorAll('#tocNav a');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      tocLinks.forEach(a => a.classList.remove('active'));
      const link = document.querySelector('#tocNav a[href="#' + e.target.id + '"]');
      if (link) link.classList.add('active');
    }
  });
}, { rootMargin: '-20% 0px -70% 0px' });
sections.forEach(s => observer.observe(s));

// Hamburger
document.getElementById('hamburger')?.addEventListener('click', () => {
  document.getElementById('mobileMenu')?.classList.add('open');
  document.getElementById('mobileOverlay')?.classList.add('open');
});
document.getElementById('mobileMenuClose')?.addEventListener('click', () => {
  document.getElementById('mobileMenu')?.classList.remove('open');
  document.getElementById('mobileOverlay')?.classList.remove('open');
});
document.getElementById('mobileOverlay')?.addEventListener('click', () => {
  document.getElementById('mobileMenu')?.classList.remove('open');
  document.getElementById('mobileOverlay')?.classList.remove('open');
});

function logout() {
  fetch('php/auth.php', { method: 'POST', body: new URLSearchParams({ accion: 'logout' }) })
    .then(r => r.json()).then(d => { if (d.success) window.location.href = 'index.php'; });
}
</script>
</body>
</html>
