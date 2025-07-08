document.addEventListener('DOMContentLoaded', () => {
  ['column-1', 'column-2'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    Sortable.create(el, {
      group: 'dashboard-columns',
      animation: 150,
      handle: '.handlediv',
      onEnd: saveLayout
    });
  });

  function saveLayout() {
    const layout = [];

    ['column-1', 'column-2'].forEach(columnId => {
      const column = document.getElementById(columnId);
      if (!column) return;
      const cards = column.querySelectorAll('.ap-widget-card');
      cards.forEach(card => {
        layout.push({
          id: card.dataset.id,
          visible: true
        });
      });
    });

    fetch(APLayout.ajax_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'ap_save_user_layout',
        nonce: APLayout.nonce,
        layout
      })
    });
  }
});

