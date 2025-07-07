document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('ap-rsvp-analytics');
  if (!canvas || typeof Chart === 'undefined' || typeof APRsvpAnalytics === 'undefined') {
    return;
  }

  fetch(`${APRsvpAnalytics.rest_root}artpulse/v1/event/${APRsvpAnalytics.event_id}/rsvp-stats`, {
    headers: { 'X-WP-Nonce': APRsvpAnalytics.nonce }
  })
    .then(res => res.ok ? res.json() : null)
    .then(data => {
      if (!data) return;
      new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.dates,
          datasets: [{
            label: 'RSVPs',
            data: data.counts,
            borderColor: '#0073aa',
            backgroundColor: 'rgba(0,115,170,0.3)',
            fill: false
          }]
        },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });

      if (data.views && document.getElementById('ap-rsvp-conversion')) {
        const pct = data.views ? ((data.total_rsvps / data.views) * 100).toFixed(1) : '0';
        document.getElementById('ap-rsvp-conversion').textContent = pct + '%';
      }

      const total = document.getElementById('ap-total-rsvps');
      if (total) total.textContent = data.total_rsvps || 0;
      const fav = document.getElementById('ap-favorite-count');
      if (fav) fav.textContent = data.favorites || 0;
      const wait = document.getElementById('ap-waitlist-count');
      if (wait) wait.textContent = data.waitlist || 0;
      const attended = document.getElementById('ap-total-attended');
      if (attended) attended.textContent = data.attended || 0;
    });
});
