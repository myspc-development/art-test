import { apiFetch, __ } from './ap-core.js';
import { Toast, Confirm } from './ap-ui.js';

export default async function render(container) {
  const table = document.createElement('table');
  table.className = 'ap-table';
  const thead = document.createElement('thead');
  const hr = document.createElement('tr');
  ['Event', 'Date', 'Status', ''].forEach((h) => {
    const th = document.createElement('th');
    th.scope = 'col';
    th.textContent = __(h);
    hr.appendChild(th);
  });
  thead.appendChild(hr);
  table.appendChild(thead);
  const tbody = document.createElement('tbody');
  table.appendChild(tbody);
  container.appendChild(table);

  try {
    const items = await apiFetch('/ap/v1/rsvps', { cacheKey: 'my-rsvps', ttlMs: 600000 });
    if (!items || !items.length) {
      const p = document.createElement('p');
      p.textContent = __('No RSVPs yet');
      container.appendChild(p);
      return;
    }
    items.forEach((item) => {
      const tr = document.createElement('tr');
      const tdEvent = document.createElement('td');
      tdEvent.textContent = item.event_title || '';
      const tdDate = document.createElement('td');
      tdDate.textContent = item.event_date || '';
      const tdStatus = document.createElement('td');
      tdStatus.textContent = item.status || '';
      const tdAction = document.createElement('td');
      if (item.can_cancel) {
        const btn = document.createElement('button');
        btn.textContent = __('Cancel');
        btn.addEventListener('click', () => {
          Confirm.show({
            message: __('Cancel this RSVP?'),
            onConfirm: async () => {
              await apiFetch(`/ap/v1/rsvps/${item.id}`, { method: 'PUT', body: { status: 'cancelled' } });
              tr.remove();
              Toast.show({ type: 'info', message: __('RSVP cancelled') });
            },
          });
        });
        tdAction.appendChild(btn);
      }
      tr.appendChild(tdEvent);
      tr.appendChild(tdDate);
      tr.appendChild(tdStatus);
      tr.appendChild(tdAction);
      tbody.appendChild(tr);
    });
  } catch (e) {
    Toast.show({ type: 'error', message: e.message });
  }
}
