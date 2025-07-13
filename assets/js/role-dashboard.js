document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('#ap-user-dashboard');
  if (!container || !window.ArtPulseDashboard) return;

  Sortable.create(container, {
    animation: 150,
    handle: '.drag-handle',
    onEnd: () => {
      const newOrder = Array.from(container.children).map(card => ({
        id: card.dataset.id,
        visible: card.dataset.visible === "1"
      }));

      fetch(ArtPulseDashboard.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'ap_save_dashboard_order',
          nonce: ArtPulseDashboard.nonce,
          order: JSON.stringify(newOrder)
        })
      })
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            throw new Error(response.data.message || 'Unknown error');
          }
          console.log('✅ Dashboard order saved.');
        })
        .catch(err => {
          console.error('❌ AJAX failed:', err.message);
        });
    }
  });
});
