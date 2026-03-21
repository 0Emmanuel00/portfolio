</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-copy">© <?= date('Y') ?> <?= e(SITE_AUTEUR) ?></span>
    <div class="footer-links">
      <?php if (SITE_GITHUB !== '#'): ?>
        <a href="<?= e(SITE_GITHUB) ?>" target="_blank" rel="noopener">GitHub</a>
      <?php endif; ?>
      <?php if (SITE_LINKEDIN !== '#'): ?>
        <a href="<?= e(SITE_LINKEDIN) ?>" target="_blank" rel="noopener">LinkedIn</a>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/mentions-legales">Mentions légales</a>
    </div>
  </div>
</footer>

<script>
const g = document.createElement('div');
g.style.cssText = 'position:fixed;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,0.13),transparent 70%);pointer-events:none;transform:translate(-50%,-50%);z-index:9999;';
document.body.appendChild(g);
let mx=window.innerWidth/2,my=window.innerHeight/2,gx=mx,gy=my;
window.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;});
(function loop(){gx+=(mx-gx)*0.25;gy+=(my-gy)*0.25;g.style.left=gx+'px';g.style.top=gy+'px';requestAnimationFrame(loop);})();
</script>

</body>
</html>