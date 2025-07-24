document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('ap-dashboard-feedback-form');
  if (!form) return;
  const msg = document.getElementById('ap-dashboard-feedback-msg');
  form.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(form);
    data.append('action', 'ap_dashboard_feedback');
    fetch(APDashFeedback.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: data
    }).then(r => r.json()).then(res => {
      if (res.success) {
        msg.textContent = APDashFeedback.thanks;
        form.reset();
      } else {
        msg.textContent = res.data && res.data.message ? res.data.message : 'Error';
      }
    });
  });
});
