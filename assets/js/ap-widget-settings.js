document.addEventListener('DOMContentLoaded', () => {
  const modal = document.createElement('div');
  modal.id = 'ap-widget-settings-modal';
  modal.className = 'ap-org-modal';
  modal.innerHTML = '<button id="ap-widget-settings-close" type="button" class="ap-form-button nectar-button">Close</button><div id="ap-widget-settings-content"></div>';
  modal.querySelector('#ap-widget-settings-close').addEventListener('click', () => modal.classList.remove('open'));
  document.body.appendChild(modal);

  async function openSettings(id) {
    const res = await fetch(`${ArtPulseDashboardApi.root}artpulse/v1/widget-settings/${id}`, {
      headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce }
    });
    if (!res.ok) return;
    const data = await res.json();
    const content = modal.querySelector('#ap-widget-settings-content');
    content.innerHTML = '';
    const form = document.createElement('form');
    (data.schema || []).forEach(field => {
      if (!field.key) return;
      const wrap = document.createElement('label');
      wrap.className = 'ap-form-label';
      wrap.textContent = field.label || field.key;
      let input;
      if (field.type === 'checkbox') {
        input = document.createElement('input');
        input.type = 'checkbox';
        input.name = field.key;
        input.value = '1';
        if (data.settings && data.settings[field.key]) input.checked = true;
      } else {
        input = document.createElement('input');
        input.type = field.type || 'text';
        input.name = field.key;
        input.value = (data.settings && data.settings[field.key]) || '';
      }
      wrap.prepend(input);
      form.appendChild(wrap);
    });
    const saveBtn = document.createElement('button');
    saveBtn.type = 'submit';
    saveBtn.className = 'ap-form-button nectar-button';
    saveBtn.textContent = apL10n?.save || 'Save';
    form.appendChild(saveBtn);
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(form);
      const settings = {};
      fd.forEach((value, key) => {
        if (settings[key]) return;
        settings[key] = value === '1' && form.querySelector(`[name="${key}"]`).type === 'checkbox' ? form.querySelector(`[name="${key}"]`).checked : value;
      });
      await fetch(`${ArtPulseDashboardApi.root}artpulse/v1/widget-settings/${id}`, {
        method: 'POST',
        headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
        body: JSON.stringify({ settings })
      });
      modal.classList.remove('open');
    }, { once: true });
    content.appendChild(form);
    modal.classList.add('open');
  }

  document.querySelectorAll('.ap-widget-settings-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      openSettings(btn.dataset.widgetSettings);
    });
  });
});
