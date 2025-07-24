document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ap-card[data-widget]').forEach(el => {
    const id = el.dataset.widget;
    if (window.dataLayer) {
      window.dataLayer.push({ event: 'widget_shown', widget: id });
    }
    fetch(APDashAnalytics.root + 'artpulse/v1/dashboard-analytics', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': APDashAnalytics.nonce },
      body: JSON.stringify({ event: 'widget_shown', details: id })
    });
  });
});
