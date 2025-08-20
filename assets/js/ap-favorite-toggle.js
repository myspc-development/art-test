document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ap-fav-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const objectId   = btn.dataset.objectId;
      const objectType = btn.dataset.objectType;
      const isActive   = btn.classList.contains('ap-favorited');

      fetch(APFavorites.apiRoot + 'artpulse/v1/favorites', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APFavorites.nonce
        },
        body: JSON.stringify({
          object_id: objectId,
          object_type: objectType
        })
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const added = data.status === 'added';
            btn.classList.toggle('ap-favorited', added);
            btn.textContent = added ? '❤' : '♡';
          }
        });
    });
  });
});

