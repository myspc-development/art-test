let favoriteEvents = [];
document.addEventListener('DOMContentLoaded', () => {
  const dash = document.querySelector('.ap-dashboard');
  if (!dash) return;

  const exportJsonBtn = document.getElementById('ap-export-json');
  if (exportJsonBtn) exportJsonBtn.onclick = () => exportUserData('json');
  const exportCsvBtn = document.getElementById('ap-export-csv');
  if (exportCsvBtn) exportCsvBtn.onclick = () => exportUserData('csv');
  const deleteBtn = document.getElementById('ap-delete-account');
  if (deleteBtn) deleteBtn.onclick = deleteUserData;

  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/dashboard`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
  .then(res => res.json())
  .then(data => {
    favoriteEvents = data.favorite_events || [];
    // Membership
    const info = document.getElementById('ap-membership-info');
    info.innerHTML = `<p>${apL10n.membership_level}: ${data.membership_level}</p>
                      <p>${apL10n.expires}: ${data.membership_expires ? new Date(data.membership_expires * 1000).toLocaleDateString() : apL10n.never}</p>`;

    const actions = document.getElementById('ap-membership-actions');
    if (actions && data.membership_level && data.membership_level !== 'Free') {
      actions.textContent = '';
      const btn = document.createElement('button');
      btn.className = 'ap-form-button ap-membership-toggle';
      if (data.membership_paused) {
        btn.textContent = apL10n.resume;
        btn.onclick = () => toggleMembership('resume', btn);
      } else {
        btn.textContent = apL10n.pause;
        btn.onclick = () => toggleMembership('pause', btn);
      }
      actions.appendChild(btn);
    }

    const nextPay = document.getElementById('ap-next-payment');
    if (nextPay) {
      nextPay.textContent = data.next_payment ? new Date(data.next_payment * 1000).toLocaleDateString() : apL10n.never;
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
        txWrap.textContent = apL10n.no_transactions;
      }
    }

    const upgrade = document.getElementById('ap-upgrade-options');
    if (upgrade) {
      if (data.org_request_pending) {
        upgrade.textContent = apL10n?.org_pending || 'Organization upgrade request pending.';
      } else if (data.artist_request_pending) {
        upgrade.textContent = apL10n?.artist_pending || 'Artist upgrade request pending.';
      } else {
        const artistBtn = document.createElement('button');
        artistBtn.className = 'ap-form-button upgrade-artist-btn';
        artistBtn.textContent = apL10n?.upgrade_artist || 'Request Artist Upgrade';
        artistBtn.onclick = () => {
          window.location.href = ArtPulseDashboardApi.artistSubmissionUrl;
        };
        const orgLink = document.createElement('button');
        orgLink.className = 'ap-form-button upgrade-org-btn';
        orgLink.textContent = apL10n?.upgrade_org || 'Upgrade to Organization';
        orgLink.onclick = () => {
          window.location.href = ArtPulseDashboardApi.orgSubmissionUrl;
        };
        upgrade.appendChild(artistBtn);
        upgrade.appendChild(orgLink);
      }
    }

    // Content
    const content = document.getElementById('ap-user-content');
    ['events','artists','artworks'].forEach(type => {
      if (data[type].length) {
        const ul = document.createElement('ul');
        data[type].forEach(item => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = item.link;
          a.textContent = item.title;
          li.appendChild(a);
          ul.appendChild(li);
        });
        content.appendChild(document.createElement('h3')).textContent = apL10n[type];
        content.appendChild(ul);
      }
    });

    const hasLocation = data.city || data.state;
    if (hasLocation) {
      const params = new URLSearchParams();
      if (data.city) params.append('city', data.city);
      if (data.state) params.append('region', data.state);
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/events?${params.toString()}`)
        .then(res => res.json())
        .then(events => {
          if (events && events.length) {
            renderCalendar(events, 'ap-local-events');
            renderEventsFeed(events);
          } else {
            renderEventsFeed([]);
          }
        })
        .catch(() => renderEventsFeed([]));
    } else if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        const params = new URLSearchParams({ lat: pos.coords.latitude, lng: pos.coords.longitude });
        fetch(`${ArtPulseDashboardApi.root}artpulse/v1/events?${params.toString()}`)
          .then(res => res.json())
          .then(events => {
            if (events && events.length) {
              renderCalendar(events, 'ap-local-events');
              renderEventsFeed(events);
            } else {
              renderEventsFeed([]);
            }
          })
          .catch(() => renderEventsFeed([]));
      }, () => {
        if (favoriteEvents.length) {
          renderCalendar(favoriteEvents, 'ap-local-events');
          renderEventsFeed(favoriteEvents);
        } else {
          renderEventsFeed([]);
        }
      });
    } else if (favoriteEvents.length) {
      renderCalendar(favoriteEvents, 'ap-local-events');
      renderEventsFeed(favoriteEvents);
    } else {
      renderEventsFeed([]);
    }

    if (favoriteEvents.length) {
      renderCalendar(favoriteEvents, 'ap-favorite-events', true);
    }

    fetchNotifications();
  });
});

