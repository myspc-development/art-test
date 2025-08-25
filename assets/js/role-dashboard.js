/* Role dashboard interactions */
document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-dashboard-root');
  const isV2 = container?.dataset.apV2 === '1';
  if (!isV2) return;

  // Sticky local nav: smooth scroll + active link + preserve ?role=
  const nav = document.querySelector('.ap-local-nav');
  if (nav) {
    const links = Array.from(nav.querySelectorAll('a[href*="#"]'));
    const sections = links
      .map(a => document.getElementById((a.getAttribute('href') || '').split('#')[1]))
      .filter(Boolean);

    // Smooth scroll (respect reduced motion) + hash update that keeps ?role
    links.forEach(link => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href') || '';
        if (!href.includes('#')) return;
        const id = href.split('#')[1];
        const target = document.getElementById(id);
        if (!target) return;
        e.preventDefault();
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        const url = new URL(window.location.href);
        url.hash = '#' + id;
        window.history.replaceState(null, '', url.toString());
        links.forEach(a => a.removeAttribute('aria-current'));
        link.setAttribute('aria-current', 'true');
        links.forEach(a => a.classList.remove('is-active'));
        link.classList.add('is-active');
      });
    });

    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
          const id = entry.target.id;
          const link = links.find(a => (a.getAttribute('href') || '').endsWith('#' + id));
          if (!link) return;
          links.forEach(a => a.classList.remove('is-active'));
          link.classList.add('is-active');
          links.forEach(a => a.removeAttribute('aria-current'));
          link.setAttribute('aria-current', 'true');
        }
      });
    }, { threshold: [0.6] });
    sections.forEach(s => io.observe(s));
  }

  // Optional: card skeleton swapper (expects .ap-card.is-loading with .ap-card__body)
  document.querySelectorAll('.ap-card.is-loading').forEach(card => {
    const body = card.querySelector('.ap-card__body');
    const src = card.dataset.src;
    if (!body || !src) return;
    fetch(src, { credentials: 'same-origin' })
      .then(r => r.ok ? r.text() : '')
      .then(html => {
        card.classList.remove('is-loading');
        body.innerHTML = (html && html.trim()) ? html
          : `<div class="ap-empty" role="status" aria-live="polite">
               <h3>${card.dataset.emptyTitle || ''}</h3>
               <p>${card.dataset.emptyMessage || ''}</p>
             </div>`;
      })
      .catch(() => {
        card.classList.remove('is-loading');
        body.innerHTML = `<div class="ap-empty" role="status" aria-live="polite">
           <h3>${card.dataset.emptyTitle || ''}</h3>
           <p>${card.dataset.emptyMessage || ''}</p>
         </div>`;
      });
  });
});
