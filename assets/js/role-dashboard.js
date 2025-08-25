document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-dashboard-root');
  if (container && window.ArtPulseDashboard && container.querySelector('.ap-drag-handle')) {
    Sortable.create(container, {
      animation: 150,
      handle: '.ap-drag-handle',
      filter: 'a, button, input, textarea, select',
      preventOnFilter: false,
      onEnd: () => {
        const newOrder = Array.from(container.children).map(card => ({
          id: card.dataset.id,
          visible: card.dataset.visible === "1"
        }));

        fetch(ArtPulseDashboard.ajax_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            action: 'ap_save_dashboard_order',
            nonce: ArtPulseDashboard.nonce,
            _wpnonce: ArtPulseDashboard.nonce,
            order: JSON.stringify(newOrder)
          })
        })
          .then(res => res.json())
          .then(response => {
            if (!response.success) {
              throw new Error(response.data.message || 'Unknown error');
            }
            console.log('✅ Dashboard order saved.');
          })
          .catch(err => {
            console.error('❌ AJAX failed:', err.message);
          });
      }
    });
  }

  const nav = document.querySelector('.ap-local-nav');
  if (nav) {
    const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
    const sections = links.map(link => document.querySelector(link.getAttribute('href')));
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    links.forEach(link => {
      link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        e.preventDefault();
        const top = target.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({ top, behavior: prefersReduced ? 'auto' : 'smooth' });
        const url = new URL(window.location.href);
        url.hash = target.id;
        history.replaceState(null, '', url);
      });
    });

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const link = nav.querySelector(`a[href="#${entry.target.id}"]`);
        if (!link) return;
        const active = entry.intersectionRatio >= 0.6;
        link.classList.toggle('is-active', active);
        if (active) {
          links.forEach(l => {
            if (l !== link) {
              l.classList.remove('is-active');
              l.removeAttribute('aria-current');
            }
          });
          link.setAttribute('aria-current', 'true');
          const url = new URL(window.location.href);
          url.hash = entry.target.id;
          history.replaceState(null, '', url);
        } else {
          link.removeAttribute('aria-current');
        }
      });
    }, { threshold: 0.6 });

    sections.forEach(sec => { if (sec) observer.observe(sec); });
  }
});

