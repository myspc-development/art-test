import { apiFetch, __, emit } from './ap-core.js';
import { Toast } from './ap-ui.js';

/**
 * Artist portfolio builder module.
 * Allows uploading images, editing metadata and reordering items.
 */
export default async function render(container) {
  const upload = document.createElement('input');
  upload.type = 'file';
  upload.accept = 'image/jpeg,image/png,image/webp';
  upload.multiple = true;
  upload.setAttribute('aria-label', __('Upload images'));

  const list = document.createElement('ul');
  list.className = 'ap-portfolio-list';

  const saveBtn = document.createElement('button');
  saveBtn.textContent = __('Save');
  saveBtn.disabled = true;

  const profileLink = document.createElement('a');
  const slug = window.ARTPULSE_BOOT?.currentUser?.slug || window.ARTPULSE_BOOT?.currentUser?.user_nicename || '';
  profileLink.href = `/artists/${slug}`;
  profileLink.target = '_blank';
  profileLink.rel = 'noopener';
  profileLink.textContent = __('View public profile');

  const copyBtn = document.createElement('button');
  copyBtn.textContent = __('Copy link');
  copyBtn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(profileLink.href);
      Toast.show({ type: 'success', message: __('Link copied') });
    } catch (e) {
      Toast.show({ type: 'error', message: __('Unable to copy link') });
    }
  });

  container.append(upload, list, saveBtn, profileLink, copyBtn);

  let items = [];
  try {
    items = await apiFetch('/ap/v1/portfolio') || [];
  } catch (e) {
    items = [];
  }
  renderList();

  upload.addEventListener('change', async () => {
    for (const file of Array.from(upload.files)) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
        Toast.show({ type: 'error', message: __('Invalid file type') });
        continue;
      }
      if (file.size > 5 * 1024 * 1024) {
        Toast.show({ type: 'error', message: __('File too large') });
        continue;
      }
      try {
        const media = await uploadFile(file);
        items.push({
          id: media.id,
          url: media.source_url,
          alt: '',
          title: '',
          caption: '',
          year: '',
          medium: '',
          tags: '',
          featured: false,
        });
      } catch (e) {
        Toast.show({ type: 'error', message: __('Upload failed') });
      }
    }
    upload.value = '';
    renderList();
  });

  saveBtn.addEventListener('click', async () => {
    if (items.some((i) => !i.alt)) {
      Toast.show({ type: 'error', message: __('Alt text required for all images') });
      return;
    }
    try {
      await apiFetch('/ap/v1/portfolio', { method: 'POST', body: { items } });
      Toast.show({ type: 'success', message: __('Portfolio saved') });
      saveBtn.disabled = true;
      emit('portfolio:changed');
    } catch (e) {
      Toast.show({ type: 'error', message: e.message || __('Error saving portfolio') });
    }
  });

  function renderList() {
    list.textContent = '';
    items.forEach((item, index) => {
      const li = document.createElement('li');
      li.className = 'ap-portfolio-item';
      li.tabIndex = 0;
      li.setAttribute('aria-label', __('Portfolio item %d', index + 1));

      const img = document.createElement('img');
      img.src = item.url;
      img.alt = item.alt;
      li.appendChild(img);

      const fields = document.createElement('div');
      fields.className = 'ap-portfolio-fields';

      const title = createInput('text', __('Title'), item.title, (v) => (item.title = v));
      const caption = createInput('text', __('Caption'), item.caption, (v) => (item.caption = v));
      const alt = createInput('text', __('Alt text'), item.alt, (v) => (item.alt = v));
      alt.required = true;
      const year = createInput('number', __('Year'), item.year, (v) => (item.year = v));
      const medium = createInput('text', __('Medium'), item.medium, (v) => (item.medium = v));
      const tags = createInput('text', __('Tags'), item.tags, (v) => (item.tags = v));

      fields.append(title, caption, alt, year, medium, tags);
      li.appendChild(fields);

      const actions = document.createElement('div');
      actions.className = 'ap-portfolio-actions';
      const up = document.createElement('button');
      up.textContent = '↑';
      up.setAttribute('aria-label', __('Move up'));
      up.addEventListener('click', () => move(index, -1));
      const down = document.createElement('button');
      down.textContent = '↓';
      down.setAttribute('aria-label', __('Move down'));
      down.addEventListener('click', () => move(index, 1));
      const feature = document.createElement('button');
      feature.textContent = __('Set as Featured');
      feature.setAttribute('aria-pressed', String(!!item.featured));
      feature.addEventListener('click', () => {
        items.forEach((i) => (i.featured = false));
        item.featured = true;
        items.sort((a, b) => (b.featured ? 1 : 0) - (a.featured ? 1 : 0));
        renderList();
        saveBtn.disabled = false;
      });
      const remove = document.createElement('button');
      remove.textContent = __('Remove');
      remove.addEventListener('click', () => {
        items.splice(index, 1);
        renderList();
        saveBtn.disabled = false;
      });
      actions.append(up, down, feature, remove);
      li.appendChild(actions);

      li.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          move(index, -1);
        } else if (e.key === 'ArrowDown') {
          e.preventDefault();
          move(index, 1);
        }
      });

      list.appendChild(li);
    });
  }

  function createInput(type, placeholder, value, onChange) {
    const input = document.createElement('input');
    input.type = type;
    input.placeholder = placeholder;
    input.value = value || '';
    input.addEventListener('input', () => {
      onChange(input.value);
      saveBtn.disabled = false;
    });
    return input;
  }

  function move(index, delta) {
    const newIndex = index + delta;
    if (newIndex < 0 || newIndex >= items.length) return;
    const tmp = items[index];
    items[index] = items[newIndex];
    items[newIndex] = tmp;
    renderList();
    saveBtn.disabled = false;
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

