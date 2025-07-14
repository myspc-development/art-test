function startDashboardTour() {
  if (typeof introJs !== 'function') return;
  const tour = introJs();
  tour.setOptions({
    steps: [
      { intro: 'Welcome to your dashboard!' },
      { element: document.querySelector('#ap-dashboard-stats'), intro: 'View your activity stats here.' },
      { element: document.querySelector('#ap-dashboard-widgets'), intro: 'Rearrange widgets to customize your dashboard.' }
    ]
  });
  const mark = () => {
    fetch(APDashboardTour.endpoint, {
      method: 'POST',
      headers: { 'X-WP-Nonce': APDashboardTour.nonce }
    });
  };
  tour.oncomplete(mark);
  tour.onexit(mark);
  tour.start();
}

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('ap-start-tour');
  if (btn) btn.addEventListener('click', startDashboardTour);
});
