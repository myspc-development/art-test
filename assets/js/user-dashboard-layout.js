document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-dashboard-root');
  const isV2 = container?.dataset.apV2 === '1';
  if (!container || !isV2) return;

  Sortable.create(container, {
    animation: 150,
    handle: '.drag-handle',
    onEnd: () => {
      const layout = Array.from(container.children).map(el => ({
        id: el.dataset.id,
        visible: el.dataset.visible === '1'
      }));

      fetch(
        `${APLayout.ajax_url}?action=ap_save_user_layout&nonce=${encodeURIComponent(APLayout.nonce)}`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ layout })
        }
      ).then(r => r.json()).then(res => {
        if (!res.success) {
          console.error('Save failed.');
        }
      });
    }
  });
});
