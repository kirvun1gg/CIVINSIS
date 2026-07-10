<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Política de Privacidad – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .legal-hero {
      background: var(--grad-hero);
      padding: calc(var(--nav-height) + 4rem) 0 4rem;
      text-align: center; position: relative; overflow: hidden;
    }
    .legal-hero::before {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(239,126,34,.1) 0%, transparent 70%);
      pointer-events: none;
    }
    .legal-hero-badge {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(239,126,34,.12); border: 1px solid rgba(239,126,34,.25);
      color: var(--naranja); border-radius: 100px;
      font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
      padding: .35rem 1rem; margin-bottom: 1.25rem;
    }
    .legal-hero h1 { font-family: var(--font-display); font-size: clamp(2rem,5vw,3.5rem); font-weight: 800; color: #fff; margin-bottom: .75rem; }
    .legal-hero h1 span { background: var(--grad-primary-r); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .legal-hero-meta { color: rgba(255,255,255,.45); font-size: .85rem; display: flex; align-items: center; justify-content: center; gap: 1.5rem; flex-wrap: wrap; }
    .legal-hero-meta span { display: flex; align-items: center; gap: .4rem; }
    .legal-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 2.5rem; padding: 3.5rem 0 5rem; align-items: start; }
    @media (max-width: 768px) { .legal-wrap { grid-template-columns: 1fr; } .legal-toc { display: none; } }
    .legal-toc { position: sticky; top: calc(var(--nav-height) + 1.5rem); background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow); }
    .legal-toc-title { font-family: var(--font-display); font-weight: 700; font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; }
    .legal-toc a { display: flex; align-items: center; gap: .6rem; padding: .55rem .75rem; border-radius: var(--radius); font-size: .85rem; color: var(--text-2); transition: var(--trans); border-left: 2px solid transparent; }
    .legal-toc a:hover, .legal-toc a.active { background: var(--naranja-alpha); color: var(--naranja); border-left-color: var(--naranja); }
    .legal-toc a i { width: 16px; text-align: center; font-size: .8rem; opacity: .7; }
    .legal-content { min-width: 0; }
    .legal-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 2.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow); scroll-margin-top: calc(var(--nav-height) + 1.5rem); transition: border-color .3s; }
    .legal-section:hover { border-color: rgba(239,126,34,.25); }
    .legal-section-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
    .legal-section-icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .legal-section-icon.green  { background: var(--verde-alpha);  color: var(--verde); }
    .legal-section-icon.orange { background: var(--naranja-alpha); color: var(--naranja); }
    .legal-section-num { font-family: var(--font-display); font-size: .75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); }
    .legal-section h2 { font-family: var(--font-display); font-weight: 700; font-size: 1.2rem; color: var(--text); }
    .legal-section p { color: var(--text-2); line-height: 1.8; font-size: .95rem; margin-bottom: .9rem; }
    .legal-section p:last-child { margin-bottom: 0; }
    .legal-section ul { list-style: none; padding: 0; display: flex; flex-direction: column; gap: .5rem; margin: .75rem 0 .9rem; }
    .legal-section ul li { display: flex; align-items: flex-start; gap: .6rem; color: var(--text-2); font-size: .95rem; line-height: 1.7; }
    .legal-section ul li::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--grad-primary-r); flex-shrink: 0; margin-top: .55rem; }
    .legal-highlight { background: var(--grad-soft); border: 1px solid rgba(239,126,34,.2); border-radius: var(--radius); padding: 1rem 1.25rem; margin: 1rem 0; font-size: .9rem; color: var(--text-2); display: flex; gap: .75rem; align-items: flex-start; }
    .legal-highlight i { color: var(--naranja); margin-top: .15rem; flex-shrink: 0; }
    .data-table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: .875rem; }
    .data-table th { background: var(--grad-soft); font-family: var(--font-display); font-size: .75rem; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); padding: .75rem 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    .data-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); color: var(--text-2); line-height: 1.6; vertical-align: top; }
    .data-table tr:last-child td { border-bottom: none; }
    .legal-related { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 1rem; margin-top: 2rem; }
    .legal-related-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; transition: var(--trans); box-shadow: var(--shadow); }
    .legal-related-card:hover { border-color: rgba(239,126,34,.35); transform: translateY(-3px); box-shadow: 0 8px 32px rgba(239,126,34,.18); }
    .legal-related-card .icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; background: var(--naranja-alpha); color: var(--naranja); display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .legal-related-card .icon.green { background: var(--verde-alpha); color: var(--verde); }
    .legal-related-card strong { font-family: var(--font-display); font-size: .9rem; color: var(--text); display: block; }
    .legal-related-card span { font-size: .78rem; color: var(--text-muted); }
  </style>
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-lock"></i> Privacidad</div>
    <h1>Política de <span>Privacidad</span></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> Última actualización: enero 2025</span>
      <span><i class="fas fa-clock"></i> Lectura estimada: 10 min</span>
      <span><i class="fas fa-shield-alt"></i> Tus datos, protegidos</span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i>Contenido</div>
      <nav id="tocNav">
        <a href="#introduccion"><i class="fas fa-info-circle"></i> Introducción</a>
        <a href="#datos-recopilados"><i class="fas fa-database"></i> Datos recopilados</a>
        <a href="#uso-datos"><i class="fas fa-cogs"></i> Uso de los datos</a>
        <a href="#base-legal"><i class="fas fa-gavel"></i> Base legal</a>
        <a href="#compartir"><i class="fas fa-share-alt"></i> Compartir datos</a>
        <a href="#cookies"><i class="fas fa-cookie-bite"></i> Cookies</a>
        <a href="#derechos"><i class="fas fa-user-shield"></i> Tus derechos</a>
        <a href="#retencion"><i class="fas fa-clock"></i> Retención</a>
        <a href="#seguridad"><i class="fas fa-lock"></i> Seguridad</a>
        <a href="#contacto-dpo"><i class="fas fa-envelope"></i> Contacto DPO</a>
      </nav>
    </aside>

    <main class="legal-content">

      <div class="legal-highlight">
        <i class="fas fa-info-circle"></i>
        <div>Este documento contiene texto de ejemplo (<em>Lorem ipsum</em>). Reemplaza cada sección con tu política de privacidad real, redactada conforme a la legislación aplicable en tu país.</div>
      </div>

      <div class="legal-section" id="introduccion">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-info-circle"></i></div>
          <div><div class="legal-section-num">Sección 01</div><h2>Introducción</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. CIVINSIS se compromete a proteger y respetar tu privacidad. Esta política explica cómo recopilamos, usamos y protegemos tu información personal cuando utilizas nuestra plataforma.</p>
        <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate.</p>
      </div>

      <div class="legal-section" id="datos-recopilados">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-database"></i></div>
          <div><div class="legal-section-num">Sección 02</div><h2>Datos que recopilamos</h2></div>
        </div>
        <p>Recopilamos los siguientes tipos de información personal cuando usas CIVINSIS:</p>
        <table class="data-table">
          <thead>
            <tr><th>Tipo de dato</th><th>Ejemplos</th><th>Finalidad</th></tr>
          </thead>
          <tbody>
            <tr><td><strong>Datos de registro</strong></td><td>Nombre, apellido, correo electrónico</td><td>Crear y gestionar tu cuenta</td></tr>
            <tr><td><strong>Datos de perfil</strong></td><td>Biografía, avatar, preferencias</td><td>Personalizar tu experiencia</td></tr>
            <tr><td><strong>Contenido generado</strong></td><td>Propuestas, comentarios, votos</td><td>Funcionamiento de la plataforma</td></tr>
            <tr><td><strong>Datos técnicos</strong></td><td>Dirección IP, tipo de navegador</td><td>Seguridad y estadísticas</td></tr>
            <tr><td><strong>Cookies</strong></td><td>Sesión, preferencias de tema</td><td>Mejorar la experiencia de usuario</td></tr>
          </tbody>
        </table>
        <p>Nulla porttitor accumsan tincidunt. Vivamus suscipit tortor eget felis porttitor volutpat.</p>
      </div>

      <div class="legal-section" id="uso-datos">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-cogs"></i></div>
          <div><div class="legal-section-num">Sección 03</div><h2>Uso de los datos</h2></div>
        </div>
        <p>Utilizamos tu información personal para los siguientes propósitos:</p>
        <ul>
          <li>Proporcionar, operar y mejorar los servicios de la plataforma CIVINSIS.</li>
          <li>Gestionar tu cuenta y autenticar tu identidad de forma segura.</li>
          <li>Enviarte notificaciones relevantes sobre tu actividad en la plataforma.</li>
          <li>Analizar el uso de la plataforma para mejorar funcionalidades.</li>
          <li>Cumplir con obligaciones legales y regulatorias aplicables.</li>
        </ul>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur aliquet quam id dui posuere blandit.</p>
      </div>

      <div class="legal-section" id="base-legal">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-gavel"></i></div>
          <div><div class="legal-section-num">Sección 04</div><h2>Base legal del tratamiento</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. El tratamiento de tus datos personales se fundamenta en las siguientes bases jurídicas según la legislación aplicable:</p>
        <ul>
          <li><strong style="color:var(--text)">Consentimiento:</strong> para el envío de comunicaciones opcionales y cookies no esenciales.</li>
          <li><strong style="color:var(--text)">Ejecución del contrato:</strong> para gestionar tu cuenta y prestarte los servicios.</li>
          <li><strong style="color:var(--text)">Interés legítimo:</strong> para mejorar la seguridad y el rendimiento de la plataforma.</li>
          <li><strong style="color:var(--text)">Obligación legal:</strong> para cumplir con requisitos normativos aplicables.</li>
        </ul>
      </div>

      <div class="legal-section" id="compartir">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-share-alt"></i></div>
          <div><div class="legal-section-num">Sección 05</div><h2>Compartir datos con terceros</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. No vendemos, alquilamos ni comercializamos tu información personal. Podemos compartir datos en los siguientes casos limitados:</p>
        <ul>
          <li>Con proveedores de servicios que nos ayudan a operar la plataforma.</li>
          <li>Cuando sea requerido por ley o por orden judicial.</li>
          <li>Para proteger los derechos, propiedad o seguridad de CIVINSIS y sus usuarios.</li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-shield-alt"></i>
          <div>Lorem ipsum: todos los terceros con acceso a tus datos están sujetos a acuerdos de confidencialidad y solo pueden usar la información para los fines autorizados.</div>
        </div>
      </div>

      <div class="legal-section" id="cookies">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-cookie-bite"></i></div>
          <div><div class="legal-section-num">Sección 06</div><h2>Cookies</h2></div>
        </div>
        <p>CIVINSIS utiliza cookies y tecnologías similares. A continuación se detallan los tipos utilizados:</p>
        <table class="data-table">
          <thead><tr><th>Tipo</th><th>Propósito</th><th>Duración</th></tr></thead>
          <tbody>
            <tr><td><strong>Sesión</strong></td><td>Mantener tu sesión activa mientras navegas</td><td>Sesión</td></tr>
            <tr><td><strong>Preferencias</strong></td><td>Recordar tu tema (claro/oscuro)</td><td>1 año</td></tr>
            <tr><td><strong>Análisis</strong></td><td>Lorem ipsum estadísticas anónimas</td><td>6 meses</td></tr>
          </tbody>
        </table>
        <p>Puedes controlar las cookies desde la configuración de tu navegador.</p>
      </div>

      <div class="legal-section" id="derechos">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-user-shield"></i></div>
          <div><div class="legal-section-num">Sección 07</div><h2>Tus derechos</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, tienes los siguientes derechos respecto a tus datos personales:</p>
        <ul>
          <li><strong style="color:var(--text)">Acceso:</strong> solicitar una copia de los datos que tenemos sobre ti.</li>
          <li><strong style="color:var(--text)">Rectificación:</strong> corregir datos inexactos o incompletos.</li>
          <li><strong style="color:var(--text)">Supresión:</strong> solicitar la eliminación de tus datos personales.</li>
          <li><strong style="color:var(--text)">Portabilidad:</strong> recibir tus datos en formato estructurado y legible.</li>
          <li><strong style="color:var(--text)">Oposición:</strong> oponerte al tratamiento de tus datos en ciertas circunstancias.</li>
        </ul>
        <p>Para ejercer cualquiera de estos derechos, contáctanos a través de <a href="contacto.php" style="color:var(--naranja);text-decoration:underline">nuestra página de contacto</a>.</p>
      </div>

      <div class="legal-section" id="retencion">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-clock"></i></div>
          <div><div class="legal-section-num">Sección 08</div><h2>Retención de datos</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, conservamos tus datos personales durante el tiempo que mantengas una cuenta activa en CIVINSIS. Una vez que elimines tu cuenta, eliminaremos o anonimizaremos tu información en un plazo de 30 días, salvo que tengamos una obligación legal de conservarla por más tiempo.</p>
        <p>Pellentesque in ipsum id orci porta dapibus. Nulla quis lorem ut libero malesuada feugiat.</p>
      </div>

      <div class="legal-section" id="seguridad">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-lock"></i></div>
          <div><div class="legal-section-num">Sección 09</div><h2>Seguridad de los datos</h2></div>
        </div>
        <p>Lorem ipsum dolor sit amet, implementamos medidas técnicas y organizativas apropiadas para proteger tus datos personales contra pérdida, uso indebido o acceso no autorizado:</p>
        <ul>
          <li>Contraseñas almacenadas con cifrado bcrypt de alta seguridad.</li>
          <li>Conexiones protegidas mediante HTTPS/TLS.</li>
          <li>Sesiones con tokens seguros y caducidad automática.</li>
          <li>Acceso restringido a datos personales solo al personal autorizado.</li>
        </ul>
      </div>

      <div class="legal-section" id="contacto-dpo">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-envelope"></i></div>
          <div><div class="legal-section-num">Sección 10</div><h2>Contacto y DPO</h2></div>
        </div>
        <p>Para cualquier consulta relacionada con esta política de privacidad o el tratamiento de tus datos personales, puedes contactar con nuestro Delegado de Protección de Datos (DPO):</p>
        <p><strong style="color:var(--text)">privacidad@civitas.com</strong></p>
        <p>O a través de nuestra <a href="contacto.php" style="color:var(--naranja);text-decoration:underline">página de contacto</a>.</p>
      </div>

      <div class="legal-related">
        <a href="terminos.php" class="legal-related-card">
          <div class="icon green"><i class="fas fa-file-contract"></i></div>
          <div><strong>Términos de Uso</strong><span>Condiciones de la plataforma</span></div>
        </a>
        <a href="comunidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-users"></i></div>
          <div><strong>Guía de Comunidad</strong><span>Normas de convivencia</span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon green"><i class="fas fa-envelope"></i></div>
          <div><strong>Contacto</strong><span>Habla con nosotros</span></div>
        </a>
      </div>

    </main>
  </div>
</div>

<?php echo view('layouts.footer')->render(); ?>
<script>
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
document.getElementById('hamburger')?.addEventListener('click', () => { document.getElementById('mobileMenu')?.classList.add('open'); document.getElementById('mobileOverlay')?.classList.add('open'); });
document.getElementById('mobileMenuClose')?.addEventListener('click', () => { document.getElementById('mobileMenu')?.classList.remove('open'); document.getElementById('mobileOverlay')?.classList.remove('open'); });
document.getElementById('mobileOverlay')?.addEventListener('click', () => { document.getElementById('mobileMenu')?.classList.remove('open'); document.getElementById('mobileOverlay')?.classList.remove('open'); });
function logout() { fetch('php/auth.php',{method:'POST',body:new URLSearchParams({accion:'logout'})}).then(r=>r.json()).then(d=>{if(d.success)window.location.href='index.php';}); }
</script>
</body>
</html>
