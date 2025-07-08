document.addEventListener('DOMContentLoaded', function () {
  if (typeof window.APDashboardWidgetsEditor === 'undefined') {
    var canvas = document.getElementById('ap-dashboard-widgets-canvas');
    var notice = document.createElement('div');
    notice.className = 'notice notice-error';
    notice.innerHTML = '<p>Dashboard Widgets editor could not be loaded. Please refresh the page and try again.</p>';
    if (canvas && canvas.parentNode) {
      canvas.parentNode.insertBefore(notice, canvas);
    } else {
      document.body.insertBefore(notice, document.body.firstChild);
    }
  }
});