function renderCalendar(events, containerId = 'ap-favorite-events', allowRemove = false) {
  const container = document.getElementById(containerId);
  if (!container) return;

  let current = new Date();
  function draw() {
    container.innerHTML = '';
    const month = current.getMonth();
    const year  = current.getFullYear();

    const header = document.createElement('div');
    header.className = 'ap-cal-header';
    const prev = document.createElement('button');
    prev.textContent = '<';
    const next = document.createElement('button');
    next.textContent = '>';
    const title = document.createElement('span');
    title.textContent = current.toLocaleString('default', { month: 'long', year: 'numeric' });
    header.appendChild(prev);
    header.appendChild(title);
    header.appendChild(next);
    container.appendChild(header);

    const table = document.createElement('table');
    table.className = 'ap-calendar';
    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    days.forEach(d => {
      const th = document.createElement('th');
      th.textContent = d;
      trh.appendChild(th);
    });
    thead.appendChild(trh);
    table.appendChild(thead);
    const tbody = document.createElement('tbody');
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    let date = 1;
    for (let i = 0; i < 6; i++) {
      const row = document.createElement('tr');
      for (let j = 0; j < 7; j++) {
        const cell = document.createElement('td');
        if ((i === 0 && j < firstDay) || date > daysInMonth) {
          cell.innerHTML = '';
        } else {
          const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(date).padStart(2,'0')}`;
          cell.innerHTML = `<div class="ap-date">${date}</div>`;
          const dayEvents = events.filter(e => e.date === dateStr);
          cell.classList.toggle('has-events', dayEvents.length > 0);
          if (dayEvents.length) {
            const ul = document.createElement('ul');
            dayEvents.forEach(ev => {
              const li = document.createElement('li');
              const a = document.createElement('a');
              a.href = ev.link;
              a.textContent = ev.title;
              li.appendChild(a);
              if (allowRemove && ev.id) {
                const btn = document.createElement('button');
                btn.textContent = 'Remove';
                btn.onclick = () => unfavoriteEvent(ev.id);
                li.append(' ', btn);
              }
              ul.appendChild(li);
            });
            cell.appendChild(ul);
          }
          date++;
        }
        row.appendChild(cell);
      }
      tbody.appendChild(row);
      if (date > daysInMonth) break;
    }
    table.appendChild(tbody);
    container.appendChild(table);

    prev.onclick = () => { current.setMonth(current.getMonth() - 1); draw(); };
    next.onclick = () => { current.setMonth(current.getMonth() + 1); draw(); };
  }
  draw();
}

function renderEventsFeed(events) {
  const feed = document.getElementById('ap-events-feed');
  if (!feed) return;
  feed.innerHTML = '';
  if (!events || !events.length) {
    feed.textContent = 'No upcoming events.';
    return;
  }
  const ul = document.createElement('ul');
  events.forEach(ev => {
    const li = document.createElement('li');
    const date = ev.date ? new Date(ev.date).toLocaleDateString() : '';
    const a = document.createElement('a');
    a.href = ev.link;
    a.textContent = ev.title;
    li.appendChild(a);
    if (date) li.append(' ', date);
    ul.appendChild(li);
  });
  feed.appendChild(ul);
}

function fetchNotifications() {
  const container = document.getElementById('ap-dashboard-notifications');
  if (!container) return;
  container.textContent = '';
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/notifications`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(res => res.json())
    .then(data => {
      const list = data.notifications || data;
      if (!list || !list.length) {
        container.textContent = 'No notifications.';
        return;
      }
      const markAll = document.createElement('button');
      markAll.textContent = 'Mark All Read';
      markAll.className = 'ap-form-button mark-all-read';
      markAll.onclick = markAllNotificationsRead;
      container.appendChild(markAll);

      const ul = document.createElement('ul');
      list.forEach(n => {
        const li = document.createElement('li');
        li.textContent = n.content || n.type;
        if (n.status !== 'read') {
          const btn = document.createElement('button');
          btn.textContent = 'Mark read';
          btn.onclick = () => markNotificationRead(n.id, li);
          li.append(' ', btn);
        }
        ul.appendChild(li);
      });
      container.appendChild(ul);
    })
    .catch(() => {
      container.textContent = 'Failed to load notifications.';
    });
}

function markNotificationRead(id, el) {
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/notifications/${id}/read`, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(res => res.json())
    .then(() => {
      el.remove();
    })
    .catch(() => {
      // ignore errors but show simple message
      alert('Failed to mark as read');
    });
}

function markAllNotificationsRead() {
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/notifications/mark-all-read`, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(res => res.json())
    .then(() => {
      fetchNotifications();
    })
    .catch(() => {
    alert('Failed to mark all as read');
  });
}

function toggleMembership(action, btn) {
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/membership/${action}`, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        window.location.reload();
      } else {
        alert(res.message || 'Request failed');
        btn.disabled = false;
      }
    })
    .catch(() => {
      alert('Request failed');
      btn.disabled = false;
    });
}

function unfavoriteEvent(id) {
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/favorites`, {
    method: 'DELETE',
    headers: {
      'X-WP-Nonce': ArtPulseDashboardApi.nonce,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ object_id: id, object_type: 'artpulse_event' })
  })
    .then(res => res.json())
    .then(() => {
      favoriteEvents = favoriteEvents.filter(ev => ev.id !== id);
      renderCalendar(favoriteEvents, 'ap-favorite-events', true);
    })
    .catch(() => {
      alert('Failed to remove favorite');
    });
}

// Uses endpoints registered in UserAccountRestController::register_routes().
function exportUserData(format) {
  fetch(`${ArtPulseDashboardApi.exportEndpoint}?format=${format}`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(async res => {
      if (!res.ok) {
        throw new Error(`Export failed: server returned ${res.status}`);
      }
      return format === 'csv' ? res.text() : res.json();
    })
    .then(data => {
      const content = format === 'csv' ? data : JSON.stringify(data, null, 2);
      const type = format === 'csv' ? 'text/csv' : 'application/json';
      const blob = new Blob([content], { type });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `user-data.${format}`;
      a.click();
      URL.revokeObjectURL(url);
    })
    .catch(err => alert(err.message));
}

function deleteUserData() {
  if (!confirm('Are you sure you want to delete your account?')) return;

  const btn = document.getElementById('ap-delete-account');
  if (btn) {
    btn.disabled = true;
    // Add a simple spinner to indicate progress
    btn.insertAdjacentHTML(
      'beforeend',
      '<span class="ap-spinner" aria-hidden="true"></span>'
    );
  }

  fetch(ArtPulseDashboardApi.deleteEndpoint, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(async res => {
      if (!res.ok) {
        throw new Error(`Deletion failed: server returned ${res.status}`);
      }
      return res.json();
    })
    .then(res => {
      if (res.success) {
        window.location.reload();
      } else {
        alert(res.message || 'Deletion failed');
        if (btn) {
          btn.disabled = false;
          const spinner = btn.querySelector('.ap-spinner');
          if (spinner) spinner.remove();
        }
      }
    })
    .catch(err => {
      alert(err.message);
      if (btn) {
        btn.disabled = false;
        const spinner = btn.querySelector('.ap-spinner');
        if (spinner) spinner.remove();
      }
    });
}
