import domReady from '@wordpress/dom-ready';

domReady(() => {
  document.querySelectorAll('.ap-card').forEach(card => {
    const toggle = document.createElement('button');
    toggle.className = 'dashicons dashicons-arrow-down';
    toggle.ariaLabel = 'Collapse widget';
    toggle.onclick = () => {
      card.classList.toggle('is-collapsed');
      localStorage.setItem(card.id + ':collapsed', card.classList.contains('is-collapsed'));
    };
    card.prepend(toggle);

    if (localStorage.getItem(card.id + ':collapsed') === 'true') {
      card.classList.add('is-collapsed');
    }
  });
});
