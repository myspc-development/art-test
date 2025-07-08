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
    });
  }
});
