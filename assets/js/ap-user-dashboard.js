let myEvents = [];
let nextEvent = null;
let engagementPage = 1;
const applyTheme = t => { document.body.dataset.theme = t; };
const { __ } = wp.i18n;

const _fetch = window.fetch;
const apNonce = window.apNonce || ArtPulseDashboardApi.nonce;
window.fetch = (url, options = {}) => {
  const headers = { 'X-WP-Nonce': apNonce, ...(options.headers || {}) };
  return _fetch(url, { credentials: 'same-origin', ...options, headers });
};

function fetchEventCard(id) {
  return fetch(`${ArtPulseDashboardApi.root}artpulse/v1/event-card/${id}`).then(r => r.text());
}
document.addEventListener('DOMContentLoaded', () => {
  const dash = document.querySelector('.ap-dashboard');
  if (!dash) return;
  if (window.ArtPulseWidgetLifecycle) {
    ArtPulseWidgetLifecycle.init("dashboard", {});
  }

  const widgetArea = document.getElementById('ap-dashboard-widgets');
  if (widgetArea && window.Sortable) {
    new Sortable(widgetArea, {
      animation: 150,
      onEnd: saveLayoutOrder
    });
    loadDashboardLayout();
  }

  const toggles = document.querySelectorAll('#ap-widget-toggles input[type="checkbox"]');
  toggles.forEach(cb => {
    cb.addEventListener('change', () => {
      const target = document.querySelector(`[data-widget="${cb.value}"]`);
      if (target) {
        target.style.display = cb.checked ? '' : 'none';
        target.dataset.visible = cb.checked ? '1' : '0';
        if (!cb.checked) {
          target.classList.add('is-hidden');
        } else {
          target.classList.remove('is-hidden');
        }
      }
      saveVisibility();
    });
  });
  // apply visibility after loading layout

  const resetBtn = document.getElementById('ap-reset-layout');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      const msg = apL10n?.reset_confirm || 'Reset dashboard layout?';
      if (!confirm(msg)) return;
      localStorage.removeItem('apDashboardLayout');
      localStorage.removeItem('apWidgetVisibility');
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/ap/layout/reset`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      }).finally(() => window.location.reload());
    });
  }

  const layoutBtn = document.getElementById('ap-customize-layout');
  const layoutControls = document.getElementById('ap-layout-controls');
  if (layoutBtn && layoutControls) {
    layoutBtn.addEventListener('click', () => {
      layoutControls.style.display = layoutControls.style.display === 'block' ? 'none' : 'block';
    });
  }

  const themeToggle = document.getElementById('ap-toggle-theme');
  if (themeToggle) {
    const saved = localStorage.getItem('apTheme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);
    themeToggle.addEventListener('click', () => {
      const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
      applyTheme(next);
      localStorage.setItem('apTheme', next);
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user-preferences`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
        body: JSON.stringify({ dashboard_theme: next })
      });
    });
  }

  const banner = document.getElementById('ap-onboarding-banner');
  if (banner && localStorage.getItem('apTourDismissed')) {
    banner.remove();
  }
  const dismissTour = document.getElementById('ap-dismiss-tour');
  if (dismissTour) {
    dismissTour.addEventListener('click', () => {
      localStorage.setItem('apTourDismissed', '1');
      banner.remove();
    });
  }
  const startTour = document.getElementById('ap-start-tour');
  if (startTour) {
    startTour.addEventListener('click', () => {
      localStorage.setItem('apTourDismissed', '1');
      banner.remove();
      if (typeof startDashboardTour === 'function') {
        startDashboardTour();
      }
    });
  }

  const nextBtn = document.getElementById('ap-onboarding-next');
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/onboarding`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
        body: JSON.stringify({ step: nextBtn.dataset.step || 'profile' })
      }).then(() => window.location.reload());
    });
  }
  const skipBtn = document.getElementById('ap-onboarding-skip');
  if (skipBtn) {
    skipBtn.addEventListener('click', () => {
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/onboarding`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
        body: JSON.stringify({ step: 'skip' })
      }).then(() => window.location.reload());
    });
  }

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
    if (data.dashboard_theme) {
      applyTheme(data.dashboard_theme);
      localStorage.setItem('apTheme', data.dashboard_theme);
    }
    myEvents = data.my_events || [];
    nextEvent = data.next_event || null;
    const statsBox = document.getElementById('ap-dashboard-stats');
    if (statsBox) {
      const rsvps = data.rsvp_count || 0;
      const favs = data.favorite_count || 0;
      statsBox.textContent = `${__('RSVPs', 'artpulse')}: ${rsvps} \u00b7 ${__('Favorites', 'artpulse')}: ${favs}`;
    }
    const supportHistory = data.support_history || [];
