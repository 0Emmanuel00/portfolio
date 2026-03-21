// Burger menu
const burger = document.getElementById('burger');
const navLinks = document.getElementById('nav-links');
if (burger && navLinks) {
  burger.addEventListener('click', () => {
    burger.classList.toggle('open');
    navLinks.classList.toggle('open');
  });
  navLinks.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      burger.classList.remove('open');
      navLinks.classList.remove('open');
    });
  });
}

// Fade-in scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.proj-card, .about-strip, .contact-banner, .section-title')
  .forEach(el => { el.classList.add('fade-in'); observer.observe(el); });
const style = document.createElement('style');
style.textContent = `.fade-in{opacity:0;transform:translateY(18px);transition:opacity .5s ease,transform .5s ease}.fade-in.visible{opacity:1;transform:none}`;
document.head.appendChild(style);

// Halo souris — un seul, créé une seule fois
if (!document.getElementById('mouse-glow')) {
  const glow = document.createElement('div');
  glow.id = 'mouse-glow';
  glow.style.cssText = 'position:fixed;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,0.13),transparent 70%);pointer-events:none;transform:translate(-50%,-50%);z-index:9999;top:0;left:0;';
  document.body.appendChild(glow);

  let mx = window.innerWidth / 2;
  let my = window.innerHeight / 2;
  let gx = mx, gy = my;

  window.addEventListener('mousemove', (e) => { mx = e.clientX; my = e.clientY; });

  (function loop() {
    gx += (mx - gx) * 0.1;
    gy += (my - gy) * 0.1;
    glow.style.left = gx + 'px';
    glow.style.top  = gy + 'px';
    requestAnimationFrame(loop);
  })();
}