document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('ap-login-form');
  const loginMsg = document.getElementById('ap-login-message');

  async function submitForm(form, action, msgEl) {
    const formData = new FormData(form);
    formData.append('action', action);
    formData.append('nonce', APLogin.nonce);

    const res = await fetch(APLogin.ajaxUrl, {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if (msgEl) msgEl.textContent = data.data && data.data.message ? data.data.message : data.message || '';
    if (res.ok && data.success && action === 'ap_do_login') {
      window.location.reload();
    }
    return {res, data};
  }

  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(loginForm, 'ap_do_login', loginMsg);
    });
  }
});
