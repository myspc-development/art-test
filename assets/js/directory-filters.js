document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('ap-artist-directory');
  if (!container) return;

  fetch('/wp-json/artpulse/v1/artists')
    .then(r => r.json())
    .then(list => {
      container.innerHTML = '';
      list.forEach(item => {
        const card = document.createElement('div');
        card.className = 'ap-directory-card';
        card.innerHTML = `<a href="${item.link}">${item.name}</a>`;
        container.appendChild(card);
      });
    })
    .catch(() => {
      container.innerHTML = '<p>Failed to load directory.</p>';
    });
});
