<?php
$activeNav = '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guía de Comunidad – CIVINSIS</title>
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
      background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(54,192,161,.1) 0%, rgba(239,126,34,.06) 100%);
      pointer-events: none;
    }
    .legal-hero-badge { display: inline-flex; align-items: center; gap: .5rem; background: rgba(54,192,161,.12); border: 1px solid rgba(54,192,161,.25); color: var(--verde); border-radius: 100px; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: .35rem 1rem; margin-bottom: 1.25rem; }
    .legal-hero h1 { font-family: var(--font-display); font-size: clamp(2rem,5vw,3.5rem); font-weight: 800; color: #fff; margin-bottom: .75rem; }
    .legal-hero h1 span { background: var(--grad-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .legal-hero-meta { color: rgba(255,255,255,.45); font-size: .85rem; display: flex; align-items: center; justify-content: center; gap: 1.5rem; flex-wrap: wrap; }
    .legal-hero-meta span { display: flex; align-items: center; gap: .4rem; }
    .legal-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 2.5rem; padding: 3.5rem 0 5rem; align-items: start; }
    @media (max-width: 768px) { .legal-wrap { grid-template-columns: 1fr; } .legal-toc { display: none; } }
    .legal-toc { position: sticky; top: calc(var(--nav-height) + 1.5rem); background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow); }
    .legal-toc-title { font-family: var(--font-display); font-weight: 700; font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; }
    .legal-toc a { display: flex; align-items: center; gap: .6rem; padding: .55rem .75rem; border-radius: var(--radius); font-size: .85rem; color: var(--text-2); transition: var(--trans); border-left: 2px solid transparent; }
    .legal-toc a:hover, .legal-toc a.active { background: var(--verde-alpha); color: var(--verde); border-left-color: var(--verde); }
    .legal-toc a i { width: 16px; text-align: center; font-size: .8rem; opacity: .7; }
    .legal-content { min-width: 0; }
    .legal-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 2.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow); scroll-margin-top: calc(var(--nav-height) + 1.5rem); transition: border-color .3s; }
    .legal-section:hover { border-color: rgba(54,192,161,.25); }
    .legal-section-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
    .legal-section-icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .legal-section-icon.green  { background: var(--verde-alpha);  color: var(--verde); }
    .legal-section-icon.orange { background: var(--naranja-alpha); color: var(--naranja); }
    .legal-section-icon.red    { background: rgba(231,76,60,.1);   color: #e74c3c; }
    .legal-section-num { font-family: var(--font-display); font-size: .75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); }
    .legal-section h2 { font-family: var(--font-display); font-weight: 700; font-size: 1.2rem; color: var(--text); }
    .legal-section p { color: var(--text-2); line-height: 1.8; font-size: .95rem; margin-bottom: .9rem; }
    .legal-section p:last-child { margin-bottom: 0; }
    .legal-section ul { list-style: none; padding: 0; display: flex; flex-direction: column; gap: .5rem; margin: .75rem 0 .9rem; }
    .legal-section ul li { display: flex; align-items: flex-start; gap: .6rem; color: var(--text-2); font-size: .95rem; line-height: 1.7; }
    .legal-section ul li::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--grad-primary); flex-shrink: 0; margin-top: .55rem; }
    .legal-highlight { background: var(--grad-soft); border: 1px solid rgba(54,192,161,.2); border-radius: var(--radius); padding: 1rem 1.25rem; margin: 1rem 0; font-size: .9rem; color: var(--text-2); display: flex; gap: .75rem; align-items: flex-start; }
    .legal-highlight i { color: var(--verde); margin-top: .15rem; flex-shrink: 0; }
    .legal-highlight.warn { border-color: rgba(239,126,34,.25); }
    .legal-highlight.warn i { color: var(--naranja); }
    .legal-highlight.danger { border-color: rgba(231,76,60,.25); }
    .legal-highlight.danger i { color: #e74c3c; }

    /* Values grid */
    .values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 1rem; margin: 1rem 0; }
    .value-card { background: var(--grad-soft); border: 1px solid rgba(54,192,161,.15); border-radius: var(--radius); padding: 1.25rem 1rem; text-align: center; }
    .value-card .vi { font-size: 1.5rem; margin-bottom: .5rem; }
    .value-card strong { font-family: var(--font-display); font-size: .9rem; color: var(--text); display: block; margin-bottom: .25rem; }
    .value-card span { font-size: .78rem; color: var(--text-muted); line-height: 1.5; display: block; }

    /* Warning list */
    .legal-section ul.warn li::before { background: linear-gradient(135deg,#e74c3c,#ef7e22); }

    .legal-related { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 1rem; margin-top: 2rem; }
    .legal-related-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; transition: var(--trans); box-shadow: var(--shadow); }
    .legal-related-card:hover { border-color: rgba(54,192,161,.35); transform: translateY(-3px); box-shadow: var(--shadow-color); }
    .legal-related-card .icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; background: var(--verde-alpha); color: var(--verde); display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .legal-related-card .icon.orange { background: var(--naranja-alpha); color: var(--naranja); }
    .legal-related-card strong { font-family: var(--font-display); font-size: .9rem; color: var(--text); display: block; }
    .legal-related-card span { font-size: .78rem; color: var(--text-muted); }
  </style>
</head>
<body>

<?php echo view('layouts.navbar')->render(); ?>

<section class="legal-hero">
  <div class="container">
    <div class="legal-hero-badge"><i class="fas fa-users"></i> Comunidad</div>
    <h1>Guía de <span>Comunidad</span></h1>
    <div class="legal-hero-meta">
      <span><i class="fas fa-calendar-alt"></i> Última actualización: enero 2025</span>
      <span><i class="fas fa-clock"></i> Lectura estimada: 6 min</span>
      <span><i class="fas fa-heart"></i> Para una comunidad sana</span>
    </div>
  </div>
</section>

<div class="container">
  <div class="legal-wrap">

    <aside class="legal-toc">
      <div class="legal-toc-title"><i class="fas fa-list" style="margin-right:.5rem"></i>Contenido</div>
      <nav id="tocNav">
        <a href="#bienvenida"><i class="fas fa-hand-wave"></i> Bienvenida</a>
        <a href="#valores"><i class="fas fa-heart"></i> Nuestros valores</a>
        <a href="#propuestas"><i class="fas fa-file-alt"></i> Normas de propuestas</a>
        <a href="#comentarios"><i class="fas fa-comments"></i> Normas de comentarios</a>
        <a href="#conducta"><i class="fas fa-check-circle"></i> Conducta esperada</a>
        <a href="#prohibido"><i class="fas fa-ban"></i> Conducta prohibida</a>
        <a href="#moderacion"><i class="fas fa-shield-alt"></i> Moderación</a>
        <a href="#sanciones"><i class="fas fa-gavel"></i> Sanciones</a>
        <a href="#reportar"><i class="fas fa-flag"></i> Cómo reportar</a>
      </nav>
    </aside>

    <main class="legal-content">
      <div class="legal-section" id="bienvenida">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-door-open"></i></div>
          <div><div class="legal-section-num">Sección 01</div><h2>Bienvenida a CIVINSIS</h2></div>
        </div>
        <p>CIVINSIS es un espacio donde la juventud puede proponer, debatir y votar ideas que transformen la comunidad. Para que este espacio funcione necesitamos que todos sigamos unas normas básicas de convivencia que aseguren un entorno seguro, inclusivo y enfocado en el desarrollo social.</p>
        <p>Al ingresar a esta plataforma te unes a una red activa de ciudadanos comprometidos con el cambio positivo. Tu voz, tus propuestas de mejora comunitaria y tus interacciones respetuosas con otros usuarios constituyen el pilar fundamental para construir un diálogo democrático constructivo y transparente.</p>
      </div>

      <div class="legal-section" id="valores">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-heart"></i></div>
          <div><div class="legal-section-num">Sección 02</div><h2>Nuestros valores</h2></div>
        </div>
        <p>La comunidad CIVINSIS se rige por los siguientes valores fundamentales:</p>
        <div class="values-grid">
          <div class="value-card"><div class="vi">🤝</div><strong>Respeto</strong><span>Todas las personas merecen un trato digno.</span></div>
          <div class="value-card"><div class="vi">💡</div><strong>Creatividad</strong><span>Fomentamos ideas originales e innovadoras.</span></div>
          <div class="value-card"><div class="vi">🌍</div><strong>Inclusión</strong><span>Todas las voces tienen valor aquí.</span></div>
          <div class="value-card"><div class="vi">⚖️</div><strong>Honestidad</strong><span>La transparencia nos hace más fuertes.</span></div>
          <div class="value-card"><div class="vi">🚀</div><strong>Acción</strong><span>Transformamos ideas en propuestas reales.</span></div>
          <div class="value-card"><div class="vi">🛡️</div><strong>Seguridad</strong><span>Un espacio libre de acoso y odio.</span></div>
        </div>
      </div>

      <div class="legal-section" id="propuestas">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-file-alt"></i></div>
          <div><div class="legal-section-num">Sección 03</div><h2>Normas para propuestas</h2></div>
        </div>
        <p>Para que tu propuesta sea válida y útil para la comunidad, debe cumplir con lo siguiente:</p>
        <ul>
          <li>El título debe ser claro, descriptivo y reflejar fielmente el contenido.</li>
          <li>La descripción debe explicar el problema y la solución propuesta de forma ordenada.</li>
          <li>Debe pertenecer a una categoría relevante de la plataforma.</li>
          <li>No se admiten propuestas duplicadas o muy similares a otras existentes.</li>
          <li>Las imágenes incluidas deben ser de tu propiedad o de uso libre.</li>
        </ul>
        <div class="legal-highlight">
          <i class="fas fa-lightbulb"></i>
          <div>Las mejores propuestas son aquellas que presentan un problema real, una solución concreta y un beneficio claro para la comunidad.</div>
        </div>
      </div>

      <div class="legal-section" id="comentarios">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-comments"></i></div>
          <div><div class="legal-section-num">Sección 04</div><h2>Normas para comentarios</h2></div>
        </div>
        <p>Los comentarios son el corazón del debate en CIVINSIS. Para que el diálogo sea enriquecedor:</p>
        <ul>
          <li>Comenta de forma constructiva, aportando argumentos o sugerencias.</li>
          <li>Dirígete a las ideas, no a las personas que las proponen.</li>
          <li>Está permitido el desacuerdo, siempre que sea respetuoso y razonado.</li>
          <li>Evita comentarios de una sola palabra o sin contenido ("bien", "+1").</li>
          <li>Cita fuentes cuando hagas afirmaciones que lo requieran.</li>
        </ul>
      </div>

      <div class="legal-section" id="conducta">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-check-circle"></i></div>
          <div><div class="legal-section-num">Sección 05</div><h2>Conducta esperada</h2></div>
        </div>
        <p>Esperamos que todos los miembros de la comunidad CIVINSIS:</p>
        <ul>
          <li>Usen un lenguaje respetuoso e inclusivo en todas sus interacciones.</li>
          <li>Acepten la diversidad de opiniones y perspectivas.</li>
          <li>Contribuyan al debate con buena fe y ánimo constructivo.</li>
          <li>Reporten contenido inapropiado en lugar de responder con agresividad.</li>
          <li>Asuman responsabilidad por el contenido que publican.</li>
        </ul>
      </div>

      <div class="legal-section" id="prohibido">
        <div class="legal-section-header">
          <div class="legal-section-icon red"><i class="fas fa-ban"></i></div>
          <div><div class="legal-section-num">Sección 06</div><h2>Conducta prohibida</h2></div>
        </div>
        <p>Están <strong style="color:#e74c3c">estrictamente prohibidas</strong> las siguientes conductas:</p>
        <ul class="warn">
          <li>Acoso, intimidación o amenazas hacia otros usuarios en cualquier forma.</li>
          <li>Discurso de odio por raza, género, orientación sexual, religión u origen.</li>
          <li>Desinformación deliberada o contenido manipulado para engañar.</li>
          <li>Spam, publicidad no autorizada o contenido repetitivo sin valor.</li>
          <li>Suplantación de identidad de personas o instituciones.</li>
          <li>Compartir información privada de terceros sin su consentimiento.</li>
          <li>Contenido explícitamente violento, sexual o perturbador.</li>
        </ul>
        <div class="legal-highlight danger">
          <i class="fas fa-exclamation-circle"></i>
          <div>El incumplimiento de estas normas puede resultar en la eliminación inmediata del contenido y la suspensión permanente de la cuenta.</div>
        </div>
      </div>

      <div class="legal-section" id="moderacion">
        <div class="legal-section-header">
          <div class="legal-section-icon orange"><i class="fas fa-shield-alt"></i></div>
          <div><div class="legal-section-num">Sección 07</div><h2>Moderación</h2></div>
        </div>
        <p>El equipo de moderación de CIVINSIS trabaja para mantener la plataforma segura y útil para todos. Los moderadores tienen la capacidad de:</p>
        <ul>
          <li>Editar o eliminar contenido que viole estas normas.</li>
          <li>Suspender temporalmente cuentas con comportamiento inapropiado.</li>
          <li>Escalar casos graves al equipo de administración.</li>
          <li>Responder a reportes de los usuarios en un plazo razonable.</li>
        </ul>
        <p>Las decisiones de moderación se toman de forma justa e imparcial.</p>
      </div>

      <div class="legal-section" id="sanciones">
        <div class="legal-section-header">
          <div class="legal-section-icon red"><i class="fas fa-gavel"></i></div>
          <div><div class="legal-section-num">Sección 08</div><h2>Sistema de sanciones</h2></div>
        </div>
        <p>Las sanciones se aplican de forma progresiva según la gravedad y reincidencia:</p>
        <ul>
          <li><strong style="color:var(--text)">Advertencia:</strong> aviso formal para infracciones leves o primeras faltas.</li>
          <li><strong style="color:var(--text)">Restricción temporal:</strong> limitación de funciones por 7 a 30 días.</li>
          <li><strong style="color:var(--text)">Suspensión:</strong> bloqueo temporal completo de la cuenta.</li>
          <li><strong style="color:var(--text)">Baneo permanente:</strong> para infracciones graves o reincidencia repetida.</li>
        </ul>
        <div class="legal-highlight warn">
          <i class="fas fa-info-circle"></i>
          <div>Puedes apelar cualquier sanción contactando al equipo de administración con una explicación detallada de tu caso.</div>
        </div>
      </div>

      <div class="legal-section" id="reportar">
        <div class="legal-section-header">
          <div class="legal-section-icon green"><i class="fas fa-flag"></i></div>
          <div><div class="legal-section-num">Sección 09</div><h2>Cómo reportar</h2></div>
        </div>
        <p>Si encuentras contenido o comportamiento que viole estas normas, ayúdanos reportándolo:</p>
        <ul>
          <li>Usa el botón de reporte disponible en cada propuesta y comentario.</li>
          <li>Escríbenos directamente a través de nuestra <a href="contacto.php?asunto=Reporte" style="color:var(--verde);text-decoration:underline">página de contacto</a>.</li>
          <li>Proporciona el mayor contexto posible para agilizar la revisión.</li>
        </ul>
        <p>Agradecemos a todos los ciudadanos que contribuyen a mantener CIVINSIS como un espacio seguro y constructivo.</p>
      </div>

      <div class="legal-related">
        <a href="terminos.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-file-contract"></i></div>
          <div><strong>Términos de Uso</strong><span>Condiciones de la plataforma</span></div>
        </a>
        <a href="privacidad.php" class="legal-related-card">
          <div class="icon"><i class="fas fa-lock"></i></div>
          <div><strong>Política de Privacidad</strong><span>Cómo tratamos tus datos</span></div>
        </a>
        <a href="contacto.php" class="legal-related-card">
          <div class="icon orange"><i class="fas fa-envelope"></i></div>
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
