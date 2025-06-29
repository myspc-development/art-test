document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ap-org-modal');
  const openBtn = document.getElementById('ap-add-event-btn');
  const closeBtn = document.getElementById('ap-modal-close');
  const form = document.getElementById('ap-org-event-form');
  const eventIdInput = document.getElementById('ap_event_id');
  const submitBtn = form?.querySelector('button[type="submit"]');
  const eventsContainer = document.getElementById('ap-org-events');
  const statusBox = document.getElementById('ap-status-message');
  const attendeeModal = document.getElementById('ap-attendee-modal');
  const attendeeContent = document.getElementById('ap-attendee-content');
  const attendeeClose = document.getElementById('ap-attendee-close');
  const attendeeExport = document.getElementById('ap-attendee-export');
  const attendeeMessageAll = document.getElementById('ap-attendee-message-all');
  const messageModal = document.getElementById('ap-message-modal');
  const messageClose = document.getElementById('ap-message-close');
  const messageForm = document.getElementById('ap-message-form');
  const messageSubject = document.getElementById('ap-message-subject');
  const messageBody = document.getElementById('ap-message-body');
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
      if (eventIdInput) eventIdInput.value = '';
      if (submitBtn) submitBtn.textContent = 'Submit';
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

  attendeeMessageAll?.addEventListener('click', () => {
    messageForm.dataset.event = attendeeMessageAll.dataset.event;
    messageForm.dataset.user = '';
    messageModal?.classList.add('open');
  });

  messageClose?.addEventListener('click', () => messageModal?.classList.remove('open'));

  messageForm?.addEventListener('submit', e => {
    e.preventDefault();
    const eventId = messageForm.dataset.event;
    const userId = messageForm.dataset.user;
    const body = messageBody.value;
    const subject = messageSubject.value;
    if (!eventId) return;
    const url = userId
      ? `${APOrgDashboard.rest_root}artpulse/v1/event/${eventId}/attendees/${userId}/message`
      : `${APOrgDashboard.rest_root}artpulse/v1/event/${eventId}/email-rsvps`;
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-WP-Nonce': APOrgDashboard.rest_nonce
      },
      body: new URLSearchParams({ event_id: eventId, user_id: userId, subject, message: body })
    }).then(() => {
      messageModal?.classList.remove('open');
      messageSubject.value = '';
      messageBody.value = '';
    });
  });

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (statusBox) {
      statusBox.textContent = '';
      statusBox.className = '';
    }

    const formData = new FormData(form);
    const action = eventIdInput && eventIdInput.value ? 'ap_update_org_event' : 'ap_add_org_event';
    formData.append('action', action);
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
          if (eventIdInput) eventIdInput.value = '';
          if (submitBtn) submitBtn.textContent = 'Submit';
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
  if (e.target.matches('.ap-inline-edit')) {
      e.preventDefault();
      const eventId = e.target.dataset.id;
      if (!eventId) return;
      fetch(APOrgDashboard.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'ap_get_org_event',
          nonce: APOrgDashboard.nonce,
          event_id: eventId
        })
      })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
          if (!data || !data.success) return;
          Object.entries(data.data).forEach(([key, val]) => {
            const field = form.querySelector(`[name="${key}"]`);
            if (!field) return;
            if (field.type === 'checkbox') {
              field.checked = val === '1' || val === true;
            } else {
              field.value = val !== null ? val : '';
            }
          });
          if (eventIdInput) eventIdInput.value = eventId;
          if (submitBtn) submitBtn.textContent = 'Save';
          modal.classList.add('open');
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
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            thead.innerHTML = '<tr><th>Name</th><th>Email</th><th>Status</th><th>RSVP Date</th><th>Attended</th><th></th></tr>';
            table.appendChild(thead);
            const tbody = document.createElement('tbody');
            list.forEach(a => {
              const tr = document.createElement('tr');
              tr.innerHTML = `<td>${a.name || ''}</td><td>${a.email}</td><td>${a.status}</td><td>${a.rsvp_date || ''}</td><td>${a.attended ? 'Yes' : 'No'}</td><td><button class="ap-mark-attended" data-event="${eventId}" data-user="${a.ID}">${a.attended ? 'Unmark' : 'Mark Attended'}</button> <button class="ap-remove-attendee" data-event="${eventId}" data-user="${a.ID}">Remove</button> <button class="ap-message-attendee" data-event="${eventId}" data-user="${a.ID}">Message</button></td>`;
              tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            wrap.appendChild(table);
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
    if (e.target.matches('.ap-message-attendee')) {
      const user = e.target.dataset.user;
      const eventId = e.target.dataset.event;
      messageForm.dataset.event = eventId;
      messageForm.dataset.user = user;
      messageModal?.classList.add('open');
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
