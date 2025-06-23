document.addEventListener('DOMContentLoaded', () => {
  const dash = document.querySelector('.ap-user-dashboard');
  if (!dash) return;

  fetch(`${ArtPulseDashboardApi.root}artpulse/v1/user/dashboard`, {
    headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
  })
  .then(res => res.json())
  .then(data => {
    // Membership
    const info = document.getElementById('ap-membership-info');
    info.innerHTML = `<p>${apL10n.membership_level}: ${data.membership_level}</p>
                      <p>${apL10n.expires}: ${data.membership_expires ? new Date(data.membership_expires * 1000).toLocaleDateString() : apL10n.never}</p>`;

    const upgrade = document.getElementById('ap-upgrade-options');
    if (upgrade) {
      if (data.artist_request_pending) {
        upgrade.textContent = apL10n?.artist_pending || 'Artist upgrade request pending.';
      } else {
        const artistBtn = document.createElement('button');
        artistBtn.textContent = apL10n?.upgrade_artist || 'Request Artist Upgrade';
        artistBtn.onclick = async () => {
          artistBtn.disabled = true;
          try {
            const res = await fetch(ArtPulseDashboardApi.artistEndpoint, {
              method: 'POST',
              headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
            });
            const data = await res.json();
            if (res.ok) {
              upgrade.textContent = data.message || (apL10n?.request_submitted || 'Request submitted');
            } else {
              upgrade.textContent = data.message || 'Request failed';
            }
          } catch (err) {
            upgrade.textContent = err.message;
          }
        };
        const orgLink = document.createElement('a');
        orgLink.href = ArtPulseDashboardApi.orgSubmissionUrl;
        orgLink.textContent = apL10n?.submit_org || 'Submit Organization';
        upgrade.appendChild(artistBtn);
        upgrade.appendChild(document.createTextNode(' '));
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
          li.innerHTML = `<a href="${item.link}">${item.title}</a>`;
          ul.appendChild(li);
        });
        content.appendChild(document.createElement('h3')).textContent = apL10n[type];
        content.appendChild(ul);
      }
    });

      const hasLocation = data.city || data.state;
      if (hasLocation) {
        const params = new URLSearchParams();
        if (data.city) {
          params.append('city', data.city);
        }
        if (data.state) {
          params.append('region', data.state);
        }
        fetch(`${ArtPulseDashboardApi.root}artpulse/v1/events?${params.toString()}`)
          .then(res => res.json())
          .then(events => {
            if (events && events.length) {
              renderCalendar(events, 'ap-local-events');
            }
          });
      } else if (data.favorite_events && data.favorite_events.length) {
        renderCalendar(data.favorite_events, 'ap-local-events');
      }

      if (data.favorite_events && data.favorite_events.length) {
        renderCalendar(data.favorite_events, 'ap-favorite-events');
      }
    });
  });

function renderCalendar(events, containerId = 'ap-favorite-events') {
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
          if (dayEvents.length) {
            const ul = document.createElement('ul');
            dayEvents.forEach(ev => {
              const li = document.createElement('li');
              li.innerHTML = `<a href="${ev.link}">${ev.title}</a>`;
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
