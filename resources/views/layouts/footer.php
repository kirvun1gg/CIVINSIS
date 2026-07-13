<?php
// includes/footer.php - Footer universal
$categorias_footer = ($categorias ?? collect());
$categorias_footer = is_array($categorias_footer) ? $categorias_footer : $categorias_footer->all();
?>
<footer class="footer">
  <div class="footer-main">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-brand-name">CIVINSIS</div>
          <p class="footer-brand-desc">La plataforma de participación social juvenil donde las ideas se convierten en propuestas reales con impacto en la comunidad.</p>
          <div class="social-links">
            <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-link" title="TikTok"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title">Plataforma</h4>
          <div class="footer-links">
            <a href="dashboard.php" class="footer-link">Explorar propuestas</a>
            <a href="crear.php" class="footer-link">Crear propuesta</a>
            <a href="debates.php" class="footer-link">Debates</a>
            <a href="desafios.php" class="footer-link">Desafíos</a>
            <a href="ranking.php" class="footer-link">Ranking</a>
            <a href="index.php#top-votadas" class="footer-link">Más votadas</a>
            <a href="index.php#como-funciona" class="footer-link">Cómo funciona</a>
            <a href="faq.php" class="footer-link">FAQ</a>
            <a href="contacto.php" class="footer-link">Contacto</a>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title">Categorías</h4>
          <div class="footer-links">
            <?php foreach (array_slice($categorias_footer, 0, 6) as $cat): ?>
              <a href="dashboard.php?cat=<?= $cat['id'] ?>" class="footer-link">
                <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>;margin-right:.4rem"></i>
                <?= htmlspecialchars($cat['nombre']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title">Legal e Info</h4>
          <div class="footer-links">
            <a href="terminos.php" class="footer-link">Términos de uso</a>
            <a href="privacidad.php" class="footer-link">Política de privacidad</a>
            <a href="comunidad.php" class="footer-link">Guía de comunidad</a>
            <a href="faq.php" class="footer-link">Preguntas frecuentes</a>
            <a href="contacto.php" class="footer-link">Contacto</a>
            <a href="contacto.php?asunto=Reporte" class="footer-link">Reportar problema</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> CIVINSIS. Todos los derechos reservados. Hecho con <span style="color:var(--naranja-400)">♥</span> para la juventud.</span>
      <div class="footer-bottom-links">
        <a href="terminos.php">Términos</a>
        <a href="privacidad.php">Privacidad</a>
        <a href="comunidad.php">Comunidad</a>
      </div>
    </div>
  </div>
</footer>

<!-- CIVINSIS · Extras (efectos por categoría, personalización y asistente IA) -->
<link rel="stylesheet" href="css/civinsis-extra.css">
<script src="js/civinsis-extra.js" defer></script>
