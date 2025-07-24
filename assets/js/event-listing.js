document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.APEventListing || {};
  const restRoot = cfg.root || (window.wpApiSettings && window.wpApiSettings.root) || '';
  const nonce = cfg.nonce || '';
  const headers = nonce ? { 'X-WP-Nonce': nonce } : {};

  const wrappers = document.querySelectorAll('.ap-event-listing-wrapper');
  if (!wrappers.length) return;

  wrappers.forEach(wrapper => {
    const form = wrapper.querySelector('#ap-event-listing-form');
    const chips = wrapper.querySelector('.ap-filter-chips');
    const results = wrapper.querySelector('.ap-event-listing-results');
    const perPage = parseInt(wrapper.dataset.perPage, 10) || 12;

    const buildParams = () => {
      const data = new FormData(form);
      const params = new URLSearchParams();
      for (const [k, v] of data.entries()) {
        if (v) params.append(k, v);
      }
      params.append('per_page', perPage);
      return params.toString();
    };

    const updateChips = () => {
      chips.innerHTML = '';
      const data = new FormData(form);
      Array.from(data.entries()).forEach(([k, v]) => {
        if (!v || k === 'sort') return;
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'ap-chip';
        chip.textContent = v;
        chip.dataset.field = k;
        chip.setAttribute('aria-label', `Remove ${v}`);
        chip.addEventListener('click', () => {
          form.querySelector(`[name="${k}"]`).value = '';
          chip.remove();
          fetchEvents();
        });
        chips.appendChild(chip);
      });
      chips.hidden = !chips.children.length;
    };

    const fetchEvents = () => {
      results.setAttribute('aria-busy', 'true');
      results.innerHTML = '<p>Loading...</p>';
      const params = buildParams();
      fetch(restRoot + 'artpulse/v1/event-list?' + params, { headers })
        .then(r => r.json())
        .then(data => {
          results.innerHTML = data.html || '<p>No events found.</p>';
          updateChips();
          results.removeAttribute('aria-busy');
        })
        .catch(() => {
          results.innerHTML = '<p>Error loading events.</p>';
          results.removeAttribute('aria-busy');
        });
    };

    form.addEventListener('submit', e => {
      e.preventDefault();
      fetchEvents();
    });

    fetchEvents();
  });
});
