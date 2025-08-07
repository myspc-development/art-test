document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('ap-dashboard-root');
  if (!container) return;

  const savedOrder = JSON.parse(localStorage.getItem('ap-widget-order') || '[]');
  if (savedOrder.length) {
    savedOrder.forEach(id => {
      const el = container.querySelector(`[data-widget-id="${id}"]`);
      if (el) container.appendChild(el);
    });
  }

  Sortable.create(container, {
    animation: 150,
    handle: '.drag-handle',
    onEnd: () => {
      const order = Array.from(container.children).map(el => el.dataset.widgetId);
      localStorage.setItem('ap-widget-order', JSON.stringify(order));

      fetch(APWidgetOrder.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'save_widget_order',
          order: JSON.stringify(order),
          nonce: APWidgetOrder.nonce
        })
      }).then(res => {
        if (res.ok) showToast('Layout saved');
      });
    }
  });
});

function showToast(message) {
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.className = 'ap-toast';
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
