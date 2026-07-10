<?php
// index.php - Página principal de CIVINSIS
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CIVINSIS – Participación Social Juvenil</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* ══ HERO ═══════════════════════════════════════════════════ */
    .hero {
      min-height: 100vh;
      background: var(--grad-hero);
      position: relative; overflow: hidden;
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
      text-align: center; padding: 8rem 1.5rem 6rem;
    }
    .hero-bg { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
    .hero-orb {
      position: absolute; border-radius: 50%; filter: blur(100px);
    }
    .hero-orb-1 {
      width: 580px; height: 580px;
      background: radial-gradient(circle, rgba(54,192,161,.2) 0%, transparent 70%);
      top: -150px; left: -150px;
      animation: orbDrift 22s ease-in-out infinite;
    }
    .hero-orb-2 {
      width: 480px; height: 480px;
      background: radial-gradient(circle, rgba(239,126,34,.16) 0%, transparent 70%);
      bottom: -100px; right: -100px;
      animation: orbDrift 28s ease-in-out infinite reverse;
    }
    @keyframes orbDrift {
      0%,100% { transform: translate(0,0); }
      50%      { transform: translate(25px,-30px); }
    }
    .hero-grid {
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(54,192,161,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(54,192,161,.04) 1px, transparent 1px);
      background-size: 56px 56px;
      mask-image: radial-gradient(ellipse 70% 55% at 50% 40%, black 20%, transparent 100%);
      -webkit-mask-image: radial-gradient(ellipse 70% 55% at 50% 40%, black 20%, transparent 100%);
    }

    /* ── Contenido centrado ─────────────────────────────────── */
    .hero-content {
      position: relative; z-index: 2;
      max-width: 900px; width: 100%;
      display: flex; flex-direction: column;
      align-items: center; text-align: center;
    }

    .hero-badge {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(54,192,161,.1); border: 1px solid rgba(54,192,161,.22);
      color: rgba(54,192,161,.88); padding: .38rem 1rem; border-radius: 100px;
      font-size: .73rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
      margin-bottom: 2rem; animation: fadeInDown .5s both;
    }
    .hero-badge i { color: rgba(239,126,34,.9); }

    /* ── CIVINSIS ───────────────────────────────────────────── */
    .civinsis-word {
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(4rem, 12vw, 9rem);
      letter-spacing: .08em; line-height: 1;
      margin-bottom: 1.2rem;
      animation: fadeInUp .6s .05s both;
    }

    .civ-l {
      display: inline-block; position: relative;
      cursor: pointer; user-select: none;
      background: linear-gradient(135deg, #36c0a1, #ef7e22);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
      transition: transform .35s cubic-bezier(.34,1.56,.64,1),
                  filter .35s ease;
    }

    /* ── C: explota hacia afuera con glow cian ─────────────── */
    .civ-C { transition-duration: .4s; }
    .civ-C:hover {
      transform: scale(1.4) translateY(-8px);
      filter: drop-shadow(0 0 12px #36c0a1)
              drop-shadow(0 0 30px rgba(54,192,161,.5))
              drop-shadow(0 0 60px rgba(54,192,161,.2));
      background: linear-gradient(135deg, #00ffcc, #36c0a1) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
      animation: cPulse .6s ease both;
    }
    @keyframes cPulse {
      0%   { transform: scale(1); }
      40%  { transform: scale(1.55) translateY(-10px) rotate(-8deg); }
      70%  { transform: scale(1.3) translateY(-6px) rotate(3deg); }
      100% { transform: scale(1.4) translateY(-8px); }
    }

    /* ── I1: se rompe en dos mitades ───────────────────────── */
    .civ-I1:hover {
      animation: splitI .5s ease both;
      filter: drop-shadow(0 0 14px rgba(255,80,120,.8))
              drop-shadow(0 0 28px rgba(255,80,120,.3));
      background: linear-gradient(180deg, #ff5078, #ff8c42) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes splitI {
      0%  { transform: scale(1); clip-path: inset(0 0 0 0); }
      30% { transform: scaleX(3) translateY(-4px); filter: drop-shadow(0 0 20px #ff5078); }
      60% { transform: scaleX(.4) scaleY(1.4) translateY(-8px); }
      100%{ transform: scale(1.15) translateY(-6px); }
    }

    /* ── V: cae y rebota desde arriba ──────────────────────── */
    .civ-V:hover {
      animation: dropV .55s cubic-bezier(.34,1.56,.64,1) both;
      filter: drop-shadow(0 10px 20px rgba(0,200,255,.6))
              drop-shadow(0 0 40px rgba(0,200,255,.2));
      background: linear-gradient(135deg, #00c8ff, #0080ff) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes dropV {
      0%  { transform: translateY(-40px) scaleY(.5); opacity: .5; }
      60% { transform: translateY(6px) scaleY(1.1); opacity: 1; }
      80% { transform: translateY(-4px) scaleY(.95); }
      100%{ transform: translateY(-6px) scale(1.2); }
    }

    /* ── I2: espejo líquido ondulante ──────────────────────── */
    .civ-I2:hover {
      animation: liquidI .7s ease both;
      filter: drop-shadow(0 0 16px rgba(255,255,255,.6))
              drop-shadow(0 0 30px rgba(54,192,161,.4));
      background: linear-gradient(180deg, #fff 0%, #a0fff0 50%, #36c0a1 100%) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes liquidI {
      0%   { transform: scaleX(1) scaleY(1); }
      20%  { transform: scaleX(1.8) scaleY(.6) translateY(6px); }
      50%  { transform: scaleX(.6) scaleY(1.6) translateY(-8px); }
      75%  { transform: scaleX(1.2) scaleY(.9); }
      100% { transform: scaleX(1) scaleY(1.15) translateY(-5px); }
    }

    /* ── N: vibración eléctrica ─────────────────────────────── */
    .civ-N:hover {
      animation: electricN .4s ease both;
      filter: drop-shadow(0 0 8px #ffe066)
              drop-shadow(0 0 20px #ef7e22)
              drop-shadow(0 0 40px rgba(239,126,34,.4));
      background: linear-gradient(135deg, #ffe066, #ef7e22, #ff5500) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes electricN {
      0%  { transform: translate(0,0) rotate(0); }
      10% { transform: translate(-4px,2px) rotate(-3deg) skewX(-8deg); }
      20% { transform: translate(4px,-2px) rotate(3deg) skewX(8deg); }
      30% { transform: translate(-3px,1px) skewX(-5deg); }
      40% { transform: translate(3px,-1px) skewX(5deg); }
      50% { transform: translate(-2px,0) skewX(-3deg); }
      60% { transform: translate(2px,0) skewX(3deg); }
      80% { transform: translate(0,-2px); }
      100%{ transform: translate(0,-6px) scale(1.15); }
    }

    /* ── S1: gira como moneda ───────────────────────────────── */
    .civ-S1:hover {
      animation: coinFlip .6s ease both;
      filter: drop-shadow(-8px 0 15px rgba(150,50,255,.7))
              drop-shadow(8px 0 15px rgba(54,192,161,.5));
      background: linear-gradient(180deg, #9632ff, #36c0a1) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes coinFlip {
      0%   { transform: rotateY(0deg) scale(1); }
      40%  { transform: rotateY(180deg) scale(1.3) translateY(-8px); }
      70%  { transform: rotateY(270deg) scale(1.1); }
      100% { transform: rotateY(360deg) scale(1.2) translateY(-6px); }
    }

    /* ── I3: se dobla como papel ────────────────────────────── */
    .civ-I3:hover {
      animation: foldI .5s ease both;
      filter: drop-shadow(0 6px 18px rgba(255,200,0,.7))
              drop-shadow(0 0 30px rgba(239,126,34,.3));
      background: linear-gradient(180deg, #ffe066, #ef7e22, #ff3300) !important;
      -webkit-background-clip: text !important; background-clip: text !important;
    }
    @keyframes foldI {
      0%   { transform: perspective(300px) rotateX(0deg); }
      35%  { transform: perspective(300px) rotateX(90deg) scaleY(1.5); }
      65%  { transform: perspective(300px) rotateX(-20deg) translateY(-8px); }
      100% { transform: perspective(300px) rotateX(0deg) translateY(-6px) scale(1.15); }
    }

    /* ── S2: explota en arcoíris ─────────────────────────────── */
    .civ-S2:hover {
      animation: rainbowExplode .7s ease both;
    }
    @keyframes rainbowExplode {
      0%   {
        transform: scale(1);
        filter: none;
        background: linear-gradient(135deg, #36c0a1, #ef7e22);
        -webkit-background-clip: text; background-clip: text;
      }
      20%  {
        transform: scale(1.5) rotate(-10deg);
        filter: drop-shadow(0 0 20px #ff0080) drop-shadow(0 0 40px #ff0080);
      }
      40%  {
        transform: scale(.8) rotate(8deg);
        filter: drop-shadow(0 0 20px #ffff00) drop-shadow(0 0 40px #ff8c00);
      }
      60%  {
        transform: scale(1.4) rotate(-5deg);
        filter: drop-shadow(0 0 20px #00ff80) drop-shadow(0 0 40px #0080ff);
      }
      80%  {
        transform: scale(1.1) rotate(3deg);
        filter: drop-shadow(0 0 20px #8000ff) drop-shadow(0 0 40px #ff0080);
      }
      100% {
        transform: scale(1.25) translateY(-6px) rotate(0);
        filter: drop-shadow(0 0 16px rgba(255,100,200,.8));
      }
    }

    /* ── Partículas al click (JS) ───────────────────────────── */
    .civ-spark {
      position: fixed; pointer-events: none; z-index: 9999;
      width: 6px; height: 6px; border-radius: 50%;
      animation: sparkFly .7s ease forwards;
    }
    @keyframes sparkFly {
      0%   { transform: translate(0,0) scale(1); opacity: 1; }
      100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
    }

    /* ── Título ─────────────────────────────────────────────── */
    .hero-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 4vw, 3.2rem);
      font-weight: 700; line-height: 1.18;
      color: rgba(255,255,255,.92);
      margin-bottom: 1.1rem; letter-spacing: -.02em;
      animation: fadeInUp .6s .15s both;
    }
    .hero-title .hl {
      background: var(--grad-primary);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text; font-style: italic;
    }

    .hero-subtitle {
      font-size: clamp(.88rem, 1.7vw, 1rem);
      color: rgba(255,255,255,.5);
      max-width: 520px; margin: 0 auto 2.25rem;
      line-height: 1.8; font-weight: 300;
      animation: fadeInUp .6s .22s both;
    }

    .hero-actions {
      display: flex; gap: .85rem; justify-content: center; flex-wrap: wrap;
      animation: fadeInUp .6s .3s both;
    }
    .hero-btn-ghost {
      background: rgba(255,255,255,.08); color: rgba(255,255,255,.88);
      border: 1.5px solid rgba(255,255,255,.18);
    }
    .hero-btn-ghost:hover {
      background: rgba(255,255,255,.13);
      border-color: rgba(255,255,255,.3);
      transform: translateY(-2px);
    }

    .hero-stats {
      display: flex; gap: 3rem; justify-content: center; flex-wrap: wrap;
      margin-top: 4rem; animation: fadeInUp .6s .38s both;
    }
    .hero-stat { text-align: center; }
    .hero-stat-num {
      font-family: var(--font-display); font-size: 2rem; font-weight: 800;
      display: block;
      background: var(--grad-primary);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero-stat-label {
      font-size: .7rem; text-transform: uppercase;
      letter-spacing: .1em; color: rgba(255,255,255,.45);
    }

    .hero-wave { position: absolute; bottom: 0; left: 0; right: 0; z-index: 2; line-height: 0; }
    .hero-wave svg { display: block; width: 100%; height: 90px; }

    /* ── Secciones ──────────────────────────────────────────── */
    .section-proposals-bg { position: relative; overflow: hidden; }
    .section-proposals-bg::before {
      content: ''; position: absolute; inset: 0;
      background:
        radial-gradient(ellipse 500px 350px at 8% 20%, rgba(54,192,161,.05) 0%, transparent 70%),
        radial-gradient(ellipse 400px 300px at 92% 80%, rgba(239,126,34,.04) 0%, transparent 70%);
      pointer-events: none;
    }

    @media (max-width: 768px) {
      .hero-stats { gap: 1.75rem; }
      .civinsis-word { font-size: clamp(3rem, 16vw, 5.5rem); letter-spacing: .05em; }
    }
    @media (max-width: 480px) {
      .hero-actions { flex-direction: column; align-items: center; }
      .civinsis-word { font-size: clamp(2.6rem, 18vw, 4rem); }
    }
  </style>
</head>
<body>

<?php echo view('layouts.navbar', ['activeNav' => 'inicio'])->render(); ?>

<!-- ── HERO ────────────────────────────────────────────────── -->
<section class="hero" id="hero">
  <div class="hero-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-grid"></div>
  </div>

  <div class="hero-content">
    <div class="hero-badge">
      <i class="fas fa-bolt"></i>
      Plataforma de Participación Juvenil
    </div>

    <!-- CIVINSIS — efectos creativos por letra -->
    <div class="civinsis-word" aria-label="CIVINSIS" id="civinsisWord">
      <span class="civ-l civ-C" data-tip="¡Comunidad!">C</span>
      <span class="civ-l civ-I1" data-tip="¡Impacto!">I</span>
      <span class="civ-l civ-V" data-tip="¡Voz!">V</span>
      <span class="civ-l civ-I2" data-tip="¡Ideas!">I</span>
      <span class="civ-l civ-N" data-tip="¡Nueva era!">N</span>
      <span class="civ-l civ-S1" data-tip="¡Social!">S</span>
      <span class="civ-l civ-I3" data-tip="¡Innovación!">I</span>
      <span class="civ-l civ-S2" data-tip="¡Salvadoreña!">S</span>
    </div>

    <h1 class="hero-title">
      donde tu voz <span class="hl">transforma el mundo</span>
    </h1>

    <p class="hero-subtitle">
      El espacio donde los jóvenes convierten ideas en propuestas reales.
      Participa, debate y haz que tu comunidad cambie hoy.
    </p>

    <div class="hero-actions">
      <a href="<?= $usuarioLogueado ? 'crear.php' : 'auth.php?tab=registro' ?>" class="btn btn-primary btn-lg">
        <i class="fas fa-rocket"></i> Publica tu propuesta
      </a>
      <a href="dashboard.php" class="btn btn-lg hero-btn-ghost">
        <i class="fas fa-compass"></i> Explorar propuestas
      </a>
    </div>

    <div class="hero-stats">
      <div class="hero-stat">
        <span class="hero-stat-num" id="statPropuestas">–</span>
        <span class="hero-stat-label">Propuestas activas</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" id="statUsuarios">–</span>
        <span class="hero-stat-label">Ciudadanos activos</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" id="statVotos">–</span>
        <span class="hero-stat-label">Votos registrados</span>
      </div>
    </div>
  </div>

  <div class="hero-wave">
    <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path
        d="M0,55 C240,90 480,25 720,55 C960,85 1200,30 1440,58 L1440,90 L0,90 Z"
        fill="var(--bg)" opacity=".85"/>
      <path
        d="M0,70 C300,40 600,85 900,60 C1100,44 1300,72 1440,65 L1440,90 L0,90 Z"
        fill="var(--bg)"/>
    </svg>
  </div>
</section>

<!-- ── PROPUESTAS DESTACADAS ───────────────────────────────── -->
<section class="section section-proposals-bg" id="destacadas">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label">En tendencia</span>
      <h2 class="section-title">Propuestas <span>recientes</span></h2>
      <p class="section-desc" style="margin:0 auto">Descubre las ideas más recientes de tu comunidad y apoya las que más te inspiran.</p>
    </div>

    <div class="cards-grid animate-stagger" id="proposalsGrid"></div>
    <div id="pagination" style="margin-top:2rem"></div>

    <div class="text-center" style="margin-top:2.5rem">
      <a href="dashboard.php" class="btn btn-outline btn-lg">
        <i class="fas fa-th-large"></i> Ver todas las propuestas
      </a>
    </div>
  </div>
</section>

<!-- ── TOP VOTADAS ─────────────────────────────────────────── -->
<section class="section top-votadas-bg" id="top-votadas">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start" class="top-votadas-grid">
      <div class="section-header reveal">
        <span class="section-label">Ranking</span>
        <h2 class="section-title">Foros más <span>votados</span></h2>
        <p class="section-desc">Las propuestas con más apoyo de la comunidad.</p>
        <a href="dashboard.php?orden=votos" class="btn btn-outline" style="margin-top:1.5rem">
          <i class="fas fa-trophy"></i> Ver ranking completo
        </a>
      </div>
      <div class="reveal">
        <div class="top-ranking-list" id="topProposals">
          <div class="skeleton" style="height:72px;border-radius:16px"></div>
          <div class="skeleton" style="height:72px;border-radius:16px;opacity:.7;margin-top:.85rem"></div>
          <div class="skeleton" style="height:72px;border-radius:16px;opacity:.5;margin-top:.85rem"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CÓMO FUNCIONA ───────────────────────────────────────── -->
<section class="section section-como-bg" id="como-funciona">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label">Proceso</span>
      <h2 class="section-title">¿Cómo <span>funciona</span>?</h2>
      <p class="section-desc" style="margin:0 auto">En 4 pasos sencillos, tu idea puede convertirse en una propuesta con impacto real.</p>
    </div>
    <div class="features-grid animate-stagger">
      <div class="feature-card reveal">
        <div class="feature-icon"><i class="fas fa-user-plus"></i></div>
        <h3 class="feature-title">1. Regístrate</h3>
        <p class="feature-desc">Crea tu cuenta gratuita en menos de un minuto. Solo necesitas tu nombre y correo electrónico.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon orange"><i class="fas fa-lightbulb"></i></div>
        <h3 class="feature-title">2. Propón tu idea</h3>
        <p class="feature-desc">Describe el problema y propón soluciones concretas. CIVI te ayuda a redactarla mejor.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon"><i class="fas fa-thumbs-up"></i></div>
        <h3 class="feature-title">3. Vota y debate</h3>
        <p class="feature-desc">Apoya las propuestas que más te importan y comparte tu perspectiva en los comentarios.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon orange"><i class="fas fa-chart-line"></i></div>
        <h3 class="feature-title">4. Genera impacto</h3>
        <p class="feature-desc">Las propuestas más votadas ganan visibilidad y pueden llegar a quienes toman decisiones.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── FAQ TEASER ──────────────────────────────────────────── -->
<section class="section" id="faq" style="background:var(--surface)">
  <div class="container">
    <div class="section-header text-center reveal">
      <span class="section-label">Ayuda</span>
      <h2 class="section-title">Preguntas <span>frecuentes</span></h2>
      <p class="section-desc" style="margin:0 auto">Todo lo que necesitas saber sobre CIVINSIS.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;max-width:820px;margin:0 auto 2.5rem" class="animate-stagger">
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon" style="margin:0 auto 1rem"><i class="fas fa-user-plus"></i></div>
        <h3 class="feature-title">¿Cómo me registro?</h3>
        <p class="feature-desc">Gratis, en menos de un minuto. Solo nombre y correo.</p>
      </div>
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon orange" style="margin:0 auto 1rem"><i class="fas fa-shield-alt"></i></div>
        <h3 class="feature-title">¿Son seguros mis datos?</h3>
        <p class="feature-desc">Sí. Contraseñas encriptadas, email privado, sin spam.</p>
      </div>
      <div class="feature-card reveal" style="text-align:center">
        <div class="feature-icon" style="margin:0 auto 1rem"><i class="fas fa-robot"></i></div>
        <h3 class="feature-title">¿Qué es CIVI?</h3>
        <p class="feature-desc">Tu asistente virtual 24/7 para redactar propuestas y orientarte.</p>
      </div>
    </div>
    <div class="text-center reveal">
      <a href="faq.php" class="btn btn-primary btn-lg">
        <i class="fas fa-question-circle"></i> Ver todas las preguntas
      </a>
      <a href="contacto.php" class="btn btn-outline btn-lg" style="margin-left:1rem">
        <i class="fas fa-envelope"></i> Contactar equipo
      </a>
    </div>
  </div>
</section>

<!-- ── CTA ─────────────────────────────────────────────────── -->
<?php if (!$usuarioLogueado): ?>
<section class="section-sm" style="background:var(--grad-primary);color:#fff;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 20% 50%,rgba(255,255,255,.1) 0%,transparent 60%);pointer-events:none"></div>
  <div class="container text-center" style="position:relative;z-index:1">
    <h2 class="reveal" style="font-family:var(--font-display);font-size:clamp(1.4rem,3vw,2.1rem);font-weight:800;margin-bottom:.6rem">
      ¿Listo para hacer la diferencia?
    </h2>
    <p class="reveal" style="opacity:.82;margin-bottom:1.75rem;font-size:.95rem">
      Únete a jóvenes que ya están cambiando su comunidad desde CIVINSIS.
    </p>
    <div class="reveal" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="auth.php?tab=registro" class="btn btn-lg" style="background:#fff;color:var(--verde-600);font-weight:700">
        <i class="fas fa-rocket"></i> Crear cuenta gratis
      </a>
      <a href="dashboard.php" class="btn btn-lg" style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.3)">
        <i class="fas fa-compass"></i> Explorar primero
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo view('layouts.footer')->render(); ?>


<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
// Contadores del hero
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
    const d = await r.json();
    if (d.total !== undefined) animateCounter('statPropuestas', d.total);
  } catch(e) {}
  animateCounter('statUsuarios', 247);
  animateCounter('statVotos', 1842);
  function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let cur = 0;
    const step = Math.ceil(target / 40);
    const t = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = cur.toLocaleString('es');
      if (cur >= target) clearInterval(t);
    }, 40);
  }
})();

// Top votadas
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=top&limit=5');
    const d = await r.json();
    if (!d.success || !d.propuestas.length) return;
    const maxV = Math.max(...d.propuestas.map(p => p.votos));
    const medals = ['gold','silver','bronze','other','other'];
    const icons  = ['🥇','🥈','🥉','4','5'];
    document.getElementById('topProposals').innerHTML = d.propuestas.map((p,i) => `
      <a href="propuesta.php?id=${p.id}" style="text-decoration:none">
        <div class="top-card">
          <div class="top-medal ${medals[i]}">${i < 3 ? icons[i] : i+1}</div>
          <div class="top-info">
            <div class="top-title">${p.titulo}</div>
            <div class="top-cat"><i class="${p.cat_icono||'fas fa-tag'}" style="color:${p.cat_color||'#36c0a1'}"></i> ${p.categoria||''}</div>
            <div class="top-vote-bar" style="margin-top:.4rem">
              <div class="top-vote-bar-fill" style="width:${Math.round((p.votos/maxV)*100)}%"></div>
            </div>
          </div>
          <div class="top-votes-wrap">
            <div class="top-votes-num">${p.votos}</div>
            <div class="top-votes-label">votos</div>
          </div>
        </div>
      </a>
    `).join('');
  } catch(e) {}
})();

// Responsive top-votadas
const s = document.createElement('style');
s.textContent = `@media(max-width:768px){.top-votadas-grid{grid-template-columns:1fr!important;gap:2rem!important}}`;
document.head.appendChild(s);

// Partículas al hacer click en letras CIVINSIS
document.querySelectorAll('.civ-l').forEach(el => {
  el.addEventListener('click', function(e) {
    const colors = ['#36c0a1','#ef7e22','#00c8ff','#ffe066','#ff5078','#9632ff','#00ffcc'];
    for (let i = 0; i < 12; i++) {
      const spark = document.createElement('div');
      spark.className = 'civ-spark';
      const angle = (i / 12) * 360;
      const dist  = 40 + Math.random() * 60;
      const tx    = Math.cos(angle * Math.PI / 180) * dist;
      const ty    = Math.sin(angle * Math.PI / 180) * dist;
      spark.style.cssText = `
        left:${e.clientX}px; top:${e.clientY}px;
        background:${colors[i % colors.length]};
        --tx:${tx}px; --ty:${ty}px;
        animation-duration:${.5 + Math.random() * .4}s;
      `;
      document.body.appendChild(spark);
      setTimeout(() => spark.remove(), 900);
    }
  });
});
</script>
</body>
</html>