// Membership
const info = document.getElementById('ap-membership-info');

if (info) {
  info.innerHTML = `<p>${apL10n.membership_level}: ${data.membership_level}</p>` +
                   `<p>${apL10n.expires}: ${data.membership_expires ? new Date(data.membership_expires * 1000).toLocaleDateString() : apL10n.never}</p>`;
}

const badgeWrap = document.querySelector('.ap-badges');
if (badgeWrap) {
  badgeWrap.textContent = '';
  (data.user_badges || []).forEach(slug => {
    const b = document.createElement('div');
    b.className = `badge badge-${slug}`;
    badgeWrap.appendChild(b);
  });
}

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
if (content) {
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
      const h3 = document.createElement('h3');
      h3.textContent = apL10n[type];
      content.appendChild(h3);
      content.appendChild(ul);
    }
  });
}

    const favMap = {
      favorite_events: 'ap-favorite-events',
      favorite_artists: 'ap-favorite-artists',
      favorite_orgs: 'ap-favorite-orgs',
      favorite_artworks: 'ap-favorite-artworks'
    };
    Object.entries(favMap).forEach(([key, id]) => {
      const container = document.getElementById(id);
      if (!container) return;
      const items = data[key] || [];
      requestAnimationFrame(() => {
        if (!items.length) {
          container.textContent = __('No favorites.', 'artpulse');
          return;
        }
        const ul = document.createElement('ul');
        const frag = document.createDocumentFragment();
        items.forEach(item => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = item.link;
          a.textContent = item.title;
          li.appendChild(a);
          ul.appendChild(li);
        });
        frag.appendChild(ul);
        container.replaceChildren(frag);
      });
    });

    renderRsvpEvents(data.rsvp_events || []);


    const hasLocation = data.city || data.state;
    if (hasLocation) {
      const params = new URLSearchParams();
      if (data.city) params.append('city', data.city);
      if (data.state) params.append('region', data.state);
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/events?${params.toString()}`,
        { headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce } })
        .then(res => res.json())
        .then(events => {
          const normalized = Array.isArray(events)
            ? events.map(ev => ({ ...ev, date: ev.event_start_date || ev.event_date }))
            : [];
          if (normalized.length) {
            renderCalendar(normalized, 'ap-local-events');
            renderEventsFeed(normalized);
          } else {
            renderEventsFeed([]);
          }
        })
        .catch(() => renderEventsFeed([]));
    } else if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        const params = new URLSearchParams({ lat: pos.coords.latitude, lng: pos.coords.longitude, radius: 0.5 });
        fetch(`${ArtPulseDashboardApi.root}artpulse/v1/events?${params.toString()}`,
          { headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce } })
          .then(res => res.json())
          .then(events => {
            const normalized = Array.isArray(events)
              ? events.map(ev => ({ ...ev, date: ev.event_start_date || ev.event_date }))
              : [];
            if (normalized.length) {
              renderCalendar(normalized, 'ap-local-events');
              renderEventsFeed(normalized);
            } else {
              renderEventsFeed([]);
            }
          })
          .catch(() => renderEventsFeed([]));
      }, () => {
        if (myEvents.length) {
          renderCalendar(myEvents, 'ap-local-events');
          renderEventsFeed(myEvents);
        } else {
          renderEventsFeed([]);
        }
      });
    } else if (myEvents.length) {
      renderCalendar(myEvents, 'ap-local-events');
      renderEventsFeed(myEvents);
    } else {
      renderEventsFeed([]);
    }

    if (myEvents.length) {
      renderCalendar(myEvents, 'ap-my-events');
    }
  highlightNextEvent(nextEvent);

  renderTrendsChart();
  renderEngagementChart();
  renderProfileMetricsChart();
  renderEventAnalyticsChart();

  renderSupportHistory(supportHistory);

    fetchNotifications();
    fetchEngagementFeed(engagementPage);
    const loadMoreBtn = document.getElementById('ap-engagement-load-more');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', () => {
        engagementPage++;
        fetchEngagementFeed(engagementPage);
      });
    }
  });

  const loadWidget = el => {
    const id = el.dataset.widgetId;
    const nonce = el.dataset.nonce;
    fetch(ArtPulseDashboardApi.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ action: 'ap_render_widget', widget_id: id, nonce })
    })
      .then(res => res.text())
      .then(html => { el.outerHTML = html; })
      .catch(() => { el.outerHTML = '<div class="ap-widget-error">Failed to load widget</div>'; });
  };

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          observer.unobserve(entry.target);
          loadWidget(entry.target);
        }
      });
    });
    document.querySelectorAll('.ap-widget-placeholder').forEach(el => observer.observe(el));
  } else {
    document.querySelectorAll('.ap-widget-placeholder').forEach(loadWidget);
  }
});

function renderCalendar(events, containerId = 'ap-my-events') {
  const container = document.getElementById(containerId);
  if (!container) return;

  let current = new Date();
  function draw() {
    const frag = document.createDocumentFragment();
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
    frag.appendChild(header);

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
            if (containerId === 'ap-my-events') {
              dayEvents.forEach(ev => {
                const wrap = document.createElement('div');
                fetchEventCard(ev.id)
                  .then(html => {
                    wrap.innerHTML = html;
                    initCardInteractions(wrap);
                  });
                cell.appendChild(wrap);
              });
            } else {
              const ul = document.createElement('ul');
              dayEvents.forEach(ev => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = ev.link;
                a.textContent = ev.title;
                li.appendChild(a);
                ul.appendChild(li);
              });
              cell.appendChild(ul);
            }
          }
          date++;
        }
        row.appendChild(cell);
      }
      tbody.appendChild(row);
      if (date > daysInMonth) break;
    }
    table.appendChild(tbody);
    frag.appendChild(table);

    prev.onclick = () => { current.setMonth(current.getMonth() - 1); draw(); };
    next.onclick = () => { current.setMonth(current.getMonth() + 1); draw(); };

    requestAnimationFrame(() => {
      container.replaceChildren(frag);
    });
  }
  draw();
}

function renderEventsFeed(events) {
  const feed = document.getElementById('ap-events-feed');
  if (!feed) return;
  requestAnimationFrame(() => {
    if (!events || !events.length) {
      feed.textContent = __('No upcoming events.', 'artpulse');
      return;
    }
    const frag = document.createDocumentFragment();
    events.forEach(ev => {
      const wrap = document.createElement('div');
      fetchEventCard(ev.id)
        .then(html => {
          wrap.innerHTML = html;
          initCardInteractions(wrap);
        });
      frag.appendChild(wrap);
    });
    feed.replaceChildren(frag);
  });
}

function renderRsvpEvents(list) {
  const container = document.getElementById('ap-rsvp-events');
  if (!container) return;
  requestAnimationFrame(() => {
    if (!list || !list.length) {
      container.textContent = __('No RSVPs.', 'artpulse');
      return;
    }
    const frag = document.createDocumentFragment();
    list.forEach(ev => {
      const wrap = document.createElement('div');
      fetchEventCard(ev.id)
        .then(html => {
          wrap.innerHTML = html;
          initCardInteractions(wrap);
        });
      frag.appendChild(wrap);
    });
    container.replaceChildren(frag);
  });
}

function renderSupportHistory(list) {
  const container = document.getElementById('ap-support-history');
  if (!container) return;
  container.innerHTML = '';
  if (!list || !list.length) {
    container.textContent = __('No support requests.', 'artpulse');
    return;
  }
  const ul = document.createElement('ul');
  list.forEach(item => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.href = item.link;
    a.textContent = item.title;
    li.appendChild(a);
    ul.appendChild(li);
  });
  container.appendChild(ul);
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
        container.textContent = __('No notifications.', 'artpulse');
        return;
      }
      const markAll = document.createElement('button');
      markAll.textContent = __('Mark All Read', 'artpulse');
      markAll.className = 'ap-form-button mark-all-read';
      markAll.onclick = markAllNotificationsRead;
      container.appendChild(markAll);

      const ul = document.createElement('ul');
      list.forEach(n => {
        const li = document.createElement('li');
        li.textContent = n.content || n.type;
        if (n.status !== 'read') {
          const btn = document.createElement('button');
          btn.textContent = __('Mark read', 'artpulse');
          btn.onclick = () => markNotificationRead(n.id, li);
          li.append(' ', btn);
        }
        ul.appendChild(li);
      });
      container.appendChild(ul);
    })
    .catch(() => {
      container.textContent = __('Failed to load notifications.', 'artpulse');
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
      alert(__('Failed to mark as read', 'artpulse'));
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
    alert(__('Failed to mark all as read', 'artpulse'));
  });
}

function fetchEngagementFeed(page = 1) {
  const container = document.getElementById('ap-engagement-feed');
  const moreBtn = document.getElementById('ap-engagement-load-more');
  if (!container) return;
  if (page === 1) container.textContent = '';
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/engagement?page=${page}`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(res => res.json())
    .then(items => {
      if (page === 1 && (!items || !items.length)) {
        container.textContent = __('No activity.', 'artpulse');
        if (moreBtn) moreBtn.style.display = 'none';
        return;
      }
      let ul = container.querySelector('ul');
      if (!ul) {
        ul = document.createElement('ul');
        container.appendChild(ul);
      }
      items.forEach(it => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = it.link;
        a.textContent = it.title || it.type;
        li.appendChild(a);
        const date = document.createElement('time');
        date.textContent = new Date(it.date).toLocaleDateString();
        li.append(' ', date);
        ul.appendChild(li);
      });
      if (moreBtn) {
        moreBtn.style.display = items.length < 10 ? 'none' : '';
      }
    });
}

