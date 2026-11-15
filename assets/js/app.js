// Animasi landing page
(function () {
  var yr = document.getElementById('yr');
  if (yr) yr.textContent = new Date().getFullYear();

  // Scroll reveal
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });

  // Tilt 3D pada kartu promo
  document.querySelectorAll('.promo').forEach(function (card) {
    card.addEventListener('mousemove', function (e) {
      var r = card.getBoundingClientRect();
      var px = (e.clientX - r.left) / r.width - .5, py = (e.clientY - r.top) / r.height - .5;
      card.style.transform = 'perspective(800px) rotateY(' + (px * 7) + 'deg) rotateX(' + (-py * 7) + 'deg) translateY(-6px)';
    });
    card.addEventListener('mouseleave', function () { card.style.transform = ''; });
  });

  // Running neon border + scan pada kartu link
  document.querySelectorAll('.link-card').forEach(function (c, i) {
    var beam = document.createElement('span'); beam.className = 'beam'; beam.style.animationDelay = (-i * 0.75) + 's';
    var scan = document.createElement('span'); scan.className = 'scan';
    c.prepend(scan); c.prepend(beam);
  });

  // Nav shrink + tombol ke atas
  var toTop = document.getElementById('toTop');
  addEventListener('scroll', function () {
    var s = scrollY;
    var nav = document.querySelector('nav');
    if (nav) nav.style.padding = s > 40 ? '8px 18px' : '14px 18px';
    if (toTop) toTop.classList.toggle('show', s > 500);
  });
  if (toTop) toTop.addEventListener('click', function () { scrollTo({ top: 0, behavior: 'smooth' }); });
})();
