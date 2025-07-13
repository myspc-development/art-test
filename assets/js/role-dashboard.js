document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-user-dashboard');
  if (!container) return;

  Sortable.create(container, {
    animation: 150,
    handle: '.drag-handle',
    onEnd: () => {
      const layout = Array.from(container.children).map(card => ({
        id: card.dataset.id,
        visible: card.dataset.visible === "1"
      }));

      const payload = new URLSearchParams({
        action: 'ap_save_user_layout',
        nonce: APLayout.nonce,
        layout: JSON.stringify(layout)
      });
      fetch(APLayout.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
      });
    }
  });
});