function refreshDashboardEvents() {
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/dashboard`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
    .then(r => r.json())
    .then(data => {
      if (data.dashboard_theme) {
        applyTheme(data.dashboard_theme);
        localStorage.setItem('apTheme', data.dashboard_theme);
      }
      myEvents = data.my_events || [];
      nextEvent = data.next_event || null;
      const statsBox = document.getElementById('ap-dashboard-stats');
      if (statsBox) {
        const rsvps = data.rsvp_count || 0;
        const favs = data.favorite_count || 0;
        statsBox.textContent = `${__('RSVPs', 'artpulse')}: ${rsvps} \u00b7 ${__('Favorites', 'artpulse')}: ${favs}`;
      }
      renderRsvpEvents(data.rsvp_events || []);
      document.getElementById('ap-my-events').innerHTML = '';
      document.getElementById('ap-next-event').innerHTML = '';
      if (myEvents.length) {
        renderCalendar(myEvents, 'ap-my-events');
      }
      highlightNextEvent(nextEvent);
    });
}

function initCardInteractions(el) {
  el.querySelectorAll('.ap-fav-btn').forEach(btn => {
    btn.addEventListener('click', ev => {
      ev.preventDefault();
      const id = btn.dataset.objectId;
      const type = btn.dataset.objectType;
      const action = btn.classList.contains('ap-favorited') ? 'remove' : 'add';
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/favorite`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': ArtPulseDashboardApi.nonce
        },
        body: JSON.stringify({ object_id: id, object_type: type, action })
      })
        .then(r => r.json())
        .then(res => { if (res.success) refreshDashboardEvents(); });
    });
  });
  el.querySelectorAll('.ap-rsvp-btn').forEach(btn => {
    btn.addEventListener('click', ev => {
      ev.preventDefault();
      const id = btn.dataset.event;
      const joining = !btn.classList.contains('ap-rsvped');
      const endpoint = joining ? 'rsvp' : 'rsvp/cancel';
      fetch(`${ArtPulseDashboardApi.root}artpulse/v1/${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': ArtPulseDashboardApi.nonce
        },
        body: JSON.stringify({ event_id: id })
      })
        .then(r => r.json())
        .then(res => { if (!res.code) refreshDashboardEvents(); });
    });
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
        alert(res.message || __('Request failed', 'artpulse'));
        btn.disabled = false;
      }
    })
    .catch(() => {
      alert(__('Request failed', 'artpulse'));
      btn.disabled = false;
    });
}

function highlightNextEvent(ev) {
  const container = document.getElementById('ap-next-event');
  if (!container) return;
  requestAnimationFrame(() => {
    container.textContent = '';
    if (!ev) return;
    const wrap = document.createElement('div');
    fetchEventCard(ev.id)
      .then(html => {
        wrap.innerHTML = html;
        initCardInteractions(wrap);
      });
    container.appendChild(wrap);
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
        alert(res.message || __('Deletion failed', 'artpulse'));
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

function renderTrendsChart() {
  const canvas = document.getElementById('ap-trends-chart');
  if (!canvas || typeof Chart === 'undefined' || !window.APUserTrends) return;

  const styles = getComputedStyle(document.documentElement);
  const rsvpColor = styles.getPropertyValue('--ap-primary-color').trim();
  const favColor = styles.getPropertyValue('--ap-accent-color').trim();

  new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels: APUserTrends.months,
      datasets: [
        {
          label: __('RSVPs', 'artpulse'),
          data: APUserTrends.rsvpCounts,
          backgroundColor: rsvpColor
        },
        {
          label: __('Favorites', 'artpulse'),
          data: APUserTrends.favoriteCounts,
          backgroundColor: favColor
        }
      ]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
}

function renderEngagementChart() {
  const canvas = document.getElementById('ap-user-engagement-chart');
  if (!canvas || typeof Chart === 'undefined' || !window.APUserStats) return;

  const styles = getComputedStyle(document.documentElement);
  const rsvpColor = styles.getPropertyValue('--ap-primary-color').trim();
  const favColor = styles.getPropertyValue('--ap-accent-color').trim();

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: APUserStats.days,
      datasets: [
        {
          label: __('RSVPs', 'artpulse'),
          data: APUserStats.rsvp_daily,
          borderColor: rsvpColor,
          fill: false
        },
        {
          label: __('Favorites', 'artpulse'),
          data: APUserStats.favorite_daily,
          borderColor: favColor,
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
}

async function renderProfileMetricsChart() {
  const canvas = document.getElementById('ap-profile-metrics-chart');
  if (!canvas || typeof Chart === 'undefined' || !window.APProfileMetrics) return;

  const styles = getComputedStyle(document.documentElement);
  const viewColor = styles.getPropertyValue('--ap-primary-color').trim();
  const followColor = styles.getPropertyValue('--ap-accent-color').trim();

  const headers = { 'X-WP-Nonce': APProfileMetrics.nonce };
  const viewRes = await fetch(`${APProfileMetrics.endpoint}/${APProfileMetrics.profileId}?metric=view`, { headers });
  if (viewRes.status === 403 || !viewRes.ok) {
    console.warn('Unauthorized to fetch profile metrics');
    return;
  }
  const followRes = await fetch(`${APProfileMetrics.endpoint}/${APProfileMetrics.profileId}?metric=follow`, { headers });
  if (followRes.status === 403 || !followRes.ok) {
    console.warn('Unauthorized to fetch profile metrics');
    return;
  }
  const views = await viewRes.json();
  const follows = await followRes.json();

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: views.days,
      datasets: [
        { label: 'Views', data: views.counts, borderColor: viewColor, fill: false },
        { label: 'Follows', data: follows.counts, borderColor: followColor, fill: false }
      ]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
}

async function renderEventAnalyticsChart() {
  const canvas = document.getElementById('ap-event-analytics-chart');
  if (!canvas || typeof Chart === 'undefined' || !window.APEventAnalytics) return;

  if (!APEventAnalytics.eventId) {
    console.warn('APEventAnalytics.eventId is not set; skipping analytics fetch');
    return;
  }

  const headers = { 'X-WP-Nonce': APEventAnalytics.nonce };
  const res = await fetch(`${APEventAnalytics.endpoint}/trends?event_id=${APEventAnalytics.eventId}`, { headers });
  const data = await res.json();

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: data.days,
      datasets: [
        { label: 'Views', data: data.views, borderColor: '#0073aa', fill: false },
        { label: 'Favorites', data: data.favorites, borderColor: '#46b450', fill: false },
        { label: 'Tickets', data: data.tickets, borderColor: '#d54e21', fill: false }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
}

function saveLayoutOrder() {
  const area = document.getElementById('ap-dashboard-widgets');
  if (!area) return;
  const layout = Array.from(area.querySelectorAll('[data-widget]')).map(w => ({
    id: w.dataset.widget,
    visible: w.dataset.visible !== '0'
  }));
  localStorage.setItem('apDashboardLayout', JSON.stringify(layout));
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/ap/layout/save`, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
    body: JSON.stringify({ layout })
  });
}

function applySavedLayout(order) {
  const area = document.getElementById('ap-dashboard-widgets');
  if (!area) return;
  let layout = order;
  if (!layout) {
    const saved = localStorage.getItem('apDashboardLayout');
    if (!saved) return;
    try { layout = JSON.parse(saved); } catch (e) { return; }
  }
  if (!Array.isArray(layout)) return;
  layout.forEach(item => {
    const id = typeof item === 'string' ? item : item.id;
    const el = area.querySelector(`[data-widget="${id}"]`);
    if (el) {
      area.appendChild(el);
      if (item && typeof item === 'object') {
        el.dataset.visible = item.visible ? '1' : '0';
      } else {
        el.dataset.visible = '1';
      }
    }
  });
}

function saveVisibility() {
  const vis = {};
  document.querySelectorAll('#ap-widget-toggles input[type="checkbox"]').forEach(cb => {
    vis[cb.value] = cb.checked;
  });
  localStorage.setItem('apWidgetVisibility', JSON.stringify(vis));
  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/ap/layout/save`, {
    method: 'POST',
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
    body: JSON.stringify({ visibility: vis })
  });
}

function applySavedVisibility(vis) {
  let settings = vis;
  if (!settings) {
    const saved = localStorage.getItem('apWidgetVisibility');
    if (!saved) return;
    try { settings = JSON.parse(saved); } catch (e) { return; }
  }
  Object.keys(settings || {}).forEach(id => {
    const widget = document.querySelector(`[data-widget="${id}"]`);
    const cb = document.querySelector(`#ap-widget-toggles input[value="${id}"]`);
    if (cb) cb.checked = settings[id];
    if (widget) {
      widget.style.display = settings[id] ? '' : 'none';
      widget.dataset.visible = settings[id] ? '1' : '0';
      if (!settings[id]) {
        widget.classList.add('is-hidden');
      } else {
        widget.classList.remove('is-hidden');
      }
    }
  });
}

async function loadDashboardLayout() {
  try {
    const roleQuery = typeof previewRole !== 'undefined' && previewRole
      ? `?role=${previewRole}`
      : '';
    const res = await fetch(`${ArtPulseDashboardApi.root}artpulse/v1/ap/layout${roleQuery}`, {
      headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
    });
    if (!res.ok) throw new Error('fail');
    const data = await res.json();
    if (data.layout) {
      localStorage.setItem('apDashboardLayout', JSON.stringify(data.layout));
      applySavedLayout(data.layout);
    } else {
      applySavedLayout();
    }
    if (data.visibility) {
      localStorage.setItem('apWidgetVisibility', JSON.stringify(data.visibility));
      applySavedVisibility(data.visibility);
    } else {
      applySavedVisibility();
    }
  } catch (e) {
    applySavedLayout();
    applySavedVisibility();
  }
}
