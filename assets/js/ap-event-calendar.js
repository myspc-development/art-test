import { apiFetch, __ } from './ap-core.js';
import { Toast } from './ap-ui.js';

export default async function render(container) {
  const stored = JSON.parse(localStorage.getItem('ap_last_location') || 'null');

  function renderList(events) {
    container.textContent = '';
    if (!events.length) {
      const p = document.createElement('p');
      p.textContent = __('No events found');
      container.appendChild(p);
      return;
    }
    const ul = document.createElement('ul');
    events.forEach((evt) => {
      const li = document.createElement('li');
      li.className = 'tile';
      const title = document.createElement('h3');
      title.textContent = evt.title;
      const meta = document.createElement('p');
      meta.textContent = `${evt.start} – ${evt.venue || ''}`;
      li.appendChild(title);
      li.appendChild(meta);
      ul.appendChild(li);
    });
    container.appendChild(ul);
  }

  async function fetchEvents(lat, lng) {
    try {
      const params = new URLSearchParams({ lat, lng });
      const events = await apiFetch(`/ap/v1/calendar?${params.toString()}`, {
        cacheKey: `calendar-${lat}-${lng}`,
        ttlMs: 600000,
      });
      renderList(events || []);
    } catch (e) {
      Toast.show({ type: 'error', message: e.message });
      renderList([]);
    }
  }

  function askManual() {
    container.textContent = '';
    const label = document.createElement('label');
    label.textContent = __('Enter city or ZIP');
    const input = document.createElement('input');
    const btn = document.createElement('button');
    btn.textContent = __('Search');
    btn.addEventListener('click', () => {
      const val = input.value.trim();
      if (!val) return;
      // For demo purposes we treat "lat,lng" entries
      const parts = val.split(',');
      if (parts.length === 2) {
        const lat = parseFloat(parts[0]);
        const lng = parseFloat(parts[1]);
        localStorage.setItem('ap_last_location', JSON.stringify({ lat, lng }));
        fetchEvents(lat, lng);
      } else {
        renderList([]);
      }
    });
    container.appendChild(label);
    container.appendChild(input);
    container.appendChild(btn);
  }

  function init() {
    if (stored) {
      fetchEvents(stored.lat, stored.lng);
      return;
    }
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
          localStorage.setItem('ap_last_location', JSON.stringify(loc));
          fetchEvents(loc.lat, loc.lng);
        },
        () => {
          askManual();
        }
      );
    } else {
      askManual();
    }
  }

  init();
}
