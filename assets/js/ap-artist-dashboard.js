document.addEventListener('DOMContentLoaded', () => {
  const list = document.querySelector('.ap-artwork-list');
  if (!list || !window.APArtistDashboard) return;

  list.addEventListener('click', (e) => {
    if (e.target.matches('.ap-delete-artwork')) {
      e.preventDefault();
      const artworkId = e.target.dataset.id;
      if (!artworkId) return;
      if (!confirm('Delete this artwork?')) return;

      fetch(APArtistDashboard.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'ap_delete_artwork',
          nonce: APArtistDashboard.nonce,
          artwork_id: artworkId
        })
      })
        .then(res => res.ok ? res.json() : Promise.reject('Request failed'))
        .then(data => {
          if (data.success) {
            list.innerHTML = data.data.updated_list_html;
          } else {
            alert(data.data.message || 'Failed to delete.');
          }
        });
    }
  });
});
