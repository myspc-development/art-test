document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.APRestLists || {};
  const restRoot = cfg.root || (window.wpApiSettings && window.wpApiSettings.root) || '';
  const nonce = cfg.nonce || '';
  const headers = nonce ? { 'X-WP-Nonce': nonce } : {};
  const noItemsText = cfg.noItemsText || 'No items found.';

  const fetchCard = id => {
    return fetch(restRoot + 'artpulse/v1/event-card/' + id, { headers })
      .then(r => r.text());
  };

  function initInteractions(el) {
    el.querySelectorAll('.ap-fav-btn').forEach(btn => {
      btn.addEventListener('click', ev => {
        ev.preventDefault();
        const id = btn.dataset.objectId;
        const type = btn.dataset.objectType;
        fetch(restRoot + 'artpulse/v1/favorites', {
          method: 'POST',
          headers: Object.assign({ 'Content-Type': 'application/json' }, headers),
          body: JSON.stringify({ object_id: id, object_type: type })
        }).then(r => r.json()).then(res => {
          if (res.success) {
            const added = res.status === 'added';
            btn.classList.toggle('ap-favorited', added);
            btn.textContent = added ? '❤' : '♡';
            const countEl = btn.closest('.ap-event-actions')?.querySelector('.ap-fav-count');
            if (countEl && typeof res.favorite_count !== 'undefined') {
              countEl.textContent = res.favorite_count;
            }
          }
        });
      });
    });
    el.querySelectorAll('.ap-rsvp-btn').forEach(btn => {
      btn.addEventListener('click', ev => {
        ev.preventDefault();
        const id = btn.dataset.event;
        const joining = !btn.classList.contains('ap-rsvped');
        const endpoint = joining ? 'rsvp' : 'rsvp/cancel';
        fetch(restRoot + 'artpulse/v1/' + endpoint, {
          method: 'POST',
          headers: Object.assign({ 'Content-Type': 'application/json' }, headers),
          body: JSON.stringify({ event_id: id })
        }).then(r => r.json()).then(res => {
          if (!res.code) {
            btn.classList.toggle('ap-rsvped', joining);
            btn.textContent = joining ? 'Cancel RSVP' : 'RSVP';
            const countEl = btn.closest('.ap-event-actions')?.querySelector('.ap-rsvp-count');
            if (countEl && typeof res.rsvp_count !== 'undefined') {
              countEl.textContent = res.rsvp_count;
            }
          }
        });
      });
    });
  }

  document.querySelectorAll('.ap-recommendations').forEach(container => {
    const type = container.dataset.type || 'event';
    const limit = container.dataset.limit || 5;
    fetch(restRoot + 'artpulse/v1/recommendations?type=' + encodeURIComponent(type) + '&limit=' + limit, { headers })
      .then(r => r.json())
      .then(list => {
        container.innerHTML = '';
        if (!list.length) {
          container.innerHTML = '<p>No recommendations found.</p>';
          return;
        }
        list.forEach(item => {
          fetchCard(item.id).then(html => {
            const tmp = document.createElement('div');
            tmp.innerHTML = html.trim();
            const card = tmp.firstElementChild;
            if (card) {
              container.appendChild(card);
              initInteractions(card);
            }
          });
        });
      });
  });

  document.querySelectorAll('.ap-collection').forEach(container => {
    const id = container.dataset.id;
    if (!id) return;
    fetch(restRoot + 'artpulse/v1/collection/' + id, { headers })
      .then(r => r.json())
      .then(list => {
        container.innerHTML = '';
        if (!list.length) {
          container.innerHTML = '<p>' + noItemsText + '</p>';
          return;
        }
        list.forEach(item => {
          fetchCard(item.id).then(html => {
            const tmp = document.createElement('div');
            tmp.innerHTML = html.trim();
            const card = tmp.firstElementChild;
            if (card) {
              container.appendChild(card);
              initInteractions(card);
            }
          });
        });
      });
  });
});
