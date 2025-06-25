document.addEventListener('DOMContentLoaded', () => {
  fetch(`${APArtistDashboard.rest_root}artpulse/v1/artist/dashboard`, {
    headers: { 'X-WP-Nonce': APArtistDashboard.rest_nonce }
  })
    .then(res => res.ok ? res.json() : null)
    .then(data => {
      if (!data) return;
      const info = document.getElementById('ap-membership-info');
      if (info) {
        const expire = data.membership_expires ? new Date(data.membership_expires * 1000).toLocaleDateString() : 'Never';
        info.innerHTML = `<p>Membership Level: ${data.membership_level || ''}</p><p>Expires: ${expire}</p>`;
      }
      const list = document.getElementById('ap-artist-artworks');
      if (list && data.artworks) {
        list.textContent = '';
        data.artworks.forEach(item => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = item.link;
          a.textContent = item.title;
          li.appendChild(a);
          list.appendChild(li);
        });
      }
    });
});
