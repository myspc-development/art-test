document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ap-org-modal');
  const openBtn = document.getElementById('ap-add-event-btn');
  const closeBtn = document.getElementById('ap-modal-close');
  const form = document.getElementById('ap-org-event-form');
  const eventsContainer = document.getElementById('ap-org-events');
  const statusBox = document.getElementById('ap-status-message');
  const attendeeModal = document.getElementById('ap-attendee-modal');
  const attendeeContent = document.getElementById('ap-attendee-content');
  const attendeeClose = document.getElementById('ap-attendee-close');
  const attendeeExport = document.getElementById('ap-attendee-export');
  const kanbanContainer = document.getElementById('kanban-board');

  if (kanbanContainer && Array.isArray(APOrgDashboard.projectStages)) {
    const board = document.createElement('div');
    board.className = 'ap-kanban';
    APOrgDashboard.projectStages.forEach(stage => {
      const col = document.createElement('div');
      col.className = 'ap-kanban-column';
      const h = document.createElement('h3');
      h.textContent = stage.name;
      col.appendChild(h);
      const list = document.createElement('ul');
      stage.items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'ap-kanban-item';
        li.textContent = item.title;
        list.appendChild(li);
      });
      col.appendChild(list);
      board.appendChild(col);
    });
    kanbanContainer.appendChild(board);
  }

  if (modal) {
    modal.style.display = '';
  }
  if (attendeeModal) {
    attendeeModal.style.display = '';
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

  openBtn?.addEventListener('click', () => {
    if (APOrgDashboard.eventFormUrl) {
      window.location.href = APOrgDashboard.eventFormUrl;
    } else {
      modal?.classList.add('open');
    }
  });
  closeBtn?.addEventListener('click', () => modal?.classList.remove('open'));
  attendeeClose?.addEventListener('click', () => attendeeModal?.classList.remove('open'));
  attendeeExport?.addEventListener('click', () => {
    const id = attendeeExport.dataset.event;
    if (!id) return;
    fetch(`${APOrgDashboard.rest_root}artpulse/v1/event/${id}/attendees/export`, {
      headers: { 'X-WP-Nonce': APOrgDashboard.rest_nonce }
    })
      .then(res => res.ok ? res.text() : null)
      .then(csv => {
        if (!csv) return;
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'attendees.csv';
        a.click();
        URL.revokeObjectURL(url);
      });
  });

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
  if (e.target.matches('.ap-view-attendees')) {
      e.preventDefault();
      const eventId = e.target.dataset.id;
      if (!eventId) return;
      attendeeContent.textContent = '';
      fetch(`${APOrgDashboard.rest_root}artpulse/v1/event/${eventId}/attendees`, {
        headers: { 'X-WP-Nonce': APOrgDashboard.rest_nonce }
      })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
          if (!data) return;
          const wrap = document.createElement('div');
          const makeSection = (title, list) => {
            if (!list.length) return;
            const h = document.createElement('h3');
            h.textContent = title;
            wrap.appendChild(h);
            const ul = document.createElement('ul');
            list.forEach(a => {
              const li = document.createElement('li');
              li.innerHTML = `${a.email} - ${a.status} <button class="ap-mark-attended" data-event="${eventId}" data-user="${a.ID}">${a.status === 'Attended' ? 'Unmark' : 'Mark Attended'}</button> <button class="ap-remove-attendee" data-event="${eventId}" data-user="${a.ID}">Remove</button>`;
              ul.appendChild(li);
            });
            wrap.appendChild(ul);
          };
          makeSection('RSVPs', data.attendees);
          makeSection('Waitlist', data.waitlist);
          attendeeContent.appendChild(wrap);
          attendeeExport.dataset.event = eventId;
          attendeeModal.classList.add('open');
        });
    }
  });

  attendeeContent?.addEventListener('click', e => {
    if (e.target.matches('.ap-mark-attended')) {
      const user = e.target.dataset.user;
      const eventId = e.target.dataset.event;
      fetch(`${APOrgDashboard.rest_root}artpulse/v1/event/${eventId}/attendees/${user}/attended`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': APOrgDashboard.rest_nonce }
      })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
          if (!data) return;
          e.target.textContent = data.attended ? 'Unmark' : 'Mark Attended';
        });
    }
    if (e.target.matches('.ap-remove-attendee')) {
      const user = e.target.dataset.user;
      const eventId = e.target.dataset.event;
      if (!confirm('Remove attendee?')) return;
      fetch(`${APOrgDashboard.rest_root}artpulse/v1/event/${eventId}/attendees/${user}/remove`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': APOrgDashboard.rest_nonce }
      })
        .then(res => res.ok ? res.json() : null)
        .then(() => {
          // reload list
          document.querySelector(`.ap-view-attendees[data-id="${eventId}"]`)?.click();
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
