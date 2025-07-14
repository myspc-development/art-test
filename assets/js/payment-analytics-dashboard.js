document.addEventListener('DOMContentLoaded', () => {
  if (typeof Chart === 'undefined' || typeof APPaymentDashboard === 'undefined') {
    return;
  }

  const revenueCanvas = document.getElementById('ap-payment-revenue-chart');
  if (revenueCanvas) {
    new Chart(revenueCanvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: APPaymentDashboard.months,
        datasets: [
          {
            label: 'Revenue',
            data: APPaymentDashboard.revenue,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }

  const subsCanvas = document.getElementById('ap-payment-subscriptions-chart');
  if (subsCanvas) {
    new Chart(subsCanvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: APPaymentDashboard.months,
        datasets: [
          {
            label: 'Subscriptions',
            data: APPaymentDashboard.subscriptions,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
});
