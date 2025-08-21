import { apiFetch, __, debounce } from './ap-core.js';
import { Toast, Confirm } from './ap-ui.js';

/**
 * Organization event editor.
 * Provides simple CRUD for events owned by the organization.
 */
export default async function render(container) {
  const search = document.createElement('input');
  search.type = 'search';
  search.placeholder = __('Search events');
  search.setAttribute('aria-label', __('Search events'));
  search.addEventListener('input', debounce(() => loadEvents(search.value), 300));

  const table = document.createElement('table');
  table.className = 'ap-table';
  const thead = document.createElement('thead');
  const headRow = document.createElement('tr');
  const headers = [
    { key: 'title', label: __('Title'), sortable: true },
    { key: 'status', label: __('Status'), sortable: true },
    { key: 'actions', label: __('Actions'), sortable: false },
  ];
  headers.forEach((h) => {
    const th = document.createElement('th');
    th.textContent = h.label;
    th.scope = 'col';
    th.dataset.key = h.key;
    if (h.sortable) {
      th.tabIndex = 0;
      th.setAttribute('aria-sort', 'none');
      th.addEventListener('click', () => setSort(h.key));
      th.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          th.click();
        }
      });
    }
    headRow.appendChild(th);
  });
  thead.appendChild(headRow);
  const tbody = document.createElement('tbody');
  table.append(thead, tbody);
  const pager = document.createElement('div');
  const prev = button(__('Prev'));
  const next = button(__('Next'));
  pager.append(prev, next);

  const form = document.createElement('form');
  form.className = 'ap-event-form';

  const fields = {
    title: input('text', __('Title')),
    description: textarea(__('Description')),
    start: input('datetime-local', __('Start')),
    end: input('datetime-local', __('End')),
    venue: input('text', __('Venue')),
    address: input('text', __('Address')),
    lat: input('number', __('Lat')),
    lng: input('number', __('Lng')),
    capacity: input('number', __('Capacity')),
    price: input('number', __('Price')),
  };

  const cover = document.createElement('input');
  cover.type = 'file';
  cover.accept = 'image/*';
  cover.setAttribute('aria-label', __('Cover image'));

  Object.values(fields).forEach((el) => form.appendChild(el.wrapper));
  form.appendChild(cover);

  const actions = document.createElement('div');
  const save = button(__('Save'));
  const dup = button(__('Duplicate'));
  const cancel = button(__('Cancel'));
  const del = button(__('Delete'));
  actions.append(save, dup, cancel, del);
  form.appendChild(actions);

  container.append(search, table, pager, form);

  let events = [];
  let current = null;
  let page = 1;

  async function loadEvents(q = '') {
    try {
      events = await apiFetch(
        `/wp/v2/artpulse_event?author=me&status=any&search=${encodeURIComponent(q)}&per_page=10&page=${page}&_embed`,
        { cacheKey: `events-${q}-${page}`, ttlMs: 5000 }
      );
    } catch (e) {
      events = [];
      Toast.show({ type: 'error', message: e.message || __('Unable to load events') });
    }
    renderTable();
  }

  prev.addEventListener('click', () => {
    if (page > 1) {
      page--;
      loadEvents(search.value);
    }
  });
  next.addEventListener('click', () => {
    page++;
    loadEvents(search.value);
  });

  let sortKey = 'title';
  let sortDir = 'asc';

  function setSort(key) {
    if (sortKey === key) {
      sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      sortKey = key;
      sortDir = 'asc';
    }
    renderTable();
  }

  function renderTable() {
    tbody.textContent = '';
    const rows = [...events];
    if (sortKey === 'title') {
      rows.sort((a, b) => (a.title?.rendered || '').localeCompare(b.title?.rendered || ''));
    } else if (sortKey === 'status') {
      rows.sort((a, b) => (a.status || '').localeCompare(b.status || ''));
    }
    if (sortDir === 'desc') {
      rows.reverse();
    }

    headers.forEach((h) => {
      if (h.sortable) {
        const th = headRow.querySelector(`th[data-key="${h.key}"]`);
        th.setAttribute('aria-sort', sortKey === h.key ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none');
      }
    });

    rows.forEach((ev) => {
      const tr = document.createElement('tr');
      const tdTitle = document.createElement('td');
      tdTitle.textContent = ev.title?.rendered || __('(no title)');
      const tdStatus = document.createElement('td');
      tdStatus.textContent = ev.status;
      const tdAction = document.createElement('td');
      const editBtn = button(__('Edit'));
      editBtn.addEventListener('click', () => edit(ev.id));
      tdAction.appendChild(editBtn);
      tr.append(tdTitle, tdStatus, tdAction);
      tbody.appendChild(tr);
    });
    focusFirstRow();
  }

  function focusFirstRow() {
    const first = tbody.querySelector('button');
    if (first) first.focus();
  }

  async function edit(id) {
    try {
      current = await apiFetch(`/wp/v2/artpulse_event/${id}?context=edit`);
      setForm(current);
    } catch (e) {
      Toast.show({ type: 'error', message: __('Unable to load event') });
    }
  }

  function setForm(ev) {
    fields.title.input.value = ev.title?.raw || ev.title?.rendered || '';
    fields.description.input.value = ev.content?.raw || ev.content?.rendered || '';
    fields.start.input.value = ev.meta?.ap_event_start || '';
    fields.end.input.value = ev.meta?.ap_event_end || '';
    fields.venue.input.value = ev.meta?.ap_event_venue || '';
    fields.address.input.value = ev.meta?.ap_event_address || '';
    fields.lat.input.value = ev.meta?.ap_event_lat || '';
    fields.lng.input.value = ev.meta?.ap_event_lng || '';
    fields.capacity.input.value = ev.meta?.ap_event_capacity || '';
    fields.price.input.value = ev.meta?.ap_event_price || '';
  }

  function getForm() {
    const data = {};
    Object.entries(fields).forEach(([k, { input }]) => {
      data[k] = input.value;
    });
    return data;
  }

  function buildPayload(data) {
    const meta = {
      ap_event_start: data.start || null,
      ap_event_end: data.end || null,
      ap_event_venue: data.venue || '',
      ap_event_address: data.address || '',
      ap_event_lat: data.lat ? Number(data.lat) : null,
      ap_event_lng: data.lng ? Number(data.lng) : null,
      ap_event_capacity: data.capacity ? parseInt(data.capacity, 10) : null,
      ap_event_price: data.price || '',
    };
    const payload = {
      title: data.title || '',
      content: data.description || '',
      status: current?.status || 'draft',
      meta,
    };
    if (data.featured_media) {
      payload.featured_media = data.featured_media;
    }
    return payload;
  }

  save.addEventListener('click', async (e) => {
    e.preventDefault();
    const data = getForm();
    if (data.start && data.end && new Date(data.start) > new Date(data.end)) {
      Toast.show({ type: 'error', message: __('Start must be before end') });
      return;
    }
    if (data.capacity && Number(data.capacity) < 0) {
      Toast.show({ type: 'error', message: __('Capacity must be positive') });
      return;
    }
    try {
      if (cover.files[0]) {
        const media = await uploadFile(cover.files[0]);
        data.featured_media = media.id;
      }
      const payload = buildPayload(data);
      const path = current?.id ? `/wp/v2/artpulse_event/${current.id}` : '/wp/v2/artpulse_event';
      const res = await apiFetch(path, { method: current?.id ? 'PUT' : 'POST', body: payload });
      if (res?.code) {
        throw { status: res.data?.status, message: res.message };
      }
      current = res;
      Toast.show({ type: 'success', message: __('Saved') });
      cover.value = '';
      await loadEvents(search.value);
    } catch (err) {
      if (err.status === 400) {
        Toast.show({ type: 'error', message: __('Invalid data') });
      } else if (err.status === 403 || err.message === 'forbidden') {
        Toast.show({ type: 'error', message: __('You don\'t have permission') });
      } else {
        Toast.show({ type: 'error', message: err.message || __('Error saving') });
      }
    }
  });

  dup.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!current) return;
    const data = getForm();
    if (data.start && data.end && new Date(data.start) > new Date(data.end)) {
      Toast.show({ type: 'error', message: __('Start must be before end') });
      return;
    }
    if (data.capacity && Number(data.capacity) < 0) {
      Toast.show({ type: 'error', message: __('Capacity must be positive') });
      return;
    }
    try {
      const payload = buildPayload(data);
      payload.status = 'draft';
      const res = await apiFetch('/wp/v2/artpulse_event', { method: 'POST', body: payload });
      if (res?.code) {
        throw { status: res.data?.status, message: res.message };
      }
      current = res;
      setForm(res);
      Toast.show({ type: 'success', message: __('Duplicated') });
      await loadEvents(search.value);
    } catch (err) {
      if (err.status === 403 || err.message === 'forbidden') {
        Toast.show({ type: 'error', message: __('You don\'t have permission') });
      } else if (err.status === 400) {
        Toast.show({ type: 'error', message: __('Invalid data') });
      } else {
        Toast.show({ type: 'error', message: err.message || __('Error duplicating') });
      }
    }
  });

  cancel.addEventListener('click', (e) => {
    e.preventDefault();
    Object.values(fields).forEach(({ input }) => (input.value = ''));
    cover.value = '';
    current = null;
  });

  del.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!current?.id) return;
    if (!(await Confirm.show(__('Delete this event?')))) return;
    try {
      await apiFetch(`/wp/v2/artpulse_event/${current.id}?force=true`, { method: 'DELETE' });
      Toast.show({ type: 'success', message: __('Deleted') });
      cancel.click();
      await loadEvents(search.value);
    } catch (err) {
      Toast.show({ type: 'error', message: err.message || __('Error deleting') });
    }
  });

  loadEvents();

  function input(type, label) {
    const wrapper = document.createElement('label');
    wrapper.textContent = label;
    const input = document.createElement('input');
    input.type = type;
    wrapper.appendChild(input);
    return { wrapper, input };
  }

  function textarea(label) {
    const wrapper = document.createElement('label');
    wrapper.textContent = label;
    const input = document.createElement('textarea');
    wrapper.appendChild(input);
    return { wrapper, input };
  }

  function button(text) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = text;
    return btn;
  }
}

async function uploadFile(file) {
  const data = new FormData();
  data.append('file', file, file.name);
  const res = await fetch((window.ARTPULSE_BOOT?.restRoot || '') + '/wp/v2/media', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-WP-Nonce': window.ARTPULSE_BOOT?.restNonce || '' },
    body: data,
  });
  if (!res.ok) throw new Error('upload failed');
  return res.json();
}

