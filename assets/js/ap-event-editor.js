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
  search.addEventListener('input', debounce(() => loadEvents(search.value), 300));

  const table = document.createElement('table');
  table.className = 'ap-table';
  const tbody = document.createElement('tbody');
  table.appendChild(tbody);
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

  function renderTable() {
    tbody.textContent = '';
    events.forEach((ev) => {
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
  }

  async function edit(id) {
    try {
      current = await apiFetch(`/wp/v2/artpulse_event/${id}`);
      setForm(current);
    } catch (e) {
      Toast.show({ type: 'error', message: __('Unable to load event') });
    }
  }

  function setForm(ev) {
    Object.entries(fields).forEach(([k, { input }]) => {
      input.value = ev[k] || '';
    });
  }

  function getForm() {
    const data = {};
    Object.entries(fields).forEach(([k, { input }]) => {
      data[k] = input.value;
    });
    return data;
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
      const method = current?.id ? 'PUT' : 'POST';
      const path = current?.id ? `/wp/v2/artpulse_event/${current.id}` : '/wp/v2/artpulse_event';
      current = await apiFetch(path, { method, body: data });
      Toast.show({ type: 'success', message: __('Saved') });
      cover.value = '';
      loadEvents();
    } catch (err) {
      if (err.message === 'forbidden') {
        Toast.show({ type: 'error', message: __('You don\'t have permission') });
      } else {
        Toast.show({ type: 'error', message: err.message || __('Error saving') });
      }
    }
  });

  dup.addEventListener('click', (e) => {
    e.preventDefault();
    const data = getForm();
    delete data.id;
    current = null;
    Object.entries(fields).forEach(([k, { input }]) => {
      input.value = data[k] || '';
    });
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
      loadEvents();
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

