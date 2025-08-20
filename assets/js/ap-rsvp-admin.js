import { apiFetch, __, emit } from './ap-core.js';
import { Toast, Confirm } from './ap-ui.js';

/**
 * RSVP administration panel for organizations.
 */
export default async function render(container) {
  const eventSelect = document.createElement('select');
  const statusFilter = document.createElement('select');
  ['all', 'going', 'waitlist', 'cancelled'].forEach((s) => {
    const o = document.createElement('option');
    o.value = s === 'all' ? '' : s;
    o.textContent = s === 'all' ? __('All statuses') : __(s.charAt(0).toUpperCase() + s.slice(1));
    statusFilter.appendChild(o);
  });

  const start = document.createElement('input');
  start.type = 'date';
  const end = document.createElement('input');
  end.type = 'date';

  const filterBtn = button(__('Filter'));
  filterBtn.addEventListener('click', () => loadRsvps());

  const table = document.createElement('table');
  table.className = 'ap-table';
  const tbody = document.createElement('tbody');
  table.appendChild(tbody);
  const pager = document.createElement('div');
  const prev = button(__('Prev'));
  const next = button(__('Next'));
  pager.append(prev, next);

  const bulkSelect = document.createElement('select');
  ['approve', 'waitlist', 'cancel'].forEach((s) => {
    const o = document.createElement('option');
    o.value = s;
    o.textContent = __(s.charAt(0).toUpperCase() + s.slice(1));
    bulkSelect.appendChild(o);
  });
  const bulkBtn = button(__('Apply'));
  bulkBtn.addEventListener('click', () => bulkUpdate());

  const exportBtn = button(__('Export CSV'));
  exportBtn.addEventListener('click', () => {
    if (!eventSelect.value) return;
    const url = `${window.ARTPULSE_BOOT.restRoot}/ap/v1/rsvps/export.csv?event_id=${eventSelect.value}`;
    window.open(url, '_blank');
    Toast.show({ type: 'info', message: __('CSV opens in a new window. Avoid leading = or + to prevent CSV injection.') });
  });

  container.append(eventSelect, statusFilter, start, end, filterBtn, table, pager, bulkSelect, bulkBtn, exportBtn);

  let rsvps = [];
  let page = 1;
  let totalPages = 1;

  async function loadEvents() {
    try {
      const events = await apiFetch('/wp/v2/artpulse_event?author=me&status=any&_fields=id,title.rendered&per_page=100');
      events.forEach((e) => {
        const o = document.createElement('option');
        o.value = e.id;
        o.textContent = e.title?.rendered || __('(no title)');
        eventSelect.appendChild(o);
      });
    } catch (e) {
      Toast.show({ type: 'error', message: __('Unable to load events') });
    }
  }

  async function loadRsvps() {
    tbody.textContent = '';
    if (!eventSelect.value) return;
    try {
      const query = new URLSearchParams({
        event_id: eventSelect.value,
        status: statusFilter.value,
        from: start.value,
        to: end.value,
        page: String(page),
        per_page: '25',
      });
      const data = await apiFetch(`/ap/v1/rsvps?${query.toString()}`, { cacheKey: `rsvps-${query.toString()}`, ttlMs: 1000 });
      rsvps = data.rows || [];
      totalPages = Math.ceil((data.total || 0) / (data.per_page || 25)) || 1;
    } catch (e) {
      rsvps = [];
      Toast.show({ type: 'error', message: e.message || __('Unable to load RSVPs') });
    }
    renderTable();
  }

  function renderTable() {
    tbody.textContent = '';
    rsvps.forEach((r) => {
      const tr = document.createElement('tr');
      const cbTd = document.createElement('td');
      const cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.dataset.id = r.id;
      cbTd.appendChild(cb);
      const nameTd = document.createElement('td');
      nameTd.textContent = r.name;
      const statusTd = document.createElement('td');
      const sel = document.createElement('select');
      ['going', 'waitlist', 'cancelled'].forEach((s) => {
        const o = document.createElement('option');
        o.value = s;
        o.textContent = __(s);
        if (r.status === s) o.selected = true;
        sel.appendChild(o);
      });
      sel.addEventListener('change', () => updateStatus(r.id, sel.value));
      statusTd.appendChild(sel);
      tr.append(cbTd, nameTd, statusTd);
      tbody.appendChild(tr);
    });
  }

  async function updateStatus(id, status) {
    try {
      await apiFetch(`/ap/v1/rsvps/${id}`, { method: 'PUT', body: { status } });
      Toast.show({ type: 'success', message: __('Updated') });
      emit('rsvps:changed');
    } catch (e) {
      Toast.show({ type: 'error', message: e.message || __('Update failed') });
    }
  }

  async function bulkUpdate() {
    const ids = Array.from(tbody.querySelectorAll('input[type="checkbox"]:checked')).map((i) => i.dataset.id);
    if (!ids.length) return;
    if (!(await Confirm.show(__('Apply to selected?')))) return;
    try {
      await apiFetch('/ap/v1/rsvps/bulk-update', {
        method: 'POST',
        body: { event_id: eventSelect.value, ids, status: bulkSelect.value },
      });
      Toast.show({ type: 'success', message: __('Updated') });
      emit('rsvps:changed');
      loadRsvps();
    } catch (e) {
      Toast.show({ type: 'error', message: e.message || __('Update failed') });
    }
  }

  eventSelect.addEventListener('change', () => { page = 1; loadRsvps(); });
  statusFilter.addEventListener('change', () => { page = 1; loadRsvps(); });
  prev.addEventListener('click', () => { if (page > 1) { page--; loadRsvps(); } });
  next.addEventListener('click', () => { if (page < totalPages) { page++; loadRsvps(); } });
  loadEvents();
}

function button(text) {
  const b = document.createElement('button');
  b.type = 'button';
  b.textContent = text;
  return b;
}

