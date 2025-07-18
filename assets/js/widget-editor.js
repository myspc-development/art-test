// Dashboard widget layout editor
// Requires SortableJS

let roleSelect;
let nonceField;

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-widget-list');
  if (container && typeof Sortable !== 'undefined') {
    Sortable.create(container, {
      handle: '.drag-handle',
      animation: 150,
      onEnd: () => {
        saveLayoutOrderToStorage();
        saveLayoutAjax();
      }
    });
  }

  // Toggle widget visibility
  document.querySelectorAll('.widget-toggle').forEach(input => {
    input.addEventListener('change', () => {
      const widget = input.closest('.ap-widget-card');
      const isVisible = input.checked;
      widget.dataset.visible = isVisible ? '1' : '0';
      if (isVisible) {
        widget.classList.remove('is-hidden');
      } else {
        widget.classList.add('is-hidden');
      }
      saveLayoutOrderToStorage();
      saveLayoutAjax();
    });
  });

  document.querySelectorAll('.widget-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.ap-widget-card');
      if (card) {
        card.remove();
        saveLayoutOrderToStorage();
        saveLayoutAjax();
      }
    });
  });

  // Copy layout export
  window.copyExportedLayout = () => {
    const textArea = document.getElementById('export_json');
    textArea.select();
    textArea.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Layout JSON copied to clipboard!');
  };

  // Submit layout data
  const form = document.getElementById('widget-layout-form');
  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner"></span> Saving...';
      const payload = new URLSearchParams({
        action: 'ap_save_role_layout',
        nonce: nonceField?.value || '',
        role: roleSelect?.value || '',
        layout: JSON.stringify(getCurrentLayout())
      });
      fetch(ajaxurl, { method: 'POST', body: payload })
        .then(r => r.json())
        .then(() => {
          saveBtn.innerHTML = '💾 Save Layout';
          saveBtn.disabled = false;
        });
    });
  }

  const categoryFilter = document.getElementById('ap-widget-category-filter');

  const saveBtn = document.getElementById('save-layout-btn');
  roleSelect = document.getElementById('ap-role-selector');
  nonceField = document.querySelector('#widget-layout-form input[name="_wpnonce"]');

  window.apFilterWidgetsByCategory = function(cat) {
    if (categoryFilter) {
      categoryFilter.value = cat;
    }
    const query = document.getElementById('ap-widget-search')?.value || '';
    apSearchWidgets(query);
  };

  document.getElementById('ap-widget-search')?.addEventListener('input', e => {
    apSearchWidgets(e.target.value);
  });
  roleSelect?.addEventListener('change', e => {
    const params = new URLSearchParams(window.location.search);
    params.set('page', 'artpulse-widget-editor');
    params.set('ap_dashboard_role', e.target.value);
    window.location.search = params.toString();
  });
  document.getElementById('toggle-preview')?.addEventListener('click', () => {
    const preview = document.getElementById('ap-widget-preview-area');
    if (preview) {
      preview.style.display = (preview.style.display === 'none') ? 'block' : 'none';
    }
  });
});

function apSearchWidgets(query) {
  query = (query || '').toLowerCase();
  const cat = document.getElementById('ap-widget-category-filter')?.value || '';
  document.querySelectorAll('.widget-card').forEach(card => {
    const name = (card.dataset.name || '').toLowerCase();
    const desc = (card.dataset.desc || '').toLowerCase();
    const matchText = name.includes(query) || desc.includes(query);
    const matchCat = !cat || card.dataset.category === cat;
    card.style.display = matchText && matchCat ? 'block' : 'none';
  });
}

function saveLayoutOrderToStorage() {
  const list = document.querySelector('#ap-widget-list');
  if (!list) return;
  const ids = Array.from(list.querySelectorAll('.ap-widget-card')).map(el => ({
    id: el.dataset.id,
    visible: el.dataset.visible === '1'
  }));
  localStorage.setItem('apDashboardWidgetLayout', JSON.stringify(ids));
}

function getCurrentLayout() {
  const list = document.querySelectorAll('#ap-widget-list .ap-widget-card');
  return Array.from(list).map(el => ({
    id: el.dataset.id,
    visible: el.dataset.visible === '1'
  }));
}

function saveLayoutAjax() {
  if (!nonceField) return;
  const payload = new URLSearchParams({
    action: 'ap_save_role_layout',
    nonce: nonceField.value,
    role: roleSelect?.value || '',
    layout: JSON.stringify(getCurrentLayout())
  });
  fetch(ajaxurl, { method: 'POST', body: payload });
}
