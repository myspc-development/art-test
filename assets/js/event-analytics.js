document.addEventListener('DOMContentLoaded', async () => {
  if (typeof Chart === 'undefined' || !window.APEventAnalytics) return;

  const canvas = document.getElementById('ap-event-analytics-chart');
  if (!canvas) return;

  const headers = { 'X-WP-Nonce': APEventAnalytics.nonce };
  const res = await fetch(`${APEventAnalytics.endpoint}/trends?event_id=${APEventAnalytics.eventId}`, { headers });
  const data = await res.json();

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: data.days,
      datasets: [
        { label: 'Views', data: data.views, borderColor: '#0073aa', fill: false },
        { label: 'Favorites', data: data.favorites, borderColor: '#46b450', fill: false },
        { label: 'Tickets', data: data.tickets, borderColor: '#d54e21', fill: false }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
});
