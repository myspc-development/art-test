import { apiFetch, __ } from './ap-core.js';
import { Toast } from './ap-ui.js';

export default async function render(container) {
  container.textContent = '';
  const list = document.createElement('ul');
  list.className = 'ap-favorites-list';
  container.appendChild(list);

  try {
    const items = await apiFetch('/ap/v1/favorites', { cacheKey: 'ap-favorites', ttlMs: 600000 });
    if (!items || !items.length) {
      const p = document.createElement('p');
      p.textContent = __('No favorites yet');
      container.appendChild(p);
      return;
    }
    items.forEach((item) => {
      const li = document.createElement('li');
      li.className = 'card';
      const title = document.createElement('span');
      title.textContent = item.title || '';
      const btn = document.createElement('button');
      btn.className = 'ap-fav-toggle';
      btn.setAttribute('aria-label', __('Save to favorites'));
      btn.setAttribute('aria-pressed', 'true');
      btn.textContent = '❤';
      btn.addEventListener('click', async () => {
        btn.disabled = true;
        await apiFetch('/ap/v1/favorites', { method: 'POST', body: { object_id: item.id, remove: true } }).catch(() => {
          Toast.show({ type: 'error', message: __('Error removing favorite') });
        });
        li.remove();
        document.dispatchEvent(new CustomEvent('favorites:changed'));
      });
      li.appendChild(btn);
      li.appendChild(title);
      list.appendChild(li);
    });
  } catch (e) {
    Toast.show({ type: 'error', message: e.message });
    const p = document.createElement('p');
    p.textContent = __('Unable to load favorites');
    container.appendChild(p);
  }
}
