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

  document.querySelectorAll('.widget-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.ap-widget-card');
      const isVisible = card.dataset.visible === '1';
      card.dataset.visible = isVisible ? '0' : '1';
      btn.innerText = isVisible ? '🙈' : '👁️';
      saveLayout();
    });
  });

  document.querySelectorAll('.collapse-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.ap-widget-card');
      card.classList.toggle('collapsed');
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
          visible: card.dataset.visible === '1'
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

