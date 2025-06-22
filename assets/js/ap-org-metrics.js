document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('ap-org-metrics');
  if (!canvas || typeof Chart === 'undefined' || typeof APOrgMetrics === 'undefined') return;

  fetch(APOrgMetrics.endpoint, {
    credentials: 'same-origin',
    headers: { 'X-WP-Nonce': APOrgMetrics.nonce }
  })
    .then(res => res.json())
    .then(data => {
      const ctx = canvas.getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Events', 'Artworks'],
          datasets: [{
            label: 'Count',
            data: [data.event_count || 0, data.artwork_count || 0],
            backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)']
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 }
            }
          }
        }
      });
    });
});
