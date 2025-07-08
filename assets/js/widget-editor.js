// Widget layout editor logic
// Initializes drag-and-drop ordering and handles layout persistence
// Requires SortableJS to be loaded on the page

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('custom-widgets');
  if (list && window.Sortable) {
    new Sortable(list, { animation: 150 });
  }

  // Toggle widget visibility
  const toggles = document.querySelectorAll('#widget-layout-form input[type="checkbox"]');
  toggles.forEach(cb => {
    cb.addEventListener('change', () => {
      const w = list?.querySelector(`[data-id="${cb.value}"]`);
      if (w) w.style.display = cb.checked ? '' : 'none';
    });
  });

  // Add widget from the selection panel
  const addConfirm = document.getElementById('add-widget-confirm');
  if (addConfirm) {
    addConfirm.addEventListener('click', () => {
      const select = document.getElementById('available-widgets');
      if (!select || !list) return;
      const id = select.value;
      if (!id) return;
      const tmpl = document.getElementById(`widget-template-${id}`);
      if (tmpl) {
        const node = tmpl.content ? tmpl.content.cloneNode(true) : tmpl.cloneNode(true);
        if (node.dataset) node.dataset.id = id;
        list.appendChild(node);
      }
      select.value = '';
    });
  }

  function collectLayout() {
    const layout = [];
    const visibility = {};
    list?.querySelectorAll('[data-id]').forEach(el => {
      const id = el.getAttribute('data-id');
      layout.push(id);
      visibility[id] = el.style.display !== 'none';
    });
    return { layout, visibility };
  }

  // Save layout via AJAX or form action
  const saveBtn = document.getElementById('ap-save-layout');
  if (saveBtn) {
    saveBtn.addEventListener('click', e => {
      e.preventDefault();
      const data = collectLayout();
      const form = document.getElementById('widget-layout-form');
      if (!form || !form.action) return;
      fetch(form.action, {
        method: form.method || 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
    });
  }

  // Export layout JSON
  const exportBtn = document.getElementById('export-layout');
  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      const data = collectLayout();
      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'widget-layout.json';
      a.click();
      URL.revokeObjectURL(url);
    });
  }

  // Import layout JSON
  const importInput = document.getElementById('import-layout');
  if (importInput) {
    importInput.addEventListener('change', () => {
      const file = importInput.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        try {
          const data = JSON.parse(reader.result);
          if (Array.isArray(data.layout)) {
            data.layout.forEach(id => {
              const el = list?.querySelector(`[data-id="${id}"]`);
              if (el) list.appendChild(el);
            });
          }
          if (data.visibility) {
            Object.entries(data.visibility).forEach(([id, vis]) => {
              const el = list?.querySelector(`[data-id="${id}"]`);
              if (el) el.style.display = vis ? '' : 'none';
              const cb = document.querySelector(`#widget-layout-form input[value="${id}"]`);
              if (cb) cb.checked = !!vis;
            });
          }
        } catch (err) {}
      };
      reader.readAsText(file);
      importInput.value = '';
    });
  }
});
