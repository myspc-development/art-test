document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ap-org-modal');
  const openBtn = document.getElementById('ap-add-event-btn');
  const closeBtn = document.getElementById('ap-modal-close');
  const form = document.getElementById('ap-org-event-form');
  const eventsContainer = document.getElementById('ap-org-events');
  const statusBox = document.getElementById('ap-status-message');

  if (modal) {
    modal.style.display = '';
  }

  // Load dashboard data
  fetch(`${APOrgDashboard.rest_root}artpulse/v1/org/dashboard`, {
    headers: { 'X-WP-Nonce': APOrgDashboard.rest_nonce }
  })
    .then(res => res.ok ? res.json() : null)
    .then(data => {
      if (!data) return;
      const info = document.getElementById('ap-membership-info');
      if (info) {
        const expire = data.membership_expires ? new Date(data.membership_expires * 1000).toLocaleDateString() : 'Never';
        info.innerHTML = `<p>Membership Level: ${data.membership_level || ''}</p><p>Expires: ${expire}</p>`;
      }
      const nextPay = document.getElementById('ap-next-payment');
      if (nextPay) {
        nextPay.textContent = data.next_payment ? new Date(data.next_payment * 1000).toLocaleDateString() : 'Never';
      }
      const txWrap = document.getElementById('ap-transactions');
      if (txWrap) {
        txWrap.textContent = '';
        if (data.transactions && data.transactions.length) {
          const ul = document.createElement('ul');
          data.transactions.forEach(t => {
            const li = document.createElement('li');
            const date = t.date ? new Date(t.date * 1000).toLocaleDateString() : '';
            li.textContent = `${date} - ${t.total ? t.total : ''} ${t.status ? t.status : ''}`.trim();
            ul.appendChild(li);
          });
          txWrap.appendChild(ul);
        } else {
          txWrap.textContent = 'No transactions found.';
        }
      }
      const metrics = document.getElementById('ap-org-analytics');
      if (metrics && data.metrics) {
        metrics.textContent = `Events: ${data.metrics.event_count || 0}, Artworks: ${data.metrics.artwork_count || 0}`;
      }
    });

  openBtn?.addEventListener('click', () => modal?.classList.add('open'));
  closeBtn?.addEventListener('click', () => modal?.classList.remove('open'));

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (statusBox) {
      statusBox.textContent = '';
      statusBox.className = '';
    }

    const formData = new FormData(form);
    formData.append('action', 'ap_add_org_event');
    if (!formData.has('nonce')) {
      formData.append('nonce', APOrgDashboard.nonce);
    }

    fetch(APOrgDashboard.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.ok ? res.json() : Promise.reject('Request failed'))
      .then(data => {
        if (data.success) {
          form.reset();
          modal.classList.remove('open');
          eventsContainer.innerHTML = data.data.updated_list_html;
        } else if (statusBox) {
          statusBox.textContent = data.data.message || 'Error submitting.';
          statusBox.className = 'error';
        }
      })
      .catch(() => {
        if (statusBox) {
          statusBox.textContent = 'Request failed.';
          statusBox.className = 'error';
        }
      });
  });

  eventsContainer?.addEventListener('click', (e) => {
    if (e.target.matches('.ap-delete-event')) {
      const eventId = e.target.dataset.id;
      if (!confirm('Delete this event?')) return;

      fetch(APOrgDashboard.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'ap_delete_org_event',
          nonce: APOrgDashboard.nonce,
          event_id: eventId
        })
      })
        .then(res => res.ok ? res.json() : Promise.reject('Request failed'))
        .then(data => {
          if (data.success) {
            eventsContainer.innerHTML = data.data.updated_list_html;
          } else {
            alert(data.data.message || 'Failed to delete.');
          }
      });
  }
  });

  // Highlight nav link for visible section
  const navLinks = document.querySelectorAll('.dashboard-nav a');
  const targets = [];
  navLinks.forEach(link => {
    const selector = link.getAttribute('href');
    if (selector && selector.startsWith('#')) {
      const target = document.querySelector(selector);
      if (target) {
        targets.push({ link, target });
      }
    }
  });

  if (targets.length) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const item = targets.find(t => t.target === entry.target);
        if (item && entry.isIntersecting) {
          navLinks.forEach(l => l.classList.remove('active'));
          item.link.classList.add('active');
        }
      });
    }, { rootMargin: '0px 0px -50% 0px' });

    targets.forEach(t => observer.observe(t.target));
  }
});
