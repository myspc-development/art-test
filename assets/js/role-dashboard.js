document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-dashboard-root');
  const isV2 = container?.dataset.apV2 === '1';
  if (!isV2) return;

  // Preserve query parameters (specifically role) when updating hashes
  const buildUrl = hash => {
    const url = new URL(window.location.origin + window.location.pathname);
    const role = new URLSearchParams(window.location.search).get('role');
    if (role) url.searchParams.set('role', role);
    url.hash = hash;
    return url;
  };

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

    const setActiveLink = activeLink => {
      links.forEach(l => {
        const isActive = l === activeLink;
        l.classList.toggle('is-active', isActive);
        if (isActive) {
          l.setAttribute('aria-current', 'true');
        } else {
          l.removeAttribute('aria-current');
        }
      });
    };

    links.forEach(link => {
      link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        e.preventDefault();
        const top = target.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({ top, behavior: prefersReduced ? 'auto' : 'smooth' });
        history.replaceState(null, '', buildUrl(target.id));
      });
    });

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const link = nav.querySelector(`a[href="#${entry.target.id}"]`);
        if (!link) return;
        if (entry.intersectionRatio >= 0.6) {
          setActiveLink(link);
          history.replaceState(null, '', buildUrl(entry.target.id));
        } else if (link.classList.contains('is-active')) {
          link.classList.remove('is-active');
          link.removeAttribute('aria-current');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    }, { threshold: 0.6 });

    sections.forEach(sec => { if (sec) observer.observe(sec); });
  }

  const cards = document.querySelectorAll('.ap-card[data-endpoint]');
  const createSkeleton = () => {
    return `\n      <div class="ap-skeleton">\n        <div class="ap-skel-line w-100 h-6"></div>\n        <div class="ap-skel-line w-75 h-6"></div>\n        <div class="ap-skel-line w-50 h-6"></div>\n      </div>`;
  };
  const emptyState = (title = '', msg = 'Nothing to display.') => {
    return `<div class="ap-empty-state" role="status" aria-live="polite">${title ? `<h3 class="ap-empty-state__title">${title}</h3>` : ''}<p class="ap-empty-state__body">${msg}</p></div>`;
  };
  cards.forEach(card => {
    const endpoint = card.dataset.endpoint;
    if (!endpoint) return;
    const body = card.querySelector('[data-card-body]') || card;
    card.classList.add('is-loading');
    body.innerHTML = createSkeleton();

    const showEmpty = () => {
      body.innerHTML = emptyState(card.dataset.emptyTitle, card.dataset.emptyMessage);
    };

    fetch(endpoint, { credentials: 'same-origin' })
      .then(r => {
        if (!r.ok) throw new Error('Network response was not ok');
        return r.text();
      })
      .then(html => {
        card.classList.remove('is-loading');
        if (html.trim()) {
          body.innerHTML = html;
        } else {
          showEmpty();
        }
      })
      .catch(() => {
        card.classList.remove('is-loading');
        showEmpty();
      });
  });
});

