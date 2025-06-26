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

  if (typeof Sortable !== 'undefined') {
    new Sortable(list, {
      onEnd: () => {
        const order = [...list.querySelectorAll('li')].map(li => li.querySelector('.ap-delete-artwork').dataset.id);
        fetch(APArtistDashboard.ajax_url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            action: 'save_artwork_order',
            nonce: APArtistDashboard.nonce,
            order: JSON.stringify(order)
          })
        });
      }
    });
  }

  if (typeof Chart !== 'undefined' && window.APArtistMetrics) {
    const canvas = document.createElement('canvas');
    canvas.id = 'ap-artwork-metrics';
    list.parentNode.insertBefore(canvas, list);

    const totals = Object.values(APArtistMetrics).reduce((acc, m) => {
      acc.views += Number(m.views) || 0;
      acc.likes += Number(m.likes) || 0;
      acc.shares += Number(m.shares) || 0;
      return acc;
    }, { views: 0, likes: 0, shares: 0 });

    new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Views', 'Likes', 'Shares'],
        datasets: [{
          label: 'Engagement',
          data: [totals.views, totals.likes, totals.shares],
          backgroundColor: ['#0073aa', '#46b450', '#d54e21']
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });
  }
});
