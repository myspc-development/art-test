document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('ap-feedback-button');
  const modal = document.getElementById('ap-feedback-modal');
  const form = document.getElementById('ap-feedback-form');
  const closeBtn = document.getElementById('ap-feedback-close');
  const msg = document.getElementById('ap-feedback-message');
  if (!btn || !modal || !form) return;

  btn.addEventListener('click', () => {
    modal.hidden = false;
    form.querySelector('#ap-feedback-description').focus();
  });
  if (closeBtn) {
    closeBtn.addEventListener('click', () => { modal.hidden = true; });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    msg.textContent = '';
    const data = new FormData(form);
    data.append('action', 'ap_submit_feedback');
    data.append('context', window.location.href);
    fetch(APFeedback.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: data
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          msg.textContent = APFeedback.thanks;
          form.reset();
        } else {
          msg.textContent = res.data && res.data.message ? res.data.message : 'Error';
        }
      });
  });
});
