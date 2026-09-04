// Dark mode
const saved = localStorage.getItem('civitas_theme');
if (saved) document.documentElement.setAttribute('data-theme', saved);
document.querySelectorAll('[data-dark-toggle]').forEach(btn => {
  btn.addEventListener('click', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('civitas_theme', next);
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
