// Dashboard widget layout editor
// Requires SortableJS

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('custom-widgets');
  if (container && typeof Sortable !== 'undefined') {
    Sortable.create(container, { handle: '.widget-handle', animation: 150 });
  }

  // Toggle widget visibility
  document.querySelectorAll('.widget-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.ap-widget');
      const visible = card.dataset.visible === '1';
      card.dataset.visible = visible ? '0' : '1';
      btn.textContent = visible ? '👁 Show' : '🙈 Hide';
      const content = card.querySelector('.widget-content');
      if (content) content.style.display = visible ? 'none' : 'block';
      if (!visible) {
        card.classList.add('is-hidden');
      } else {
        card.classList.remove('is-hidden');
      }
    });
  });

  document.querySelectorAll('.widget-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.ap-widget');
      if (card) card.remove();
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
    form.addEventListener('submit', () => {
      const layout = Array.from(container.children).map(el => ({
        id: el.dataset.id,
        visible: el.dataset.visible === '1'
      }));
      document.getElementById('layout_input').value = JSON.stringify(layout);
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner"></span> Saving...';
      }
    });
  }

  const categoryFilter = document.getElementById('ap-widget-category-filter');

  const saveBtn = document.getElementById('save-layout-btn');

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
